<?php
/**
 * Bulk Noindex & Nofollow Toolkit Core Class
 *
 * Handles all the core operations required to run on a WordPress platform.
 *
 * @package bulk-noindex-toolkit
 * @since 3.4
 */


if(!class_exists('bulkNoindexToolkit')) 
{

	class bulkNoindexToolkit	
	{	
		public $index_val_set = '';
		public $follow_val_set = '';

		public function __construct()
	    {
	    		    	
	    	//identify and set a known SEO plugin currently being used by the site
			$this->set_active_seo_plugin();
	    		        
	    }

	   	/**
		 * Keep the bnitkmfd post_meta values in sync with the native SEO plugins
		 * even if the native plugins settings are changed from the post editor
		 * @access public
		 * @return void		 
		 */
	    public function after_updated_post($post_id, $post ){
	   
	   		$meta_key_noidx = $this->get_meta_keys('noindex');
	   		$meta_key_nofllw = $this->get_meta_keys('nofollow');

	   		$meta_key_noidx_cln = substr($meta_key_noidx,1);
	   		$meta_key_nofllw_cln = substr($meta_key_nofllw,1);
	   		


	   		//update or delete the bulk no index post_meta data (no )
	   		if(isset($_POST[$meta_key_noidx_cln]) && $_POST[$meta_key_noidx_cln] > 0){
	   			
	   			$noidx_field_cln = sanitize_text_field($_POST[$meta_key_noidx_cln]);	
				update_post_meta( $post_id, '_bnitk_mfd_meta-robots-noindex', $noidx_field_cln );  
				
	   		}else{
	   			delete_post_meta($post_id, '_bnitk_mfd_meta-robots-noindex');
	   		}

	   		//update or delete the bulk no index post_meta data (nofollow)
	   		if(isset($_POST[$meta_key_nofllw_cln]) && $_POST[$meta_key_nofllw_cln] > 0){

	   			$nofllw_field_cln = sanitize_text_field($_POST[$meta_key_nofllw_cln]);
	   			update_post_meta( $post_id, '_bnitk_mfd_meta-robots-nofollow', $nofllw_field_cln );
	   		
	   		}else{
	   			delete_post_meta($post_id, '_bnitk_mfd_meta-robots-nofollow');
	   		} 
	   			    
		}
	    	    
	    /**
		 * Check to see see if a meta robots tag should be implemented to noindex
		 * or nofollow the page
		 * @access public
		 * @return void		 
		 */
		public function check_meta_robots($term_page = False){
			
			//initialize the directive variables to empty
			$idx_directive = '';
			$follow_directive = '';
			$directive_array = array();

			if($this->active_seo_plugin == 'bnitkmfd' || $term_page == True){
				
				if(is_tax() || is_tag() || is_category()){
					$tag_id = get_queried_object()->term_id;
					
					
					$meta_data_noidx = get_term_meta($tag_id,'_bnitk_mfd_meta-robots-noindex');
					$meta_data_nofllw = get_term_meta($tag_id,'_bnitk_mfd_meta-robots-nofollow');	
					
				}elseif(is_page()){
					$post_id = get_the_ID();
					$meta_data_noidx = get_post_meta($post_id,'_bnitk_mfd_meta-robots-noindex');
					$meta_data_nofllw = get_post_meta($post_id,'_bnitk_mfd_meta-robots-nofollow');
				}

				if(isset($meta_data_noidx[0])){					
					$idx_directive = ($meta_data_noidx[0] == 1) ? 'noindex' : 'index';	
					$this->index_val_set = $idx_directive;
				}

				if(isset($meta_data_nofllw[0])){					
					$follow_directive = ($meta_data_nofllw[0] == 1) ? 'nofollow' : 'follow';
					$this->follow_val_set = $follow_directive;
				}				

				if($idx_directive == 'noindex' || $follow_directive == 'nofollow'){
					
					//build the appropriate directive for the meta robots tag
					$directive_array[] = ($idx_directive == 'noindex') ? 'noindex' : 'index';
					$directive_array[] = ($follow_directive == 'nofollow') ? 'nofollow' : 'follow';
					
					//add the robots meta tag with the proper directive to the page
					$this->add_robots_meta_to_page($directive_array);
				}
				
			}			

		}

		/**
		 * Outputs a robots meta tag for the page or term
		 * @access public
		 * @return void		 
		 */
		public function add_robots_meta_to_page($directive_array = array()) {
	

			if($directive_array){
				$directive = implode(',',$directive_array);
				
				//this filter is used to debug the robots meta tag content				
				//add_filter( 'wp_robots', array(&$this,'list_hooks'),1);				

				//update the Yoast directive on tag pages
				if($this->active_seo_plugin == 'yoast'){

					add_filter( 'wpseo_robots', function( $robots ) use ( $directive_array ) {
						$fresh_robots = array();
						
						foreach(explode(', ',$robots) as $rVal){
							//if yoast has already set a noindex value, then obey that		
							if(in_array($rVal,array('noindex','index'))){							
								if($this->index_val_set && $rVal != $this->index_val_set){
									$fresh_robots[] = $this->index_val_set;
								}else{
									$fresh_robots[] = $rVal;
								}
							}
							
							//update follow/nofollow directive accordingly						
							if(in_array($rVal,array('nofollow','follow'))){
								$fresh_robots[] = ($this->follow_val_set) ? 'nofollow' : 'follow';

							}

						}

							echo "<!-- robots meta tag updated by Mad Fish bulk noindex plugin https://www.madfishdigital.com/wp-plugins/ -->\n";									        
				        	return implode(', ',$fresh_robots);
				    	
				    	}
					);

				}else{

					//push the directie to the WP Robots filter - custom robots directive
					add_filter( 'wp_robots', function( $robots ) use ( $directive_array ) {
						
						echo "<!-- robots meta tag added by Mad Fish bulk noindex plugin https://www.madfishdigital.com/wp-plugins/ -->\n";
						
						if($directive_array){
							foreach($directive_array as $dKey => $dVal){
								$robots[$dVal] = True;
							}					
						}
				        
				        return $robots;
				    	
				    	}
					);				
				}

			}

			
		}

		/**
		* This is function is for debugging the contents of the robots meta tag
		* Only used when debugging the output
		* @access public
		* @return void		 
		*/

		public function list_hooks( $robots ) {
			global $wp_filter;

			echo "<!-- This is a list of callback functions hooked into the 'wp_robots' filter:";
			echo json_encode($wp_filter['wp_robots'], JSON_PRETTY_PRINT);
			echo "-->";

			return $robots;
		}


		/**
		* Check to see if a specific plugin is active
		* can't rely on is_plugin_active() since it
		* may not have loaded before this plugin loads
		* @access public
		* @return void		 
		*/

		public function is_plugin_active_mfd($plugin){
			return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );

		}

		/**
		 * Set the appropriate post meta keys based on the installed SEO plugins
		 */
		public function set_meta_keys(){

			//if none are available, then use our post meta key

			//Yoast support
			$this->meta_keys['yoast'] = array( 
	        	'noindex' => '_yoast_wpseo_meta-robots-noindex', 
	        	'nofollow' => '_yoast_wpseo_meta-robots-nofollow' 
	        );

			//All in One SEO Support
	        $this->meta_keys['aioseo'] = array( 
	        	'noindex' => 'robots_noindex', 
	        	'nofollow' => 'robots_nofollow' 
	        );

	        //Our own support
	       	$this->meta_keys['bnitkmfd'] = array( 
	        	'noindex' => '_bnitk_mfd_meta-robots-noindex', 
	        	'nofollow' => '_bnitk_mfd_meta-robots-nofollow'
	        );
		}
		
		/**
		 * Identify the active SEO plugins that are being used
		 * This way we don't add multiple meta robots tags to a page
		 * @access public
		 * @return void		 
		 */
		public function set_active_seo_plugin(){
			
			//if Yoast or AIOSEO are already installed, use thier post meta key instead of reinventing the wheel by using our own

			try {

				//double check that the get_plugins function has been loaded
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';

				}

				//set the default value assuming Yoast and AIOSEO are not active
				$this->active_seo_plugin = 'bnitkmfd';

				//check to see if the yoast or AIOSEO plugins are active
				$apl=get_option('active_plugins');
				$plugins=get_plugins();
				$activated_plugins=array();
				foreach ($apl as $p){           
				    if(isset($plugins[$p])){				         
				    	
				    	//confirm that Yoast is installed
				        if($plugins[$p]['Name'] == 'Yoast SEO'){
				        	$this->active_seo_plugin = 'yoast';	
				        
				        //confirm that AIOSEO is installed
				        }elseif($plugins[$p]['Name'] == 'All in One SEO Pro' || $plugins[$p]['Name'] == 'All in One SEO'){
				        	$this->active_seo_plugin = 'aioseo';
				        }
				    }           
				}

					
	    		
			}catch (exception $e) {
			    //code to handle the exception
			    $this->active_seo_plugin = 'bnitkmfd';

			}			

		}

		/**
		 * Return the value of the current active SEO plugin in use
		 * @access public
		 * @return void		 
		 */

		public function get_seo_plugin(){
			 		    
			return $this->active_seo_plugin;
		}	

		/**
		 * Populate an array that contains the appropriate post_meta values
		 * @access public
		 * @return void		 
		 */

		public function get_meta_keys($directive = 'noindex'){

			$this->set_meta_keys();	    		    	

			return $this->meta_keys[$this->active_seo_plugin][$directive];
		}

		/**
		 * create_menu function
		 * generate the link to the options page under settings
		 * @access public
		 * @return void
		 */
		public function create_menu() {
		  	
		  	//add the link to the tools section of the wordpress admin area
			add_submenu_page( 'tools.php', 'Bulk NoIndex/Nofollow Toolkit', 'Bulk NoIndex/NoFollow', 'manage_options', 'no-index-toolkit', array($this,'options_page')); 

		}
	
		/**
		 * create_search_form function
		 * generate the search box for the table
		 * @access public
		 * @return void
		 */

		public function create_search_box(){

			//set the search value if we're in the middle of a search
			$search_filter_val = '';
			if ( isset( $_GET['s'] ) AND $_GET['s'] ){
				$search_filter_val = $this->xss_clean(filter_var(esc_sql($_GET['s']), FILTER_SANITIZE_STRING));
			}
            			
		    $rendered_html = '<form name="search" method="post">';
		    $rendered_html .= '<input type="hidden" name="page" value="no-index-toolkit" />';		           
		    $rendered_html .= '<p class="search-box">';
			$rendered_html .= '<label class="screen-reader-text" for="search-res-search-input">Search:</label>';
			$rendered_html .= '<input type="search" id="search-res-search-input" name="s" value="'.$search_filter_val.'">';
			$rendered_html .= '<input type="submit" id="search-submit" class="button" value="Search"></p>';
			$rendered_html .= '</form>';

			return $rendered_html;
		}

		/**
		 * xss_clean function
		 * sanitize the search input to prevent Cross-Site Scripting (XSS) attack
		 * @access public
		 * @return void
		 */

		public function xss_clean($data)
		{
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
		    // Remove really unwanted tags
		    $old_data = $data;
		    $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		
		return $data;
		}


		/**
		 * options_page function
		 * generate the options page in the wordpress admin
		 * @access public
		 * @return void
		 */
		public function options_page() {
			
			//include the extendedtable class
			include('bulk-noindex-toolkit-table.php');

			//add the jquery javascript to the options page
			wp_enqueue_script( 'bulk-toolkit',  plugin_dir_url( __FILE__ ) .'../js/bulk-toolkit.js', array('jquery'), '1.10' );

			//add the CSS stylesheet to the options page
			wp_enqueue_style('bulk-toolkit',plugin_dir_url( __FILE__ ) .'../css/bulk-toolkit.css');

			
			//build the table that contains the list of posts/pages on the options page
			echo '<div class="wrap">';            
            echo '<div class="head">';
            echo '	<h2>Bulk NoIndex and NoFollow Posts</h2>';
            echo '</div>';
            echo '<div class="logo-col">';
            echo '<img src="'.plugin_dir_url( __FILE__ ) .'../img/madfishdigital-logo.png" width="200" alt="Mad Fish Digital Logo">';
            echo '<div class="docTxt">';            
            echo '<p>This plugin was built by the team at Mad Fish Digital to help manage the indexation of content posts and pages in large websites that may have had thin content added to them over time. Primarily websites that may have been hit by a search engine penalty or filter as a result of the thin content. </p>            
            	<p>Toggle on/off a robots meta tag containing NoIndex, or Nofollow with the controls below.</p>
            	<p>This plugin supports existing meta robots tags set by the <strong>Yoast and All in One SEO Pack</strong> plugins for pages only. Robots directives set by Yoast and AIOSEO are not synced with this plugin. The Yoast global noindex settings for categories and terms will override this plugin\'s individual page settings. </p>

            	<p>This is an advanced tool. Only use it if you feel comfortable with noindexing and nofollowing web pages. Make sure to check back here if you disable or activate the Yoast or AIOSEO plugins, as your noindex settings could be out of sync.</p>
            	<p class="red-clr">NoIndexing a post or a page will prevent it from appearing in the search engines. We are not responsible if you remove important pages from search engines.<br />Please use this tool with caution, and at your own risk. <br />

            	</p>

            	
            	';

            echo '</div>';
            
            //div tag to handle notifications
            echo '<div class="request-notification"></div>';
         	
         	//show the correct tab based on the GET param
         	if(isset($_GET['tab']) && $_GET['tab'] == 'cats'){
         		$tab1 = '';
         		$tab2 = 'class="active"';         		         		
         	}else{
         		$tab1 = 'class="active"';
         		$tab2 = '';         		
         	}

            
			echo '<div id="container">
				  <header class="tabs-nav">
				    <ul>
				      <li '.$tab1.'><a href="'.sanitize_text_field( esc_html(remove_query_arg(array('ct','orderby','order','items_per_page','paged')))).'&tab=posts">Posts</a></li>
				      <li '.$tab2.'><a href="'.sanitize_text_field(esc_html(remove_query_arg(array('pt','orderby','order','items_per_page','paged')))).'&tab=cats"">Categories</a></li>
				      
				    </ul>
				  </header>';

			echo '<section class="tabs-content">';            



            $BNI_Table = new BNI_MFD_WP_Table(); 
            $BNI_Table->set_search_filter();


            if(!isset($_GET['tab']) || $_GET['tab'] == 'posts' || !in_array($_GET['tab'],array('posts','cats'))){
	        	echo '<div class="tabList" id="tab1" >';
	            echo '<div>';            
					//render the search filter            
	            	echo $this->create_search_box();

		            //render the items per page filter            
		            $BNI_Table->show_items_per_page();
	            
	            echo '</div>';
				
	            echo '<form id="bulk-update" method="post" rel="pageForm">';    
	            echo '<input type="hidden" name="nonce" value="'.wp_create_nonce('bulk-noindex').'">';        
	            $BNI_Table->prepare_items_post();            
	            $BNI_Table->display();
	            echo '</form>';

	            echo '</div>';  //end tab

	        }


            if(isset($_GET['tab'])  && $_GET['tab'] == 'cats'){
	            echo '<div class="tabList" id="tab2">';

 				echo '<div>';            
					//render the search filter            
	            	echo $this->create_search_box();

		            //render the items per page filter            
		            $BNI_Table->show_items_per_page();
	            
	            echo '</div>';

	            echo '<form id="bulk-update" method="post" rel="catForm">';    
	            echo '<input type="hidden" name="nonce" value="'.wp_create_nonce('bulk-noindex').'">';        

	            $BNI_Table->prepare_items_cats();            
	            $BNI_Table->display();
	            echo '</form>';

	            echo '</div>'; 
	        }

            echo '</section>';
			echo '</div>';

            echo '</div>'; //end main wrapper


		}

	
		
		/**
		 * Check Page Status function
		 * this function serves as a fallback to implement a robots meta tag incase 
		 * no common SEO plugin is being used to implement the robots meta tag directives
		 * @access public
		 * @return void
		 */
		public function check_page_status(){
						

			if(is_archive()){
				$this->check_meta_robots(True);				
			}elseif(is_page()){
				$this->check_meta_robots(False);	
			}			
		
		}	

		/**
		 * Update bulk pages noindex and nofollow status
		 * this function is used as an AJAX callback to modify the noindex or nofollow 
		 * status in bulk for categories and terms
		 * @access public
		 * @return void
		 */
		public function update_cat_bulk_callback(){
					

			$bulk_directive = sanitize_text_field($_POST['directive']);			
			$nonce = sanitize_text_field($_POST['nonce']);

			if(current_user_can('manage_options') && wp_verify_nonce( $nonce, 'bulk-noindex' ) ){
			

				if(isset($_POST) && $bulk_directive != '-1'){

					//set the appropriate directive based on the post
					$directive = explode('_',$bulk_directive);

					//set the appropriate post_meta key based on the current SEO plugin
					$active_key = $this->get_meta_keys($directive[0]);
					
					//set the appropriate post_meta key value to use based on the directve received from the bulk drop down form
					$key_val = ($directive[1] == 'set') ? 1 : 0;

					if(isset($_POST['post_ids'])){
						$post_id_vals = $_POST['post_ids'];
						$post_id_vals = array_map( 'sanitize_text_field', $post_id_vals );

						foreach($post_id_vals as $idx => $post_id){

							
							//update each post with the new directive and key value
							if($key_val == 0){

								if($active_key){
									
									//we keep track of the current setting with a custom meta value
									//since Yoast and/or AISEO plugins are not yet suppoted for categories
									delete_term_meta($post_id, '_bnitk_mfd_meta-robots-'.$directive[0]);
								}
								
							}else{
								
								
								if($active_key){
																										
									//we keep track of the current setting with a custom meta value
									//just in case the Yoast or AISEO plugin are disabled
									update_term_meta( $post_id, '_bnitk_mfd_meta-robots-'.$directive[0], $key_val );
								} 
							}
							
						}
						$msg_post = (count($_POST['post_ids']) == 1) ? 'post' : 'posts';

						$msg_nfi = ($key_val == 1) ? 'added' : 'removed';
						
						$status = array(
							'status' => 'OK',
							'msg' => count($_POST['post_ids']).' '.$msg_post.' have had the '.ucwords($directive[0]).' robots direcive '.$msg_nfi,
							'directive' => $directive[0],
							'val' => $key_val,
							'post_ids' => $post_id_vals,

						);

					}else{
						$status = array(
							'status' => 'err',
							'msg' => 'No posts were selected'
						);

					}

					echo json_encode($status);

				}
			}else{
				$status = array(
					'status' => 'err',
					'msg' => 'Security check failed. No posts were changed.'
				);
				echo json_encode($status);
			}

			wp_die(); 

		}

		/**
		 * Update bulk pages noindex and nofollow status
		 * this function is used as an AJAX callback to modify the noindex or nofollow 
		 * status of bulk posts/pages		 
		 * @access public
		 * @return void
		 */
		public function update_page_bulk_callback(){
					

			$bulk_directive = sanitize_text_field($_POST['directive']);			
			$nonce = sanitize_text_field($_POST['nonce']);
			
			if(current_user_can('edit_post' ,$_POST['post_ids'][0]) && wp_verify_nonce( $nonce, 'bulk-noindex' ) ){

				if(isset($_POST) && $bulk_directive != '-1'){

					//set the appropriate directive based on the post
					$directive = explode('_',$bulk_directive);

					//set the appropriate post_meta key based on the current SEO plugin
					$active_key = $this->get_meta_keys($directive[0]);
					
					//set the appropriate post_meta key value to use based on the directve received from the bulk drop down form
					$key_val = ($directive[1] == 'set') ? 1 : 0;

					if(isset($_POST['post_ids'])){
						$post_id_vals = $_POST['post_ids'];
						$post_id_vals = array_map( 'sanitize_text_field', $post_id_vals );

						foreach($post_id_vals as $idx => $post_id){

							
							
						
						//since AISEO uses it's own DB tables to store the post's robots settings
						//we need to access the post settings via AISEO

						if($this->active_seo_plugin == 'aioseo'){
							
							$postAISEO = aioseo()->core->db->start( 'aioseo_posts' )
							->where( 'post_id', $post_id )
							->run()
							->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' ); 					
						}


							/**
							* Always update the fallback post meta key,
							* that way if any of the supported plugins are disabled, 
							* we don't lose track of the pages which should be noindexed
							* remove the fallback post_meta key if we're no longer nonindexing
							**/


							//update each post with the new directive and key value
							if($key_val == 0){

								if($active_key){

									//check to see which plugin is being used
									switch($this->active_seo_plugin){

										case "aioseo":
									
											
											$postAISEO->{$active_key} = false;


											//if there are no longer any custom AISEO settings for the post
											//reset the "Use default settings" option

											if($postAISEO->robots_noindex == 0 && 
												$postAISEO->robots_nofollow == 0 &&
												$postAISEO->robots_noarchive == 0 &&
												$postAISEO->robots_notranslate == 0 &&
												$postAISEO->robots_noimageindex == 0 &&
												$postAISEO->robots_nosnippet == 0 &&
												$postAISEO->robots_noodp == 0 &&

												$postAISEO->robots_max_snippet == -1 &&
												$postAISEO->robots_max_videopreview == -1 &&
												$postAISEO->robots_max_imagepreview == 'large'){
																					

												$postAISEO->robots_default = true;	
											}									
											
											$postAISEO->save();
										break;
										case "yoast":
											delete_post_meta($post_id, sanitize_text_field( $active_key ));
										break;								
										
									}
									//no matter what, we keep track of the current setting 
									//just in case the Yoast or AISEO plugin are disabled
									delete_post_meta($post_id, '_bnitk_mfd_meta-robots-'.$directive[0]);
								}
								
							}else{
								
								
								if($active_key){
									//check to see which plugin is being used
									switch($this->active_seo_plugin){

										case "aioseo":
											$postAISEO->robots_default = false; 
											$postAISEO->{$active_key} = $key_val;
											$postAISEO->save();  
										break;
										case "yoast":
											update_post_meta( $post_id, sanitize_text_field( $active_key ), $key_val );
										break;
										
										
									}
									//no matter what, we keep track of the current setting 
									//just in case the Yoast or AISEO plugin are disabled
									update_post_meta( $post_id, '_bnitk_mfd_meta-robots-'.$directive[0], $key_val );
								} 
							}
							
						}
						$msg_post = (count($_POST['post_ids']) == 1) ? 'post' : 'posts';

						$msg_nfi = ($key_val == 1) ? 'added' : 'removed';
						
						$status = array(
							'status' => 'OK',
							'msg' => count($_POST['post_ids']).' '.$msg_post.' have had the '.ucwords($directive[0]).' robots direcive '.$msg_nfi,
							'directive' => $directive[0],
							'val' => $key_val,
							'post_ids' => $post_id_vals,

						);

					}else{
						$status = array(
							'status' => 'err',
							'msg' => 'No posts were selected'
						);

					}

					echo json_encode($status);

				}
			}else{
				$status = array(
					'status' => 'err',
					'msg' => 'Security check failed. No posts were changed.'
				);
				echo json_encode($status);
			}

			wp_die(); 

		}

		/**
		 * Update a single post/page's noindex and nofollow status
		 * this function is used as an AJAX callback to modify the noindex/nofollow 
		 * status of a single post or page 
		 * @access public
		 * @return void
		 */
		public function update_page_callback() {
				
				$new_val = 0;								
				
				//double check that the user has the neccessary permissions to edit posts	
				//and that the nonce is correct

				$post_id = (int)sanitize_text_field($_POST['post_id']);
				$nonce = sanitize_text_field($_POST['nonce']);
				
				if(current_user_can( 'edit_post', $post_id) && wp_verify_nonce( $nonce, 'bulk-noindex' )){
					
					if(isset($_POST) && isset($_POST['result'])){
						
						$directive = sanitize_text_field(str_replace('[]','',$_POST['check_class']));
						
						$active_key = $this->get_meta_keys($directive);
						
						$post_id = (int)sanitize_text_field($_POST['post_id']);
						$key_val = (sanitize_text_field($_POST['result']) == 1) ? 1 : 0;
					
						
				
						//since AISEO uses it's own DB tables to store the post's robots settings
						//we need to access the post settings via AISEO

						if($this->active_seo_plugin == 'aioseo'){
							
							$postAISEO = aioseo()->core->db->start( 'aioseo_posts' )
							->where( 'post_id', $post_id )
							->run()
							->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' ); 					
						}

						/**
						* Always update the fallback post_meta key that's specific for this plugin
						* that way if any of the supported plugins are disabled, we don't lose
						* track of the pages which should be noindexed
						**/
							
						if($key_val == 0){

							if($active_key){

								//check to see which plugin is being used
								switch($this->active_seo_plugin){

									case "aioseo":
								
										
										$postAISEO->{$active_key} = false;


										//if there are no longer any custom AISEO settings for the post
										//reset the "Use default settings" option

										if($postAISEO->robots_noindex == 0 && 
											$postAISEO->robots_nofollow == 0 &&
											$postAISEO->robots_noarchive == 0 &&
											$postAISEO->robots_notranslate == 0 &&
											$postAISEO->robots_noimageindex == 0 &&
											$postAISEO->robots_nosnippet == 0 &&
											$postAISEO->robots_noodp == 0 &&

											$postAISEO->robots_max_snippet == -1 &&
											$postAISEO->robots_max_videopreview == -1 &&
											$postAISEO->robots_max_imagepreview == 'large'){
																				

											$postAISEO->robots_default = true;	
										}									
										
										$postAISEO->save();
									break;
									case "yoast":
										delete_post_meta($post_id, sanitize_text_field( $active_key ));
									break;								
									
								}
								//no matter what, we keep track of the current setting 
								//just in case the Yoast or AISEO plugin are disabled
								delete_post_meta($post_id, '_bnitk_mfd_meta-robots-'.$directive);				
							}
							
						}else{
							
							if($active_key){
								//check to see which plugin is being used
								switch($this->active_seo_plugin){

									case "aioseo":
										$postAISEO->robots_default = false; 
										$postAISEO->{$active_key} = $key_val;
										$postAISEO->save();  
									break;
									case "yoast":
										update_post_meta( $post_id, sanitize_text_field( $active_key ), $key_val );
									break;
									
									
								}
								//no matter what, we keep track of the current setting 
								//just in case the Yoast or AISEO plugin are disabled
								update_post_meta( $post_id, '_bnitk_mfd_meta-robots-'.$directive, $key_val );
							} 
									
						}
						


				
						//let the user know that the update was successful
						if($key_val == 1){
							$message = 'The post\'s robots directive has been set to '.$directive;
						}else{
							$message = 'The '.$directive.' directive has been removed from the post';
						}
						$status = array(
							'status' => 'OK',
							'directive' => $directive,
							'msg' => $message,
							'val' => $key_val,
							'post_id' => $post_id
						);

					}else{
						$status = array(
							'status' => 'err',
							'msg' => 'No post was selected'
						);

					}
				}else{
					$status = array(
							'status' => 'err',
							'msg' => 'Edit failed. Must be logged in to make edits.'
						);

				}					
				
				echo json_encode($status);
			    
			    wp_die(); 
			}



	

	/**
		 * Update a single term's noindex and nofollow status
		 * this function is used as an AJAX callback to modify the noindex/nofollow 
		 * status of a single post or page 
		 * @access public
		 * @return void
		 */
		public function update_cat_callback() {
				
				$new_val = 0;								
				
				//double check that the user has the neccessary permissions to edit posts	
				//and that the nonce is correct

				$term_id = (int)sanitize_text_field($_POST['post_id']);
				$nonce = sanitize_text_field($_POST['nonce']);
				
				
				if(current_user_can( 'manage_options') &&  wp_verify_nonce( $nonce, 'bulk-noindex' )){
					
					if(isset($_POST) && isset($_POST['result'])){
						
						$directive = sanitize_text_field(str_replace('[]','',$_POST['check_class']));
						
						$active_key = $this->get_meta_keys($directive);
						
						$term_id = (int)sanitize_text_field($_POST['post_id']);
						$key_val = (sanitize_text_field($_POST['result']) == 1) ? 1 : 0;
					
						
				
						//since AISEO uses it's own DB tables to store the post's robots settings
						//we need to access the post settings via AISEO
						/*
						if($this->active_seo_plugin == 'aioseo'){
							
							$postAISEO = aioseo()->core->db->start( 'aioseo_posts' )
							->where( 'post_id', $post_id )
							->run()
							->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' ); 					
						} */

						/**
						* Always update the fallback post_meta key that's specific for this plugin
						* that way if any of the supported plugins are disabled, we don't lose
						* track of the pages which should be noindexed
						**/
							
						if($key_val == 0){

							if($active_key){

								update_term_meta( $term_id, '_bnitk_mfd_meta-robots-'.$directive, $key_val );

								//check to see which plugin is being used
								
								//no matter what, we keep track of the current setting 
								//just in case the Yoast or AISEO plugin are disabled
								delete_term_meta($term_id, '_bnitk_mfd_meta-robots-'.$directive);				
							}
							
						}else{
							
							if($active_key){
						
								//no matter what, we keep track of the current setting 
								//just in case the Yoast or AISEO plugin are disabled
								update_term_meta( $term_id, '_bnitk_mfd_meta-robots-'.$directive, $key_val );
							} 
									
						}
						


				
						//let the user know that the update was successful
						if($key_val == 1){
							$message = 'The term\'s robots directive has been set to '.$directive;
						}else{
							$message = 'The '.$directive.' directive has been removed from the term';
						}
						$status = array(
							'status' => 'OK',
							'directive' => $directive,
							'msg' => $message,
							'val' => $key_val,
							'term_id' => $term_id
						);

					}else{
						$status = array(
							'status' => 'err',
							'msg' => 'No post was selected'
						);

					}
				}else{
					$status = array(
							'status' => 'err',
							'msg' => 'Edit failed. Must be logged in to make edits.'
						);

				}					
				
				echo json_encode($status);
			    
			    wp_die(); 
			}



	}


} //end check for the existing class