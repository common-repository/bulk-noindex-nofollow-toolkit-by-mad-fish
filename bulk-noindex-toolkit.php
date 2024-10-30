<?php
/*
Plugin Name: Bulk NoIndex & NoFollow Toolkit (by Mad Fish)
Plugin URI: https://www.madfishdigital.com/wp-plugin/
Description: Easily make bulk changes to the noindex and nofollow robots directive of pages, posts, custom post types, categories, and terms in your website
Author: Mad Fish Digital
Version: 2.16
Author URI: https://www.madfishdigital.com/
License: GPLv3


*/
	include('inc/bulk-noindex-toolkit-class.php');
	
	$bulkToolKit_plugin = new bulkNoindexToolkit();

	if (is_admin()){
		//implement the link into the admin menu
		add_action('admin_menu', array($bulkToolKit_plugin,'create_menu'));

		//allow for ajax functionality on the option page
		add_action( 'wp_ajax_update_page_callback', array($bulkToolKit_plugin, 'update_page_callback') );
		add_action( 'wp_ajax_update_page_bulk_callback', array($bulkToolKit_plugin, 'update_page_bulk_callback' ));	

		add_action( 'wp_ajax_update_cat_callback', array($bulkToolKit_plugin, 'update_cat_callback') );
		add_action( 'wp_ajax_update_cat_bulk_callback', array($bulkToolKit_plugin, 'update_cat_bulk_callback' ));	
	}

			

	//update this plugins post_meta data when posts are edited through the WP editor
	add_action( 'save_post', array($bulkToolKit_plugin, 'after_updated_post'), 12, 3 );	

	//implement a robots meta tag if one is not implemented by another plugin
	add_action('wp_head', array($bulkToolKit_plugin,'check_page_status'),0);