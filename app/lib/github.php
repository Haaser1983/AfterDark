<?php
declare(strict_types=1);

/**
 * Minimal GitHub client: commits a set of file changes as ONE commit using the Git Data API.
 * Requires config github.token (fine-grained, Contents read/write on this repo only).
 */

function github_enabled(): bool
{
    return (string) config('github.token', '') !== '' && function_exists('curl_init');
}

function github_request(string $method, string $path, ?array $body = null): array
{
    $owner = rawurlencode((string) config('github.owner'));
    $repo = rawurlencode((string) config('github.repo'));
    $url = rtrim((string) config('github.api', 'https://api.github.com'), '/') . '/repos/' . $owner . '/' . $repo . $path;
    $ch = curl_init($url);
    $headers = [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . config('github.token'),
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: eliza-penn-studio',
    ];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('Could not reach GitHub: ' . $err);
    }
    $data = json_decode((string) $raw, true) ?? [];
    if ($status >= 400) {
        $msg = $data['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('GitHub said: ' . $msg . ' (' . $status . ' on ' . $method . ' ' . $path . ')', $status);
    }
    return $data;
}

/**
 * @param array<string, string|null> $files repo path => file contents (null = delete)
 * @return string the new commit sha
 */
function github_commit(array $files, string $message): string
{
    $branch = rawurlencode((string) config('github.branch', 'main'));
    $attempt = 0;
    while (true) {
        $attempt++;
        try {
            $ref = github_request('GET', '/git/ref/heads/' . $branch);
            $parentSha = $ref['object']['sha'];
            $parent = github_request('GET', '/git/commits/' . $parentSha);

            $tree = [];
            foreach ($files as $path => $contents) {
                if ($contents === null) {
                    $tree[] = ['path' => $path, 'mode' => '100644', 'type' => 'blob', 'sha' => null];
                    continue;
                }
                $blob = github_request('POST', '/git/blobs', ['content' => base64_encode($contents), 'encoding' => 'base64']);
                $tree[] = ['path' => $path, 'mode' => '100644', 'type' => 'blob', 'sha' => $blob['sha']];
            }
            $newTree = github_request('POST', '/git/trees', ['base_tree' => $parent['tree']['sha'], 'tree' => $tree]);
            if ($newTree['sha'] === $parent['tree']['sha']) {
                return $parentSha; // nothing actually changed
            }
            $who = [
                'name' => (string) config('github.committer_name'),
                'email' => (string) config('github.committer_email'),
                'date' => date('c'),
            ];
            $commit = github_request('POST', '/git/commits', [
                'message' => $message,
                'tree' => $newTree['sha'],
                'parents' => [$parentSha],
                'author' => $who,
                'committer' => $who,
            ]);
            github_request('PATCH', '/git/refs/heads/' . $branch, ['sha' => $commit['sha'], 'force' => false]);
            return $commit['sha'];
        } catch (RuntimeException $e) {
            // 422 on the ref update = someone pushed in between; rebuild on the new head once.
            if ($e->getCode() === 422 && $attempt < 3) {
                continue;
            }
            throw $e;
        }
    }
}
