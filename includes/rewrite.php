<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rewrite rules for .md endpoints.
 *
 * Uses WordPress query vars for routing rather than server-level rewrite rules,
 * so this works out of the box on nginx and Apache hosts alike,
 * with no .htaccess or nginx config changes required.
 */

function mdep_add_query_vars( $vars ) {
    $vars[] = 'mdep_export'; // signals a .md request
    return $vars;
}
add_filter( 'query_vars', 'mdep_add_query_vars' );

/**
 * Prevent WordPress canonical redirect from stripping .md URLs.
 */
function mdep_disable_canonical( $redirect ) {
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    if ( strpos( $uri, '.md' ) !== false ) {
        return false;
    }
    return $redirect;
}
add_filter( 'redirect_canonical', 'mdep_disable_canonical' );

/**
 * Register all .md rewrite rules.
 * Called on init (so rules are always registered) and on activation.
 */
function mdep_register_rewrites() {

    // Front page → /index.md
    add_rewrite_rule(
        '^index\.md$',
        'index.php?mdep_export=1',
        'top'
    );

    // Nested slugs → /parent/child/page.md
    add_rewrite_rule(
        '^(.+/[^/]+)\.md$',
        'index.php?pagename=$matches[1]&mdep_export=1',
        'top'
    );

    // Single slug → /post-or-page.md
    add_rewrite_rule(
        '^([^/]+)\.md$',
        'index.php?name=$matches[1]&mdep_export=1',
        'top'
    );
}
add_action( 'init', 'mdep_register_rewrites' );
