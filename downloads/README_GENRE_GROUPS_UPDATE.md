# tunetofond genre groups — Core 0.13.0 / Theme 0.7.0

This update groups the existing labels into **15 shared navigation families**. It preserves the original research, genre term IDs/assignments, moods, countries, artist/song identities, media rights and public ten/128 selection. Earlier reader cleanup and rebranding are included.

## Apply to your existing Windows/WSL project

1. Download and extract `tunetofond_genre_update_Core_0.13.0_Theme_0.7.0.zip` into a separate folder.
2. Merge its **plugin** and **theme** folders into your existing HvN-Space folder (the one with `compose.yaml` and `.env`). Replace matching source files. Copy both folders, including the new `plugin/genre-groups.php` and `plugin/data/genre-groups.json`. Keep `.env`, uploads and Docker volumes.
3. In Ubuntu/WSL, from that same project folder:

   ```bash
   chmod -R a+rX plugin theme
   docker compose restart wordpress
   ```

4. Open **http://localhost:8080** and press **Ctrl+F5**. Check the genre dropdown on Artists and Songs, the genre tags on profiles, and the labels below artist cards.
5. WordPress **Artists → Genre groups** shows the original labels, their default families and optional overrides. Choosing a different family changes navigation only. **Use default grouping** clears that label’s override. New unrecognized labels remain available to editors and can be mapped here; they are not guessed into an unrelated family.

**No import, branding migration, database reset or npm installation is needed.** This feature reads existing genre assignments. The plugin/theme versions in WordPress should be **0.13.0 / 0.7.0**.

## The 15 families

Pop; Indie Pop; Alternative Pop; Electronic; Dance Pop; Acoustic Pop; Folk Pop; Indie Folk; Singer-Songwriter; Country Pop; R&B / Soul; Piano Pop; Ballads; Chill Pop; Bedroom Pop.

Examples: electropop/electronic-pop/synth-pop → Electronic; country-influenced/country-tinged/country-pop influence → Country Pop; emotional/confessional/romantic/duet/Norwegian/Swedish pop → Pop. Country, moods and themes remain separate filters.

The retained catalog has 42 source labels. All map to these 15 families. The current public ten/128 selection uses 11 families overall: nine are available for Artists and ten for Songs. Each dropdown shows only families actually present in that directory. Hidden records do not create empty public options. A valid family requested explicitly with no matches remains selected and shows an empty result.

Older `filter_genre` label links resolve to the same broader family; public legacy `/genre/.../` links redirect to a populated grouped directory. WordPress’s original genre taxonomy/editor/REST term IDs remain compatible and retain the source labels. Grouped card labels, profile tags, native directory filters, saved artist subtitles and optional navigation overrides use the shared mapping.

This is a local source update. No production deployment, visibility expansion, indexing change or new artist import occurred. Credentials, workbooks, SQL backups, uploads, dependencies and runtime state are excluded.
