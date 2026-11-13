# Afterglow local downloads

## Research cleanup handoff

[Download the cleanup helper pack](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/local-update-2026-11-13/downloads/Afterglow_cleanup_helper_pack_2026-11-13.zip)

[Read/copy the full cleanup prompt](downloads/Afterglow_research_cleanup_prompt.md)

Give another research assistant your **latest Afterglow_Master_Worksheet.xlsx** plus this helper ZIP, then use the included `PROMPT_CLEANUP.md`. The full master workbook is not included or replaced. The pack contains the QA map, actual example fields and evidence references, a worked-review guide, correction/unresolved/readiness templates, and reference handoff documents. It contains no executable setup scripts or credentials.

Scope: clean supporting research for the approved ten artists / 128 selections, preserving approved About wording, IDs, source scope, later edits and the hidden larger catalog. Resolve only evidence-supported contradictions; keep unresolved QA internal. No deployment or automatic imports.

## Latest: Theme 0.5.3 — public details, internal QA

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

Use the full update first if the reviewed ten-artist catalog is not installed locally yet, then apply the latest theme update above. The full package contains plugin, theme, helper scripts and local instructions. Back up before applying data changes and review import conflicts. These downloads do not deploy a production website.