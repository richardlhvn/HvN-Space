# tunetofond emotional moods — Core 0.14.0 / Theme 0.8.0

This is the combined update: it adds simplified emotional moods and retains the 15 genre families, reader cleanup and rebranding from the previous updates. Use this ZIP instead of installing the earlier genre ZIP separately.

## Update your Windows/WSL local site

1. Extract `tunetofond_mood_update_Core_0.14.0_Theme_0.8.0.zip` into a separate folder.
2. Merge **plugin** and **theme** into your existing HvN-Space project, replacing matching source files. Copy both complete folders, including the new `taxonomy-groups.php`, `mood-groups.php` and `data/mood-groups.json`. Keep `.env`, uploads, existing accounts and Docker volumes.
3. In Ubuntu/WSL, from your existing project folder:

   ```bash
   chmod -R a+rX plugin theme
   docker compose restart wordpress
   ```

4. Open **http://localhost:8080** and press **Ctrl+F5**. The homepage feeling links, directory mood filters, profile tags and song discovery **Feeling** selector now use the same emotion groups.
5. Optional: WordPress **Artists → Mood groups** lets an administrator adjust how an existing source label maps to an emotion. **Artists → Genre groups** remains available. These controls preserve the original research labels and term assignments.

**No import, branding migration, database reset, npm installation or new credentials are needed.** This reads existing mood tags and projects them into broader navigation groups. Existing custom genre mappings are preserved. Versions should show **Core 0.14.0 / Theme 0.8.0**.

## Twenty emotional moods

Heartbreak, Reflection, Playful, Hopeful, Nostalgia, Love & Affection, Longing, Melancholy, Vulnerability, Comfort, Confidence, Anxiety, Mixed Feelings, Resilience, Letting Go, Calm, Dreamy, Energetic, Intense, Bittersweet.

Examples: heartbroken/hurt → Heartbreak; reflective/introspective/regretful → Reflection; cheeky/wry/carefree → Playful; comforting/reassuring → Comfort. The 64 public mood labels now share these 20 emotion groups. All 20 are present among the public songs; the artist directory shows its nine populated families.

The same grouping powers **Same feeling** recommendations, so related source labels can connect songs even when their exact old wording differed. Theme/topic filters remain separate. Existing mood/feeling URLs continue to work through their aliases, and filtering preserves the loaded Spotify iframe.

Musical/context descriptors such as melodic, atmospheric and late-night remain in the research without being guessed into an emotion. They occur only in retained hidden records in this staging catalog. The homepage’s former empty late-night filter link now goes to Reflection; the main feeling shortcuts are Heartbreak, Playful, Hopeful and Reflection.

The public scope remains ten artists / 128 songs. Original research, mood/genre term IDs and assignments, facts, photos/licenses, source citations, Spotify URLs, indexing and saved items are preserved. No production deployment occurred. The ZIP excludes private workbooks, credentials, SQL backups, uploads, dependencies and runtime state.
