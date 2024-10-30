<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class	CF7LM{
	
	var	$http_referer;
	var	$ip_address;
	var	$landing_page;
	var	$original_referer;
	var $user_agent;
	var	$cf7lm_table;
	
	/*
		*
		*	Constructor function
		*
	*/
	
	function	__construct(){
		$this->cf7lm_table		=	CF7LM_get_db_tables_details();
		$visitor_data			=	CF7LM_getcookie();
		$this->http_referer		=	apply_filters( 'cf7lm_http_referer',			$_SERVER['HTTP_REFERER']);
		$this->ip_address		=	apply_filters( 'cf7lm_remote_address',		$_SERVER['REMOTE_ADDR']);
		$this->user_agent		=	apply_filters( 'cf7lm_user_agent',			$this->get_browser_name());
		$this->original_referer	=	apply_filters( 'cf7lm_original_referer',		$visitor_data['original_referer']);
		$this->landing_page		=	apply_filters( 'cf7lm_landing_page',			$visitor_data['landing_page']);
	}
	
	
	
	function CF7LM_before_send_mail( $form_tag ){
		global $wpdb;
		
		
		$form 			= 	WPCF7_Submission::get_instance();
		$form_id		=	$form_tag->id();
		$data           = 	$form->get_posted_data();
		$files          = 	$form->uploaded_files();
	
			foreach ($data as $key => $tmpD) {
			   
					switch($key){
						default:
							$form_data[$key] = $tmpD;
						break;
						
						case strpos($key, "name") >= 1:
							$form_data['name'] = $tmpD;
						break;	
						
						case strpos($key, "email") >= 1:
							$form_data['email'] = $tmpD;
						break;
						
						case strpos($key, "message") >= 1:
							$form_data['message'] = $tmpD;
						break;
						
						case strpos($key, "subject") >= 1:
							$form_data['subject'] = $tmpD;
						break;
					}
				
			}
		
			
			$wpdb->insert( $this->cf7lm_table, array( 
				'first_name' 					=> 	$form_data['name'],
				'email'   						=> 	$form_data['email'],
				'subject'    					=> 	$form_data['subject'],
				'message'    					=> 	$form_data['message'],
				'referer_url'    				=> 	$this->http_referer,
				'user_agent'    					=> 	$this->user_agent,
				'landing_page'    				=> 	$this->landing_page,
				'original_referer'    			=> 	$this->original_referer,
				'ip_address'						=> 	$this->ip_address,
				'contact_form7_id'				=>	$form_data['_wpcf7'],
				'contact_form7_container_post'	=>	$form_data['_wpcf7_container_post'],
				'serialized_data'				=>	serialize( $form_data ),
				'created'						=>	current_time( 'mysql' )
			) );
	
			$insert_id = $wpdb->insert_id;
	}
	
	function get_browser_name(){ 
	
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
	
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
		
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){ 
			$bname = 'Internet Explorer'; 
			$ub = "MSIE"; 
		}elseif(preg_match('/Firefox/i',$u_agent)){ 
			$bname = 'Mozilla Firefox'; 
			$ub = "Firefox"; 
		}elseif(preg_match('/Chrome/i',$u_agent)) { 
			$bname = 'Google Chrome'; 
			$ub = "Chrome"; 
		}elseif(preg_match('/Safari/i',$u_agent)) { 
			$bname = 'Apple Safari'; 
			$ub = "Safari"; 
		}elseif(preg_match('/Opera/i',$u_agent)){ 
			$bname = 'Opera'; 
			$ub = "Opera"; 
		}elseif(preg_match('/Netscape/i',$u_agent)){ 
			$bname = 'Netscape'; 
			$ub = "Netscape"; 
		} 
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
		}
		$i = count($matches['browser']);
		if ($i != 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		if ($version==null || $version=="") {$version="?";}
		return $bname;
		/*return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'   => $pattern
		);*/
	} 
	
	
	function	CF7LM_get_number_leads(){
		global $wpdb;
		return $wpdb->get_var("select count(id) from $this->cf7lm_table");
	}
	
	function	CF7LM_list_leads(){
		global $wpdb;
		$limit	=	get_option("CF7LM-records-per-page");
		if(empty($limit)){$limit=10;}
		
		if( isset( $_GET['paged'] ) ) {
            $page = $_GET['paged'];
            $offset = ($limit * $page) - $limit ;
         }else {
            $page = 0;
            $offset = 0;
         }
		
		return $wpdb->get_results("select * from $this->cf7lm_table limit $offset, $limit");
	}
	
	
	
	function	CF7LM_get_leads_group_by($col="user_agent"){
		global $wpdb;
		
		if($col=="created"){
			return $wpdb->get_results("select date($col) as created, count(id) as total from $this->cf7lm_table group by date(created) order by total desc");
		}else{
			return $wpdb->get_results("select $col, count(id) as total from $this->cf7lm_table group by $col order by total desc");
		}
	}
	
	
	
	function	CF7LM_display_stats_in_table($col="user_agent"){
	
		$count	=	$this->CF7LM_get_leads_group_by($col);
		
		echo '<table class="widefat fixed" cellspacing="0">';
			echo '<thead>';
				echo '<th colspan="3">'.$col.'</th>';
				echo '<th>Total Leads</th>';
			echo '</thead>';
			
			foreach ($count	as	$data){
				echo '<tr class="alternate">';
					echo '<td colspan="3">'.$data->$col.'</td>';
					echo '<td>'.$data->total.'</td>';
				echo '</tr>';
			}
		echo '</table>';
	
	}
	
	
	function	CF7LM_export_leads(){
		global $wpdb;
		@header("Pragma: public");
		@header("Expires: 0");
		@header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		@header("Content-Type: application/force-download");
		@header("Content-Type: text/csv");
		@header("Content-Type: application/download");
		@header("Content-Disposition: attachment;filename=".CF7LM_PLUGIN_SHORT_NAME."-leads.csv"); 
		@header("Content-Transfer-Encoding: binary ");	
		
		$col_names	=	array("id","first_name","last_name","email","subject","message","referer_url","original_referer","landing_page","ip_address","user_agent", "serialized_data");
		
		$results 	=	$wpdb->get_results("select * from $this->cf7lm_table", ARRAY_A);
		
		
		$fh = @fopen( 'php://output', 'w' );
		fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
		fputcsv( $fh, array_keys($results[0]));
		foreach ( $results as $data_row ) {
			
			$data_row['serialized_data']		=	print_r(unserialize($data_row['serialized_data']), true);
			fputcsv( $fh, $data_row );
			
		}
		fclose( $fh );
		die();
		
	}
	
	/*
	*
	*	Function to paginate results in admin area.
	*
	*/
	
	function	CF7LM_paginate(){
		
		$limit_per_page		=	get_option("CF7LM-records-per-page");
		if(empty($limit_per_page)){$limit_per_page=10;}
		$current_page		=	empty($_GET['paged'])?"1":$_GET['paged'];
		$total_records		=	$this->CF7LM_get_number_leads();
		
		$total_pages		=	ceil($total_records/$limit_per_page);
		$URi				=	$_SERVER['PHP_SELF'].'?page='.$_GET['page'];
		
		echo '<div class="tablenav-pages"><span class="displaying-num">'.$total_records.' items</span>
				<span class="tablenav-pages-navspan" aria-hidden="true"><a href="'.$URi.'&paged=1">«</a></span>';
		for($x	=	1; $x<=$total_pages; $x++){
			
			if($x==$current_page){
				echo '<span class="tablenav-pages-navspan" aria-hidden="true">'.$x.'</span>';
			}else{
				echo '<span class="tablenav-pages-navspan" aria-hidden="true"><a class="next-page" href="'.$URi.'&paged='.$x.'">'.$x.'</a></span>';
			}
		}
		echo '<span class="tablenav-pages-navspan" aria-hidden="true"><a href="'.$URi.'&paged='.$total_pages.'">»</a></span></div>';
	}
	
	
}
