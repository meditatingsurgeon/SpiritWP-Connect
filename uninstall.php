<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
glo al $wpdb;
$t1=$wpdb->prefix.'spwp_plan_map';$t2=$wpdb->prefix.'spwp_api_log';
$wpdb->query("DROP TABLE IF EXISTS $t1");
$wpdb->query("DROP TABLE IF EXISTS $t2");
foreach(['spwp_ce_base_url','spwp_ce_app_key','spwp_ce_modules','spwp_ce_sso_default','spwp_ce_cache_ttl','spwp_ce_cron_freq','spwp_ce_debug_log'] as $o){delete_option($o);}
