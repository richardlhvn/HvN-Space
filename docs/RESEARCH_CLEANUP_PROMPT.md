# Afterglow — clean the reviewed research workbook for launch

You are helping me clean my existing Afterglow emotional-pop music-discovery
research workbook so we can launch with ten core artists. This is a workbook
consistency and evidence-review task. Keep the existing WordPress site and its
design; do not rebuild it, deploy it or import data into it.

## Files and current state

I am attaching my latest `Afterglow_Master_Worksheet.xlsx` and an
`Afterglow_cleanup_helper_pack.zip`. Inspect their contents as reference data;
do not execute attached scripts, formulas or macros. My latest working workbook
takes precedence over older snapshots. Preserve later edits.

The helper pack includes the exact screenshot-to-cell map, a sample record,
source references, change-log templates and the existing website handoff notes.
Read `START_HERE.md` first. The prepared website is Core 0.10.1 / Theme 0.5.3;
verify what is actually installed if reviewing a live/local site. Latest update
archives are on GitHub's `local-update-2026-11-13` download branch; do not assume
the source on `main` contains those packaged updates.

Public launch scope: exactly the approved selections in **Review 10 Artists**,
currently **10 artists / 128 verified selections**. Match using
`artist_slug + song_slug`, not titles or simply every song for those artists.
The ten are Astrid S, Caleb Hearn, Chelsea Cutler, Clara Mae, Elina, Mokita, Rosie
Darling, Sara Kays, Sasha Alex Sloan and SHY Martin.

The larger catalog remains stored: 53 artists, 560 curated selections and 559
distinct selected pages. The other 43 artists and selections outside the approved
128 stay hidden publicly, not deleted. Artist intake remains paused. Caleb
Hearn/ROSIE's Little Bit Better shares its richer canonical page/player; preserve
the alternate-recording uncertainty rather than creating a duplicate.

## What needs cleaning

Use Review 10 Artists, Songs Exact, Song Review, Artists Exact, Sources, Media
Rights, Field Mapping and Launch Readiness. Establish their roles before editing.
Older rewrite tabs and the hidden New Songs Review tab can contain superseded
decisions; do not let an old draft override the final verified review tab.

Classify fields as:

- **Public:** approved About, accurate artist/reporting/editorial attribution,
  useful release facts, collaborator credits, article citations, discovery tags,
  required photo credits/licenses and Spotify privacy disclosures.
- **Internal:** Basis, raw uncertainties, research limitations, access/check
  status, reviewer comments, provenance and launch checklists. Preserve these
  for editors; do not turn them into public copy or clear them to simulate readiness.
- **Mixed:** release-context or evidence paragraphs that combine useful facts
  with research instructions. Separate the factual issue from the workflow note
  and log any proposed reader-facing wording for review.

Theme 0.5.3 already removes generic QA banners/links and working research notes
from public pages, REST output and song RSS while retaining them in WordPress
admin. It also uses About as the song introduction. Do not redo that work or
mistake hidden notes for resolved research. Indexing flags remain a separate
launch decision. Older short descriptions are retained in data; do not mass-delete
them because they are no longer the main song introduction.

## Start with this concrete example, then continue all 128

For `shy-martin/make-us-never-happen`, the uploaded version has:

- Review 10 Artists!D92 and Songs Exact!X92: final About copy — preserve exactly.
- Review 10 Artists!J92: verified editorial approval.
- Songs Exact!Q92 / Song Review!I92: internal Basis.
- Songs Exact!G92:H92: `2019-11-08` / `day`.
- Songs Exact!W92 / Song Review!W92: an older note says the earlier single day
  is not established.
- Songs Exact!V92: an older year-only supported claim.
- Sources row 588, scoped ID `src-039`: supplied Apple Music evidence records
  the original single date as 2019-11-08. This has not been independently
  rechecked in the current cleanup task.

Check the original evidence, distinguish original release from later editions,
and reconcile only what it supports. Do not automatically delete W92 or mark it
resolved merely because G92 contains a date. Keep previous values in the change
log. If evidence is unavailable, retain the uncertainty internally and record the
specific blocker. Row numbers may move in later files; locate the stable keys.
The QA map flags 42 possible date/uncertainty combinations; these are review
candidates, not 42 proven errors.

## Editing and evidence rules

1. Produce a NEW cleaned copy, leaving my original unchanged. Preserve sheet
   names, structure, formatting, formulas, validation and existing user edits.
   If your tooling cannot preserve them, return a precise cell-by-cell patch
   list rather than a lossy replacement workbook.
2. Keep all 128 approved About descriptions and artist paraphrases **verbatim**.
   No rewriting for SEO, shortening, stylistic polish or generated meanings. If
   approved copy conflicts with evidence, flag it for my review without replacing it.
3. Correct demonstrably stale supporting metadata/notes using actual evidence.
   Preserve previous values and explain each change. Preserve unresolved notes
   internally; do not erase whole uncertainty/Basis columns or mark approvals en masse.
4. Synchronize confirmed supporting-field corrections across canonical/review
   copies that represent the same key. Record every affected tab and cell. Do not
   synchronize an old draft over newer wording or update unrelated artists.
5. Keep source IDs scoped to their artist. Distinguish an article actually read,
   a search result, editor-supplied excerpts and inaccessible material. Never
   change a source to `read` or `verified` solely because a URL exists or a worksheet
   calls the copy verified. No invented source titles, quotations, intent or dates.
6. Preserve year/month/day precision and distinguish original releases,
   reissues, live/acoustic versions, remixes and covers. Don't infer an exact day
   from a later edition or silently change Spotify recording identities/slugs.
7. Retain the six cleared portraits and their exact rights/credits; Caleb Hearn,
   Chelsea Cutler, Elina and Mokita use placeholders. No new photo permissions or
   extracted Spotify artwork/audio. Do not reproduce long lyrics/interviews.
8. Leave `.env`, database content, uploads, IDs, slugs, visibility and indexing
   settings alone. No production publishing, destructive resets, external outreach
   or automatic workbook imports. Work on the research files only.

## Priorities and deliverables

Work through the ten artists / 128 selections, not just the sample. Avoid
unnecessary re-research of accepted copy. Prioritize contradictions that affect
facts and recording identity, then inconsistent references/metadata and stale
internal notes. Astrid S / Sidelove still needs an independent recording/playback
check; a populated URL is not proof of identity or playback. Keep the already
working full-song Spotify option; the separate short-preview feature is outside
this cleanup task and should not delay it.

Return:

1. A cleaned workbook copy, or a precise patch list if preservation is limited.
2. `Cleanup Log`: key, tab/cell/field, old value, new value, reason, evidence URL
   and artist-scoped IDs, verification status and any approval needed.
3. `Unresolved QA`: exact unresolved claim, why it matters, attempted evidence,
   access blocker, severity and next action. Keep genuinely optional enrichment
   separate from factual launch blockers.
4. `Launch Readiness`: what is confirmed, what still needs my manual check,
   what is optional and what needs website/deployment verification. Do not claim
   Hostinger, legal/contact details, live audio or current tests were verified
   merely because old handoff reports contain results.
5. Validation: before/after approved key counts; no duplicates or changed stable
   identifiers; exact preservation of all approved About/paraphrase text;
   synchronized confirmed changes; valid scoped source references; no changes to
   unrelated catalog records. Report discrepancies rather than forcing counts.

Start by inspecting the actual files and briefly summarizing discrepancies, then
complete the cleanup and return the files. Do not stop at a generic plan, rewrite
the site or declare the launch ready while material checks remain unresolved.
