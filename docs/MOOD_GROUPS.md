**Current policy:** [Core 0.15.0 / Theme 0.9.0](TAG_SYNC.md) normalizes stored genre/mood/theme tags and active source arrays, following the owner’s later instruction. The Sheet/XLSX update is deferred. The report below describes its original release.

# Emotional mood groups — Core 0.14.0 / Theme 0.8.0

Source: [`tunetofond-mood-groups-2026-11-14`](https://github.com/richardlhvn/HvN-Space/tree/tunetofond-mood-groups-2026-11-14), based on `127fce0`. The owner clarified that the requested simplification primarily concerned emotions such as heartbreak, reflection and playful feelings, while also retaining the simplified genres.

[Download the combined update](https://github.com/richardlhvn/HvN-Space/raw/refs/heads/tunetofond-mood-groups-2026-11-14/downloads/tunetofond_mood_update_Core_0.14.0_Theme_0.8.0.zip) · [Local instructions](../downloads/README_MOOD_GROUPS_UPDATE.md) · [Checksum](../downloads/tunetofond_mood_update_Core_0.14.0_Theme_0.8.0.zip.sha256)

## Controlled emotion vocabulary

The retained catalog has 72 populated mood labels, 64 used by public records. Sixty-nine source labels map to 20 emotion families; all 64 public labels are covered. The remaining three contextual/musical descriptors are deliberately not classified as emotions. The song directory and discovery selector have 20 populated groups; the artist directory has nine.

| Emotion | Source mood labels |
| --- | --- |
| Heartbreak | heartbreak, heartbroken, hurt |
| Reflection | reflective, introspective, self-aware, confessional, candid, regretful |
| Playful | playful, cheeky, wry, carefree |
| Hopeful | hopeful, uplifting |
| Nostalgia | nostalgic, wistful |
| Love & Affection | romantic, affectionate, tender, warm, appreciative |
| Longing | longing, romantic longing, yearning |
| Melancholy | sad, melancholic, somber, sorrowful, grieving, lonely, excluded |
| Vulnerability | vulnerable, raw, intimate, heartfelt |
| Comfort | comforting, reassuring, compassionate, empathetic |
| Confidence | confident, assertive, bold, defiant |
| Anxiety | anxious, uneasy, unsettled, restless, self-conscious, cautious |
| Mixed Feelings | conflicted, uncertain, hesitant |
| Resilience | resilient, resolute, determined |
| Letting Go | accepting, resigned, cathartic |
| Calm | calm, restrained |
| Dreamy | dreamy |
| Energetic | energetic, upbeat, euphoric, summer energy |
| Intense | intense, dramatic |
| Bittersweet | bittersweet |

**melodic, atmospheric and late-night** remain source evidence/context without an automatic emotional assignment. Their current records are hidden; no public record loses its existing emotional coverage. Unknown new labels are also retained for editor review rather than guessed from biography, genre or unverified listening.

## Consistent browsing and recommendations

A shared grouping engine supports the existing 15 genre families and new emotion families while preserving all previous `ag_genre_*` helper interfaces and existing genre override settings. Mood aliases live in `plugin/data/mood-groups.json`; no source taxonomy terms or post assignments are created, deleted or renamed.

The directory mood filters, profile tags, song-row mood labels and starter-note fallback labels use the same projection. Name, grouped genre, country and mood continue to intersect. Group counts deduplicate records that have multiple synonymous source tags. Legacy `filter_mood` aliases normalize to the chosen family; public legacy mood taxonomy archives redirect into grouped browsing without weakening hidden-record protection. Unknown filters keep exact source matching; a selected valid empty family shows an honest empty state.

Discovery builds its in-memory mood index from these same groups. Thus “heartbroken” and “hurt” can match **Same feeling** even when their original tags differ. Explicit emotional filters require a matching family, while themes/topics, curated relations, visibility and recent-track exclusion retain their existing roles. Scoring weights and the official player are unchanged; group matching is intentionally broader than exact raw-tag matching. Legacy `ag_feeling` aliases normalize before validation. Inline and no-JavaScript forms both work, and filtering preserves the existing iframe.

Homepage shortcuts now reach actual Heartbreak, Playful, Hopeful and Reflection results. The old empty late-night destination is replaced by Reflection, without pretending the stored contextual descriptor is itself an emotion. Existing layout, fonts and four pill colors are retained.

**Artists → Mood groups** provides optional administrator-controlled mappings, separately stored from genre overrides. Save handlers require administrator capability, POST and nonce; invalid mappings are rejected before writes. Default browsing requires no stored options or migration. Original research, raw labels and IDs, relationships, import baselines, source references, rights, dates, links, publication scope and indexing remain intact. Native WordPress REST taxonomy IDs/source labels remain compatible; public UI families do not overwrite the evidence.

## Current validation

| Check | Passed |
| --- | ---: |
| Disposable mood families, synonyms, non-emotion handling, hidden scope, grouped recommendation matching, legacy values, permissions, overrides and source preservation | 27 |
| Mood browser: every populated family/pagination set, independent source comparisons, profile tags, aliases, combined filters, home links, responsive layout, inline matching/iframe preservation, no-JS and admin permissions | 427 |
| Existing genre mapping/query/override/preservation checks against shared engine | 20 |
| Existing genre browser suite | 302 |
| General smoke workflow | 41 |
| Desktop/mobile automated accessibility | 22 audits |
| Approved song copy, citations, exact Spotify links, dates, consent and filtering across all 128 songs | 1,158 |
| Public artist/song details and APIs | 1,492 |
| Branding/responsive/admin scope checks | 252 |
| Read-only canonical reconciliation | 811 |
| Before/after source post/meta/term relationships/attachment fingerprints and scoped settings | Identical |

No real grouping override options were saved during validation. Write tests ran in guarded disposable table namespaces, without importing the other 43 artists. Automated accessibility does not replace manual review. No cloud audio verification, separate short-sample feature, production deployment, indexing change or data import/migration occurred.

## Actual screenshots

[Homepage feeling links](../downloads/mood-groups-home.png) · [Emotional song filter](../downloads/mood-groups-directory.png) · [Mobile filter](../downloads/mood-groups-mobile.png) · [Discovery selector](../downloads/mood-groups-discovery.png) · [Editor mood mappings](../downloads/mood-groups-admin.png)

Captures are from the running WordPress site; no generated mockup or simulated playback is presented as live audio.
