**Later combined update:** [Core 0.14.0 / Theme 0.8.0](MOOD_GROUPS.md) adds emotional mood groups and retains all 15 genre families, existing helper interfaces and optional overrides. The report below records the original genre release.

# Shared genre families — Core 0.13.0 / Theme 0.7.0

Source: [`tunetofond-genre-groups-2026-11-14`](https://github.com/richardlhvn/HvN-Space/tree/tunetofond-genre-groups-2026-11-14), based on `5ff38bb`.

[Download the local update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-genre-groups-2026-11-14/downloads/tunetofond_genre_update_Core_0.13.0_Theme_0.7.0.zip) · [Windows/WSL instructions](../downloads/README_GENRE_GROUPS_UPDATE.md) · [Checksum](../downloads/tunetofond_genre_update_Core_0.13.0_Theme_0.7.0.zip.sha256)

The source taxonomy contained 42 distinct populated labels, with 21 used by the current public catalog. The new controlled vocabulary groups these into 15 families suited to this emotional-pop discovery site, preserving useful pop subgenres. It is a navigation layer over the evidence, not a rewrite of the workbook or a certification of official/primary genre classifications.

## Default grouping

| Public family | Original labels included |
| --- | --- |
| Pop | pop, emotional pop, confessional pop, duet pop, pop duet, romantic pop, Norwegian pop, Swedish pop |
| Indie Pop | indie pop |
| Alternative Pop | alternative pop, alt-pop |
| Electronic | electronic, electronic-pop, electropop, electronic emotional pop, emotional electronic pop, synth-pop |
| Dance Pop | dance-pop |
| Acoustic Pop | acoustic pop, acoustic-influenced pop, stripped-back pop |
| Folk Pop | folk-pop |
| Indie Folk | indie folk |
| Singer-Songwriter | singer-songwriter, singer-songwriter pop, alternative singer-songwriter pop, confessional singer-songwriter pop, folk-influenced singer-songwriter pop |
| Country Pop | country pop, country-influenced pop, country-pop influence, country-tinged pop |
| R&B / Soul | alternative R&B, R&B influence, soul-influenced pop, soul-influenced singer-songwriter pop |
| Piano Pop | piano pop |
| Ballads | ballad, pop ballad, pop ballads |
| Chill Pop | chill pop |
| Bedroom Pop | bedroom pop |

The current visible selection uses 11 families overall. Artist browsing has nine populated choices; song browsing has ten. The additional families are present only in retained hidden records, so they do not appear as empty public choices. No hidden record is imported, published or enabled to populate a genre menu. Counts deduplicate records that have multiple source aliases in one family.

## Behavior and compatibility

The plugin’s `genre-groups.php` and `data/genre-groups.json` supply one mapping shared by the theme’s genre dropdowns, profile links, artist captions and saved-artist subtitles. Main directory queries expand a selected family to the corresponding source slugs, then intersect with the existing name/mood/country filters and public visibility. Pagination returns the exact matching set without duplicates.

Legacy directory alias links resolve to the broader family and keep the correct selection visible. Public legacy taxonomy archives redirect temporarily to a populated grouped directory; hidden-only archives retain their existing visibility protections. Unknown legacy filter values retain their source exact-match behavior; they never silently become the entire catalog. A valid empty family stays selected with an honest empty state.

The native WordPress `genre` taxonomy, source term IDs, native REST genre fields and editor assignments remain unchanged for compatibility. The original research JSON, source registries, import baselines, descriptions, dates, Spotify mappings and attribution enums are untouched. The discovery engine still uses its existing moods/themes; this feature does not change recommendation scoring or Spotify behavior.

**Artists → Genre groups** is administrator-only. The form can override how an existing raw label is projected into one of the same 15 families. It writes only the `ag_genre_group_overrides` option and validates every choice before saving. Missing fields preserve existing overrides; choosing default clears an override. Unknown new labels remain available in the editor and are clearly marked for mapping rather than guessed. No initialization script or migration creates terms or changes relationships.

## Validation

| Current check | Result |
| --- | ---: |
| Disposable mapping, aliases, unique counts, hidden-only exclusion, source preservation, permissions, malformed inputs and explicit overrides | 20 passed |
| Browser: every populated family and pagination set; independent raw-assignment comparisons; legacy links; combined filters; profile tags; scope; responsive layouts; admin permissions | 302 passed |
| Legacy taxonomy redirects and selected empty-family behavior | 3 redirects plus empty-state checks passed |
| Functional smoke suite | 41 passed |
| Desktop/mobile automated accessibility | 22 audits passed |
| Public artist/song details and APIs | 1,492 passed |
| Read-only canonical record reconciliation | 811 passed |
| Before/after post/meta/taxonomy/attachment fingerprints and scoped settings | Identical |

The live grouping-override option remained absent during verification: defaults work without database changes. All mapping-write tests used guarded disposable tables; no real catalog mappings were modified for tests. Automated accessibility does not replace manual review. No public deployment, indexing change, artist import or visibility expansion occurred.

## Actual screenshots

[Desktop genre filter](../downloads/genre-groups-desktop.png) · [Mobile filter](../downloads/genre-groups-mobile.png) · [Grouped profile tags](../downloads/genre-groups-profile.png) · [Editor mapping screen](../downloads/genre-groups-admin.png)
