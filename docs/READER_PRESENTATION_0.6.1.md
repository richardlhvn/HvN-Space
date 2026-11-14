# Cleaner reader presentation — Theme 0.6.1

Current source: [`tunetofond-reader-cleanup-2026-11-14`](https://github.com/richardlhvn/HvN-Space/tree/tunetofond-reader-cleanup-2026-11-14), based on `a192eb1`. Core remains 0.12.0; no plugin, data or database migration is required.

[Download the theme update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-reader-cleanup-2026-11-14/downloads/tunetofond_theme_update_0.6.1.zip) · [Local instructions](../downloads/README_THEME_0.6.1.md) · [Checksum](../downloads/tunetofond_theme_update_0.6.1.zip.sha256)

The owner requested removing the visible editorial/workflow messages in the supplied four screenshots, replacing the branded recommendation prefix with the existing icon, and omitting the “Artist statement · paraphrased” badge because citations already accompany the narrative.

## Result

The presentation helper removes only the exact known site-editorial endings from the current nine affected biographies, plus the generated selection boilerplate. It preserves factual biography text, inline formatting, custom sections, source links and quoted passages. SHY Martin’s biography has no matching editorial ending and retains its factual paragraphs. Related-artist cards no longer render their stored editorial pairing/QA notes. All related identities and photo rights remain.

About the Song renders its existing prose and citations directly under the heading, without a generated attribution badge. Attribution enums, source scope and internal editorial evidence remain in the editor/database. No narrative was rewritten, and no quotation, artist statement, source, release fact or listening claim was invented.

The discovery renderer uses `ag_icon('spark')` before the actual theme/feeling value. A screen-reader-only “Theme”/“Feeling” label preserves category meaning; the SVG is decorative and adds no focus target or link. The same renderer is used for the primary next-song card and inline-filtered recommendation cards. Discovery ranking/filter logic is unchanged. The editorial-tags explanatory note and generic editor’s-pick starter boilerplate are also omitted from reader presentation.

Artist/song public REST and RSS use the same content cleanup. Authenticated editor REST context=edit retains original content and excerpts. Copyright/privacy notices, source citations, readable release precision, photo creator/license/modification credits, player consent/fallbacks and noindex flags remain.

## Current validation

| Check | Passed |
| --- | ---: |
| Read-only exact boilerplate removal, formatting/custom/quoted/source text preservation, tag escaping/accessibility and stored evidence | 32 |
| Reader cleanup across ten artists/128 songs, public APIs, citations, actual icons, desktop/mobile layout and inline-filter iframe preservation | 973 |
| Public artist/song details and APIs | 1,492 |
| Approved copy, source links, exact Spotify mappings, dates, consent and filter behavior across all 128 songs | 1,158 |
| Public/editor REST/RSS/date-precision checks | 17 |
| General functional smoke checks | 41 |
| Desktop/mobile automated accessibility audits | 22 |
| Before/after stored artist/song/attachment metadata/taxonomy fingerprints and scope/settings | Identical |

Automated accessibility is not a replacement for manual review. Actual Spotify audio was not newly verified, and no separate short-sample feature was added. The historical all-catalog test fixtures were not imported or run. No production deployment, indexing change, data import or migration occurred.

## Actual screenshots

- [Artist biography](../downloads/reader-cleanup-artist-desktop.png)
- [Related artists and retained photo credits](../downloads/reader-cleanup-related.png)
- [Song introduction and citations](../downloads/reader-cleanup-song-desktop.png)
- [Sparkle recommendation tags](../downloads/reader-cleanup-tags.png)
- [Mobile song page](../downloads/reader-cleanup-song-mobile.png)

All captures are from the running WordPress pages, before loading an external player. They are not generated mockups or simulated Spotify playback.
