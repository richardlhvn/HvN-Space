# Ten-artist public catalog

Current versions: **Core 0.10.1 / Theme 0.5.1**.

The public site now shows **ten core artists and their 128 reviewed song
selections**. The full stored catalog still contains 53 artists, 560 selections
and 559 distinct selected pages. All records, metadata, IDs, URLs, post statuses,
media, sources and import baselines are retained. No catalog records were deleted
or rewritten to hide them.

The ten artists are Astrid S, Caleb Hearn, Chelsea Cutler, Clara Mae, Elina,
Mokita, Rosie Darling, Sara Kays, Sasha Alex Sloan and SHY Martin. The exact 128
song keys come from the reviewed workbook. The two older selections outside that
review, legacy starter content, and the other 43 artists are hidden publicly.
Little Bit Better retains its canonical page and player; ROSIE can still be
credited without exposing an unfinished ROSIE profile.

## How visibility works

**Artists → Public catalog** manages a separate, reversible visibility setting.
It does not change post status or mark research independently verified. Hidden
records remain available in WordPress admin and to authenticated editors through
the editing API. Use **Preview** to inspect them; ordinary visitor URLs return
404. Public directories, search, homepage queries, saved-card retrieval,
recommendations, related links, taxonomy options and public REST/oEmbed endpoints
respect the selection. Native sitemap post queries are also filtered; local
indexing remains disabled.

Imports or a normal Publish action cannot expand the public selection by
themselves. This protects the ten-artist launch from an accidental all-catalog
import. A small editor-side box identifies whether each record is included or
hidden from the public catalog.

### Add another artist gradually

1. Finish that artist's evidence, approved text, song identity checks and media
   permissions using the existing editor/research workflow.
2. Open **Artists → Public catalog → Grow the directory after review**.
3. Choose the artist and click **Review songs**. Every addition starts unchecked.
4. Select only the songs ready to appear, then click **Include artist and selected
   reviewed songs**. Existing public selections remain included. Shared songs
   reuse their canonical keys/pages, and a shared recording's primary artist must
   already be included.

The artist and selected songs must already have published status and stable
research keys. This control changes visibility only; it does not publish drafts,
invent source evidence or approve picture licenses. Repeat additions do not make
duplicate pages. It also supports adding another reviewed song to an existing
core artist later.

**Restore full catalog visibility** exposes all otherwise-published records
again. Do not use it to add only one new artist. **Use ten-artist core catalog**
resets the selection to the initial reviewed ten/128 without deleting later work.

CLI from `/workspace/HvN-Space`:

```bash
# Preview only (default).
docker compose run --rm cli wp --user=editor eval-file /scripts/set-public-catalog.php
# Enable the initial ten/128 catalog.
docker compose run --rm cli wp --user=editor eval-file /scripts/set-public-catalog.php core
```

## Checks performed

- 32 disposable-database checks passed for exact scope, unchanged records,
  explicit-ID query protection, public APIs, saved cards, oEmbed, editor access,
  hidden-only taxonomy values, controlled expansion, shared recording reuse,
  idempotent additions and restoring full visibility.
- 775 browser/HTTP checks passed: **all 475 excluded artist/song URLs return
  404**, the public API totals are ten artists / 128 songs, approved pages load,
  and public links do not lead to hidden records. This includes the homepage,
  directories, global search and mobile directory rendering.
- All 902 reviewed-content browser checks passed again with the core scope active.
- The accessibility suite now tests public core pages and explicitly requires
  HTTP 200 before auditing, so hidden 404 templates cannot count as song/profile
  accessibility evidence.
- All 22 desktop/mobile accessibility audits passed on those actual public pages.
- A before/after read-only audit confirmed the complete stored catalog and all
  audited post fields, metadata, relationships and statuses remain unchanged.
- The admin control was checked in a real browser: all 53 artists remain
  selectable for editing/review, ROSIE's ten selections can be previewed, and
  additions are all unchecked by default. No extra artist was enabled locally.
  An authenticated editor can preview hidden ROSIE; its ordinary URL remains 404.

Screenshots: ignored `test-results/core-catalog-preview.png`,
`core-catalog-artists.png` and `core-catalog-mobile.png`. The machine-readable
public check summary is `test-results/core-catalog-summary.json`.

## Applying this to the owner's separate site

Update both the plugin and theme to the versions above. Back up the existing
database and uploads privately, and retain `.env` and database volumes. If the
reviewed enrichment is absent there, follow the dry run and protected import in
[the workbook enrichment report](REVIEWED_WORKBOOK_ENRICHMENT_2026-11-13.md) first.
Then enable **Artists → Public catalog → Use ten-artist core catalog** and purge
any site/CDN caches so previously cached full-directory pages are not served.

This cloud change has **not deployed a production website**. The production
domain/Hostinger installation still needs to be identified. Keep staging noindex
until the real go-live. At that point, review the remaining playback/source QA,
contact/privacy/legal details and per-record review/indexing flags before enabling
production indexing. The Sidelove identity/playback check and the other evidence
follow-ups remain documented in the enrichment report; visibility is not proof
that those external checks passed.
