<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Intercepts requests where ?mdep_export=1 is set and serves
 * the matched post/page/CPT as clean Markdown.
 *
 * Works on nginx because routing is handled entirely by
 * WordPress query vars — no server-level rewrite config needed.
 */
add_action( 'template_redirect', 'mdep_handle_export' );

function mdep_handle_export() {
    if ( get_query_var( 'mdep_export' ) !== '1' ) {
        return;
    }

    // -------------------------------------------------------------------------
    // Resolve the post
    // -------------------------------------------------------------------------
    $post = mdep_resolve_post();

    if ( ! $post ) {
        // Nothing matched — let WordPress handle the 404
        return;
    }

    // Only serve published content
    if ( $post->post_status !== 'publish' ) {
        status_header( 404 );
        exit;
    }

    // -------------------------------------------------------------------------
    // Build the Markdown
    // -------------------------------------------------------------------------
    setup_postdata( $GLOBALS['post'] = $post );

    $title = get_the_title( $post );
    $html  = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core WP filter

    wp_reset_postdata();

    $converter = new MDEP_Converter();
    $body_md   = $converter->convert( $html );

    $md = "# {$title}\n\n" . $body_md;

    // -------------------------------------------------------------------------
    // Serve
    // -------------------------------------------------------------------------
    status_header( 200 );
    header( 'Content-Type: text/markdown; charset=UTF-8' );
    header( 'X-Robots-Tag: noindex' ); // keep .md copies out of search indexes

    // Allow downstream caching at the edge (CDN / reverse proxy) for 1 hour
    header( 'Cache-Control: public, max-age=3600' );

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $md;
    exit;
}

// -------------------------------------------------------------------------
// Post resolution
// -------------------------------------------------------------------------

/**
 * Figure out which post the current request maps to.
 *
 * WP's rewrite rules already set `name` or `pagename` in the query,
 * so we piggyback on those rather than re-parsing the URI.
 *
 * @return WP_Post|null
 */
function mdep_resolve_post(): ?WP_Post {
    global $wp_query;

    // Front page / index.md
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    $path = trim( wp_parse_url( $uri, PHP_URL_PATH ) ?? '', '/' );
    $path = preg_replace( '#\.md$#', '', $path );

    if ( $path === 'index' || $path === '' ) {
        $front = get_option( 'show_on_front' );
        if ( $front === 'page' ) {
            $id = (int) get_option( 'page_on_front' );
            return $id ? get_post( $id ) : null;
        }
        return null;
    }

    // Try pagename (hierarchical pages) first
    $pagename = get_query_var( 'pagename' );
    if ( $pagename ) {
        $post = get_page_by_path( $pagename, OBJECT, mdep_all_post_types() );
        if ( $post ) return $post;
    }

    // Try name (posts + CPTs)
    $name = get_query_var( 'name' );
    if ( $name ) {
        $args = [
            'name'           => $name,
            'post_type'      => mdep_all_post_types(),
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ];
        $posts = get_posts( $args );
        if ( ! empty( $posts ) ) return $posts[0];
    }

    // Last resort: try url_to_postid on the path
    $url     = home_url( "/{$path}/" );
    $post_id = url_to_postid( $url );
    if ( $post_id ) {
        return get_post( $post_id );
    }

    return null;
}

/**
 * Return all public post type slugs so CPTs work automatically.
 *
 * @return string[]
 */
function mdep_all_post_types(): array {
    $types = get_post_types( [ 'public' => true ], 'names' );
    return array_values( $types );
}
