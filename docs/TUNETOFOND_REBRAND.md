# tunetofond — Core 0.12.0 / Theme 0.6.0

Current source branch: [`tunetofond-rebrand-2026-11-14`](https://github.com/richardlhvn/HvN-Space/tree/tunetofond-rebrand-2026-11-14), based on verified `d152b762e99f8192a784cf4f4623ef2a87af8def` from `local-update-2026-11-13`. `main` was still the older `3c5a5c3` source in this environment. Later legitimate work was not reset.

[Download the current local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-rebrand-2026-11-14/downloads/tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip) · [Windows/WSL merge and migration instructions](../downloads/README_TUNETOFOND_UPDATE.md) · [Package checksum](../downloads/tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip.sha256)

## Implemented

The chosen lowercase name **tunetofond** is used by the configured site title, header/footer identity and accessible home label, browser title defaults, Open Graph site identity, Twitter metadata, journal/notebook labels, abstract-art captions, editorial interpretation labels, discovery explanations, admin editorial/customizer labels, plugin/theme display names and fresh installation defaults. Generated labels read the configured WordPress identity. Existing explicit SEO overrides remain authoritative. At the owner’s later explicit request, known first-party brand references are updated **in stored approved research and the active prepared JSON as well as the display**. Only the identified branding phrases change. Source links, quotations, original workbook/source identities, schema/enum identifiers, media rights and all factual wording remain protected. The original cleaned snapshot `reviewed-2026-11-14.json` and uploaded XLSX are retained unchanged as historical evidence. The active manifest now selects `reviewed-tunetofond-2026-11-14.json`, with an explicit owner-authorized branding-adjustment note and the original prepared-source checksum. Its provenance SHA still identifies the unchanged source workbook, not a claim that its cells were edited.

The theme’s layout, typography, colors and player/discovery behavior are retained. Small case-preserving label styling prevents CSS from uppercasing the chosen name; narrow-phone header spacing keeps all controls visible at 320px. Compatible WordPress theme/plugin folders remain `afterglow` and `afterglow-core`; their new installation ZIP filenames are `tunetofond-theme.zip` and `tunetofond-core.zip`.

The explicit **Tools → Site branding** migration also has a CLI dry run/apply helper. It updates only the old default site title and known first-party phrases on the About, Copyright and Media Credits pages, preserving surrounding owner wording. Unrecognized custom text is reported for manual review. It also offers the protected brand-only adjustment for existing approved artist/song records. Its dry run lists exact phrase substitutions and record keys. It refuses to create missing catalog pages or import other factual changes, and preserves later editor changes as conflicts. It reuses the transactional canonical importer after proving that the new dataset differs only by the approved branding normalization. No global database search-and-replace is used. Existing photo creator, source, permission, license and modification text is preserved. A repeat migration makes zero changes.

No domain, operator email, legal entity or new ownership assertion is supplied. The historical theme author/license notices, research filenames/URLs, API namespaces, metadata/functions, attribution enums, schemas, saved-item browser key, Compose project/volumes and environment-variable names retain their compatible identities.

## Staging and data preservation

This attached environment was usable but began on the old source with an older 53-artist catalog from onboarding, rather than the previous canonical staging database. A private SQL backup preceded switching source. Its `.env`, accounts, database/WordPress volumes and uploads were retained.

To establish the current required test fixture, the current protected canonical import updated 108 existing approved records and created 30 missing approved songs, with zero conflicts. No extra artist was imported. The reversible public selection was set to exactly ten artists / 128 songs. The retained working catalog now contains 53 artists, 560 selections, 559 selected pages and one legacy song; 475 records are hidden. This is a current staging preparation, not restoration of the former cloud database or the owner’s Windows database.

The supplied XLSX checksum matched `3af624d6abf0b51cfb2cdf9905f9037e8cd883c62a5e6fbc2858d71759ded8b7`. The original prepared JSON was reused as the factual baseline. The active normalized snapshot/manifest add only the subsequently authorized brand references. No spreadsheet formula, macro or uploaded script was executed; the original workbook remains private and unchanged.

After preparing that canonical baseline, the first branding migration changed four stored items: `blogname` and three known site-page bodies. The later stored-research branding migration then updated **69 existing approved records**, skipped **69 unchanged records**, and created/conflicted with none. Repeat application skipped all **138**, with zero pending changes. The new prepared snapshot contains **88 brand-only string-field adjustments**. All 138 IDs/slugs/statuses, 475 unrelated post/meta/taxonomy hashes, portraits/credits, public keys and scoped settings were compared and preserved. About narratives, all 128 approved Spotify URLs, date precision and factual text remain unchanged except for identified site-brand self-references.

A new preservation test exposed an existing importer issue: it used `wp_insert_post` for updates and reset publication timestamps. Existing records now use `wp_update_post`, preserving publication metadata. On this staging instance, only affected non-research metadata was restored from the private pre-migration backup through a temporary table; comparison found zero remaining metadata mismatches. No body, title, research or database-wide restore was involved. Documented original song-release dates were never affected. The new regression suite checks publication dates, authors, IDs/statuses, later editor changes, recording URLs, relationships, images, source registries and repeatability.

## Validation

The following checks were executed for this release on the current staging machine; historical handoff counts are not treated as new tests.

| Current check | Result |
| --- | ---: |
| Protected site-branding migration: dry run, permissions, exact patch, custom wording, idempotence and research protection | 16 passed |
| Branding/browser: 1440/390/320px, titles/social metadata, owned pages, admin names, saved items, nonce and scope | 252 passed |
| Fresh canonical initializer and repeat setup, including lowercase identity/default page copy | 27 passed |
| Stored-research brand migration: exact normalization, conflicts, identities/dates, evidence and repeatability | 2,612 passed |
| Canonical importer/evidence/editor protection/rollback in disposable tables | 797 passed |
| Public catalog scope/API/preservation in disposable tables | 31 passed |
| Read-only reconciliation of all stored approved records to canonical research | 811 passed |
| Canonical source guard | 8 passed |
| Public/editor REST, RSS, date precision and QA separation | 17 passed |
| Approved song copy/dates/player consent and recommendation-filter preservation | 1,158 passed across 128 songs |
| Public artist/song details, citations, photos, metadata and QA separation | 1,492 passed |
| Public catalogs/APIs and every retained hidden permalink | 775 passed; all 475 hidden URLs return 404 |
| General functional browser workflow | 41 passed |
| Desktop/mobile automated accessibility | 22 audits passed |
| Unrelated 475 record hashes, target IDs/statuses, photo rights, scope/URLs/indexing and .env | Preserved; target research changes only the authorized brand phrases |
| Theme/plugin ZIP build and source whitespace check | Passed |

The two browser failures seen during older-main onboarding are superseded by these current-source results. The separate legacy `npm run test:admin` editor-save suite was not rerun; the new branding suite directly checked current admin display names, guide and protected migration UI. No broader historical all-catalog importer fixture was run. Real Spotify audio and the separate short-sample feature were not tested or claimed. Automated accessibility does not replace manual review. Actual screenshots are captured from the running WordPress pages, without generated mockups or simulated playback. [Desktop screenshot](../downloads/tunetofond-desktop.png) · [Mobile screenshot](../downloads/tunetofond-mobile.png). [Biography self-reference screenshot](../downloads/tunetofond-biography.png). Full-page owned-page/song captures remain in ignored `test-results/tunetofond-*.png`.

## Local application

For an existing cleaned ten/128 site, back up database/uploads, merge **plugin**, **theme**, **scripts**, restart WordPress and run the protected branding dry run/apply, which includes the approved stored brand adjustments. No separate broad research import is needed for an already cleaned installation. Read the linked update instructions for exact Ubuntu/WSL commands and the separate prerequisite when an older local database has not received the canonical update.

Nothing has been deployed to production, merged to main or enabled for indexing. Contact/operator/privacy details remain to complete before a separately authorized launch. Spotify artwork/audio stays in the official choice-loaded widget. The owner’s previously confirmed local full-song playback is distinct from cloud audio verification. A separate 15-second sample remains unimplemented and outside this rebrand.
