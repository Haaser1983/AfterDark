# Eliza A. Penn — author site

A small PHP 8.1+ site with no database. Content is JSON in `content/`. Liz edits through the **Studio** at `/studio`. Developers and Claude edit through git. Every push to `main` deploys over FTP.

```
           ┌──────── Studio save (writes file + commits via GitHub API) ────────┐
Liz ──► /studio                                                                ▼
                                                                         GitHub main ──► Action ──► FTP ──► live site
Claude / John ──► edit content/*.json ──► git push ────────────────────────────┘
```

## One-time setup

### 1. GitHub Actions (FTP deploy)
In the repo, go to **Settings → Secrets and variables → Actions**.

- **Secrets:** `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`
- **Variables:**
  - `FTP_SERVER_DIR` (required, as a variable or a secret): the website folder relative to where the FTP login opens, ending in `/`. **For this site it is `./`**: the HostGator FTP account opens directly in `/home2/hiefcnte/public_html/afterdarkromance.com`. The deploy refuses to run without it.
  - `FTP_PROTOCOL`: `ftps` by default. Set it to `ftp` only if the host doesn't support FTPS.
  - Every push to `main` deploys. You can also run it by hand from **Actions → Deploy to the server → Run workflow**.

The first deploy uploads everything. After that, only changed files are sent.

> **Old site:** if the old PHP site is still in the target folder, delete it first (especially `admin/`, `shop/`, `api/`, `config/.env`). The deploy doesn't remove files it didn't upload.

### 2. Studio login and GitHub sync (on the server)
1. Generate a password hash locally: `php tools/hash-password.php "Liz's password"`
2. Create a fine-grained GitHub token: **github.com → Settings → Developer settings → Fine-grained tokens**. Give it **Repository access: only Haaser1983/AfterDark** and **Permissions: Contents → Read and write**.
3. Copy `app/config.sample.php` to `app/config.local.php`, fill in `site_url`, `password_hash` and `github.token`, and upload it over FTP to `app/config.local.php`. The deploy never touches this file.
4. Make sure the web server can write to `content/`, `assets/uploads/` and the site root (so it can create `storage/`).

Liz logs in at `/studio` with username `liz`. She can change her password there.

### 3. Host requirements
Apache with `mod_rewrite` and PHP 8.1+ with the `curl` extension. If the site lives in a subfolder, set `RewriteBase` in `.htaccess`.

## Working on it locally
```
php -S localhost:8000 index.php
```
For the Studio locally, create `app/config.local.php` with a password hash. Leave the GitHub token empty unless you want local saves to commit.

## How syncing stays safe
- Studio saves are written on the server first, then committed to `main` as one commit (content plus any image).
- If GitHub can't be reached, the change stays live and the Studio shows **"not synced"** with a **Sync now** button.
- If a file changed in git after Liz opened the form, her save is refused with a "reload" message instead of overwriting.
- Git-side editors should `git pull` first, because the Studio commits to `main` too.

See `CLAUDE.md` for the content format and the content rules (spoilers, no invented facts).
