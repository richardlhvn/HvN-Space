# Transfer the cloud site to tunetofond.com

The owner selected the cloud installation as the migration source. The desktop/local copy is independent. This procedure uses the complete cloud database, active custom theme/plugin, and every upload; it preserves IDs, relationships, source evidence, image rights, hidden records, and prior content/tag changes.

The source has 53 stored artists and 560 song posts. The public selection remains ten artists / 128 songs, with 475 artist/song records excluded. These are database counts, distinct from the 558 selected song rows in the master workbook. Current code is Core 0.15.0 / Theme 0.9.0. The source WordPress core is 7.1.3; the destination's newer core is retained.

## Destination preparation

1. Use the fresh WordPress installation already created for `tunetofond.com`. In WordPress, open **Settings → Reading**, select **Discourage search engines**, and save. This prevents indexing requests; it is not an access restriction. Use the host's staging/access controls if a private preview is needed.
2. In Hostinger hPanel, open the website's **Dashboard → Advanced → SSH Access** and enable access. Record the host/IP, port and username. The account's comparison table includes SSH/WP-CLI on Premium, Unlimited and Cloud plans.
3. Find the WordPress document root; a common path is `$HOME/domains/tunetofond.com/public_html`. Confirm it contains `wp-load.php` and the new site's configuration. Use the actual path shown by Hostinger.
4. Upload the private migration `.tar.gz` and checksum file into a private directory in the hosting account's home directory, **outside every `public_html` directory**. The archive contains the database and must never be committed to GitHub or placed in a public download folder.

## Check and apply

Connect using the exact SSH command shown in hPanel. The example paths below are placeholders; use the real archive filename and document root.

```bash
mkdir -p "$HOME/tunetofond-transfer"
chmod 700 "$HOME/tunetofond-transfer"
cd "$HOME/tunetofond-transfer"
# Upload the archive/checksum into this directory before continuing.
sha256sum -c tunetofond-cloud-migration-TIMESTAMP.tar.gz.sha256
tar -xzf tunetofond-cloud-migration-TIMESTAMP.tar.gz
cd tunetofond-cloud-migration-TIMESTAMP
bash restore-hostinger.sh --check --wp-root "$HOME/domains/tunetofond.com/public_html"
```

The check verifies file hashes and the target domain. It refuses existing custom content or a database table-prefix collision. It makes no import changes. It cannot run against the `localhost` cloud source.

Apply the already-reviewed transfer:

```bash
bash restore-hostinger.sh --apply --wp-root "$HOME/domains/tunetofond.com/public_html"
```

The script asks for a new production administrator username and your private administrator email. It backs up the destination configuration/database outside the web root before importing. It retains Hostinger's database connection details, WordPress core and authentication salts; sets the source table prefix; copies the custom code/media; and uses WordPress CLI's serialized-data-safe URL replacement. GUIDs and post IDs are preserved. All source post/meta/relationship hashes, taxonomy data and catalog/theme/visibility settings must match the expected source after the URL change.

The source-only cloud account's passwords, sessions and application passwords are rotated on the destination. A separate production owner account is created. Its new random password is saved only in the reported private `production-admin.json` file, mode 600; the script does not print it. Read that file yourself in the private SSH session and use the new production account at `https://tunetofond.com/wp-admin/`. The cloud installation and its login remain unchanged.

An error after database import triggers restoration of the destination's original configuration/database. When the prefix changes, only the exact newly introduced source tables are removed after the original configuration is restored. Copied custom files remain inactive after rollback. Review any rollback errors before retrying rather than deleting a database. Re-running a successful exact migration exits without overwriting later work.

## Verify the hosted result

- Verify HTTPS, canonical `.com` URLs and the homepage, artist/song directories, an artist page, a song page, source links and photo credits.
- In WordPress **Settings → Permalinks**, confirm **Post name** and save once if the CLI reports that regenerating `.htaccess` needs server configuration. This uses the actual web-server context while retaining Hostinger's own directives.
- Confirm ten public artists / 128 songs, all retained hidden records, simplified 15 genres / 20 feelings / 20 themes, reader cleanup, and existing IDs/relationships.
- Test mobile navigation, search/filter combinations, saved items and consent-based Spotify players. Browser-saved collections remain stored per browser origin; a localhost collection does not automatically transfer to the public domain.
- Set the real public contact/operator details and finish the privacy/contact/copyright pages. The production administrator email is private and is not automatically made a public contact address.
- Purge the existing hosting/CDN caches after transfer, using hPanel's **Clear cache** action and CDN purge if available. Configure/test backups and caching. Keep administrator/API/nonce responses out of inappropriate page caching. Review the live site's appearance and behavior before enabling indexing.
- Indexing remains disabled. Existing per-record review/noindex flags remain unchanged. Migration does not add analytics, ads, a new artist intake flow, or a 15-second sample player.

Keep the package and rollback copy private, then remove temporary uploaded migration files from the hosting account when a separate verified backup exists. Preserve the original source backup privately.
