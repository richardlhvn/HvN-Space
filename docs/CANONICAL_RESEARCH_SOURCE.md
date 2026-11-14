**Current policy:** [Core 0.15.0 / Theme 0.9.0](TAG_SYNC.md) normalizes stored genre/mood/theme tags and active source arrays, following the owner’s later instruction. The Sheet/XLSX update is deferred. The report below describes its original release.

**Current source adjustment:** The owner subsequently authorized stored brand-reference updates. The active manifest selects `reviewed-tunetofond-2026-11-14.json`; its brand-only adjustment note identifies the unchanged original `reviewed-2026-11-14.json` and workbook. All facts, approved keys/links and rights remain authoritative; the historical import report below describes the original cleaned snapshot.

**Current brand/release:** tunetofond Core 0.12.0 / Theme 0.6.0. See [the current rebrand and local update](TUNETOFOND_REBRAND.md). Older brand names, package filenames, source identities and validation results below retain their historical meaning. The canonical ten/128 selection remains authoritative.

# Canonical research source and staging update — 14 November 2026

The owner-designated [cleaned Google Sheet](https://docs.google.com/spreadsheets/d/1CBJWklaLjymaRVw_wiPcmRMsumhfr_VUQheJeI7PvcI/edit) is authoritative for future imports. Its supplied full XLSX export, `Afterglow_Master_Worksheet_CLEANUP_2026-11-14.xlsx`, has now been read, validated and applied to cloud staging. Documents and QA tabs were treated as reference data; no uploaded scripts, formulas or macros were executed.

Current source: **Core 0.11.0 / Theme 0.5.3**. Public scope remains **exactly ten artists and 128 verified selections**, identified by `artist_slug + song_slug` in **Review 10 Artists**. Existing design and WordPress architecture are retained.

## Source identity and preservation

- Export SHA-256: `3af624d6abf0b51cfb2cdf9905f9037e8cd883c62a5e6fbc2858d71759ded8b7`.
- Prepared snapshot: `plugin/data/reviewed-2026-11-14.json`, selected by `plugin/data/canonical-research-source.json`. The older master and Nov 13 snapshot are historical only. The loader rejects a mismatched source; historical bulk imports are blocked.
- All 128 approval keys and Spotify URLs match the earlier selection. All links are owner-checked and approved. This is separate from live playback testing in the cloud.
- Approved About wording and artist statements are preserved exactly, including the supplied correction from “then one” to “the one” for Caleb Hearn’s **It’s Always Been You**. No additional grammar or style changes were made.
- Original documented song-release dates and precision are used. Later Spotify album/EP edition dates do not replace them.
- Artist-scoped source IDs/URLs and media permissions are preserved. There are 21 additional source records across the ten artists. Hypochondriac’s already documented PAPER reference is reconciled to its registered artist-scoped source ID.
- Six licensed portraits keep their credits; Caleb Hearn, Chelsea Cutler, Elina and Mokita intentionally keep placeholders. Spotify artwork/audio remains inside its official widget.
- **Cleanup Log, Unresolved QA, Core 128 QA Audit and Cleanup Readiness are internal QA tabs.** They are not imported as website content. Research uncertainty/provenance stays available to editors; public pages retain About attribution, release facts, citations and photo credits.
- No other artist was imported, no database reset occurred, and production/indexing was not changed. The owner’s Windows/WSL database remains separate.

## Date corrections from the cleaned workbook

| Selection | Earlier stored precision | Cleaned documented release |
| --- | --- | --- |
| Chelsea Cutler — Your Shirt | 2017, year | 24 February 2017 |
| Chelsea Cutler — Men on the Moon | 2022, year | 11 November 2022 |
| Elina — Wild Enough | 2018, year | 20 April 2018 |
| Sara Kays — No Matter the Season | 2020, year | March 2020, month only |
| Sasha Alex Sloan — House With No Mirrors | August 2020, month | 25 August 2020 |

The month-only date stays **March 2020**; no day is invented. Updated release contexts, research claims and resolved uncertainty fields follow the supplied cleaned export.

## Applied staging result

A private SQL backup preceded the import. Dry run and apply each reported **138 updates, zero creations, zero conflicts**. A second apply reported **138 skips and zero changes**. All 138 target IDs, slugs and publication statuses remained identical. Full post/meta/taxonomy hashes for **475 unrelated records** were unchanged, as were portraits/credits, site pages, settings and public selection keys. Repeating the complete setup also preserved these records and the existing `.env`.

The retained working database still contains **53 artists / 560 curated selections / 559 distinct selected pages**, plus one legacy song post. The additional 43 artists remain hidden and unchanged. Fresh setup initializes **only ten artists / 128 songs**; it never bootstraps the other 43 from old research.

## Functional QA

| Check | Passed |
| --- | ---: |
| Disposable canonical import, exact copy/evidence, collision checks, editor protection and transaction rollback | 797 |
| Disposable public-scope, API, preservation and reversible visibility | 31 |
| Fresh ten/128 setup and repeatability, permitted portraits and site pages | 23 |
| Read-only reconciliation of staging against the new canonical snapshot | 811 |
| Canonical source guard | 8 |
| Public/editor REST, RSS, QA separation and date precision | 16 |
| All 128 song pages: approved copy, original dates, official player URLs, consent and filter preservation | 1,158 |
| Ten artist / 128 song public pages and APIs: QA hidden, citations/credits retained, responsive layout | 1,216 |
| Public directories, search, APIs and all 475 hidden permalinks | 775 |
| General browser workflow: saved collection, filtering, navigation and search | 41 |
| Desktop/mobile automated accessibility audits | 22 |

Screenshots in `test-results/reader-details-shy-desktop.png`, `reader-details-shy-sources.png`, `reviewed-crash-desktop.png` and the mobile counterparts are actual staging captures. Automated accessibility tests do not replace manual review. Live Spotify audio was not independently verified in this environment; the owner has confirmed full-song playback locally. No separate 15-second sample is implemented.

## Local update

Back up the existing local database with `bash scripts/backup.sh`. Merge the new package’s **plugin**, **theme** and **scripts** folders into the existing project, replacing matching source files. Keep `.env`, uploads and Docker volumes. Do not replace the whole project or database.

From Ubuntu/WSL in that project folder:

```bash
chmod -R a+rX plugin theme scripts
docker compose restart wordpress
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php
```

The last command is a dry run. Review any conflicts before proceeding; later local edits are preserved, never forced. Then apply using **Artists → Reviewed workbook enrichment**, or:

```bash
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php apply show-new
```

For an existing ten/128 installation, no public-scope change is needed. Check **Artists → Public catalog** still shows ten/128. A fresh local installation can use `npm ci` followed by `npm run setup`, which now uses only the canonical selection. Do not use historical all-catalog installers.

## Next work

Have the owner review the new local pages and report later workbook edits against this canonical source. Complete operator/contact/privacy details before an authorized production launch. Preserve source-specific nuances and alternate-recording uncertainty in internal research; do not invent artist intent, listening experiences, IDs or photo permissions. Artist additions remain paused. Production deployment and indexing changes require separate authorization.

Google export access remains blocked under the currently applied cloud network policy; this update used the supplied XLSX. Future Sheet changes require a fresh export or an applied, verified network setting. The original uploaded workbook is retained privately and unchanged; it is not included in the public download package.
