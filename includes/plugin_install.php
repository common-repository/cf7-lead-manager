<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function	CF7LM_get_db_tables_details($table){
	global $wpdb;
	switch($table){
		default:
		return $cf7lm_table 		= $wpdb->prefix . CF7LM_PLUGIN_SHORT_NAME;
		break;
		
		case "meta_table":
		return	$cf7lm_table_meta 	= $wpdb->prefix . CF7LM_PLUGIN_SHORT_NAME. "_meta";
		break;
	}	
}




function	CF7LM_activating_plugin(){
	
	global	$wpdb;
	
	update_option("CF7LM_VERSION", CF7LM_VERSION);
	
	$cf7lm_table		=	CF7LM_get_db_tables_details();
	$cf7lm_table_meta	=	CF7LM_get_db_tables_details("meta_table");	
	$charset_collate 	= $wpdb->get_charset_collate();
	
	$sql1 = "CREATE TABLE $cf7lm_table (
			  `id` int(9) NOT NULL AUTO_INCREMENT,
			  `first_name` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `last_name` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `email` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
			  `referer_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `original_referer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `landing_page` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `ip_address` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `contact_form7_container_post` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `contact_form7_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `serialized_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
			  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) $charset_collate;";
			
	$sql2 = "CREATE TABLE $cf7lm_table_meta (
			  `id` int(9) NOT NULL AUTO_INCREMENT,
			  `meta_key` varchar(55) NOT NULL,
			  `meta_value` varchar(55) NOT NULL,
			  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql1 );
	//dbDelta( $sql2 );
}



function	CF7LM_deactivating_plugin(){
	global	$wpdb;
	$cf7lm_table		=	CF7LM_get_db_tables_details();
	$cf7lm_table_meta	=	CF7LM_get_db_tables_details("meta_table");
	$delete_database_table	=	get_option("CF7LM-delete-records-uninstall");
	if($delete_database_table){
		$wpdb->query("DROP TABLE $cf7lm_table");
		$wpdb->query("DROP TABLE $cf7lm_table_meta");
	}
}