# Hosted state and GitHub sync — 19 November 2026

The owner-approved Hostinger installation at https://tunetofond.com is the current live site. This source sync records the changes applied on 16–19 November; it does not deploy, import a database, change indexing, or expand the public selection. The working source branch remains `tunetofond-tag-sync-2026-11-14`. Core 0.15.0 / Theme 0.9.0 identifiers are retained.

## Approved changes

- Contact: finished template and introduction, public contact@tunetofond.com address, and subject-specific email links. The owner confirmed two-way email delivery.
- About and Copyright: finished copy reflects the researched selection, licensed photography and abstract placeholders.
- Privacy: interim feature-based notice covers browser saves, Hostinger/CDN request information, administrator cookies, optional Spotify connections and email. Operator identity and retention practices remain undecided; the owner explicitly deferred them. This is not a claim of completed legal review.
- Song country filtering follows the primary artist's country without changing song terms. Existing metadata conditions, visibility, search and pagination remain intact. Counts use “1 discovery” and plural forms otherwise.
- Featured discovery first chooses a song sharing a theme. If none is eligible, it promotes another song by the current primary artist and labels it “More from this artist”. Recently visited songs are excluded; no unrelated artist is used as the featured fallback.
- Up to three distinct alternatives remain available on desktop and phone. They exclude the current and featured songs, honor selected filters, reserve at most one relevant current-artist song, and diversify other artists. Sparse selections show fewer cards rather than unrelated filler.
- Unchanged WordPress Sample Page and Hello world! were privately backed up and moved to drafts on 19 November. They were retained, not deleted.

The approved page bodies are reflected in `plugin/demo-content.php` for fresh setup. Its existing guard still protects existing pages and editor changes. The already-completed hosted database edits must not be replayed by rerunning seeding or a full migration.

## Canonical source and preservation

The existing Google Sheet remains canonical. The 27-tab inventory and public 128-song classification mapping were checked against the hosted site. The owner reports opening and verifying all source links in the Sheet, as well as all Spotify links. Public citation targets matched the selected canonical references in the prior library audit. Supplemental references are not automatically all published. Automated 403/TLS blocks are not evidence that readers receive a broken destination.

Public scope remains **10 artists / 128 songs**; hidden records, IDs, URLs, metadata, taxonomy relationships, licensed portraits and their credits are preserved. Four artists retain abstract placeholders: Caleb Hearn, Chelsea Cutler, Elina and Mokita. Keep lowercase tunetofond branding and the established afterglow/ag_ compatibility identifiers. Keep search indexing disabled until separately authorized.

The retained hosted database and private workbooks/backups are not part of this source repository. The 560 retained song posts and 558 workbook rows are an acknowledged historical difference, not an instruction to delete or import records.

## Validation

- Fresh Hostinger reads of `plugin/afterglow-core.php`, `plugin/discovery.php`, `theme/archive.php` and `theme/discovery-ui.php` matched the previously tested candidates, allowing only the file API's omitted final newline.
- Fresh public page reads matched the approved About, Privacy and Copyright text.
- PHP syntax checks passed for every PHP file included in this sync.
- Standalone checks: `php -n tests/discovery-recommendations-unit.php` (29) and `php -n tests/archive-filters-unit.php` (17).
- Earlier hosted checks covered all 128 recommendation panels: 106 shared-theme featured choices and 22 same-artist fallbacks, without duplicate featured/alternative links. Phone layouts at 390/320 pixels were checked. The owner tested Spotify playback on computer and phone.
- After default-content cleanup, both default URLs returned 404, the homepage/journal/contact still returned 200, the public API retained 10/128, and noindex remained enabled.
- The disposable WordPress integration suite's discovery assertions were updated for the separate featured selection. That Docker/WordPress suite was not rerun during this Windows source sync; standalone fixtures do not replace a full integration run.
- At sync preparation, Hostinger reported no Git auto-deployment and the repository had no GitHub Actions workflow files. GitHub writes are source updates only.

## Guarded historical patches and packages

The six `scripts/hosting/update-*.php` / `cleanup-default-content.php` files added in this sync preserve the exact public guarded patches used for these changes. They require the known domain, expected content/files and a writable private backup directory outside public_html. Run check mode first only if a future task actually needs one. These are chronological patches, not a new combined release or instructions to reapply them on the current site. Earlier patches can correctly refuse later file versions. Never bypass their guards or restore an old full-site database.

Chronology: Privacy → About/Copyright → interaction fixes v2 (including corrected Spotify Privacy wording) → recommendations → featured artist fallback → unchanged default-content cleanup. The earlier Contact package is already in GitHub.

Downloadable ZIPs and cloud snapshots created before this sync are historical and do not contain all hosted follow-up fixes. Do not use them as a current full-site restore. Current editable source is this branch; a new combined release package can be built and validated when needed.

Next development priority is an owner-reviewed library update in small batches. Preserve the current public scope until that expansion is explicitly approved.
