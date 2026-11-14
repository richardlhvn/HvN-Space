# Source in the SSH Bash session: source cli-session.sh
# Limit the compatibility change to WP-CLI processes; keep website PHP unchanged.
tf_prepare_cli_session() {
    export TF_CLI_PHP_BIN TF_CLI_WP_FILE TF_CLI_DISABLED
    TF_CLI_PHP_BIN="${TF_CLI_PHP_BIN:-$(php -r 'echo PHP_BINARY;')}"
    TF_CLI_WP_FILE="$(readlink -f "$(type -P wp)")"
    if [[ ! -f "$TF_CLI_WP_FILE" ]] || ! head -c 256 "$TF_CLI_WP_FILE" | grep -Fq '<?php'; then
        printf 'WP-CLI is a shell wrapper rather than a PHP entry point: %s\n' "$TF_CLI_WP_FILE" >&2
        return 1
    fi
    "$TF_CLI_PHP_BIN" -r 'exit(PHP_VERSION_ID>=80100?0:1);' || return 1
    TF_CLI_DISABLED="$("$TF_CLI_PHP_BIN" -r '$d=array_filter(array_map("trim",explode(",",ini_get("disable_functions"))));echo implode(",",array_diff($d,["proc_open","proc_close","proc_get_status","proc_terminate","exec"]));')" || return 1
    unalias wp 2>/dev/null || true
    function wp {
        "$TF_CLI_PHP_BIN" -d "disable_functions=$TF_CLI_DISABLED" "$TF_CLI_WP_FILE" "$@"
    }
    export -f wp
}
tf_prepare_cli_session
