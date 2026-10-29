# Afterglow

A working WordPress MVP for independent emotional-pop discovery. The name is a starting point: change it in **Settings → General**, and change the site’s tagline and public contact email in **Appearance → Customize → Afterglow identity**.

The site includes a designed homepage, artist and song directories, name/genre/mood/country filters, individual profiles, manually curated relationships, global search, an editorial journal, and a collection saved in the visitor’s browser. No music, downloaded album covers, analytics, or advertisements are bundled. Six separately licensed artist photographs are included with their own attribution and license records; fourteen artists retain abstract placeholders.

**New in Core 0.6:** [research batches 3 and 4](docs/RESEARCH-UPDATE-2026-10-29.md) add Sadie Jean, Lyn Lapid, Alexander 23, Anson Seabra, BLÜ EYES, Hanniou, Loote, Rachel Grae, NERIAH, and Fly By Midnight. The catalog now contains **20 artists and 200 curated songs**. In **Artists → Import supplied research**, review the **2026-10-29** collection to import only these ten artists and their hundred songs. Forty-two new exact track links support official players; 58 deliberately missing track links use verified artist-profile fallbacks. Original records and photo-rights files are preserved. Linked collaboration credits share existing song pages rather than create duplicate recordings. Imports remain noindex pending editorial review.

**New in Core 0.4:** [seven additional supplied research collections](docs/RESEARCH-UPDATE-2026-10-27.md) bring the bundled catalog to **ten artists and 100 curated songs**. Use the same **Artists → Import supplied research** screen after updating the code. Edited records remain protected, and imported pages remain noindex pending review. The former Clara Mae website is excluded from public links.

**New in Core 0.3:** [apply the supplied research update](docs/RESEARCH-UPDATE.md) for Astrid S, Sara Kays, and Caleb Hearn (three artist profiles and thirty curated songs). Update the plugin/theme/scripts and use **Artists → Import supplied research**. Existing edits are protected and imports remain noindex pending editorial review. The older 0.2 draft queue below is retained for compatibility.

Afterglow Core 0.2 adds **Artists → Research collections**, an explicit, draft-only import for a provisional Astrid S ten-song research queue. It retains existing records and edits. The titles are unranked candidates; live source verification is blocked by the cloud network policy and remains outstanding. See [Astrid S research status and local import instructions](docs/ASTRID-S-RESEARCH.md). Nothing is imported automatically when updating the plugin.

**Core 0.5 media update:** see [the local update instructions](docs/LOCAL_MEDIA_UPDATE.md), [implementation and rights report](docs/MEDIA_SYNC_REPORT.md), and [Hostinger media release checklist](docs/HOSTINGER_MEDIA_RELEASE.md). It adds six source-verified portraits, complete credits, and 100 click-to-load official track embed mappings. Use **Artists → Media and embeds** after the catalog is imported. The owner approved publishing this reviewed source update to GitHub; production deployment still requires separate approval.

On song pages with an exact track link, the official Spotify player occupies the main artwork position. Click **Load Spotify player** to show Spotify's cover and playback controls together. Directory cards retain original abstract artwork; songs without an exact track link retain their artwork and offer the artist's verified Spotify profile, or a labeled search when no verified profile exists. No cover image is copied into WordPress.

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
npm test                         # 40 functional checks in Chromium; writes screenshots
bash scripts/test-editorial.sh   # 16 permission, validation, and editorial-integrity checks
bash scripts/test-research.sh    # 39 import safety checks in a disposable DB namespace
bash scripts/test-handoffs.sh    # 1,310 supplied-research checks in a disposable DB namespace
python3 scripts/validate-research.py # Catalog, nulls, source references, dates, and duplicate recordings
bash scripts/backup.sh           # Private database backup before content updates
npm run test:a11y                # Automated WCAG checks on representative templates
bash scripts/test-media.sh       # 243 media sync and permission-gate checks
node tests/media-browser.mjs      # All 200 song pages, real photos, credits, embeds, and null-track fallbacks
node tests/research-admin.mjs     # Research collection selection and protected admin import
npm run test:admin               # Save structured fields through WordPress administration
npm run package                  # Creates dist/afterglow.zip and dist/afterglow-core.zip
```

Browser tests use `/usr/bin/chromium` by default. Set `CHROMIUM_PATH` to your Chromium/Chrome executable and `TEST_URL` if using another port. Install a current Chromium through your system package manager if needed. Packaging uses Python 3’s standard library. Tests require the demo content and are intended for this local/staging installation, not an edited public production database. The PHP test creates temporary draft records and a subscriber and removes them in `finally`.

See [Hostinger deployment](docs/HOSTINGER.md), [editorial workflow](docs/EDITORIAL.md), and [architecture](docs/ARCHITECTURE.md). No deployment or purchase has been performed.

## Content status

The twenty artist profiles and two hundred selected song pages use supplied source-linked research and remain **noindex pending editorial review**. Mood/genre labels and recommendations are editorial choices. Exact recording links are present for 142 selections; 58 remain deliberately missing. Release precision, edition context, uncertainties, source provenance, and missing artist statements are retained. One legacy starter song and five older research drafts remain rather than being deleted to force a raw post count of 200. Review-flagged records are excluded from WordPress’s post sitemap, even after public indexing is enabled.

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

Source code and original design assets are licensed under GPL-2.0-or-later. Bundled DM Sans and DM Serif Display font files retain their SIL Open Font License notices in `theme/assets/fonts/`. Third-party photographs are excluded from the code/design GPL grant; their respective CC BY, CC BY-SA, or public-domain terms and attribution are documented in plugin/media-assets/ATTRIBUTION.md and the site’s Media Credits page. Artist names and song titles identify editorial subjects and imply no affiliation or endorsement.
