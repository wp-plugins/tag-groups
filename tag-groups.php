<?php
/*
Plugin Name: Tag Groups
Plugin URI: http://www.christoph-amthor.de/software/tag-groups/
Description: Assign tags to groups and display them in a tabbed tag cloud
Author: Christoph Amthor
Version: 0.10
Author URI: http://www.christoph-amthor.de
License: GNU GENERAL PUBLIC LICENSE, Version 3
*/

define("TAG_GROUPS_VERSION", "0.10");

define("TAG_GROUPS_BUILT_IN_THEMES", "ui-gray,ui-lightness,ui-darkness");

define("TAG_GROUPS_STANDARD_THEME", "ui-gray");



add_action( 'init', 'tg_widget_hook' );

add_action( 'admin_init', 'tg_register_settings' );

add_action( 'admin_menu', 'tg_register_tag_label_page' );

add_shortcode( 'tag_groups_cloud', 'tag_groups_cloud' );

add_action( 'wp_enqueue_scripts', 'tg_add_js_css' );

add_action( 'admin_enqueue_scripts', 'tg_add_admin_js_css' );

add_action( 'wp_head', 'tg_custom_js' );


function tg_widget_hook() {
/*
	Hooks for frontend
*/

	$tag_group_shortcode_widget = get_option( 'tag_group_shortcode_widget', 0 );

	if ( $tag_group_shortcode_widget ) add_filter('widget_text', 'do_shortcode');

}

function tg_register_settings() {
/*
	Initial settings after calling the plugin
*/

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	add_action( "{$tag_group_taxonomy}_edit_form_fields", 'tg_tag_input_metabox' );

	add_action( "{$tag_group_taxonomy}_add_form_fields", 'tg_create_new_tag' );

	add_filter( "manage_edit-{$tag_group_taxonomy}_columns", 'tg_add_taxonomy_columns' );

	add_filter( "manage_{$tag_group_taxonomy}_custom_column", 'tg_add_taxonomy_column_content', 10, 3 );

	add_action( 'quick_edit_custom_box', 'tg_quick_edit_tag', 10, 3 );
	
	add_action( 'create_term', 'tg_update_edit_term_group' );
		
	add_action( 'edit_term', 'tg_update_edit_term_group' );
	
	$plugin = plugin_basename(__FILE__);

	add_filter("plugin_action_links_$plugin", 'tg_plugin_settings_link' );
	
	add_action('admin_footer', 'tg_quick_edit_javascript');

	add_filter('tag_row_actions', 'tg_expand_quick_edit_link', 10, 2);
	
	add_action( 'restrict_manage_posts', 'tg_add_filter' );

	add_filter( 'parse_query', 'tg_apply_filter' );

	tg_init();

}


function tg_plugin_settings_link($links) {
/*
	adds Settings link to plugin list
*/

  $settings_link = '<a href="edit.php?page=tag-groups-settings">Settings</a>'; 
  array_unshift($links, $settings_link); 

  return $links; 

}
 

function tg_add_admin_js_css() {
/*
	adds css to backend
*/

	wp_register_style( 'tag-groups-css-backend', plugins_url('css/style.css', __FILE__) );
	
	wp_enqueue_style( 'tag-groups-css-backend' );

}


function tg_add_js_css() {
/*
	adds js and css to frontend
*/

	$theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

	$default_themes = explode( ',', TAG_GROUPS_BUILT_IN_THEMES );
	
	$tag_group_enqueue_jquery = get_option( 'tag_group_enqueue_jquery', true );


	if ($tag_group_enqueue_jquery) {

		wp_enqueue_script('jquery');

		wp_enqueue_script('jquery-ui-core');

		wp_enqueue_script('jquery-ui-tabs');

	}

	if ($theme == '' ) return;
	
	if (in_array($theme, $default_themes)) {

		wp_register_style( 'tag-groups-css-frontend-theme-1', plugins_url('css/'.$theme.'/jquery-ui-1.10.2.custom.min.css', __FILE__) );
		
		wp_register_style( 'tag-groups-css-frontend-theme-2', plugins_url('css/jquery-ui.default.min.css', __FILE__) );

	} else {
	
		if ( file_exists( WP_CONTENT_DIR.'/uploads/'.$theme.'/jquery-ui.min.css' ) ) {
		
			wp_register_style( 'tag-groups-css-frontend-theme-2', get_bloginfo('wpurl').'/wp-content/uploads/'.$theme.'/jquery-ui.min.css' );
			
		} else {
		
			wp_register_style( 'tag-groups-css-frontend-theme-2', plugins_url('css/jquery-ui.default.min.css', __FILE__) );
		
		}

		$dh  = @opendir(WP_CONTENT_DIR.'/uploads/'.$theme);
		
		if ($dh) {
		
			while (false !== ($filename = readdir($dh))) {

			    if (preg_match("/jquery-ui-\d+\.\d+\.\d+\.custom\.(min\.)?css/i", $filename) ) {
		    		wp_register_style( 'tag-groups-css-frontend-theme-1', get_bloginfo('wpurl').'/wp-content/uploads/'.$theme.'/'.$filename );
		    	
		    		break;
		    
		    	}
		    
		    }
		
		}

	}

	wp_enqueue_style( 'tag-groups-css-frontend-theme-1' );

	wp_enqueue_style( 'tag-groups-css-frontend-theme-2' );
	
}


function tg_register_tag_label_page() {
/*
	adds the submenus to the admin backend
*/

	add_submenu_page( 'edit.php', 'Tag Groups', 'Tag Groups', 'edit_pages', 'tag-groups', 'tg_group_administration' );

	add_submenu_page( 'edit.php', 'Tag Groups Settings', 'Tag Groups Settings', 'manage_options', 'tag-groups-settings', 'tg_settings_page');

}


function tg_add_taxonomy_columns($columns) {
/*
	adds a custom column to the table of tags/terms
	thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/
*/
		
	$columns['term_group'] = __('Tag Group', 'tag-groups');
	
	return $columns;
 		
}

	
function tg_add_taxonomy_column_content($empty = '', $empty = '', $term_id) {
/*
	adds data into custom column of the table for each row
	thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/
*/

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	$tag = get_term($term_id, $tag_group_taxonomy);
	
	$i = array_search($tag->term_group, $tag_group_ids);

	return $tag_group_labels[$i];

}


function tg_update_edit_term_group($term_id) {
/*
	get the $_POSTed value after saving a tag/term and save it in the table
*/

	// next two lines to prevent infinite loops when the hook edit_term is called again from the function wp_update_term

	global $tg_update_edit_term_group_called;

	if ( $tg_update_edit_term_group_called > 0 ) return;
	
	$screen = get_current_screen();

	if ( !isset($_POST['term-group']) && !isset($_POST['term-group-option']) ) return;
	
	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	if ( is_object($screen) && ($screen->taxonomy != $tag_group_taxonomy) && (!isset($_POST['new-tag-created']))) return;
	
	$tg_update_edit_term_group_called++;
	
	if ( current_user_can('edit_posts') ) {

		$term_id = (int) $term_id;

		$term = array();
		

		if ( isset($_POST['term-group-option']) ) {

			if ( !isset($_POST['tag-groups-option-nonce']) || ! wp_verify_nonce($_POST['tag-groups-option-nonce'], 'tag-groups-option') ) die("Security check");

			$term['term_group'] = (int) $_POST['term-group-option'];

		} elseif ( isset($_POST['term-group']) ) {

			if ( !isset($_POST['tag-groups-nonce']) || ! wp_verify_nonce($_POST['tag-groups-nonce'], 'tag-groups') ) die("Security check");

			$term['term_group'] = (int) $_POST['term-group'];

		}

		if ( isset($_POST['name']) && ($_POST['name'] != '') ) $term['name'] = stripslashes(sanitize_text_field($_POST['name']));

		if ( isset($_POST['slug']) && ($_POST['slug'] != '') ) $term['slug'] = sanitize_title($_POST['slug']);

		if ( isset($_POST['description']) && ($_POST['description'] != '') ) $term['description'] = stripslashes($_POST['description']);
		
		wp_update_term( $term_id, $tag_group_taxonomy, $term );
		
	} else wp_die( __( 'Cheatin&#8217; uh?' ) );

}

 
function tg_quick_edit_javascript() {
/*
	adds JS function that sets the saved tag group for a given element when it's opened in quick edit
	thanks to http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
*/

	$screen = get_current_screen();
	
	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	if ( $screen->taxonomy != $tag_group_taxonomy ) return;
 
	?>
	<script type="text/javascript">
	<!--
	function set_inline_tag_group_selected(tag_group_Selected, nonce) {
		inlineEditTax.revert();
		var tag_group_Input = document.getElementById('term-group-option');
		var nonceInput = document.getElementById('tag-groups-option-nonce');
		nonceInput.value = nonce;
		for (i = 0; i < tag_group_Input.options.length; i++) {
			if (tag_group_Input.options[i].value == tag_group_Selected) { 
				tag_group_Input.options[i].setAttribute("selected", "selected");
			} else { tag_group_Input.options[i].removeAttribute("selected");}
		}
	}

	//-->
	</script>
	<?php
}


function tg_expand_quick_edit_link($actions, $tag) {
/*
	modifies Quick Edit link to call JS when clicked
	thanks to http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
*/

	$screen = get_current_screen();

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	if ( is_object($screen) && ( $screen->taxonomy != $tag_group_taxonomy ) ) return $actions;
 
	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$tag_group_id = $tag->term_group;
	
	$nonce = wp_create_nonce('tag-groups-option');
	
	$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';

	$actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '" ';

	$actions['inline hide-if-no-js'] .= " onclick=\"set_inline_tag_group_selected('{$tag_group_id}', '{$nonce}')\">"; 

	$actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );

	$actions['inline hide-if-no-js'] .= '</a>';

	return $actions;	
}


function tg_quick_edit_tag() {
/*
	assigning tags to tag groups directly in tag table ('quick edit')
*/

	$screen = get_current_screen();

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	if ( $screen->taxonomy != $tag_group_taxonomy ) return;

 	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	?>

		<fieldset><div class="inline-edit-col">
		
		<label><span class="title"><?php _e( 'Group' , 'tag-groups') ?></span><span class="input-text-wrap">
		
		<select id="term-group-option" name="term-group-option" class="ptitle">
		
			<option value="0" ><?php _e('not assigned', 'tag-groups') ?></option>

			<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>" ><?php echo $tag_group_labels[$i]; ?></option>

		<?php endfor; ?>

		</select>

		<input type="hidden" name="tag-groups-option-nonce" id="tag-groups-option-nonce" value="" />

		</span></label>
		
		</div></fieldset>
	<?php
	
}


function tg_create_new_tag($tag) {
/*
	assigning tags to tag groups upon new tag creation (left of the table)
*/

 	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	?>

	<div class="form-field"><label for="term-group"><?php _e('Tag Group', 'tag-groups') ?></label>
	
	<select id="term-group" name="term-group">
		<option value="0" selected ><?php _e('not assigned', 'tag-groups') ?></option>

		<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>"><?php echo $tag_group_labels[$i]; ?></option>

		<?php endfor; ?>

		</select>		
	<input type="hidden" name="tag-groups-nonce" id="tag-groups-nonce" value="<?php echo wp_create_nonce('tag-groups') ?>" />
	<input type="hidden" name="new-tag-created" id="new-tag-created" value="1" />
	</div>

	<?php
}


function tg_tag_input_metabox($tag) {
/*
	assigning tags to tag groups on single tag view (after clicking tag for editing)
*/

 	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$number_of_tag_groups = count($tag_group_labels) - 1; ?>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="tag_widget"><?php _e('Tag group' , 'tag-groups') ?></label></th>
		<td>
		<select id="term-group" name="term-group">
			<option value="0" <?php if ($tag->term_group == 0) echo 'selected'; ?> ><?php _e('not assigned', 'tag-groups') ?></option>

		<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>"

			<?php if ($tag->term_group == $tag_group_ids[$i]) echo 'selected'; ?> ><?php echo $tag_group_labels[$i]; ?></option>

		<?php endfor; ?>

		</select>
		<input type="hidden" name="tag-groups-nonce" id="tag-groups-nonce" value="<?php echo wp_create_nonce('tag-groups') ?>" />
		<p><a href="edit.php?page=tag-groups"><?php _e('Edit tag groups' , 'tag-groups') ?></a>. (<?php _e('Clicking will leave this page without saving.', 'tag-groups') ?>)</p>
		</td>
	</tr>

<?php
}


function tg_init() {
/*
	If it doesn't exist: create the default group with ID 0 that will only show up on tag pages as "unassigned".
*/

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ((!isset($tag_group_labels)) || (!isset($tag_group_labels[0])) || ($tag_group_labels[0] == '')) {

		$tag_group_labels[0] = 'not assigned';

		$tag_group_ids[0] = 0;

		$number_of_tag_groups = 0;

		$max_tag_group_id = 0;
		
		$tag_group_taxonomy = 'post_tag';

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		update_option( 'max_tag_group_id', $max_tag_group_id );
		
		update_option( 'tag_group_taxonomy', $tag_group_taxonomy );

		$tag_group_theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

		if ($tag_group_theme == '') $tag_group_theme = TAG_GROUPS_STANDARD_THEME;

	}
}


function tg_group_administration() {
/*
	Outputs a table on a submenu page where you can add, delete, change tag groups, their labels and their order.
*/

	$tag_group_labels = get_option( 'tag_group_labels', array());

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$max_tag_group_id = get_option( 'max_tag_group_id', 0 );
		
	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ($max_tag_group_id < 0) $max_tag_group_id = 0;

	if (isset($_REQUEST['action'])) $action = $_REQUEST['action']; else $action = '';

	if (isset($_GET['id'])) (int) $tag_groups_id = $_GET['id']; else $tag_groups_id = 0;

	if (isset($_POST['ok'])) $ok = $_POST['ok']; else $ok = '';


	?>
	
	<div class='wrap'>
	<h2>Tag Groups</h2>
	
<?php

	// save a new label
	if (isset($_POST['label'])) {
	
		$label = stripslashes(sanitize_text_field($_POST['label']));
		
		if ($label == '') : ?>
	
			<div class="updated fade"><p>
			<?php _e('The label cannot be empty. Please correct it or go back.', 'tag-groups') ?>
			</p></div><br clear="all" /><?php
	
		elseif ((is_array($tag_group_labels)) && (in_array($label, $tag_group_labels))) : ?>
	
			<div class="updated fade"><p>
			<?php _e( 'A tag group with the label \''.$label.'\' already exists, or the label has not changed. Please choose another one or go back.', 'tag-groups' ) ?>
			</p></div><br clear="all" /> <?php
	
		else:

			if ( !isset($_POST['tag-groups-settings-nonce']) || ! wp_verify_nonce($_POST['tag-groups-settings-nonce'], 'tag-groups-settings') ) die("Security check");
	
	
			if (isset($tag_groups_id) && $tag_groups_id!='0' && $tag_groups_id!='') {

			// update
		
				tg_unregister_string_wpml( $tag_group_labels[$tag_groups_id] );
				
				$tag_group_labels[$tag_groups_id] = $label;
				
				tg_register_string_wpml( 'Group Label ID '.$tag_groups_id, $tag_group_labels[$tag_groups_id] );
				
			} else {
			//new

				$max_tag_group_id++;

				$number_of_tag_groups++;

				$tag_group_labels[$number_of_tag_groups] = $label;
				
				tg_register_string_wpml( 'Group Label ID '.$number_of_tag_groups, $label );

				$tag_group_ids[$number_of_tag_groups] = $max_tag_group_id;
				
			}
	
			update_option( 'tag_group_labels', $tag_group_labels );
	
			update_option( 'tag_group_ids', $tag_group_ids );
	
			update_option( 'max_tag_group_id', $max_tag_group_id ); ?>

			<div class="updated fade"><p>
			<?php _e( 'The tag group with the label \''.$label.'\' has been saved!', 'tag-groups' ) ?>
			</p></div><br clear="all" />

			<?php
			$action = '';

			$tag_group_labels = get_option( 'tag_group_labels', array() );

			$tag_group_ids = get_option( 'tag_group_ids', array() );

			$number_of_tag_groups = count($tag_group_labels) - 1;	

		endif;
	
	} else {
	
		$label = '';
	
	}
	
	// change order - move up
	if (($action == 'up') && ($tag_groups_id > 1)) {
	
		tg_swap($tag_group_labels,$tag_groups_id-1,$tag_groups_id);

		tg_swap($tag_group_ids,$tag_groups_id-1,$tag_groups_id);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		$action = "";
	
	}
	
	// change order - move down
	if (($action == 'down') && ($tag_groups_id < $number_of_tag_groups)) {

		tg_swap($tag_group_labels,$tag_groups_id,$tag_groups_id+1);

		tg_swap($tag_group_ids,$tag_groups_id,$tag_groups_id+1);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		$action = "";
	
	}

	switch ($action) {
	case 'new': ?>
	
		<h3><?php _e('Create a new tag group', 'tag-groups' ) ?></h3>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="tag-groups-settings-nonce" id="tag-groups-settings-nonce" value="<?php echo wp_create_nonce('tag-groups-settings') ?>" />
		<ul>
			<li><label for="label"><?php _e('Label' , 'tag-groups') ?>: </label>
			<input id="label" maxlength="100" size="70" name="label" value="<?php echo $label ?>" /></li>   
		</ul>
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Create Group', 'tag-groups'); ?>' id='submitbutton' />
		<input class='button-primary' type='button' name='Cancel' value='<?php _e('Cancel'); ?>' id='cancel' onclick="location.href='edit.php?page=tag-groups'"/>
		</form>
	<?php break;
	
	case 'edit': ?>
	
		<h3><?php _e('Edit the label of an existing tag group', 'tag-groups' ) ?></h3>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="tag-groups-settings-nonce" id="tag-groups-settings-nonce" value="<?php echo wp_create_nonce('tag-groups-settings') ?>" />
		<ul>
			<li><label for="label"><?php _e('Label', 'tag-groups' ) ?>: </label>
			<input id="label" maxlength="100" size="70" name="label" value="<?php echo $tag_group_labels[$tag_groups_id] ?>" /></li>   
		</ul>
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Group', 'tag-groups' ); ?>' id='submitbutton' />
		<input class='button-primary' type='button' name='Cancel' value='<?php _e('Cancel'); ?>' id='cancel' onclick="location.href='edit.php?page=tag-groups'"/>
		</form>

	<?php break;

	case 'delete':

		if (($tag_groups_id < 1) || ($tag_groups_id > $max_tag_group_id)) break;

		$label = $tag_group_labels[$tag_groups_id];

		$id = $tag_group_ids[$tag_groups_id];

		if ( !isset($_GET['tag-groups-delete-nonce']) || ! wp_verify_nonce($_GET['tag-groups-delete-nonce'], 'tag-groups-delete-'.$tag_groups_id) ) die("Security check");

		array_splice($tag_group_labels, $tag_groups_id, 1);

		array_splice($tag_group_ids, $tag_groups_id, 1);
		
		tg_unregister_string_wpml('Group Label ID '.$id);

		$max = 0;
		
		foreach($tag_group_ids as $check_id) {	
		
			if ($check_id > $max) $max = $check_id;
			
		}
		
		$max_tag_group_id = $max;

		tg_unassign($tag_groups_id);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		update_option( 'max_tag_group_id', $max_tag_group_id ); ?>
		
		<div class="updated fade"><p>
			<?php printf(__('A tag group with the id %s and the label \'%s\' has been deleted.', 'tag-groups'), $id, $label); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
	<?php break;

	default:
?>
		<p><?PHP _e('On this page you can define tag groups. Tags (or terms) can be assigned to these groups on the page where you edit the tags (terms).', 'tag-groups') ?></p>
		<h3><?php _e('List', 'tag-groups') ?></h3>
		<table class="widefat">
		<thead>
		<tr>
			<th><?php _e('ID', 'tag-groups') ?></th>
			<th><?php _e('Label displayed on the frontend', 'tag-groups') ?></th>
			<th><?php _e('Number of assigned tags', 'tag-groups') ?></th>
			<th><?php _e('Action', 'tag-groups') ?></th>
			<th><?php _e('Change sort order', 'tag-groups') ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th><?php _e('ID', 'tag-groups') ?></th>
			<th><?php _e('Label displayed on the frontend', 'tag-groups') ?></th>
			<th><?php _e('Number of assigned tags', 'tag-groups') ?></th>
			<th><?php _e('Action', 'tag-groups') ?></th>
			<th><?php _e('Change sort order', 'tag-groups') ?></th>
		</tr>
		</tfoot>
		<tbody>

		<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

		   <tr>
			 <td><?php echo $tag_group_ids[$i]; ?></td>
			 <td><?php echo $tag_group_labels[$i] ?></td>
			 <td><?php echo tg_number_assigned($tag_group_ids[$i]) ?></td>
			 <td><a href="edit.php?page=tag-groups&action=edit&id=<?php echo $i; ?>"><?php _e('Edit') ?></a>, <a href="#" onclick="var answer = confirm('<?PHP _e('Do you really want to delete the tag group', 'tag-groups') ?> \'<?php echo esc_js($tag_group_labels[$i]) ?>\'?'); if( answer ) {window.location ='edit.php?page=tag-groups&action=delete&id=<?php echo $i ?>&tag-groups-delete-nonce=<?php echo wp_create_nonce('tag-groups-delete-'.$i) ?>'}"><?php _e('Delete') ?></a></td>
			 <td>
				 <div style="overflow:hidden; position:relative;height:15px;width:27px;clear:both;">
				 <?php if ($i > 1) :?>
				 	<a href="edit.php?page=tag-groups&action=up&id=<?php echo $i ?>">
				 	<div class="tag-groups-up"></div>
				 	</a>
				<?php endif; ?>
				</div>

				 <div style="overflow:hidden; position:relative;height:15px;width:27px;clear:both;">
				<?php if ($i < $number_of_tag_groups) :?>
				 	<a href="edit.php?page=tag-groups&action=down&id=<?php echo $i ?>">
				 	<div class="tag-groups-down"></div>
				 	</a>
				<?php endif; ?>
				</div>
			</td>
		  	</tr>

		<?php endfor; ?>

		<tr>
		 <td><?php _e('new') ?></td>
		 <td></td>
		 <td></td>
		 <td><a href="edit.php?page=tag-groups&action=new"><?php _e('Create') ?></a></td>
		 <td></td>
		</tr>
		</tbody>
		</table>
	</div>
	
	<?php if ( current_user_can('manage_options') ) :	?>
		<p><a href="edit.php?page=tag-groups-settings"><?php _e('Go to the settings.' , 'tag-groups') ?></a></p>
	<?php endif;	?>
	
	<?php }	?>
	
	<?php
}


function tg_settings_page() {
/*
	Outputs the general settings page and handles the main actions: select taxonomy, theming options, WPML integration, reset all
*/

	$tag_group_labels = get_option( 'tag_group_labels', array());

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$max_tag_group_id = get_option( 'max_tag_group_id', 0 );
	
	$tag_group_theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );
	
	$tag_group_mouseover = get_option( 'tag_group_mouseover', '' );

	$tag_group_collapsible = get_option( 'tag_group_collapsible', '' );
	
	$tag_group_enqueue_jquery = get_option( 'tag_group_enqueue_jquery', true );
	
	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	$tag_group_shortcode_widget = get_option( 'tag_group_shortcode_widget' );

	$number_of_tag_groups = count($tag_group_labels) - 1;
	
	$show_filter = get_option( 'tag_group_show_filter', true );


	if ($max_tag_group_id < 0) $max_tag_group_id = 0;
	
	$default_themes = explode(',', TAG_GROUPS_BUILT_IN_THEMES);

	$label = '';
	
	$active_tab = 0;
	?>
	
	<div class='wrap'>
	<h2>Tag Groups Settings</h2>
	
	<?php

	// performing actions
	
	if (isset($_REQUEST['action'])) $action = $_REQUEST['action']; else $action = '';

	if (isset($_GET['id'])) (int) $tag_groups_id = $_GET['id']; else $tag_groups_id = 0;
	
	if (isset($_POST['theme-name'])) $theme_name = stripslashes(sanitize_text_field($_POST['theme-name'])); else $theme_name = '';
	
	if (isset($_POST['theme'])) $theme = stripslashes(sanitize_text_field($_POST['theme'])); else $theme = '';
	
	if (isset($_POST['taxonomy'])) $taxonomy = stripslashes(sanitize_text_field($_POST['taxonomy'])); else $taxonomy = '';

	if (isset($_POST['ok'])) $ok = $_POST['ok']; else $ok = '';

	if (isset($_GET['active-tab'])) $active_tab = (int) $_GET['active-tab'];
		
	if ( $active_tab<0 || $active_tab>5) $active_tab = 0;	


	if (($action == 'reset') && ($ok != 'yes')) $action = '';
	
	
	$number_of_tag_groups = count($tag_group_labels) - 1;
	
	switch ($action) {
	
	case 'widget':
	
		if ( !isset($_POST['tag-groups-widget-nonce']) || ! wp_verify_nonce($_POST['tag-groups-widget-nonce'], 'tag-groups-widget') ) die("Security check");

		if ( isset($_POST['widget']) && ($_POST['widget'] == '1') ) {
		
			update_option( 'tag_group_shortcode_widget', 1 );
		
		} else {
		
			update_option( 'tag_group_shortcode_widget', 0 );
		
		}
		
		?>
		<div class="updated fade"><p>
			<?php _e('Settings saved.', 'tag-groups'); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=3'"/>
		<?php
		
	break;
	
	case 'reset':

		if ( !isset($_POST['tag-groups-reset-nonce']) || ! wp_verify_nonce($_POST['tag-groups-reset-nonce'], 'tag-groups-reset') ) die("Security check");

 		$tag_group_labels = array();

 		$tag_group_ids = array();

 		$max_tag_group_id = 0;

 		update_option( 'tag_group_labels', $tag_group_labels );
	
		update_option( 'tag_group_ids', $tag_group_ids );
	
		update_option( 'max_tag_group_id', $max_tag_group_id );

		tg_unassign(0);
		
		?>
		<div class="updated fade"><p>
			<?php _e('All groups are deleted and assignments reset.', 'tag-groups'); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=4'"/>
		<?php
	break;
	
	case 'wpml':

		for ($i = 1; $i <= $number_of_tag_groups; $i++) {

			tg_register_string_wpml( 'Group Label ID '.$i, $tag_group_labels[$i] );

		} ?>
		
		<div class="updated fade"><p>
			<?php _e('All labels were registered.', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=2'"/>

	<?php break;

	case 'theme':

		if ($theme == 'own') $theme = $theme_name;

		if ( !isset($_POST['tag-groups-settings-nonce']) || ! wp_verify_nonce($_POST['tag-groups-settings-nonce'], 'tag-groups-settings') ) die("Security check");

		update_option( 'tag_group_theme', $theme );
		
		$mouseover = (isset($_POST['mouseover']) && $_POST['mouseover'] == '1') ? true : false;

		$collapsible = (isset($_POST['collapsible']) && $_POST['collapsible'] == '1') ? true : false;
		
		update_option( 'tag_group_mouseover', $mouseover );

		update_option( 'tag_group_collapsible', $collapsible );

		$tag_group_enqueue_jquery = ($_POST['enqueue-jquery'] && $_POST['enqueue-jquery'] == '1') ? true : false;
		
		update_option( 'tag_group_enqueue_jquery', $tag_group_enqueue_jquery );
		
		tg_clear_cache();

		?> <div class="updated fade"><p>
		<?php _e('Your tag cloud theme settings have been saved.', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=1'"/>
		<?php
		
	break;

	case 'taxonomy':

		if ( !isset($_POST['tag-groups-taxonomy-nonce']) || !wp_verify_nonce($_POST['tag-groups-taxonomy-nonce'], 'tag-groups-taxonomy') ) die("Security check");

		$args=array(
			'public'   => true
		);
		
		$taxonomies=get_taxonomies( $args, 'names' );
		
		if ( !in_array( $taxonomy, $taxonomies ) ) die("Security check t");

		update_option( 'tag_group_taxonomy', $taxonomy );
				
		tg_clear_cache();

		?> <div class="updated fade"><p>
		<?php _e('Your tag taxonomy settings have been saved.', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=0'"/>
		<?php
		
	break;

	case 'backend':

		if ( !isset($_POST['tag-groups-backend-nonce']) || !wp_verify_nonce($_POST['tag-groups-backend-nonce'], 'tag-groups-backend') ) die("Security check");
	
		$show_filter = isset($_POST['filter']) ? 1 : 0;

		update_option( 'tag_group_show_filter', $show_filter );
				
		?> <div class="updated fade"><p>
		<?php _e('Your back end settings have been saved.', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups-settings&active-tab=0'"/>
		<?php
		
	break;

	default:		
		?>
		<h2 class="nav-tab-wrapper">
			<a href="edit.php?page=tag-groups-settings&active-tab=0" class="nav-tab <?php if ( $active_tab == 0 ) echo 'nav-tab-active' ?>"><?php _e('Basics', 'tag-groups') ?></a>
			<a href="edit.php?page=tag-groups-settings&active-tab=1" class="nav-tab <?php if ( $active_tab == 1 ) echo 'nav-tab-active' ?>"><?php _e('Theme', 'tag-groups') ?></a>
			<?php if (function_exists('icl_register_string')) :?>
				<a href="edit.php?page=tag-groups-settings&active-tab=2" class="nav-tab <?php if ( $active_tab == 2 ) echo 'nav-tab-active' ?>"><?php _e('WPML', 'tag-groups') ?></a>
			<?php endif; ?>
			<a href="edit.php?page=tag-groups-settings&active-tab=3" class="nav-tab <?php if ( $active_tab == 3 ) echo 'nav-tab-active' ?>"><?php _e('Tag Cloud', 'tag-groups') ?></a>
			<a href="edit.php?page=tag-groups-settings&active-tab=4" class="nav-tab <?php if ( $active_tab == 4 ) echo 'nav-tab-active' ?>"><?php _e('Delete Groups', 'tag-groups') ?></a>
			<a href="edit.php?page=tag-groups-settings&active-tab=5" class="nav-tab <?php if ( $active_tab == 5 ) echo 'nav-tab-active' ?>"><?php _e('About', 'tag-groups') ?></a>
		</h2>
		<p>&nbsp;</p>

		<?php if ( $active_tab == 0 ): ?>
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="tag-groups-taxonomy-nonce" id="tag-groups-taxonomy-nonce" value="<?php echo wp_create_nonce('tag-groups-taxonomy') ?>" />
			<h3>Taxonomy</h3>
			<p><?php _e('Choose the taxonomy for which you want to use tag groups. Default is <b>post_tag</b>. Please note that the tag cloud might not work with all taxonomies and that some taxonomies listed here may not be accessible in the admin backend. If you don\'t understand what is going on here, just leave the default.', 'tag-groups') ?></p>
			<?php
			$args=array(
				'public'   => true
			);
			
			$taxonomies=get_taxonomies( $args, 'names' );
			?>
			
			<ul>
		
				<?php foreach( $taxonomies as $taxonomy ) : ?>
		
					<li><input type="radio" name="taxonomy" id="<?php echo $taxonomy ?>" value="<?php echo $taxonomy ?>" <?php if ($tag_group_taxonomy == $taxonomy) echo 'checked'; ?> />&nbsp;<label for="<?php echo $taxonomy ?>"><?php echo $taxonomy ?></label></li>
		
				<?php endforeach; ?>
		
			</ul>
	
			<input type="hidden" name="action" value="taxonomy">
			<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Taxonomy', 'tag-groups'); ?>' id='submitbutton' />
			</form>
			<p>&nbsp;</p>
			
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="tag-groups-backend-nonce" id="tag-groups-backend-nonce" value="<?php echo wp_create_nonce('tag-groups-backend') ?>" />
			
			<h3>Back End Settings</h3>
			<p><?php _e('You can add a pull-down menu to the filters above the list of posts. If you filter posts by tag groups, then only items will be shown that have tags (terms) in that particular group. This feature can be turned off so that the menu won\'t obstruct your screen if you use a high number of groups. (May not work with all custom taxonomies.)', 'tag-groups') ?></p>
			<ul>
				<li><input type="checkbox" id="tg_filter" name="filter" value="1" <?php if ( $show_filter ) echo 'checked'; ?> />&nbsp;<label for="tg_filter"><?php _e('Display filter menu', 'tag-groups') ?></label></li>
			</ul>			
			<input type="hidden" name="action" value="backend">
			<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Back End Settings', 'tag-groups'); ?>' id='submitbutton' />
			</form>

		<?php endif; ?>

		<?php if ( $active_tab == 1 ): ?>
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="tag-groups-settings-nonce" id="tag-groups-settings-nonce" value="<?php echo wp_create_nonce('tag-groups-settings') ?>" />
			<p><?php _e('Here you can choose a theme for the tag cloud. The path to own themes is relative to the <i>uploads</i> folder of your Wordpress installation. Leave empty if you don\'t use any.</p><p>New themes can be created with the <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>:
			<ol>
			 <li>On the page "Theme Roller" you can customize all features or pick one set from the gallery. Finish with the "download" button.</li>
			 <li>On the next page ("Download Builder") you will need to select the components "Core", "Widget" and "Tabs". Make sure that before download you enter at the bottom as "CSS Scope" <b>.tag-groups-cloud-tabs</b> (including the dot) and as "Theme Folder Name" the name that you wish to enter below (for example "my-theme" or the name used in the theme gallery - avoid spaces and exotic characters).</li>
			 <li>Then you unpack the downloaded zip file and open the css folder. Inside it you will find a folder with the previously chosen "Theme Folder Name" (containing a folder "images" and files named like "jquery-ui-1.10.2.custom.(min.)css").</li>
			 <li>Copy this folder to your <i>wp-content/uploads</i> folder and enter its name below.</li>
			</ol>', 'tag-groups') ?></p>
	
			<table>
			<tr>
			<td style="width:400px; padding-right:50px;">
			<ul>
		
				<?php foreach($default_themes as $theme) : ?>
		
					<li><input type="radio" name="theme" id="tg_<?php echo $theme ?>" value="<?php echo $theme ?>" <?php if ($tag_group_theme == $theme) echo 'checked'; ?> />&nbsp;<label for="tg_<?php echo $theme ?>"><?php echo $theme ?></label></li>
		
				<?php endforeach; ?>
		
				<li><input type="radio" name="theme" value="own" id="tg_own" <?php if (!in_array($tag_group_theme, $default_themes)) echo 'checked' ?> />&nbsp;<label for="tg_own">own: /wp-content/uploads/</label><input type="text" id="theme-name" name="theme-name" value="<?php if (!in_array($tag_group_theme, $default_themes)) echo $tag_group_theme ?>" /></li>
				<li><input type="checkbox" name="enqueue-jquery" id="tg_enqueue-jquery" value="1" <?php if ($tag_group_enqueue_jquery) echo 'checked' ?> />&nbsp;<label for="tg_enqueue-jquery"><?php _e('Use jQuery.  (Default is on. Other plugins might override this setting.)', 'tag-groups' ) ?></label></li>
			</ul>
			</td>
	
			<td>
			<h4>Further options</h4>
			<p><?php _e('These will not work if you change the parameter div_id for the cloud.', 'tag-groups') ?></p>
			<ul>
				<li><input type="checkbox" name="mouseover" id="mouseover" value="1" <?php if ($tag_group_mouseover) echo 'checked'; ?> >&nbsp;<label for="mouseover"><?php _e('Tabs triggered by hovering mouse pointer (without clicking).', 'tag-groups' ) ?></label></li>
				<li><input type="checkbox" name="collapsible" id="collapsible" value="1" <?php if ($tag_group_collapsible) echo 'checked'; ?> >&nbsp;<label for="collapsible"><?php _e('Collapsible tabs (toggle open/close).', 'tag-groups' ) ?></label></li>
			</ul>
			</td>
			</tr>
			</table>
	
			<input type="hidden" id="action" name="action" value="theme">
			<input class='button-primary' type='submit' name='save' value='<?php _e('Save Theme Options', 'tag-groups'); ?>' id='submitbutton' />
			</form>
		<?php endif; ?>

		<?php if ( $active_tab == 2 ): ?>
			<?php if (function_exists('icl_register_string')) :?>
				<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<h3><?php _e('Register group labels with WPML', 'tag-groups') ?></h3>
				<p><?php _e('Use this button to register all existing group labels with WPML for string translation. This is only necessary if labels have existed before you installed WPML.', 'tag-groups') ?></p>
				<input type="hidden" id="action" name="action" value="wpml">
				<input class='button-primary' type='submit' name='register' value='<?php _e('Register Labels', 'tag-groups' ); ?>' id='submitbutton' />
				</form>
			<?php endif; ?>
		<?php endif; ?>

		
		<?php if ( $active_tab == 3 ): ?>
			<p><?php _e('You can use a shortcode to embed the tag cloud directly in a post, page or widget or you call the function in the PHP code of your theme.') ?></p>
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="tag-groups-widget-nonce" id="tag-groups-widget-nonce" value="<?php echo wp_create_nonce('tag-groups-widget') ?>" />
			<ul>
				<li><input type="checkbox" name="widget" id="tg_widget" value="1" <?php if ($tag_group_shortcode_widget) echo 'checked'; ?> >&nbsp;<label for="tg_widget"><?php _e('Enable shortcode in sidebar widgets (if not visible anyway).', 'tag-groups' ) ?></label></li>
			</ul>
			<input type="hidden" id="action" name="action" value="widget">
			<input class='button-primary' type='submit' name='save' value='<?php _e('Save', 'tag-groups' ); ?>' id='submitbutton' />
			</form>

			<p>&nbsp;</p>
			<h3><?php _e('Further Instructions') ?></h3>
			<h4>a) <?php _e('Shortcode') ?></h4>
			<p>[tag_groups_cloud]</p>
			<p><b><?php _e('Parameters', 'tag-groups') ?></b><br /><?php _e('example', 'tag-groups') ?>: [tag_groups_cloud smallest=9 largest=30 include=1,2,10]
			<?php _e('<ul>
			<li>&nbsp;</li>
			<li><b>Tags or Terms:</b></li>
			<li><b>smallest=x</b> Font-size in pt of the smallest tags. Default: 12</li>
			<li><b>largest=x</b> Font-size in pt of the largest tags. Default: 22</li>
			<li><b>orderby=abc</b> Which field to use for sorting, e.g. count. Default: name</li>
			<li><b>order=ASC or =DESC</b> Whether to sort the tags in ascending or descending order. Default: ASC</li>
			<li><b>amount=x</b> Maximum amount of tags in one cloud (per group). Default: 40</li>
			<li><b>hide_empty=1 or =0</b> Whether to hide or show also tags that are not assigned to any post. Default: 1 (hide empty)</li>
			<li><b>tags_post_id=x</b> Display only tags that are assigned to the post (or page) with the ID x. If set to 0, it will try to retrieve the current post ID. Default: -1 (all tags displayed)</li>
			<li><b>separator="•"</b> A separator between the tags. Default: empty</li>
			<li><b>separator_size=12</b> The size of the separator. Default: 12</li>
			<li><b>adjust_separator_size=1 or =0</b> Whether to adjust the separator\'s size to the size of the following tag. Default: 0</li>
			
			<li>&nbsp;</li>
			<li><b>Groups and Tabs:</b></li>
			<li><b>include=x,y,...</b> IDs of tag groups (left column in list of groups) that will be considered in the tag cloud. Empty or not used means that all tag groups will be used. Default: empty</li>
			<li><b>groups_post_id=x</b> Display only groups of which at least one assigned tag is also assigned to the post (or page) with the ID x. If set to 0, it will try to retrieve the current post ID. Default: -1 (all groups displayed). Matching groups will be added to the list specified by the parameter <b>include</b>.</li>
			<li><b>show_tabs=1 or =0</b> Whether to show the tabs. Default: 1</li>
			<li><b>hide_empty_tabs=1 or =0</b> Whether to hide tabs without tags. Default: 0 (Not implemented for PHP function with second parameter set to \'true\'. Not effective with <b>groups_post_id</b>.)</li>

			<li>&nbsp;</li>
			<li><b>Advanced Styling:</b></li>
			<li><b>div_id=abc</b> Define an id for the enclosing '.htmlentities('<div>').' Default: tag-groups-cloud-tabs</li>
			<li><b>div_class=abc</b> Define a class for the enclosing '.htmlentities('<div>').'. Default: tag-groups-cloud-tabs</li>
			<li><b>ul_class=abc</b> Define a class for the '.htmlentities('<ul>').' that generates the tabs with the group labels. Default: empty</li>
			</ul>', 'tag-groups') ?></p>
			<p>&nbsp;</p>
			<h4>b) PHP</h4>
			<p><?php _e('By default the function <b>tag_groups_cloud</b> returns the html for a tabbed tag cloud.', 'tag-groups') ?></p>
			<p><?php _e('Example: ', 'tag-groups'); echo htmlentities("<?php if ( function_exists( 'tag_groups_cloud' ) ) echo tag_groups_cloud( array( 'include' => '1,2,5,6' ) ); ?>") ?></p>
			<p><?php _e('If the optional second parameter is set to \'true\', the function returns a multidimensional array containing tag groups and tags.', 'tag-groups'); ?></p>
			<p><?php _e('Example: ', 'tag-groups'); echo htmlentities("<?php if ( function_exists( 'tag_groups_cloud' ) ) print_r( tag_groups_cloud( array( 'orderby' => 'count', 'order' => 'DESC' ), true ) ); ?>") ?></p>
		<?php endif; ?>


		<?php if ( $active_tab == 4 ): ?>
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<input type="hidden" name="tag-groups-reset-nonce" id="tag-groups-reset-nonce" value="<?php echo wp_create_nonce('tag-groups-reset') ?>" />
			<p><?php _e('Use this button to delete all tag groups and assignments. Your tags will not be changed. Check the checkbox to confirm.', 'tag-groups') ?></p>
			<input type="checkbox" id="ok" name="ok" value="yes" />
			<label><?php _e('I know what I am doing.', 'tag-groups') ?></label>
			<input type="hidden" id="action" name="action" value="reset">
			<input class='button-primary' type='submit' name='delete' value='<?php _e('Delete Groups', 'tag-groups' ); ?>' id='submitbutton' />
			</form>
		<?php endif; ?>


		<?php if ( $active_tab == 5 ): ?>
			<h4>Tag Groups, Version: <?php echo TAG_GROUPS_VERSION ?></h4>
			<p>If you find a bug or have a question, please visit the official <a href="http://wordpress.org/support/plugin/tag-groups" target="_blank">support forum</a>. There is also a <a href="http://www.christoph-amthor.de/software/tag-groups/" target="_blank">dedicated page</a> with more examples and instructions for particular applications.</p>
			<h2>Donations</h2>
			<p>Support the author with a microdonation <a href="http://flattr.com/thing/721303/Tag-Groups-plugin" target="_blank">
	<img src="<?php echo plugins_url('images/flattr-badge-large.png', __FILE__) ?>" alt="Flattr this" title="Support through micro-donation" border="0" /></a> or <a href="http://www.burma-center.org/donate/" target="_blank">donate to his favourite charity</a>.</p>
	<p>Or support his work by a nice link to one of these websites:
<ul>
	<li><a href="http://www.burma-center.org" target="_blank">www.burma-center.org</a></li>
	<li><a href="http://www.ecoburma.com" target="_blank">www.ecoburma.com</a></li>
	<li><a href="http://www.weirdthingsinprague.com" target="_blank">www.weirdthingsinprague.com</a></li>
	<li><a href="http://digitalmyanmar.net" target="_blank">digitalmyanmar.net</a></li>
</ul>
	Thanks!</p>
		<?php endif; ?>
	
	<?php }	?>

	</div>
	
<?php
}


function tag_groups_cloud( $atts = array(), $return_array = false ) {
/*
	Rendering the tag cloud, usually by a shortcode, or returning a multidimensional array
*/
	$include_array = array();
	
	$html_tabs = array();
	
	$html_tags = array();

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	$number_of_tag_groups = count($tag_group_labels) - 1;
	
	extract( shortcode_atts( array(
		'smallest' => 12,
		'largest' => 22,
		'amount' => 40,
		'hide_empty' => true,
		'include' => '',
		'div_id' => 'tag-groups-cloud-tabs',
		'div_class' => 'tag-groups-cloud-tabs',
		'ul_class' => '',
		'show_tabs' => '1',
		'orderby' => 'name',
		'order' => 'ASC',
		'separator' => '',
		'separator_size' => 12,
		'adjust_separator_size' => false,
		'tags_post_id' => -1,
		'groups_post_id' => -1,
		'hide_empty_tabs' => false
		), $atts ) );

	if ($smallest < 1) $smallest = 1;
	
	if ($largest < $smallest) $largest = $smallest;
	
	if ($amount < 1) $amount = 1;

	$posttags = get_terms($tag_group_taxonomy, array('hide_empty' => $hide_empty, 'orderby' => $orderby, 'order' => $order));

	$div_id_output = ($div_id) ? ' id="'.$div_id.'"' : '';

	$div_class_output = ($div_class) ? ' class="'.$div_class.'"' : '';

	$ul_class_output = ($ul_class) ? ' class="'.$ul_class.'"' : '';
	
	if ($include != '') $include_array = explode(',', $include);

	if ($separator_size < 1) $separator_size = 12; else $separator_size = (int) $separator_size;
	
	
	// applying parameter tags_post_id
	
	if ($tags_post_id < -1) $tags_post_id = -1;
	
	if ($tags_post_id == 0) $tags_post_id = get_the_ID();
	
	if ($tags_post_id) {
	
		// get all tags of this post
		$post_id_terms = get_the_terms( (int) $tags_post_id, $tag_group_taxonomy );
		
		if ($post_id_terms) {

			// clean all others from $posttags
			foreach ( $posttags as $key => $tag ) {
			
				$found = false;
					
				foreach ( $post_id_terms as $id_tag ) {
				
					if ( $tag->term_id == $id_tag->term_id ) {
					
						$found = true;
						
						break;
						
					}
					
				}
				
				if (!$found) unset( $posttags[$key] );
			
			}
		
		}
	
	}
	
	
	// applying parameter groups_post_id
	
	if ($groups_post_id < -1) $groups_post_id = -1;
	
	if ($groups_post_id == 0) $groups_post_id = get_the_ID();

	if ($groups_post_id) {
	
		// get all tags of this post
		$post_id_terms = get_the_terms( (int) $groups_post_id, $tag_group_taxonomy );
		
		// get all involved groups, append them to $include
		if ($post_id_terms) {

			foreach ( $post_id_terms as $term ) {

				if (!in_array( $term->term_group, $include_array )) $include_array[] = $term->term_group;
			
			}
		
		}
	
	}
	

	if ($return_array) {
	
	// return tags as array
	
		$output = array ();
	
		for ($i = 1; $i <= $number_of_tag_groups; $i++) {

			if ((!$include_array) || (in_array( $tag_group_ids[$i], $include_array ))) {
			
				$output[$i]['name'] = tg_translate_string_wpml( 'Group Label ID '.$tag_group_ids[$i], $tag_group_labels[$i] );

				$output[$i]['term_group'] = $tag_group_ids[$i];

				if ($posttags) {

					// find minimum and maximum of quantity of posts for each tag
					$count_amount = 0;
				
					$max = 0;
				
					$min = 9999999;
					
					foreach($posttags as $tag) {
				
						if ($count_amount >= $amount) break;
				
						if ($tag->term_group == $tag_group_ids[$i]) {
				
							if ($tag->count > $max) $max = $tag->count;
					 
							if ($tag->count < $min) $min = $tag->count;
							
							$count_amount++;
				
						}
				
					}

					$count_amount = 0;

					foreach($posttags as $tag) {

						if ($count_amount >= $amount) break;

						if ($tag->term_group == $tag_group_ids[$i]) {
							
							$output[$i]['tags'][$count_amount]['term_id'] = $tag->term_id;
							
							$output[$i]['tags'][$count_amount]['link'] = get_term_link($tag->slug, $tag_group_taxonomy);

							$output[$i]['tags'][$count_amount]['description'] = $tag->description;
							
							$output[$i]['tags'][$count_amount]['count'] = $tag->count;
							
							$output[$i]['tags'][$count_amount]['slug'] = $tag->slug;

							$output[$i]['tags'][$count_amount]['name'] = $tag->name;

							$output[$i]['tags'][$count_amount]['tg_font_size'] = tg_font_size($tag->count,$min,$max,$smallest,$largest);
															
							$count_amount++;
						
						}
					
					}
					
					$output[$i]['amount'] = $count_amount;

				}
				
			}
	
		}

	return $output;
	
	} else {

	// return as html (in shape of a tabbed cloud)
	
		$html = '<div'.$div_id_output.$div_class_output.'>';

		if ($show_tabs == '1') {
	
			$html_tabs[0] = '<ul'.$ul_class_output.'>';
		
			for ($i = 1; $i <= $number_of_tag_groups; $i++) {
		
				if ((!$include_array) || (in_array($tag_group_ids[$i],$include_array))) {
		
					$html_tabs[$i] = '<li><a href="#tabs-'.$i.'" >'.tg_translate_string_wpml('Group Label ID '.$tag_group_ids[$i], $tag_group_labels[$i]).'</a></li>';
		
				}
		
			}
		
			$html_tabs[] .= '</ul>';
	
		}
	
		for ($i = 1; $i <= $number_of_tag_groups; $i++) {
		
			if ((!$include_array) || (in_array($tag_group_ids[$i],$include_array))) {
			
				$html_tags[$i] = '<div id="tabs-'.$i.'">';
	
					if ($posttags) {
	
						// find minimum and maximum of quantity of posts for each tag
						$count_amount = 0;
					
						$max = 0;
					
						$min = 9999999;
						
						foreach($posttags as $tag) {
					
							if ($count_amount > $amount) break;
					
							if ($tag->term_group == $tag_group_ids[$i]) {
					
								if ($tag->count > $max) $max = $tag->count;
						 
								if ($tag->count < $min) $min = $tag->count;
								
								$count_amount++;
					
							}
					
						}
					
						$count_amount = 0;
	
						foreach($posttags as $tag) {
	
							if ($count_amount >= $amount) break;
	
							if ($tag->term_group == $tag_group_ids[$i]) {
	
								$tag_link = get_term_link($tag->slug, $tag_group_taxonomy);
								
								$font_size = tg_font_size($tag->count,$min,$max,$smallest,$largest);
								
								$font_size_tag = $adjust_separator_size ? $font_size : $separator_size;
								
								if ($count_amount > 0) $html_tags[$i] .= '<span style="font-size:'. $font_size_tag .'px">'.$separator.'</span> ';
								
								$html_tags[$i] .= '<a href="'.$tag_link.'" title="'.htmlentities($tag->description).' ('.$tag->count.')"  class="'.$tag->slug.'"><span style="font-size:'.$font_size.'px">'.$tag->name.'</span></a>&nbsp; ';
								
								$count_amount++;
							
							}
						
						}
					
					}
					
				if ($hide_empty_tabs && !$count_amount) {
				
					unset($html_tabs[$i]);

					unset($html_tags[$i]);
				
				} else {
				
					$html_tags[$i] .= '</div>';
					
				}
				
			}
			
		}
	
		foreach ( $html_tabs as $html_tab) $html .= $html_tab;
		
		foreach ( $html_tags as $html_tag) $html .= $html_tag;
	
		$html .= '</div>';
		
		return $html;

	}
}


function tg_unassign($id) {
/*
	After deleting a tag group, this function removes its ID from the previously assigned tags.
*/

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	$posttags = get_terms($tag_group_taxonomy, array('hide_empty' => false));
	
	foreach($posttags as $tag) {

		if (($tag->term_group == $id) || ($id == 0)) {

			$tag->term_group = 0;

			$ret = wp_update_term( $tag->term_id, $tag_group_taxonomy, array( 'term_group' => $tag->term_group ) );
		}
		
	}

}


function tg_number_assigned($id) {
/*
	Returns number of tags that are assigned to a given tag group. Needed for the table.
*/

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	$posttags = get_terms($tag_group_taxonomy, array('hide_empty' => false));
	
	$number = 0;

	foreach($posttags as $tag) {

		if ($tag->term_group == $id) $number++;
	
	}
	
	return $number;

}


function tg_custom_js() {
/*
	jquery needs some script in the html for the tabs to work - opportunity to facilitate some options
*/

	if ( get_option( 'tag_group_mouseover', '' ) ) $mouseover = 'event: "mouseover"'; else $mouseover = '';

	if ( get_option( 'tag_group_collapsible', '' ) ) $collapsible = 'collapsible: true'; else $collapsible = '';

	if ( !$mouseover && !$collapsible ) {

		$options = '';

	} else {

		$options = $collapsible ? $mouseover . ",\n" . $collapsible : $mouseover;

		$options = $mouseover ? $options : $collapsible;

		$options = "{\n" . $options . "\n}";

	}

	// Not necessarily in HEAD section, but can't do no harm to have it there.
	echo '
	<!-- begin Tag Groups plugin -->
	<script type="text/javascript">
		jQuery(function() {
	
			jQuery( "#tag-groups-cloud-tabs" ).tabs(' . $options . ');

		});
	</script>
	<!-- end Tag Groups plugin -->
	';

}


function post_in_tag_group($post_id, $tag_group_id) {
/*
	Checks if the post with $post_id has a tag that is in the tag group with $tag_group_id.
*/

	$tag_group_taxonomy = get_option( 'tag_group_taxonomy', 'post_tag' );

	$tags = get_the_tags( $post_id, $tag_group_taxonomy );
	
	if ( $tags ) {

		foreach( $tags as $tag ) {

			if ($tag->term_group == $tag_group_id) return true;
		}
		
	} else {

		return false;
	
	}
	
	return false;
}


function tg_font_size($count, $min, $max, $smallest, $largest) {
/*
	Calculates the font size for the cloud tag for a particular tag ($min, $max and $size with same unit, e.g. pt.)
*/

	if ($max > $min) {

		$size = round(($count - $min) * ($largest - $smallest) / ($max - $min) + $smallest);
	
	} else {

		$size = round($smallest);
	
	}

	return $size;

}


function tg_register_string_wpml($name, $value) {
/*
	Makes sure that WPML knows about the tag group label that can have different language versions.
*/

	if (function_exists('icl_register_string')) icl_register_string('tag-groups', $name, $value);

}


function tg_unregister_string_wpml($name) {
/*
	Asks WPML to forget about $name.
*/

	if (function_exists('icl_unregister_string')) icl_unregister_string('tag-groups', $name);

}


function tg_translate_string_wpml($name, $string) {
/*
	If WPML is installed: return translation; otherwise return original
*/

	if (function_exists('icl_t')) return icl_t('tag-groups', $name, $string); else return $string;

}

 
function tg_swap(&$ary,$element1,$element2) {
/*
	swaps the position of two elements in an array - needed for changing the order of list items
*/

	$temp=$ary[$element1];

	$ary[$element1]=$ary[$element2];

	$ary[$element2]=$temp;

}


function tg_clear_cache()  {
/*
	Good idea to purge the cache after changing theme options - else your visitors won't see the change for a while. Currently implemented for W3T Total Cache and WP Super Cache.
*/

	if (function_exists('flush_pgcache')) flush_pgcache;

	if (function_exists('flush_minify')) flush_minify;

	if (function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();

}


function tg_add_filter(){
/*
	Adds a pull-down menu to the filters above the posts.

	Based on the code by Ohad Raz, http://wordpress.stackexchange.com/q/45436/2487
	License: Creative Commons Share Alike
*/

	$show_filter = get_option( 'tag_group_show_filter', true );
	
	if ( !$show_filter ) return;

	$tg_type = get_option( 'tag_group_taxonomy', 'post_tag' );
	
    $type = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';

    if ( in_array( $tg_type, get_object_taxonomies( $type ) ) ) {
    
    	$tag_group_labels = get_option( 'tag_group_labels', array() );

		$tag_group_ids = get_option( 'tag_group_ids', array() );

        $values = array();
        
        $number_of_tag_groups = count($tag_group_labels) - 1;
        
        $values[0] = __('not assigned', 'tag-groups');
        
        for ($i = 1; $i <= $number_of_tag_groups; $i++) {
        
        	$values[$tag_group_ids[$i]] = $tag_group_labels[$i];
        	
        }
        ?>
        <select name="tg_filter_value">
        <option value=""><?php _e('Filter by tag group ', 'tag-groups'); ?></option>
        <?php
        
            $current_v = isset( $_GET['tg_filter_value'] ) ? $_GET['tg_filter_value'] : '';
            
            foreach ( $values as $value => $label ) {
            
                printf( '<option value="%s"%s>%s</option>', $value, ( $current_v != '' && $value == $current_v ) ? ' selected="selected"' : '', $label );
                }
        ?>
        </select>
        <?php
    }
}


function tg_apply_filter( $query ) {
/*
	Applies the filter, if used.

	Based on the code by Ohad Raz, http://wordpress.stackexchange.com/q/45436/2487
	License: Creative Commons Share Alike
*/

    global $pagenow;
    
    $filter_terms = array();
    
    $show_filter = get_option( 'tag_group_show_filter', true );
	
	if ( !$show_filter ) return;
	
	$tg_type = get_option( 'tag_group_taxonomy', 'post_tag' );
	
	$tg_prefix = ($tg_type == 'post_tag') ? 'tag' : $tg_type;

    $type = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : 'post';
    
    if ( in_array( $tg_type, get_object_taxonomies( $type ) ) && is_admin() && $pagenow == 'edit.php' && isset( $_GET['tg_filter_value']) && $_GET['tg_filter_value'] != '' ) {
    
		$terms = get_terms( $tg_type );
		
		$tag_group_ids = get_option( 'tag_group_ids', array() );
		
		$tg_selected = (int) $_GET['tg_filter_value'];

		if ( $terms ) {
		
	    	if ( $tg_selected == '0' ) {
    	
    			foreach( $terms as $term ) {

    				if ( $term->term_group != 0 && in_array( $term->term_group, $tag_group_ids ) ) {
    				
    					$filter_terms[] = $term->term_id;
    					
    				}
    			
    			}
    			
    			$query->query_vars[$tg_prefix.'__not_in'] = $filter_terms;
    	
    		} else {

    			$filter_terms[] = 0;
    
				foreach( $terms as $term ) {

					if ( $term->term_group == $tg_selected ) {
			
						$filter_terms[] = $term->term_id;
			
					}
					
				}
				
				$query->query_vars[$tg_prefix.'__in'] = $filter_terms;
			
			}
			
		}
		
    }

}

/*
	guess what - the end
*/
?>