# Afterglow cleaned canonical update — Core 0.11.0 / Theme 0.5.3

This package uses your cleaned 14 November workbook export from the new canonical Google Sheet. It keeps the existing website design and WordPress architecture. Only the ten approved artists / 128 verified selections are eligible for this import.

The full master XLSX, internal cleanup tabs, passwords, .env, uploads and database backups are NOT included. Your local WordPress database is separate from the cloud staging database. Copying source files does not import the new research into your local database.

## Existing local installation: merge, do not replace everything

1. Open Ubuntu/WSL in your existing HvN-Space project folder (the folder containing compose.yaml and your .env).
2. Back up the local database before copying source files:

```bash
bash scripts/backup.sh
```

3. Extract this ZIP into a separate folder. Copy its **plugin**, **theme** and **scripts** folders into your existing HvN-Space folder. Merge folders and replace matching source files. Keep your .env, uploads and Docker volumes. Do not delete the project folder or reset the database.
4. In Ubuntu/WSL, from that same existing project folder:

```bash
chmod -R a+rX plugin theme scripts
docker compose restart wordpress
```

5. Log in to local WordPress as **editor**, using the existing password privately stored in .env. Open **Artists → Reviewed workbook enrichment**. Its dry run should show 138 updates and no new pages if your earlier ten/128 import is installed and unedited. Local edits can produce conflicts; those records are preserved. Review the listed conflicts rather than forcing an overwrite.
6. Click **Apply reviewed enrichment**. Existing publication statuses remain unchanged. If you are creating approved pages that do not already exist, select **Show new pages on this local/staging site for review (noindex)**. A repeat dry run should show 138 skipped records, or preserved local conflicts, with zero changes to unchanged records.
7. Check **Artists → Public catalog** still shows ten artists / 128 songs. If this is the first time enabling the launch selection, use **Use ten-artist core catalog** after all approved pages are available. Do not restore the full public catalog.
8. Refresh a song page with Ctrl+F5. About remains the introduction; internal research/QA is hidden from public pages and APIs; citations, release information and photo credits remain.

CLI alternative to steps 5–6 (first line is dry run):

```bash
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php apply show-new
```

No SQL restore, full project replacement or production deployment is needed. Never use docker compose down -v.

## Changes from the preceding snapshot

- Same ten artists / 128 approved composite keys; all Spotify URLs remain exactly as owner-approved.
- Five original-release date/precision corrections: Your Shirt (24 February 2017), Men on the Moon (11 November 2022), Wild Enough (20 April 2018), No Matter the Season (March 2020 only), House With No Mirrors (25 August 2020).
- 21 added artist-scoped source records and the cleaned supporting research. The supplied Caleb Hearn “then one” → “the one” typo correction is retained exactly; no extra rewrite was made.
- Six cleared portraits retain their credits. Caleb Hearn, Chelsea Cutler, Elina and Mokita intentionally keep placeholders.
- Fresh npm run setup initializes only the approved ten/128, never the other 43 artists. Existing hidden records are retained.

Cloud staging: 138 existing records updated, zero created/conflicted; repeat import skipped all 138. All IDs/slugs/statuses, 475 unrelated records, images, pages, settings and .env stayed intact. Every approved song page passed copy/date/player/browser QA; hidden URLs, public APIs, editor/RSS separation, fresh/repeat setup and automated accessibility were checked. See docs/CANONICAL_RESEARCH_SOURCE.md for results and commands.

This is staging source, not a production launch. Complete operator/contact/privacy details and review locally before separately authorizing deployment/indexing. Full-song Spotify playback remains; cloud audio and a separate 15-second sample were not newly verified or implemented.

## Fresh local project

This is an update package, not a complete Docker project. Use an existing repository checkout containing compose.yaml/package-lock.json. Merge these source folders, then run npm ci and npm run setup. The initializer imports only the canonical ten/128 and preserves existing editor changes on later runs.

Canonical Google Sheet:
https://docs.google.com/spreadsheets/d/1CBJWklaLjymaRVw_wiPcmRMsumhfr_VUQheJeI7PvcI/edit

Uploaded XLSX SHA-256:
3af624d6abf0b51cfb2cdf9905f9037e8cd883c62a5e6fbc2858d71759ded8b7
