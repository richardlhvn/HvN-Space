# tunetofond — Core 0.12.0 / Theme 0.6.0

Current source branch: [`tunetofond-rebrand-2026-11-14`](https://github.com/richardlhvn/HvN-Space/tree/tunetofond-rebrand-2026-11-14), based on verified `d152b762e99f8192a784cf4f4623ef2a87af8def` from `local-update-2026-11-13`. `main` was still the older `3c5a5c3` source in this environment. Later legitimate work was not reset.

[Download the current local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-rebrand-2026-11-14/downloads/tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip) · [Windows/WSL merge and migration instructions](../downloads/README_TUNETOFOND_UPDATE.md) · [Package checksum](../downloads/tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip.sha256)

## Implemented

The chosen lowercase name **tunetofond** is used by the configured site title, header/footer identity and accessible home label, browser title defaults, Open Graph site identity, Twitter metadata, journal/notebook labels, abstract-art captions, editorial interpretation labels, discovery explanations, admin editorial/customizer labels, plugin/theme display names and fresh installation defaults. Generated labels read the configured WordPress identity. Existing explicit SEO overrides remain authoritative. Approved research is kept verbatim: some biographies and stored editorial descriptions still say “For Afterglow” or “Afterglow editor’s pick”. Those are deliberate preserved research phrases, not missed template labels. No public-display substitution has been applied to approved narratives.

The theme’s layout, typography, colors and player/discovery behavior are retained. Small case-preserving label styling prevents CSS from uppercasing the chosen name. Compatible WordPress theme/plugin folders remain `afterglow` and `afterglow-core`; their new installation ZIP filenames are `tunetofond-theme.zip` and `tunetofond-core.zip`.

The explicit **Tools → Site branding** migration also has a CLI dry run/apply helper. It updates only the old default site title and known first-party phrases on the About, Copyright and Media Credits pages, preserving surrounding owner wording. Unrecognized custom text is reported for manual review. It never visits artist/song records or runs a global database search-and-replace. Existing photo creator, source, permission, license and modification text is preserved. A repeat migration makes zero changes.

No domain, operator email, legal entity or new ownership assertion is supplied. The historical theme author/license notices, research filenames/URLs, API namespaces, metadata/functions, attribution enums, schemas, saved-item browser key, Compose project/volumes and environment-variable names retain their compatible identities.

## Staging and data preservation

This attached environment was usable but began on the old source with an older 53-artist catalog from onboarding, rather than the previous canonical staging database. A private SQL backup preceded switching source. Its `.env`, accounts, database/WordPress volumes and uploads were retained.

To establish the current required test fixture, the current protected canonical import updated 108 existing approved records and created 30 missing approved songs, with zero conflicts. No extra artist was imported. The reversible public selection was set to exactly ten artists / 128 songs. The retained working catalog now contains 53 artists, 560 selections, 559 selected pages and one legacy song; 475 records are hidden. This is a current staging preparation, not restoration of the former cloud database or the owner’s Windows database.

The supplied XLSX checksum matched `3af624d6abf0b51cfb2cdf9905f9037e8cd883c62a5e6fbc2858d71759ded8b7`. The prepared JSON/authority manifest were reused for this task; no spreadsheet formula, macro or uploaded script was executed, no canonical research file was rewritten, and the workbook remains private.

After preparing that canonical baseline, the branding migration changed exactly four stored items: `blogname` and the three known page bodies. Full before/after hashes of **all artist/song records and attachments, including metadata and taxonomy relationships**, plus home/site URLs, indexing, permalink structure, theme identity/settings and public selection were compared. They stayed identical through branding. All 128 approved Spotify URLs, dates/precision, About copy, source registries, photo rights, existing IDs/slugs/statuses and hidden records are retained. Raw canonical reconciliation checks validate the stored data separately from the public display.

## Validation

The following checks were executed for this release on the current staging machine; historical handoff counts are not treated as new tests.

| Current check | Result |
| --- | ---: |
| Protected branding migration: dry run, permissions, exact patch, custom wording, idempotence and research protection | 12 passed |
| Branding/browser: 1440/390/320px, titles/social metadata, owned pages, admin names, saved items, nonce and scope | 252 passed |
| Fresh canonical initializer and repeat setup, including lowercase identity/default page copy | 27 passed |
| Canonical importer/evidence/editor protection/rollback in disposable tables | 797 passed |
| Public catalog scope/API/preservation in disposable tables | 31 passed |
| Read-only reconciliation of all stored approved records to canonical research | 811 passed |
| Canonical source guard | 8 passed |
| Public/editor REST, RSS, date precision and QA separation | 16 passed |
| Approved song copy/dates/player consent and recommendation-filter preservation | 1,158 passed across 128 songs |
| Public artist/song details, citations, photos, metadata and QA separation | 1,216 passed |
| Public catalogs/APIs and every retained hidden permalink | 775 passed; all 475 hidden URLs return 404 |
| General functional browser workflow | 41 passed |
| Desktop/mobile automated accessibility | 22 audits passed |
| Full artist/song/attachment fingerprints, scoped settings and .env preservation | Unchanged through branding |
| Theme/plugin ZIP build and source whitespace check | Passed |

The two browser failures seen during older-main onboarding are superseded by these current-source results. The separate legacy `npm run test:admin` editor-save suite was not rerun; the new branding suite directly checked current admin display names, guide and protected migration UI. No broader historical all-catalog importer fixture was run. Real Spotify audio and the separate short-sample feature were not tested or claimed. Automated accessibility does not replace manual review. Actual screenshots are captured from the running WordPress pages, without generated mockups or simulated playback. [Desktop screenshot](../downloads/tunetofond-desktop.png) · [Mobile screenshot](../downloads/tunetofond-mobile.png). Full-page owned-page/song captures remain in ignored `test-results/tunetofond-*.png`.

## Local application

For an existing cleaned ten/128 site, back up database/uploads, merge **plugin**, **theme**, **scripts**, restart WordPress and run only the branding dry run/apply. Do not reimport canonical data just to rename the site. Read the linked update instructions for exact Ubuntu/WSL commands and the separate prerequisite when an older local database has not received the canonical update.

Nothing has been deployed to production, merged to main or enabled for indexing. Contact/operator/privacy details remain to complete before a separately authorized launch. Spotify artwork/audio stays in the official choice-loaded widget. The owner’s previously confirmed local full-song playback is distinct from cloud audio verification. A separate 15-second sample remains unimplemented and outside this rebrand.
