# Seven additional artist research collections

Core 0.4 bundles the owner's seven additional Markdown handoffs dated 26 October 2026. Only the JSON research blocks are imported as data. Suggestions or instructions elsewhere in the documents are not executed. The original artist facts, source IDs, uncertainties, and null fields are retained in `plugin/data/`.

| Artist | Curated songs | Status |
| --- | ---: | --- |
| shy martin | 10 | Source-linked research; review pending |
| Clara Mae | 10 | Source-linked research; former website excluded |
| Elina | 10 | Source-linked research; website unverified |
| Chelsea Cutler | 10 | Source-linked research; website unverified |
| Mokita | 10 | Source-linked research; most meanings unavailable |
| Rosie Darling | 10 | Source-linked research; some release dates unavailable |
| Sasha Alex Sloan | 10 | Source-linked research; year/edition precision retained |

Together with Astrid S, Sara Kays, and Caleb Hearn, the bundled catalog contains **ten artist profiles and 100 selected song records**. These are editorial starter selections, not Spotify Popular rankings. All 77 new Spotify URLs (seven artists and seventy tracks) returned matching titles through Spotify's public oEmbed endpoint on 27 October 2026. This does not independently verify artist credits, biographical facts, source interviews, release dates, or playback availability by market. Evidence for those fields is attributed to the supplied research.

## Content decisions

- Clara Mae's `official_website` remains empty. The research says the former `claramaemusic.com` domain has unrelated gambling content. Its `website_current_status` source is retained in the research registry but excluded from rendered source/website links.
- Unknown websites, precise release days, photo licenses, and song meanings remain empty. In particular, a dated recording is not treated as proof of its lyrical meaning.
- Country, genre/mood taxonomy, biographies, Spotify URLs, source links, recording credits, and hand-curated relationships use the supplied fields. Similar artists are connected only to profiles that already exist; no extra unsourced biographies are generated.
- Existing starter IDs and URLs are reused where records match. The SHY Martin starter song `sad songs` is not in this supplied selection and is retained separately; unused older drafts are not deleted. Selected recording counts do not include those retained starter records.
- Imported pages retain their review/noindex flag. Abstract artwork remains the default. No photos, audio, lyrics, ads, or analytics are imported.

## Apply this update locally

Follow the same process as the [previous update](RESEARCH-UPDATE.md):

1. Download and extract the latest GitHub ZIP into a separate folder.
2. Copy **plugin/**, **theme/**, and **scripts/** contents into the matching folders of your existing project. Include every new JSON file under **plugin/data/**. Keep `.env`, your database, and uploads volumes.
3. In Ubuntu, from the existing project folder, run:

   ```bash
   bash scripts/backup.sh
   docker compose restart wordpress
   ```

4. In WordPress, open **Artists → Import supplied research**. The table now covers 110 records: ten artists and one hundred songs. Review the planned Create, Update, or Preserve actions.
5. Leave **Show imported pages on the site for review** checked if you want the imported pages visible locally, then click **Apply supplied research**.

Already imported, unchanged records can be refreshed safely; edited or trashed records are preserved. The import does not run automatically when replacing plugin code. A protected artist's biography/recommendations need manual review before merging new content. Source material remains available in its bundled JSON file.

## Validation

The disposable-database suite now runs **445 checks** across all ten packages: source mapping, stable IDs, 100 artist-song relationships, credits, precision-preserving dates, transcript withholding, repeat imports, existing edits, Trash preservation, excluding the former Clara Mae domain, and missing interpretations. The public browser suite derives filter counts from the current catalog so it supports both the original starter data and the expanded collection. Admin import, new profiles, collaborators, review flags, and mobile layout are checked in the running preview.

These checks validate the current cloud instance. Your Windows installation is updated only after you copy the code and apply the import. No public deployment is performed.
