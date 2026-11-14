# tunetofond

## Current: cleaner reader pages — Theme 0.6.1

[Download Theme 0.6.1](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-reader-cleanup-2026-11-14/downloads/tunetofond_theme_update_0.6.1.zip) · [Simple local update steps](downloads/README_THEME_0.6.1.md) · [Report and screenshots](docs/READER_PRESENTATION_0.6.1.md)

The reader pages now omit the editorial workflow messages and attribution badges shown in the owner’s screenshots. Recommendations use the existing sparkle icon beside the theme/feeling. Citations, photo credits, factual narratives and stored research remain. Core stays **0.12.0**; merge only the **theme** folder and restart WordPress. **No import or migration is needed.**

## Previous: full rebrand — Core 0.12.0 / Theme 0.6.0

[Download the tunetofond local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-rebrand-2026-11-14/downloads/tunetofond_local_update_Core_0.12.0_Theme_0.6.0.zip) · [Local update instructions](downloads/README_TUNETOFOND_UPDATE.md) · [Implementation and validation report](docs/TUNETOFOND_REBRAND.md)

**tunetofond** is the chosen lowercase identity for the existing emotional-pop discovery website. This release rebrands the website, editorial attribution, discovery/journal labels, admin display names and fresh defaults. Merge the source folders into your existing Windows/WSL project, preserve your data and run only the protected branding migration when your cleaned ten/128 catalog is already installed. Do not replace your whole project or restore a cloud database.

The public launch selection remains **ten artists / 128 songs**. Approved research, Spotify URLs, scoped sources, dates, licenses, saved items, URLs and compatibility identifiers are preserved. No domain/contact identity is invented. Production deployment and indexing remain separate. Historical downloads below retain their original source/release names.

## Historical: cleaned canonical workbook — Core 0.11.0 / Theme 0.5.3

[Download the complete local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_local_update_Core_0.11.0_Theme_0.5.3.zip) · [GitHub file page](downloads/Afterglow_local_update_Core_0.11.0_Theme_0.5.3.zip)

[Read local update instructions](downloads/README_CLEANED_CANONICAL_UPDATE.md) · [Source and staging QA report](docs/CANONICAL_RESEARCH_SOURCE.md)

Use this update for the owner’s cleaned 14 November canonical workbook. It supersedes the older full update for future research imports. Keep the current site design; merge plugin/theme/scripts into the existing project, preserve .env/uploads/volumes, and apply the protected reviewed import locally. Do not replace the whole project or restore the cloud database.

The public selection remains exactly **10 artists / 128 verified songs**. Same approved Spotify URLs and image permissions; five original-release date/precision corrections and 21 additional scoped sources. Four intentional placeholders stay. Internal cleanup tabs and the full master workbook are not in this download.

Cloud staging import: 138 existing records updated, zero created/conflicted, repeat import zero changes. All IDs/slugs/statuses, 475 unrelated records, image credits, pages and settings were retained. All approved song pages, public API/RSS QA separation, hidden URLs, fresh/repeat setup and automated accessibility passed. No production deployment or indexing change occurred.

ZIP SHA-256: `9d9ff89b83a4635b7a148fe759464e250eb872abbcc20d4958a658f2728f655b`

## Archived downloads and earlier research references

The sections below record earlier work. Use the current tunetofond update above for branding; the cleaned update remains the historical data-import reference. The earlier QA map/helper pack references the superseded master and is historical context, not the current import authority.

## Research cleanup handoff

[Download the cleanup helper pack](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_cleanup_helper_pack_2026-11-13.zip)

[Read/copy the full cleanup prompt](downloads/Afterglow_research_cleanup_prompt.md)

Give another research assistant your **latest Afterglow_Master_Worksheet.xlsx** plus this helper ZIP, then use the included `PROMPT_CLEANUP.md`. The full master workbook is not included or replaced. The pack contains the QA map, actual example fields and evidence references, a worked-review guide, correction/unresolved/readiness templates, and reference handoff documents. It contains no executable setup scripts or credentials.

Scope: clean supporting research for the approved ten artists / 128 selections, preserving approved About wording, IDs, source scope, later edits and the hidden larger catalog. Resolve only evidence-supported contradictions; keep unresolved QA internal. No deployment or automatic imports.

## Earlier: Theme 0.5.3 — public details, internal QA

[Download Theme 0.5.3](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_theme_update_0.5.3.zip) · [GitHub file page](downloads/Afterglow_theme_update_0.5.3.zip)

[Download the Website QA Map workbook](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_Website_QA_Map.xlsx) · [GitHub file page](downloads/Afterglow_Website_QA_Map.xlsx)

The QA map identifies the screenshot text in the uploaded master XLSX, especially **Songs Exact row 92** / **Review 10 Artists row 92** for SHY Martin's Make us never happen. It includes 19 screen-element mappings and internal QA fields for all 128 approved selections. It is a separate reference workbook. The owner's full working master is not included or modified.

### What remains public

Approved About text, artist/reporting/editorial attribution, readable release date/title/format, source links, recording credits, discovery tags and required photo author/license/modification credits.

### What stays internal

Generated Research review pending banners/links, Basis paragraphs, raw uncertainty lists, research import/provenance footers and source-workflow status suffixes. The original WordPress data and workbook remain unchanged. Editors can inspect the notes in **Edit artist/song → Internal research and QA**. Removing visible QA does not resolve open research or change indexing.

The map flags a real reconciliation example: **Songs Exact!G92/H92** give 2019-11-08 at day precision, while **W92** retains an old 'day not established' uncertainty. **Sources row 588 (src-039)** records newer date evidence. Other flagged date/uncertainty combinations are review candidates, not automatically invalid notes.

### Apply this theme-only update locally

Extract the ZIP and merge its `theme` folder into the existing `HvN-Space` folder, replacing matching theme files. Keep `.env`, plugin, scripts, uploads and database volumes. From Ubuntu/WSL in that same project folder:

```bash
chmod -R a+rX theme
docker compose restart wordpress
```

Refresh a song page (Ctrl+F5 if necessary). **No data reimport is required.** The public catalog remains ten artists / 128 songs. The current full-song Spotify option is unchanged. A separate short sample remains pending verification of supported playback/source access.

Theme ZIP SHA-256: `7ed7b087e6d335c3b2c99cac960b5dead36914acb69a6ef87da1d7faaf2512ed`  
QA map SHA-256: `cabea2492cac7ed276480e3b9a2b825a2fb175c767dc8894ce1fdb41b4d4d767`

## Earlier downloads

[Theme 0.5.2 — About as the introduction](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_theme_update_0.5.2.zip)

[Full local update — Core 0.10.1 / Theme 0.5.1](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_local_update_Core_0.10.1_Theme_0.5.1.zip)

The old full update is superseded for new imports. Use the cleaned canonical update at the top of this page. The full package contains plugin, theme, helper scripts and local instructions. Back up before applying data changes and review import conflicts. These downloads do not deploy a production website.