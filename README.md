# Afterglow

A working WordPress MVP for independent emotional-pop discovery. The name is a starting point: change it in **Settings → General**, and change the site’s tagline and public contact email in **Appearance → Customize → Afterglow identity**.

The site includes a designed homepage, artist and song directories, name/genre/mood/country filters, individual profiles, manually curated relationships, global search, an editorial journal, and a collection saved in the visitor’s browser. No music, artist photographs, analytics, or advertisements are bundled.

Afterglow Core 0.2 adds **Artists → Research collections**, an explicit, draft-only import for a provisional Astrid S ten-song research queue. It retains existing records and edits. The titles are unranked candidates; live source verification is blocked by the cloud network policy and remains outstanding. See [Astrid S research status and local import instructions](docs/ASTRID-S-RESEARCH.md). Nothing is imported automatically when updating the plugin.

## Why WordPress

WordPress fits Hostinger’s managed WordPress/PHP hosting and lets an editor maintain structured artist/song records without coding. The custom theme provides the design; the companion plugin owns the content models so records survive theme changes. MariaDB stores content. No build framework, music API, paid plugin, or subscription is required to run the site.

The tradeoff is normal WordPress maintenance: core/plugin updates, backups, caching, and editorial review. Manually curated relationships are appropriate for a small launch catalog; a future recommendation service can read the same post IDs and taxonomy data. At a substantially larger scale, replace the comma-separated relationship metadata with a dedicated indexed relationship table while retaining the public records and URLs.

## Run locally

Prerequisites: Docker Engine with Docker Compose v2, Node.js 20+ and npm. Chromium is needed only for browser tests. The tested container images are pinned by digest in `compose.yaml` (WordPress 7.1.3, PHP 8.3, MariaDB 11.4). Check for patched upstream images regularly; update the digests deliberately and re-test.

```bash
cd /workspace/HvN-Space
npm ci --cache /tmp/afterglow-npm
npm run setup
```

Open the local site on port **8080** in your own development environment. WordPress administration is at `/wp-admin/`. The local administrator username is `editor`. Read `AFTERGLOW_ADMIN_PASSWORD` privately from the generated `.env` file; do not share or commit it. The file also contains randomly generated local database passwords. No public service credentials are required.

The cloud onboarding interface does not provide a browser preview link for this local server. The included screenshots in `test-results/` show the locally tested site. To interact with it on your computer, clone/download the repository and run the commands above. If an existing Git checkout has no commit yet, download the working files before moving to another machine.

```bash
npm start     # Restart services; retains database content
npm run stop # Stop this project's services; retains volumes
```

To choose a different port, add `PORT=8081` to `.env` **before the first setup**, then use that port in your browser and tests. If changing an existing installation’s port, update both WordPress `home` and `siteurl` with WP-CLI and search-replace the old local URL in the database. Do not delete the database to change a URL.

`setup` is repeatable: it retains the existing administrator and edited content. The demo seed runs once and skips pre-existing matching slugs. Docker named volumes persist the database and WordPress runtime, while theme/plugin source is mounted read-only. Never use `docker compose down -v` unless you intend to permanently delete the local database and uploads. Back up valuable work first. Live containers and Docker volumes are not assumed to survive a new cloud machine; run setup if they are missing.

## Tests and packages

```bash
npm test                         # 38 functional checks in Chromium; writes screenshots
bash scripts/test-editorial.sh   # 16 permission, validation, and editorial-integrity checks
bash scripts/test-research.sh    # 39 import safety checks in a disposable DB namespace
npm run test:a11y                # Automated WCAG checks on representative templates
npm run test:admin               # Save structured fields through WordPress administration
npm run package                  # Creates dist/afterglow.zip and dist/afterglow-core.zip
```

Browser tests use `/usr/bin/chromium` by default. Set `CHROMIUM_PATH` to your Chromium/Chrome executable and `TEST_URL` if using another port. Install a current Chromium through your system package manager if needed. Packaging uses Python 3’s standard library. Tests require the demo content and are intended for this local/staging installation, not an edited public production database. The PHP test creates temporary draft records and a subscriber and removes them in `finally`.

See [Hostinger deployment](docs/HOSTINGER.md), [editorial workflow](docs/EDITORIAL.md), and [architecture](docs/ARCHITECTURE.md). No deployment or purchase has been performed.

## Content status

Six artist profiles and six song pages are **clearly labeled demo listening prompts**, not researched biographies or authoritative song meanings. Mood/genre labels and connections are provisional editorial choices. No country, interview, quote, release date, official Spotify entity ID, or social account is invented. Unverified Spotify destinations are labeled searches. Demo records carry `noindex` and are excluded from WordPress’s post sitemap, even after public indexing is enabled.

Three original journal articles discuss listening and editorial practice. Legal/contact pages are pre-launch templates requiring the owner’s details and appropriate review. Before launch, replace or finish the sample records, verify links and claims, document artwork rights, and complete contact/privacy information. Local WordPress additionally has **Discourage search engines** enabled.

## Source layout

```text
theme/       Installable Afterglow classic theme; local fonts, original abstract artwork
plugin/      Afterglow Core: data models, editor fields, validation, optional demo seed
scripts/     Local Docker setup, bootstrap, packaging, and test helpers
tests/       Browser, accessibility, and WordPress editorial tests
docs/        Hosting, editorial, and architecture guides
compose.yaml Local-only WordPress / MariaDB / WP-CLI stack
```

Source code and original design assets are licensed under GPL-2.0-or-later. Bundled DM Sans and DM Serif Display font files retain their SIL Open Font License notices in `theme/assets/fonts/`. Artist names and song titles identify editorial subjects and imply no affiliation or endorsement.
