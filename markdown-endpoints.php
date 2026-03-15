<?php
/**
 * Plugin Name: Markdown Endpoints
 * Plugin URI:  https://markdownendpoints.com
 * Description: Serves any WordPress post, page, or custom post type as clean Markdown via a .md URL — making your site readable by AI engines, crawlers, and LLMs.
 * Version:     1.1.0
 * Author:      Kerry Ben-Joseph
 * Author URI:  https://kerrybenjoseph.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: markdown-endpoints
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MDEP_VERSION',     '1.1.0' );
define( 'MDEP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MDEP_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

require_once MDEP_PLUGIN_PATH . 'includes/rewrite.php';
require_once MDEP_PLUGIN_PATH . 'includes/converter.php';
require_once MDEP_PLUGIN_PATH . 'includes/exporter.php';

// Flush rewrite rules on activation / deactivation
register_activation_hook( __FILE__, function() {
    mdep_register_rewrites();
    flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
