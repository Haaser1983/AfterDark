<?php
declare(strict_types=1);

/**
 * Keeps Studio edits and git in step. Every save writes the file on the server, adds it to a
 * pending list, then tries to commit everything pending to GitHub. If GitHub is unreachable or
 * not configured, the change stays live on the site and the Studio shows "not synced" with a
 * retry button, so nothing is lost and nothing gets silently overwritten by the next deploy.
 */

function pending_file(): string
{
    return STORAGE . '/pending-sync.json';
}

function pending_paths(): array
{
    return array_keys(read_json(pending_file()) ?? []);
}

function mark_pending(array $paths): void
{
    $data = read_json(pending_file()) ?? [];
    foreach ($paths as $p) {
        $data[$p] = date('c');
    }
    file_put_contents(pending_file(), json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function clear_pending(array $paths): void
{
    $data = read_json(pending_file()) ?? [];
    foreach ($paths as $p) {
        unset($data[$p]);
    }
    file_put_contents(pending_file(), json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function sync_status(): array
{
    return read_json(STORAGE . '/last-sync.json') ?? [];
}

/**
 * Record changed repo paths and push every pending path to GitHub as one commit.
 * Returns [bool ok, string message].
 */
function sync_changes(array $changedPaths, string $message): array
{
    if ($changedPaths) {
        mark_pending($changedPaths);
    }
    $paths = pending_paths();
    if (!$paths) {
        return [true, 'Nothing to sync.'];
    }
    if (!github_enabled()) {
        return [false, 'Saved on the site. GitHub sync is not set up yet, so this change is not in git.'];
    }
    $files = [];
    foreach ($paths as $p) {
        $abs = ROOT . '/' . $p;
        $files[$p] = is_file($abs) ? (string) file_get_contents($abs) : null;
    }
    try {
        $sha = github_commit($files, $message . "\n\nVia the Studio on " . ($_SERVER['HTTP_HOST'] ?? 'the site') . '.');
        clear_pending($paths);
        @file_put_contents(STORAGE . '/last-sync.json', json_encode(['at' => date('c'), 'sha' => $sha, 'ok' => true]), LOCK_EX);
        return [true, 'Saved and synced to GitHub.'];
    } catch (Throwable $e) {
        @file_put_contents(STORAGE . '/last-sync.json', json_encode(['at' => date('c'), 'ok' => false, 'error' => $e->getMessage()]), LOCK_EX);
        error_log('[studio sync] ' . $e->getMessage());
        return [false, 'Saved on the site, but syncing to GitHub failed: ' . $e->getMessage()];
    }
}
