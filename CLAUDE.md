# Eliza A. Penn — author website

This repo is the live site for **Eliza A. Penn** (Liz), a romance author. It's a small PHP site with no database. **All content lives in JSON files under `content/`**, and editing those files is how the site gets updated.

## How updates reach the site

- **From git (Claude or John):** edit `content/…`, commit, push to `main`. The GitHub Action (`.github/workflows/deploy.yml`) checks PHP and JSON and uploads the changed files over FTP.
- **From the Studio (Liz, at `/studio`):** each save writes the JSON on the server *and* commits it to `main` through the GitHub API, so git stays the source of truth.
- Because the Studio commits too, **always `git pull` before editing** and keep commits small. If a Studio edit and a git edit touch the same file, the Studio refuses stale saves and asks Liz to reload, but git can't stop you overwriting her change. Pull first.

## Content rules (from Liz's project docs — these matter)

1. **Don't invent author facts.** No made-up bio lines, awards, review quotes, release dates, prices or links. If something is missing, leave it empty and add a note to that file's `needs_review` list.
2. **Spoilers.** Only blurb-level material goes on public pages. Book 1 spoilers go in a character with `"spoiler": true` (shown behind a "show spoilers" button) and nowhere else. Anything from unpublished books' plans **never** goes in this repo, including code comments, alt text and commit messages. When unsure, leave it out and ask Liz.
3. **Two separate worlds.** *Siren Unleashed* (dark academy romantasy) and the *Interspecies Relations Agency* books (sci-fi/alien romance) share no characters, world or branding. Never cross-reference them beyond "also by Eliza A. Penn".
4. **Content warnings** are wanted, specific and plain-spoken. Use Liz's wording. Don't soften the genre or add moralizing copy.
5. **Liz approves public copy.** Anything you draft goes in with a `needs_review` note saying it's a draft.
6. Never publish Liz's real name. The site uses her pen name only.

## Content files

`content/site.json` holds the author name, tagline, intro, bio, photo, social links, newsletter, ARC sign-up, the 18+ gate and a footer note.

`content/series/<slug>.json`:
```json
{ "slug": "siren-unleashed", "name": "…", "kind": "series | universe", "tagline": "…",
  "description": "…", "planned_count": 4, "theme": "siren", "order": 1, "visible": true, "needs_review": [] }
```

`content/books/<slug>.json` (the slug is the file name and the URL `/books/<slug>`):
```json
{
  "slug": "…", "title": "…", "subtitle": "", "series": "<series slug or empty>", "series_number": 1,
  "series_label": "optional override for the series line",
  "status": "published | coming-soon | in-progress", "release_date": "YYYY-MM-DD or null",
  "visible": true, "featured": true, "order": 1,
  "tagline": "one line", "blurb": "paragraphs separated by a blank line; *italic*, **bold**",
  "excerpt": { "title": "", "text": "" },
  "genres": [], "tropes": [], "heat": "0-5 or null", "heat_label": "", "pov": "", "length": "",
  "kindle_unlimited": true,
  "links": { "amazon": "", "goodreads": "", "bookbub": "", "other": [{ "label": "…", "url": "…" }] },
  "content_warnings": "…",
  "characters": [{ "name": "", "role": "", "description": "", "color": "#hex | rainbow | iridescent",
                   "supporting": false, "spoiler": false }],
  "theme": "nightfall | siren | glacier | aelithra | ember", "accent": "#hex or empty",
  "cover": "assets/uploads/covers/<file> or null", "cover_alt": "",
  "needs_review": ["notes for Liz; shown as a checklist in the Studio"]
}
```

- `featured: true` puts a book in the home page's world strip (sorted by `order`).
- `visible: false` hides a book everywhere public.
- Covers go in `assets/uploads/covers/`. The site draws a typographic cover when `cover` is null.
- Worlds (colors and backgrounds) are defined in `app/lib/themes.php` plus a `.world-<key>` block in `assets/css/site.css`. A new book world needs both.

## Code map

- `index.php`: front controller and routes (`/`, `/books`, `/books/<slug>`, `/series/<slug>`, `/about`, `/sitemap.xml`, `/studio/*`)
- `app/lib/`: content loading, themes, UI helpers, auth, GitHub commits (`github.php`), sync queue (`sync.php`), Studio form helpers
- `app/views/`: public templates. `app/views/studio/`: Studio templates
- `app/studio.php`: Studio controllers
- `app/config.local.php`: **server-only secrets** (password hash, GitHub token). Never commit it.
- `storage/`: server-only runtime state (login throttling, sync queue). Never commit it.

## Local preview

```
php -S localhost:8000 index.php
```
