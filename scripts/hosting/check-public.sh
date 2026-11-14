#!/usr/bin/env bash
# Anonymous, read-only HTTP checks; no WordPress/database or indexing changes.
set -euo pipefail
tf_base="${1:-https://tunetofond.com}"
tf_base="${tf_base%/}"
tf_tmp="$(mktemp -d)"
trap 'rm -rf "$tf_tmp"' EXIT
tf_checks=0
tf_failures=0
tf_request() {
  curl --silent --show-error --location --max-time 30 \
    --dump-header "$tf_tmp/headers" --output "$tf_tmp/body" \
    --write-out '%{http_code}' "$tf_base$1"
}
tf_check_status() {
  local tf_path="$1" tf_expected="$2" tf_actual
  tf_actual="$(tf_request "$tf_path")" || { printf 'FAIL: request failed: %s\n' "$tf_path"; tf_failures=$((tf_failures+1)); return; }
  if [[ "$tf_actual" == "$tf_expected" ]]; then
    printf 'PASS: HTTP %s %s\n' "$tf_actual" "$tf_path"; tf_checks=$((tf_checks+1))
  else
    printf 'FAIL: HTTP %s %s (expected %s)\n' "$tf_actual" "$tf_path" "$tf_expected"; tf_failures=$((tf_failures+1))
  fi
}
for tf_path in / /artists/ /songs/ /artists/astrid-s/ /songs/astrid-s-think-before-i-talk/ /saved/ /contact/ /privacy/ /copyright/; do
  tf_check_status "$tf_path" 200
done
tf_check_status /artists/sadie-jean/ 404
for tf_kind in artist song; do
  if [[ "$tf_kind" == artist ]]; then tf_expected=10; else tf_expected=128; fi
  tf_status="$(tf_request "/wp-json/wp/v2/$tf_kind?per_page=1&_fields=id")" || { printf 'FAIL: public %s API request failed\n' "$tf_kind"; tf_failures=$((tf_failures+1)); continue; }
  tf_total="$(awk 'tolower($1)=="x-wp-total:" {gsub("\r", "", $2); print $2; exit}' "$tf_tmp/headers")"
  if [[ "$tf_status" == 200 && "$tf_total" == "$tf_expected" ]]; then
    printf 'PASS: anonymous public %s total = %s\n' "$tf_kind" "$tf_total"; tf_checks=$((tf_checks+1))
  else
    printf 'FAIL: public %s API HTTP %s, total %s (expected %s)\n' "$tf_kind" "$tf_status" "${tf_total:-missing}" "$tf_expected"; tf_failures=$((tf_failures+1))
  fi
done
tf_status="$(tf_request /)" || tf_status=000
if [[ "$tf_status" == 200 ]] && grep -Eiq '<meta[^>]*robots[^>]*noindex' "$tf_tmp/body"; then
  echo 'PASS: homepage retains the pre-launch noindex setting'; tf_checks=$((tf_checks+1))
else
  echo 'FAIL: could not confirm the homepage noindex setting'; tf_failures=$((tf_failures+1))
fi
printf '\n%s checks passed; %s failed.\n' "$tf_checks" "$tf_failures"
echo 'This checks HTTP routes, public catalog totals and indexing only. Browser/player, mobile and final public-page content review are separate.'
((tf_failures == 0))
