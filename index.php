<?php
/**
 * @package Contact Form 7 Lead Manager
 * @version 1.0
 */
/*
Plugin Name: Contact Form 7 Lead Manager
Plugin URI: http://wpbloghelp.com
Description: Capture all form submissions from Contact Form 7 plugin and display more insights about the leads captured from the Contact form.
Author: Shahzad Ahmad
Version: 1.0
Author URI: http://shahzadmirza.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define(CF7LM_PLUGIN_SHORT_NAME, "CF7LM");
define(CF7LM_VERSION, "1.0");
define(CF7LM_PLUGIN_NAME, "Contact Form 7 Lead Manager");
define(CF7LM_PLUGIN_ROOT, dirname(__FILE__));
include(CF7LM_PLUGIN_ROOT."/includes/plugin_install.php");
include(CF7LM_PLUGIN_ROOT."/admin/tabs.php");
include(CF7LM_PLUGIN_ROOT."/includes/CF7LM.class.php");


/*
	*
	*	setting cookies to store orinal HTTP_REFERER & LANDING PAGE
	*	this information is retrieved later to add to database
	*
*/
add_action( 'init', 'CF7LM_setcookie' );
function CF7LM_setcookie() {
	//unset( $_COOKIE['CF7LM_visitor_information'] );
	if(!isset($_COOKIE["CF7LM_visitor_information"])) {
		$visitor_data	=	array();
		$visitor_data['original_referer']	=	$_SERVER['HTTP_REFERER'];
		$visitor_data['landing_page']		=	$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
   		setcookie( "CF7LM_visitor_information", serialize($visitor_data), 1 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	}
}

function	CF7LM_getcookie(){
	return unserialize($_COOKIE["CF7LM_visitor_information"]);
}


/*
	*
	*	Creating and dropping tables for this plugin on
	*	Plugin activation and deactivation
	*
*/
register_activation_hook( __FILE__, 'CF7LM_activating_plugin' );
register_deactivation_hook( __FILE__, 'CF7LM_deactivating_plugin' );



/*
	*
	*	Capturing data sent by Contact Form 7
	*
*/
$cf7lm	=	new CF7LM();
add_action( 'wpcf7_before_send_mail', array( $cf7lm, 'CF7LM_before_send_mail' ) );


/*
	*
	*	Invoking Leadins import function on a certain condition
	*
*/
if(isset($_GET['page']) & $_GET['page']=="CF7LM-leads" and $_GET['action']=="export"){
	add_action( 'admin_init', array( $cf7lm, 'CF7LM_export_leads' ) );
}

