<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// create custom plugin settings menu
add_action('admin_menu', 'CF7LM_Admin_Page');

function CF7LM_Admin_Page() {
	add_menu_page(CF7LM_PLUGIN_NAME, CF7LM_PLUGIN_SHORT_NAME.' Settings', 'administrator', __FILE__, 'CF7LM_settings_page_output' , plugins_url('/config.png', __FILE__), 30 );
	add_submenu_page( "contact-form-7-lead-manager/admin/tabs.php", "Leads", "Leads", "administrator", "CF7LM-leads", "CF7LM_leads_page");
	add_submenu_page( "contact-form-7-lead-manager/admin/tabs.php", "Stats", "Stats", "administrator", "CF7LM-stats", "CF7LM_stats_page");
}


add_action( 'admin_init', 'CF7LM_settings_page' );

function CF7LM_settings_page() {
	//register our settings
	register_setting( 'CF7LM-general-group', 'CF7LM-records-per-page' );
	register_setting( 'CF7LM-general-group', 'CF7LM-delete-records-uninstall' );
	//register_setting( 'CF7LM-minify-group', 'minify_input' );
}


function CF7LM_settings_page_output() {
?>
<div class="wrap">
<h2><img src="<?php echo plugins_url('/settings.png', __FILE__)?>" style="margin-right:10px;"/><?php echo CF7LM_PLUGIN_NAME; ?></h2>

<?php
if( isset( $_GET[ 'tab' ] ) ) {
    $active_tab = $_GET[ 'tab' ];
}else{
	$active_tab = "general_options";
}
?>




    
    <div class="wrap">
     
        <div id="icon-themes" class="icon30"></div>
        <h4><?php echo CF7LM_PLUGIN_NAME; ?> Options</h4>
        <?php settings_errors(); ?>
         
        <h2 class="nav-tab-wrapper">
    <a href="?page=contact-form-7-lead-manager%2Fadmin%2Ftabs.php&tab=general_options" class="nav-tab <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>">General Settings</a>
    <a href="?page=contact-form-7-lead-manager%2Fadmin%2Ftabs.php&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
    
    
    
</h2>
         <div class="admin_contents">
        <form method="post" action="options.php">
 
            <?php
				
         		
				switch($active_tab){
					case	"general_options":
						settings_fields('CF7LM-general-group');
						do_settings_sections('CF7LM-general-group');
						echo '<h3>General Settings</h3>';
						echo '<label>Show Leads Per Page: <input type="text" name="CF7LM-records-per-page" value="'.esc_attr( get_option("CF7LM-records-per-page") ).'" /></label><br />';
						printf( '<label>Delete Data on Plugin Uninstall:<input type="checkbox" name="CF7LM-delete-records-uninstall" value="1" %s /></label><br />', $checked	=	(get_option("CF7LM-delete-records-uninstall")==1)?"checked":"");
					break;
					
					case	"help":
						settings_fields('CF7LM-minify-group');
						do_settings_sections('CF7LM-minify-group');
						echo '<h3>Help</h3>';
						echo '<h4>Does his plugin captures data from other contact form plugins than <strong>Contact Form 7</strong>?</h4>
							No, it only captures and shows data submitted through all forms generated with <strong>Contact Form 7</strong> plugin.
							<h4>Do I need to configure this plugin to capture data from contact form?</h4>
							No, once you install and activate this plugin, it will start grabbing the data automatically when a <strong>Contact Form 7</strong> form is submitted.
						';
			
					break;
					
					
					
				}
				
        submit_button();
			?>
         
             
        </form>
        </div> 
    </div><!-- /.wrap -->
<cite>All Rights Reserved@<a href="http://designsvalley.com" target="_blank">Designs Valley</a></cite>
</div>
<?php } 

function	CF7LM_stats_page(){
	global $cf7lm;
	global $wpdb;
	
	echo '<div class="CF7LM-stats">';
		echo '<h2>'.CF7LM_PLUGIN_SHORT_NAME.' Leads Stats</h2>';
		
		echo '<div class="CF7LM-stats-widget">';
			echo '<h3>Top Browsers</h3>';
			$cf7lm->CF7LM_display_stats_in_table("user_agent");
		echo '</div>';
		
		echo '<div class="CF7LM-stats-widget">';
			echo '<h3>Top Landing Pages</h3>';
			$cf7lm->CF7LM_display_stats_in_table("landing_page");
		echo '</div>';
		
		echo '<div class="CF7LM-stats-widget">';
			echo '<h2>Top External Referer Pages</h2>';
			$cf7lm->CF7LM_display_stats_in_table("original_referer");
		echo '</div>';
		
		echo '<div class="CF7LM-stats-widget">';
			echo '<h2>Top Internal Referer Pages</h2>';
			$cf7lm->CF7LM_display_stats_in_table("referer_url");
		echo '</div>';
	
	echo '</div>';
	
	echo '<style type="text/css">
	.CF7LM-stats-widget{ width:40%; display:inline-block; margin:0 10px 30px 0px;}
	</style>';
}

function	CF7LM_leads_page(){
	global $cf7lm;
	global $wpdb;
	
	switch($_GET['action']){
		case "delete":
			$wpdb->delete( $cf7lm->cf7lm_table, array( 'id' => $_GET['lead_id'] ) );
		break;
		
	}
	
	echo '
	<h2>'.CF7LM_PLUGIN_SHORT_NAME.' Leads</h2>
	<div style="float:left; margin-right:50px;"><a href="?page=CF7LM-leads&action=export"><img width="30" src="'.plugins_url('/csv-export.png', __FILE__).'"></a></div>
	<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>

            <th id="cb" class="manage-column column-columnname" scope="col">ID</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Full Name</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Email</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Subject</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Landing Page</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">IP Address</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Date</th>

    </tr>
    </thead>

    <tfoot>
    <tr>

            <th id="cb" class="manage-column column-cb check-column num" scope="col">ID</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Full Name</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Email</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Subject</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Landing Page</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">IP Address</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Date</th>

    </tr>
    </tfoot>
	<tbody>';
	
	$records_found	=	$cf7lm->CF7LM_get_number_leads();
	
	if($records_found>0){
		$leads	=	$cf7lm->CF7LM_list_leads();
		foreach($leads	as $lead){
			$meta_data	=	unserialize($lead->serialized_data);
			$meta_data_array	=	print_r($meta_data, true);
			echo '<tr class="alternate">
					<th class="check-column num" scope="row">'.$lead->id.'</th>
					<td class="column-columnname">'.$lead->first_name.' '.$lead->last_name.'</td>
					<td class="column-columnname">'.$lead->email.'</td>
					<td class="column-columnname">'.$lead->subject.'</td>
					<td class="column-columnname">'.$lead->landing_page.'</td>
					<td class="column-columnname">'.$lead->ip_address.'</td>
					<td class="column-columnname">'.$lead->created.'</td>
				</tr>
				<tr class="alternate" valign="top">
					<th class="check-column" scope="row"></th>
					<td class="column-columnname">
						<div class="row-actions">
							<span><a href="?page=CF7LM-leads&action=delete&lead_id='.$lead->id.'">Delete</a> |</span>
							<span><a href="?page=CF7LM-leads&action=edit&lead_id='.$lead->id.'">Edit</a></span>
						</div>
					</td>
					<td class="column-columnname" colspan="5"></td>
				</tr>';
		}
	}else{
		echo '<tr class="alternate" valign="top">
            <td class="column-columnname" colspan="6">No records found</td>
        </tr>';
	}
   echo '     
    </tbody>
</table>';


	$cf7lm->CF7LM_paginate();
	
}




?>