# Contact page update — 15 November 2026

The owner requested a finished Contact page after verifying two-way delivery for `contact@tunetofond.com`. The former generic page displayed setup instructions. The new page uses a large introduction, a lavender inbox panel with a visible email address, and three sections for recommendations, corrections, and artist/rights enquiries. Each email link includes a relevant subject. There is no form, new service, tracker, or response-time promise.

`theme/page-contact.php` is selected by WordPress's normal slug template hierarchy. Its stylesheet is loaded only on Contact; shared page styles remain unchanged. The introduction and title still come from the existing WordPress page. New seed data uses the finished introduction. The established public email Customizer setting remains authoritative.

## Hosted update

Cloud has the update. Hosting has not yet been updated or verified. Do not repeat the full database migration for this change.

`downloads/tunetofond-contact-update.php` is generated with `python scripts/package-contact-update.py`. It contains only the two public theme files and a guarded WP-CLI updater. It checks the site/theme, the original or already-updated Contact body, the configured email, and the absence of conflicting Contact files. It preserves owner edits by refusing unexpected values.

Run from the owner's existing Hostinger SSH session, using the restricted-CLI helper already downloaded during migration. Download the update from the approved GitHub commit to `$HOME/tunetofond-transfer/contact-update.php`. Then:

```bash
source "$HOME/tunetofond-transfer/cli-session.sh" &&
wp --path="$HOME/domains/tunetofond.com/public_html" eval-file "$HOME/tunetofond-transfer/contact-update.php" "$HOME/tunetofond-transfer" check --use-include &&
wp --path="$HOME/domains/tunetofond.com/public_html" eval-file "$HOME/tunetofond-transfer/contact-update.php" "$HOME/tunetofond-transfer" --use-include
```

Use `--use-include`: WP-CLI's default magic-path preprocessing can interfere with the embedded release payload. Both check and apply are verified with include mode. The backup parent must already exist, be writable, and be outside `public_html`. The updater saves the previous Contact text/title, public email state, and prior file state in a private `before.json`, with directory mode 0700 and file mode 0600. Only the Contact body, its public email, and the two new Contact files change; all other catalog records, account settings and indexing settings are retained. New theme files have mode 0644. Repeating the exact patch exits without changes. Errors trigger restoration of Contact text/email and removal of newly installed files.

After success, clear hPanel/CDN caches and inspect `https://tunetofond.com/contact/`, including phone layout and the email links. This patch does not complete Privacy/Copyright text or enable search indexing.

For a manual rollback, use the reported `before.json`: restore the Contact body's `post_content` for `page_id`, restore or remove `ag_contact_email` according to `had_contact_email`, and restore/remove only the two file paths according to `previous_files`. Preserve any subsequent owner edits first. Do not import the old full-site migration database.

## Verification

- Cloud browser checks at 1440, 768, 390 and 320 pixels: HTTP 200, one H1, no setup copy, valid email subjects, no horizontal overflow or JavaScript errors.
- Automated WCAG 2 A/AA and 2.1 AA checks pass at all four sizes. Desktop and phone screenshots were visually inspected.
- Packaged updater: dry-run, already-installed repeat, and fresh theme file installation in a writable fixture, including mode 0644 checks.
- Current environment cannot reach the public Hostinger site (HTTP proxy rejects the connection). Hostinger plugin setup was found under the website dashboard's **Connect your coding agent → Codex → Install**, but no Hostinger tools are currently callable here. Do not claim live deployment from cloud-only checks.
