# Encrypted cloud migration download

The owner requested delivery through GitHub. This repository is public, so the database/media archive is encrypted before upload. The plaintext archive and its decryption key are not committed. The key is delivered separately to the owner in the originating private chat.

- File: `tunetofond-cloud-migration-20261114-195450.tar.gz.enc`
- Cipher: OpenSSL AES-256-CBC with a random salt and a randomly generated 256-bit-entropy passphrase.
- Password derivation: PBKDF2-HMAC-SHA256, 600000 iterations.
- Download integrity: verify the accompanying `.enc.sha256` file.
- After decryption, the original archive SHA-256 must be `36e08134019b203d081a0736d2f33ace21f3629569d8d0dde1a38858bc78fa4f`.
- The original archive includes its own `SHA256SUMS` for the payload.

Store the encrypted download and decrypted archive in the hosting account's private home directory, outside `public_html`. Keep the current SSH session and its WordPress CLI compatibility function active. Do not place the decryption key in a URL, shell command argument, Git commit, issue or public log; enter it at OpenSSL's password prompt.

```bash
tf_bundle="tunetofond-cloud-migration-20261114-195450"
tf_download="https://raw.githubusercontent.com/richardlhvn/HvN-Space/tunetofond-tag-sync-2026-11-14/downloads"
mkdir -p "$HOME/tunetofond-transfer" &&
chmod 700 "$HOME/tunetofond-transfer" &&
cd "$HOME/tunetofond-transfer" &&
curl -fL "$tf_download/${tf_bundle}.tar.gz.enc" -o "${tf_bundle}.tar.gz.enc" &&
curl -fL "$tf_download/${tf_bundle}.tar.gz.enc.sha256" -o "${tf_bundle}.tar.gz.enc.sha256" &&
sha256sum -c "${tf_bundle}.tar.gz.enc.sha256"
```

Decrypt, entering the separately provided key at the password prompt:

```bash
umask 077
openssl enc -d -aes-256-cbc -pbkdf2 -iter 600000 -md sha256 -in "${tf_bundle}.tar.gz.enc" -out "${tf_bundle}.tar.gz"
```

Verify the original archive before extracting or running it:

```bash
printf '%s  %s\n' '36e08134019b203d081a0736d2f33ace21f3629569d8d0dde1a38858bc78fa4f' "${tf_bundle}.tar.gz" | sha256sum -c - &&
tar -xzf "${tf_bundle}.tar.gz" &&
cd "$tf_bundle" &&
bash restore-hostinger.sh --check --wp-root "$HOME/domains/tunetofond.com/public_html" &&
bash restore-hostinger.sh --apply --wp-root "$HOME/domains/tunetofond.com/public_html"
```

The installer first checks that the destination is fresh, backs up its original database/configuration, preserves all source IDs/content/relationships/visibility, creates a separate production administrator and verifies all 1419 source post/meta/relationship fingerprints. The cloud-only credentials are rotated on the destination. The source cloud installation is unchanged. Indexing remains disabled for the hosted review. The production administrator's generated credentials are written only to the private path printed by the installer, not to GitHub.
