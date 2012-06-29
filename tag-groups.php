<?php
/*
Plugin Name: Tag Groups
Plugin URI: http://www.christoph-amthor.de/software/tag-groups/
Description: Assign tags to groups and display them in a tabbed tag cloud
Author: Christoph Amthor
Version: 0.5
Author URI: http://www.christoph-amthor.de
License: GNU GENERAL PUBLIC LICENSE, Version 3
*/

define("TAG_GROUPS_VERSION", "0.5");

define("TAG_GROUPS_BUILT_IN_THEMES", "ui-gray,ui-lightness,ui-darkness");

define("TAG_GROUPS_STANDARD_THEME", "ui-gray");




add_action( 'admin_init', 'register_group_tag_settings' );

add_action( 'admin_menu', 'register_tag_label_page' );

add_shortcode( 'tag_groups_cloud', 'tag_groups_cloud' );

add_action( 'wp_enqueue_scripts', 'add_tag_groups_js_css' );

add_action( 'admin_enqueue_scripts', 'add_tag_groups_admin_js_css' );

add_action( 'wp_head', 'tag_group_custom_js' );

// register_activation_hook();

// register_deactivation_hook();


function register_group_tag_settings() {

	add_action( 'edit_tag_form_fields', 'tag_input_metabox' );
	
	add_action( 'post_tag_add_form_fields', 'create_new_tag' );
	
	add_filter( 'manage_edit-post_tag_columns', 'add_post_tag_columns' );
	
	add_filter( 'manage_post_tag_custom_column', 'add_post_tag_column_content', 10, 3 );

	add_action( 'quick_edit_custom_box', 'quick_edit_tag', 10, 3 );
	
	add_action( 'create_term', 'update_edit_term_group' );
		
	add_action( 'edit_term', 'update_edit_term_group' );
	
	$plugin = plugin_basename(__FILE__);

	add_filter("plugin_action_links_$plugin", 'tag_groups_plugin_settings_link' );
	
	add_action('admin_footer', 'tag_groups_quick_edit_javascript');

	add_filter('tag_row_actions', 'tag_groups_expand_quick_edit_link', 10, 2);

	tag_groups_init();

}


function tag_groups_plugin_settings_link($links) {
/*
adds Settings link to plugin list
*/

  $settings_link = '<a href="edit.php?page=tag-groups">Settings</a>'; 
  array_unshift($links, $settings_link); 

  return $links; 

}
 

function add_tag_groups_admin_js_css() {
/*
adds css to backend
*/

	wp_register_style( 'tag-groups-css-backend', plugins_url('css/style.css', __FILE__) );
	
	wp_enqueue_style( 'tag-groups-css-backend' );

}


function add_tag_groups_js_css() {
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

		wp_register_style( 'tag-groups-css-frontend', plugins_url('css/'.$theme.'/jquery-ui-1.8.21.custom.css', __FILE__) );

		
	} else {

		wp_register_style( 'tag-groups-css-frontend', get_bloginfo('wpurl').'/wp-content/uploads/'.$theme.'/jquery-ui-1.8.21.custom.css' );
	
	}

	wp_enqueue_style( 'tag-groups-css-frontend' );

}


function register_tag_label_page() {

	add_posts_page('Tag Groups', 'Tag Groups', 'manage_options', 'tag-groups', 'tag_groups');

}


function add_post_tag_columns($columns) {
// thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/

/*
adds a custom column
*/
		
	$columns['term_group'] = __('Tag Group', 'tag-groups');
	
	return $columns;
 		
}

	
function add_post_tag_column_content($empty = '', $empty = '', $term_id) {
// thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/

/*
adds data into custom column for each row
*/

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$tag = get_tag($term_id);
	
	$i = array_search($tag->term_group, $tag_group_ids); 

	return $tag_group_labels[$i];

}


function update_edit_term_group($term_id) {
/*
get the $_POSTed value and save it in the table
*/

	// next two lines to prevent infinite loops when the hook edit_term is called again from the function wp_update_term

	global $update_edit_term_group_called;

	if ($update_edit_term_group_called > 0) return;

	$update_edit_term_group_called++;
	
	if (current_user_can('edit_posts')) {

		$term_id = (int) $term_id;
		
		$term = array();
		

		if ( isset($_POST['term-group-option']) ) {

			if ( !isset($_POST['tag-groups-option-nonce']) || ! wp_verify_nonce($_POST['tag-groups-option-nonce'], 'tag-groups-option') ) die("Security check");

			$term['term_group'] = (int) $_POST['term-group-option'];

		} elseif ( isset($_POST['term-group']) ) {

			if ( !isset($_POST['tag-groups-nonce']) || ! wp_verify_nonce($_POST['tag-groups-nonce'], 'tag-groups') ) die("Security check");

			$term['term_group'] = (int) $_POST['term-group'];

		}

		if ( isset($_POST['name']) && ($_POST['name'] != '') ) $term['name'] = trim(sanitize_text_field($_POST['name']));

		if ( isset($_POST['slug']) && ($_POST['slug'] != '') ) $term['slug'] = trim(sanitize_title($_POST['slug']));

		if ( isset($_POST['description']) && ($_POST['description'] != '') ) $term['description'] = trim(sanitize_text_field($_POST['description']));
		
		wp_update_term( $term_id, 'post_tag', $term );
		
	} else wp_die( __( 'Cheatin&#8217; uh?' ) );

}

 
function tag_groups_quick_edit_javascript() {
// thanks to http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu

/*
adds JS function that selects right tag group for given element opened for quick edit
*/

	$screen = get_current_screen();
	
	if ( $screen->taxonomy != 'post_tag' ) return;
 
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


function tag_groups_expand_quick_edit_link($actions, $tag) {
// thanks to http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu

/*
modifies Quick Edit link to call JS when clicked
*/

	$screen = get_current_screen();
	
	if ( $screen->taxonomy != 'post_tag' ) return $actions;
 
	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$tag_group_id = array_search($tag->term_group, $tag_group_ids); 
	
	$nonce = wp_create_nonce('tag-groups-option');
	
	$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';

	$actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '" ';

	$actions['inline hide-if-no-js'] .= " onclick=\"set_inline_tag_group_selected('{$tag_group_id}', '{$nonce}')\">"; 

	$actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );

	$actions['inline hide-if-no-js'] .= '</a>';

	return $actions;	
}


function quick_edit_tag() {
/*
assigning tags to tag groups directly in tag table
*/

	$screen = get_current_screen();
	
	if ( $screen->taxonomy != 'post_tag' ) return;

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


function create_new_tag($tag) {
/*
assigning tags to tag groups upon new tag creation
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
	</div>

	<?php
}


function tag_input_metabox($tag) {
/*
assigning tags to tag groups on single tag view
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


function tag_groups_init() {
/*
If it doesn't exist: create the default group with ID 0 that will only show up on tag pages as "unassigned".
*/

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ((!isset($tag_group_labels)) || ($tag_group_labels[0] == '')) {

		$tag_group_labels[0] = 'not assigned';

		$tag_group_ids[0] = 0;

		$number_of_tag_groups = 0;

		$max_tag_group_id = 0;

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		update_option( 'max_tag_group_id', $max_tag_group_id );

		$tag_group_theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );

		if ($tag_group_theme == '') $tag_group_theme = TAG_GROUPS_STANDARD_THEME;

	}
}


function tag_groups() {
/*
creates the sub-menu with its page on the admin backend and handles the main actions that you perform with tag groups and themes
*/

	$tag_group_labels = get_option( 'tag_group_labels', array());

	$tag_group_ids = get_option( 'tag_group_ids', array() );

	$max_tag_group_id = get_option( 'max_tag_group_id', 0 );
	
	$tag_group_theme = get_option( 'tag_group_theme', TAG_GROUPS_STANDARD_THEME );
	
	$tag_group_mouseover = get_option( 'tag_group_mouseover', '' );

	$tag_group_collapsible = get_option( 'tag_group_collapsible', '' );
	
	$tag_group_enqueue_jquery = get_option( 'tag_group_enqueue_jquery', true );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ($max_tag_group_id < 0) $max_tag_group_id = 0;
	
	$default_themes = explode(',', TAG_GROUPS_BUILT_IN_THEMES);

	$label = '';
	?>
	
	<div class='wrap'>
	<h2>Tag Groups</h2>
	
	<?php

	if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];

	if (isset($_GET['id'])) (int) $tag_groups_id = $_GET['id'];
	
	if (isset($_POST['theme-name'])) $theme_name = trim(sanitize_text_field($_POST['theme-name']));
	
	if (isset($_POST['theme'])) $theme = trim(sanitize_text_field($_POST['theme']));

	if (isset($_POST['ok'])) $ok = $_POST['ok'];
	
	// save a new label
	if (isset($_POST['label'])) {
	
		$label = trim(sanitize_text_field($_POST['label']));
		
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
		
				unregister_string_wpml( $tag_group_labels[$tag_groups_id] );
				
				$tag_group_labels[$tag_groups_id] = $label;
				
				register_string_wpml( 'Group Label ID '.$tag_groups_id, $tag_group_labels[$tag_groups_id] );
				
			} else {
			//new

				$max_tag_group_id++;

				$number_of_tag_groups++;

				$tag_group_labels[$number_of_tag_groups] = $label;
				
				register_string_wpml( 'Group Label ID '.$number_of_tag_groups, $label );

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
	
	}
	
	// change order - move up
	if (($action == 'up') && ($tag_groups_id > 1)) {
	
		swap($tag_group_labels,$tag_groups_id-1,$tag_groups_id);

		swap($tag_group_ids,$tag_groups_id-1,$tag_groups_id);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		$action = "";
	
	}
	
	// change order - move down
	if (($action == 'down') && ($tag_groups_id < $number_of_tag_groups)) {

		swap($tag_group_labels,$tag_groups_id,$tag_groups_id+1);

		swap($tag_group_ids,$tag_groups_id,$tag_groups_id+1);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		$action = "";
	
	}

	if (($action == 'reset') && ($ok != 'yes')) $action = '';
	
	
	$number_of_tag_groups = count($tag_group_labels) - 1;
	
	switch ($action) {
	
	case 'reset':

		if ( !isset($_POST['tag-groups-reset-nonce']) || ! wp_verify_nonce($_POST['tag-groups-reset-nonce'], 'tag-groups-reset') ) die("Security check");

 		unset($tag_group_labels);

 		unset($tag_group_ids);

 		$max_tag_group_id = 0;

 		update_option( 'tag_group_labels', $tag_group_labels );
	
		update_option( 'tag_group_ids', $tag_group_ids );
	
		update_option( 'max_tag_group_id', $max_tag_group_id );

		tag_groups_unassign(0);
		
		?>
		<div class="updated fade"><p>
			<?php _e('All groups are deleted and assignments reset.', 'tag-groups'); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
		<?php
	break;
	
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

	case 'wpml':

		for ($i = 1; $i <= $number_of_tag_groups; $i++) {

			register_string_wpml( 'Group Label ID '.$i, $tag_group_labels[$i] );

		} ?>
		
		<div class="updated fade"><p>
			<?php _e('All labels were registered.', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>

	<?php break;

	case 'delete':

		if (($tag_groups_id < 1) || ($tag_groups_id > $max_tag_group_id)) break;

		$label = $tag_group_labels[$tag_groups_id];

		$id = $tag_group_ids[$tag_groups_id];

		if ( !isset($_GET['tag-groups-delete-nonce']) || ! wp_verify_nonce($_GET['tag-groups-delete-nonce'], 'tag-groups-delete-'.$tag_groups_id) ) die("Security check");

		array_splice($tag_group_labels, $tag_groups_id, 1);

		array_splice($tag_group_ids, $tag_groups_id, 1);
		
		unregister_string_wpml('Group Label ID '.$id);

		$max = 0;
		foreach($tag_group_ids as $check_id) {	
			if ($check_id > $max) $max = $check_id;
		}
		$max_tag_group_id = $max;

		tag_groups_unassign($tag_groups_id);

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		update_option( 'max_tag_group_id', $max_tag_group_id ); ?>
		
		<div class="updated fade"><p>
			<?php printf(__('A tag group with the id %i and the label \'%s\' has been deleted.', 'tag-groups'), $id, $label); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
	<?php break;

	case 'theme':

		if ($theme == 'own') $theme = $theme_name;

		if ( !isset($_POST['tag-groups-settings-nonce']) || ! wp_verify_nonce($_POST['tag-groups-settings-nonce'], 'tag-groups-settings') ) die("Security check");

		update_option( 'tag_group_theme', $theme );
		
		$mouseover = ($_POST['mouseover'] && $_POST['mouseover'] == '1') ? true : false;

		$collapsible = ($_POST['collapsible'] && $_POST['collapsible'] == '1') ? true : false;
		
		update_option( 'tag_group_mouseover', $mouseover );

		update_option( 'tag_group_collapsible', $collapsible );

		$tag_group_enqueue_jquery = ($_POST['enqueue-jquery'] && $_POST['enqueue-jquery'] == '1') ? true : false;
		
		update_option( 'tag_group_enqueue_jquery', $tag_group_enqueue_jquery );
		
		clearCache;

		?> <div class="updated fade"><p>
		<?php _e('Your tag cloud theme settings have been saved', 'tag-groups' ); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
		<?php
		
	break;

	
	default: ?>
		<p><?PHP _e('On this page you can define tag groups. Tags can be assigned to these groups on the page where you edit single tags.', 'tag-groups') ?></p>
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
			 <td><?php echo group_tags_number_assigned($tag_group_ids[$i]) ?></td>
			 <td><a href="edit.php?page=tag-groups&action=edit&id=<?php echo $i; ?>"><?php _e('Edit') ?></a>, <a href="#" onclick="answer = confirm('<?PHP _e('Do you really want to delete the tag group', 'tag-groups') ?> \'<?php echo $tag_group_labels[$i] ?>\'?'); if( answer ) {window.location ='edit.php?page=tag-groups&action=delete&id=<?php echo $i ?>&tag-groups-delete-nonce=<?php echo wp_create_nonce('tag-groups-delete-'.$i) ?>'}"><?php _e('Delete') ?></a></td>
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

		
		
		<p>&nbsp;</p>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="tag-groups-settings-nonce" id="tag-groups-settings-nonce" value="<?php echo wp_create_nonce('tag-groups-settings') ?>" />
		<h3><?php _e('Theme', 'tag-groups') ?></h3>
		<p><?php _e('Here you can choose a theme for the tag cloud. The path is relative to the <i>uploads</i> folder of your Wordpress installation. Leave empty if you don\'t use any.</p><p>New themes can be created with the <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>. Make sure that before download you open the "Advanced Theme Settings" and enter as "CSS Scope" <b>.tag-groups-cloud-tabs</b> (including the dot) and as "Theme Folder Name" the name that you wish to enter below (for example "my-theme" - avoid spaces and exotic characters). Then you unpack the downloaded zip file and open the css folder. Inside it you will find a folder with the chosen Theme Folder Name - copy it to your <i>uploads</i> folder and enter its name below.', 'tag-groups') ?></p>

		<table>
		<tr>
		<td style="width:400px; padding-right:50px;">
		<ul>
	
			<?php foreach($default_themes as $theme) : ?>
	
				<li><input type="radio" name="theme" value="<?php echo $theme ?>" <?php if ($tag_group_theme == $theme) echo 'checked'; ?> />&nbsp;<?php echo $theme ?></li>
	
			<?php endforeach; ?>
	
			<li><input type="radio" name="theme" value="own" <?php if (!in_array($tag_group_theme, $default_themes)) echo 'checked' ?> />&nbsp;own: /wp-content/uploads/<input type="text" id="theme-name" name="theme-name" value="<?php if (!in_array($tag_group_theme, $default_themes)) echo $tag_group_theme ?>" /></li>
			<li><input type="checkbox" name="enqueue-jquery" value="1" <?php if ($tag_group_enqueue_jquery) echo 'checked' ?> />&nbsp;<?php _e('Use jQuery.  (Default is on. Other plugins might override this setting.)', 'tag-groups' ) ?></li>
		</ul>
		</td>

		<td>
		<h4>Further options</h4>
		<p><?php _e('These will not work if you change the parameter div_id for the cloud.', 'tag-groups') ?></p>
		<ul>
			<li><input type="checkbox" name="mouseover" value="1" <?php if ($tag_group_mouseover) echo 'checked'; ?> >&nbsp;<?php _e('Tabs triggered by hovering mouse pointer (without clicking).', 'tag-groups' ) ?></li>
			<li><input type="checkbox" name="collapsible" value="1" <?php if ($tag_group_collapsible) echo 'checked'; ?> >&nbsp;<?php _e('Collapsible tabs (toggle open/close).', 'tag-groups' ) ?></li>
		</ul>
		</td>
		</tr>
		</table>

		<input type="hidden" id="action" name="action" value="theme">
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Theme Options', 'tag-groups'); ?>' id='submitbutton' />
		</form>

		<?php if (function_exists('icl_register_string')) :?>
			<p>&nbsp;</p>
			<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<h3><?php _e('Register group labels with WPML', 'tag-groups') ?></h3>
			<p><?php _e('Use this button to register all existing group labels with WPML for string translation. This is only necessary if labels have existed before you installed WPML.', 'tag-groups') ?></p>
			<input type="hidden" id="action" name="action" value="wpml">
			<input class='button-primary' type='submit' name='register' value='<?php _e('Register Labels', 'tag-groups' ); ?>' id='submitbutton' />
			</form>
		<?php endif; ?>

		<p>&nbsp;</p>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="tag-groups-reset-nonce" id="tag-groups-reset-nonce" value="<?php echo wp_create_nonce('tag-groups-reset') ?>" />
		<h3><?php _e('Delete Groups', 'tag-groups') ?></h3>
		<p><?php _e('Use this button to delete all tag groups and assignments. Your tags will not be changed. Check the checkbox to confirm.', 'tag-groups') ?></p>
		<input type="checkbox" id="ok" name="ok" value="yes" />
		<label><?php _e('I know what I am doing.', 'tag-groups') ?></label>
		<input type="hidden" id="action" name="action" value="reset">
		<input class='button-primary' type='submit' name='delete' value='<?php _e('Delete Groups', 'tag-groups' ); ?>' id='submitbutton' />
		</form>

		<p>&nbsp;</p>
		<h3><?php _e('Displaying the Tag Cloud', 'tag-groups') ?></h3>
		<h4>a) <?php _e('Shortcode') ?></h4>
		<p>[tag_groups_cloud]</p>
		<p><b><?php _e('Parameters', 'tag-groups') ?>:</b> (example: [tag_groups_cloud smallest=9 largest=30 include=1,2,10]
		<?php _e('<ul>
		<li><b>smallest=x</b> Font-size in pt of the smallest tags. Default: 12</li>
		<li><b>largest=x</b> Font-size in pt of the largest tags. Default: 22</li>
		<li><b>amount=x</b> Maximum amount of tags in one cloud. Default: 40</li>
		<li><b>hide_empty=1 or =0</b> Whether to hide or show also tags that are not assigned to any post. Default: 1 (hide empty)</li>
		<li><b>include=x,y,...</b> IDs of tag groups (left column in table above) that will be considered in the tag cloud. Empty or not used means that all tag groups will be used. Default: empty</li>
		<li><b>div_id=abc</b> Define an id for the enclosing '.htmlentities('<div>').' Default: tag-groups-cloud-tabs</li>
		<li><b>div_class=abc</b> Define a class for the enclosing '.htmlentities('<div>').'. Default: tag-groups-cloud-tabs</li>
		<li><b>ul_class=abc</b> Define a class for the '.htmlentities('<ul>').' that generates the tabs with the group labels. Default: empty</li>
		<li><b>show_tabs=1 or =0</b> Whether to show the tabs. Default: 1</li>
		</ul>', 'tag-groups') ?></p>
		<h4>b) PHP</h4>
		<p><?php _e('example: ', 'tag-groups'); echo htmlentities("<?php if (function_exists(tag_groups_cloud)) echo tag_groups_cloud(array( 'include' => '1,2,5,6' )); ?>") ?></p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<h4><a href="http://www.christoph-amthor.de/plugins/tag-groups/" target="_blank">Tag Groups</a>, Version: <?php echo TAG_GROUPS_VERSION ?></h4>
		<h4><a href="http://flattr.com/thing/721303/Tag-Groups-plugin" target="_blank">
<img src="<?php echo plugins_url('images/flattr-badge-large.png', __FILE__) ?>" alt="Flattr this" title="Support through micro-donation" border="0" /></a></h4>
	
	<?php }	?>

	</div>
	
<?php
}


function tag_groups_cloud( $atts ) {
/*
Rendering of the tag cloud, usually by a shortcode [tag_groups_cloud xyz=1 ...]
*/

	$tag_group_labels = get_option( 'tag_group_labels', array() );

	$tag_group_ids = get_option( 'tag_group_ids', array() );

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
		), $atts ) );

	if ($smallest < 1) $smallest = 1;
	
	if ($largest < $smallest) $largest = $smallest;
	
	if ($amount < 1) $amount = 1;
	
	if ($include != '') {

		$include_groups = explode(',', $include);
	
	}

	$posttags = get_tags(array('hide_empty' => $hide_empty));

	$div_id_output = ($div_id) ? ' id="'.$div_id.'"' : '';

	$div_class_output = ($div_class) ? ' class="'.$div_class.'"' : '';

	$ul_class_output = ($ul_class) ? ' class="'.$ul_class.'"' : '';


	$html = '<div'.$div_id_output.$div_class_output.'>';


	if ($show_tabs == '1') {

		$html .= '<ul'.$ul_class_output.'>';
	
		for ($i = 1; $i <= $number_of_tag_groups; $i++) {
	
			if (($include == '') || (in_array($tag_group_ids[$i],$include_groups))) {
	
				$html .= '<li><a href="#tabs-'.$i.'" >'.translate_string_wpml('Group Label ID '.$tag_group_ids[$i], $tag_group_labels[$i]).'</a></li>';
	
			}
	
		}
	
		$html .= '</ul>';

	}

	for ($i = 1; $i <= $number_of_tag_groups; $i++) {
	
		if (($include == '') || (in_array($tag_group_ids[$i],$include_groups))) {
		
			$html .= '<div id="tabs-'.$i.'">';

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

						if ($count_amount > $amount) break;

			    		if ($tag->term_group == $tag_group_ids[$i]) {

							$tag_link = get_tag_link($tag->term_id);
							$html .= '<a href="'.$tag_link.'" title="'.htmlentities($tag->description).' ('.$tag->count.')"  class="'.$tag->slug.'"><span style="font-size:'.font_size($tag->count,$min,$max,$smallest,$largest).'px">'.$tag->name.'</span></a>&nbsp; ';
							$count_amount++;
						
						}
					
					}
				
				} 
			$html .= '</div>';
		}
	}

	$html .= '</div>';
	
	return $html;

}


function tag_groups_unassign($id) {

	$posttags = get_tags(array('hide_empty' => false));
	
	foreach($posttags as $tag) {

		if (($tag->term_group == $id) || ($id == 0)) {

			$tag->term_group = 0;

			$ret = wp_update_term( $tag->term_id, 'post_tag', array( 'term_group' => $tag->term_group ) );
		}
		
	}

}


function group_tags_number_assigned($id) {

	$posttags = get_tags(array('hide_empty' => false));
	
	$number = 0;

	foreach($posttags as $tag) {

		if ($tag->term_group == $id) $number++;
	
	}
	
	return $number;

}


function tag_group_custom_js() {
/*
jquery needs some script in the html - opportunity to facilitate some options
*/

	if ( get_option( 'tag_group_mouseover', '' ) ) $mouseover = 'event: "mouseover"';

	if ( get_option( 'tag_group_collapsible', '' ) ) $collapsible = 'collapsible: true';

	if ( !$mouseover && !$collapsible ) {

		$options = '';

	} else {

		$options = $collapsible ? $mouseover . ",\n" . $collapsible : $mouseover;

		$options = $mouseover ? $options : $collapsible;

		$options = "{\n" . $options . "\n}";

	}

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


function font_size($count, $min, $max, $smallest, $largest) {
/*
calculates the font size for the cloud tag ($min, $max and $size with same unit)
*/

	if ($max > $min) {

		$size = round(($count - $min) * ($largest - $smallest) / ($max - $min) + $smallest);
	
	} else {

		$size = round($smallest);
	
	}

	return $size;

}


function register_string_wpml($name, $value) {

	if (function_exists('icl_register_string')) icl_register_string('tag-groups', $name, $value);

}


function unregister_string_wpml($name) {

	if (function_exists('icl_unregister_string')) icl_unregister_string('tag-groups', $name);

}


function translate_string_wpml($name, $string) {

	if (function_exists('icl_t')) return icl_t('tag-groups', $name, $string); else return $string;

}

 
function swap(&$ary,$element1,$element2) {
/*
swaps the position in an array - needed for changing the order of list items
*/

	$temp=$ary[$element1];

	$ary[$element1]=$ary[$element2];

	$ary[$element2]=$temp;

}


function clearCache()  {
/*
good idea to purge the cache after changing the theme options - else your visitors won't see the change for a while
*/

	if (function_exists('w3tc_pgcache_flush')) {

		$plugin_totalcacheadmin->flush_pgcache();
		$plugin_totalcacheadmin->flush_minify();

	} 

	if (function_exists('wp_cache_clear_cache')) {

		wp_cache_clear_cache();

	} 

}

?>