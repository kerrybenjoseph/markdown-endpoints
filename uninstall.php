<?php
// Only run when WordPress triggers uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Flush rewrite rules so .md endpoints are removed cleanly
flush_rewrite_rules();
