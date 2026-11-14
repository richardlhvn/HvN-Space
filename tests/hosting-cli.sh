#!/usr/bin/env bash
set -euo pipefail
mkdir -p /tmp/tf-cli-restricted
printf '%s\n' 'disable_functions=proc_open,proc_close,proc_get_status,proc_terminate,exec,shell_exec,passthru,popen' > /tmp/tf-cli-restricted/99-process.ini
export PHP_INI_SCAN_DIR=:/tmp/tf-cli-restricted
if wp --info >/tmp/tf-cli-denied.log 2>&1; then
  echo 'Restriction fixture did not activate.' >&2
  exit 1
fi
shopt -s expand_aliases
alias wp='false'
source /scripts/hosting/cli-session.sh
wp --info
# The migration runs in a child Bash shell and must inherit the compatibility function.
bash -ec 'wp --path=/var/www/html --skip-plugins --skip-themes db prefix; wp --path=/var/www/html --skip-plugins --skip-themes db export /tmp/tf-cli-private-check.sql --single-transaction --skip-lock-tables --no-tablespaces=1'
wp --path=/var/www/html --skip-plugins --skip-themes eval 'if(function_exists("shell_exec")||function_exists("passthru")||function_exists("popen"))throw new RuntimeException("Unrelated restrictions changed.");WP_CLI::success("Unrelated restrictions remain in WP-CLI.");'
php -r 'if(function_exists("proc_open")||function_exists("proc_close")||function_exists("exec")||function_exists("shell_exec"))exit(1);echo "Default PHP restrictions remain unchanged.\n";'
echo 'PASS: Reproduced Hostinger error; session-only WP-CLI info/database export and inherited commands work, with default PHP restrictions retained.'
