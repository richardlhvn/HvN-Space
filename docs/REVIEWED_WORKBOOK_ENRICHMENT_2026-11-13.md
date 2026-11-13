# Reviewed workbook enrichment — 13 November 2026

**Subsequent owner decision:** [the public catalog is now restricted to ten core artists / 128 songs](CORE_CATALOG_LAUNCH.md). The full enriched catalog described below remains stored for later expansion. Core 0.10.1 / Theme 0.5.1 implement that reversible visibility setting; the older instruction below to preserve full public visibility has been superseded.

The existing WordPress site has been enriched locally from the owner's
`Afterglow_Master_Worksheet.xlsx`. Core is now **0.10.0**; Theme remains **0.5.0**.
The design, official click-to-load players, three starter cards, optional About
sections, discovery filters, media credits and abstract artwork remain intact.
Production and the owner's separate Windows/WSL database have not been changed.

## Data and scope

`Review 10 Artists` contains **128 verified selections** across ten existing
artists. About text, artist paraphrases, Spotify URLs and attribution match
`Songs Exact` for all 128 keys. The export reads canonical/review/source/rights
cells as data, rejects formulas in those cells, and never executes workbook
scripts or macros. The supplied workbook is retained unchanged.

| Artist | Reviewed selections |
| --- | ---: |
| Astrid S | 14 |
| Caleb Hearn | 10 |
| Chelsea Cutler | 12 |
| Clara Mae | 13 |
| Elina | 10 |
| Mokita | 10 |
| Rosie Darling | 13 |
| Sara Kays | 12 |
| Sasha Alex Sloan | 19 |
| SHY Martin | 15 |

The update adds **30 songs**, enriches **98 existing songs** and updates the ten
artist records. Clara Mae's existing Taped Up Heart and Mokita's Lonely City are
retained even though they are absent from this review list. Artist collections
show the approved order first, then any retained older selections. The other 43
artists are preserved. The reference prompt's ten-artist public-launch scope
does not authorize hiding or deleting this existing catalog.

| Catalog measure | Before | After |
| --- | ---: | ---: |
| Artists | 53 | 53 |
| Curated selections | 530 | 560 |
| Distinct selected song pages | 529 | 559 |
| Exact track mappings | 174 | 204 |
| Missing-track fallbacks | 355 | 355 |
| Artist players / search fallbacks | 44 / 9 | 44 / 9 |
| Cleared portraits / abstract artist artwork | 6 / 47 | 6 / 47 |

Caleb Hearn/ROSIE's Little Bit Better still shares its existing page and canonical
Spotify player. The ROSIE alternate-recording reference and uncertainty remain
unchanged. Existing post IDs, URLs and statuses are preserved. New songs are
visible only on the local development site with noindex retained.

## Reusable import and preservation

- `scripts/export-reviewed-workbook.py` produces the dated, explicit approval
  overlay at `plugin/data/reviewed-2026-11-13.json`. It requires Python's openpyxl
  package. The JSON records the original workbook SHA-256 and exact approval
  keys; it does not include the entire working workbook or unrelated sheet data.
- Historical ten-song packages remain unchanged. The new path in
  `plugin/reviewed-research.php` supports variable selection counts, reuses
  existing identity/protection helpers and preserves later editor/evidence edits.
- Both **Artists → Reviewed workbook enrichment** and the CLI provide a dry run.
  New pages default to drafts unless explicitly shown on local/staging review.
  Existing private/draft/trash statuses are never promoted by this importer.
- Batch validation, global recording-collision checks and an import lock run
  before writes. Database write failures roll back the batch. Reports distinguish
  created, updated, unchanged/skipped, conflicting, rejected and failed records.
- A separate reviewed baseline protects the imported content and source data;
  historical research/media sync cannot revert it to old copy. Conflicts require
  a field comparison, never a forced overwrite.
- Approved wording is stored exactly, including outer whitespace. Display uses
  the existing template's whitespace handling. Original fine-grained worksheet
  attribution/topics remain in provenance; documented exporter mappings map them
  to the existing artist-statement/reporting/editorial/mixed categories.
- Source access statuses are preserved. Approved editor-supplied evidence can
  accompany this reviewed copy without being relabeled independently verified.
  Pending original checks and the transcript's pending primary-audio check are
  identified in source labels. The ordinary unreviewed import validation remains
  strict; later edits do not inherit the reviewed-copy exception automatically.

From `/workspace/HvN-Space`, after updating the plugin source:

```bash
# Review proposed changes; this writes no posts.
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php

# Back up before applying data changes. Keep the resulting SQL private.
bash scripts/backup.sh

# Apply to an authorized local/staging database, showing new pages for review.
docker compose run --rm cli wp --user=editor eval-file /scripts/import-reviewed.php apply show-new
```

Do not rerun imports merely to load context. Do not replace `.env`, reset volumes,
or upload private backups. On an existing edited database, inspect the dry-run
conflicts before proceeding. A GitHub source download alone does not update the
owner's local WordPress database.

## Validation evidence

The first local import created 30 songs and updated 108 records, with no conflicts
or failures. The second made zero changes and skipped all 138 records. A subsequent
normalization of the ten artist image JSON objects refreshed raw metadata only;
no images were imported or replaced. The final repeat again made zero changes.

Before/after hashes confirmed **475 unrelated artist/song records**, including
their post fields, metadata and taxonomy relationships, were unchanged. Existing
target IDs, slugs and statuses and `.env` content/permissions were unchanged.
A private SQL backup was taken before applying the enrichment. Existing
photographs, licenses and uploads were reused.

| Check | Result |
| --- | --- |
| Disposable importer suite | 795 passed: approvals, evidence, exact wording, parent links, dry run, idempotence, unrelated data, recording collision, editor/source/trash protection, malformed evidence handling, rollback |
| XLSX export integrity | 6 passed: valid data, approval exclusion, mismatches, formulas, duplicates, unknown source IDs |
| Reviewed-content browser suite | 902 passed across all 128 approved pages, plus mobile, player fallback and filter preservation |
| Artist-listening browser regression | 665 passed across all 53 profiles; count assertion now includes reviewed additions and retained originals |
| Accessibility | 22 desktop/mobile audits passed; no external requests before player choice |
| Existing About schema/migration/protection | 29 passed |
| Existing editorial permission/integrity | 16 passed |
| Historical research validator | Passed; continues to describe the original 530-selection snapshot |

Screenshots and browser summaries are under ignored `test-results/`, including
`reviewed-crash-desktop.png`, `reviewed-crash-mobile.png` and
`reviewed-astrid-desktop.png`. These are actual pages, not generated mockups.
Browser iframe/fallback tests do not establish live audio playback or independent
verification of external research. The pre-existing broad smoke suite's hardcoded
two-related-artists expectation remains an outdated assertion; it is not a passing
check for this updated catalog.

## Next work and relevance

1. **Manual recording QA:** confirm Astrid S / Sidelove's supplied Spotify URL
   identifies and plays the intended recording. Do not mark it independently
   checked merely because all 128 URLs are populated.
2. **Source QA:** worksheet approval is separate from independent original-source
   checking. SHY Martin / Forget to Forget now uses the owner's approved wording
   with its supplied-excerpt caveat; the complete Billboard passage remains
   independently unverified. Clara Mae's additional piano-demo detail remains
   outside this update; the older supported Taped Up Heart page is preserved.
   Verify transcript/secondhand/search-only/editor-supplied evidence where noted.
3. **Missing bibliography:** Hypochondriac includes an approved PAPER URL without
   a matching source-register row. Its explicit URL is retained and labeled as
   an editor-supplied reference; add verified source metadata to the workbook
   rather than inventing a title, source ID or access status.
4. **Stale supporting fields:** some approved records retain older uncertainties,
   short descriptions or release claims that conflict with newer review text or
   dates. Examples include Hurts So Good's old first-release uncertainty and
   Forget to Forget's old “no first-person narrative” note. These are retained,
   not silently rewritten. Reconcile them in the working workbook with evidence.
5. **Owner's local copy:** apply the updated plugin and dry-run/import there when
   desired; its content and IDs can differ from this cloud database. Compare any
   reported editor conflicts, preserving later worksheet edits.
6. **Before any production launch:** review actual playback, external links,
   legal/contact/privacy details, hosting HTTPS/backups and the intended launch
   visibility rules. Advertising remains deferred. Production publishing requires
   the owner's explicit approval.

Artist intake remains paused. No redesign, production deployment, public indexing,
advertising activation or external outreach was performed.
