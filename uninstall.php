<?php

/*
	This script is executed when the (inactive) plugin is deleted through the admin backend.
	
	It removes the plugin settings from the option table and all tag groups. It does not change the term_group field of the taxonomies.
	
	last change: version 0.7.1
*/

if( defined('WP_UNINSTALL_PLUGIN') ) {

	delete_option( 'tag_group_taxonomy' );

	delete_option( 'tag_group_labels' );

	delete_option( 'tag_group_ids' );

	delete_option( 'tag_group_theme' );

	delete_option( 'max_tag_group_id' );

	delete_option( 'tag_group_mouseover' );

	delete_option( 'tag_group_collapsible' );

	delete_option( 'tag_group_enqueue_jquery' );

	delete_option( 'tag_group_shortcode_widget' );

}
 
?>