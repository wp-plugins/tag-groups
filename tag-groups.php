<?php
/*
Plugin Name: Tag Groups
Plugin URI: http://www.christoph-amthor.de/plugins/tag-groups/
Description: Assign tags to groups and display them in a tabbed tag cloud
Author: Christoph Amthor
Version: 0.2
Author URI: http://www.christoph-amthor.de
License: GNU GENERAL PUBLIC LICENSE, Version 3
*/

define("TAG_GROUPS_VERSION", "0.2");

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
	
	tag_groups_init();

}


function add_tag_groups_admin_js_css() {
/*
adds css to backend
*/

	wp_register_style( 'tag-groups-style2', plugins_url('css/style.css', __FILE__) );
	
	wp_enqueue_style( 'tag-groups-style2' );

}


function add_tag_groups_js_css() {
/*
adds js and css to frontend
*/

	$theme = get_option( 'tag_group_theme', $tag_group_theme );

	$default_themes = explode(',', TAG_GROUPS_BUILT_IN_THEMES);


	if ($theme == '' ) return;

	if (in_array($theme, $default_themes)) {

		wp_register_style( 'tag-groups-style1', plugins_url('css/'.$theme.'/jquery-ui-1.8.21.custom.css', __FILE__) );

		
	} else {

		wp_register_style( 'tag-groups-style1', get_bloginfo('wpurl').'/wp-content/uploads/'.$theme.'/jquery-ui-1.8.21.custom.css' );
	
	}
	
	wp_enqueue_style( 'tag-groups-style1' );

	wp_enqueue_script('jquery');

	wp_enqueue_script('jquery-ui-core');

	wp_enqueue_script('jquery-ui-tabs');

}


function register_tag_label_page() {

	add_posts_page('Tag Groups', 'Tag Groups', 'manage_options', 'tag-groups', 'tag_groups');

}


function add_post_tag_columns($columns) {
// thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/		
		
	$columns['term_group'] = __('Tag Group', 'tag-groups');
	
	return $columns;
 		
}

	
function add_post_tag_column_content($empty = '', $empty = '', $term_id) {
// thanks to http://coderrr.com/add-columns-to-a-taxonomy-terms-table/

	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$tag = get_tag($term_id);
	
	$i = array_search($tag->term_group, $tag_group_ids); 

	return $tag_group_labels[$i];

}


function update_edit_term_group($term_id) {
/*
get the $_POSTed value and save it in the table
*/
		
	global $wpdb;
		
	if (isset($_POST['term-group'])) {

		if ($_POST['term-group'] == '') return;

		$term_group = (int) $_POST['term-group'];
		
		$term_id = (int) $term_id;

		$result = $wpdb->update($wpdb->terms, array('term_group' => $term_group), array('term_id' => $term_id));
		
	}
		
}


function quick_edit_tag() {
/*
assigning tags to tag groups directly in tag table
*/

 	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	?>

		<fieldset><div class="inline-edit-col">
		
		<label><span class="title"><?php _e( 'Group' , 'tag-groups') ?></span><span class="input-text-wrap">
		
		<select id="term-group" name="term-group" class="ptitle">
		
			<option value="" selected><?php _e('no change', 'tag-groups') ?></option>
		
			<option value="0" ><?php _e('not assigned', 'tag-groups') ?></option>

			<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>" ><?php echo $tag_group_labels[$i] ?></option>

		<?php endfor; ?>

		</select>
		
		</span></label>
		
		</div></fieldset>
		
	<?php
	
}


function create_new_tag($tag) {
/*
assigning tags to tag groups upon new tag creation
*/

 	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	?>

	<div class="form-field"><label for="term-group"><?php _e('Tag Group', 'tag-groups') ?></label>
	
	<select id="term-group" name="term-group">
		<option value="0" selected ><?php _e('not assigned', 'tag-groups') ?></option>

		<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>"><?php echo $tag_group_labels[$i] ?></option>

		<?php endfor; ?>

		</select>
	</div>

	<?php
}


function tag_input_metabox($tag) {
/*
assigning tags to tag groups on single tag view
*/

 	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$number_of_tag_groups = count($tag_group_labels) - 1; ?>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="tag_widget"><?php _e('Tag group') ?></label></th>
		<td>
		<select id="term-group" name="term-group">
			<option value="0" <?php if ($tag->term_group == 0) echo 'selected'; ?> ><?php _e('not assigned', 'tag-groups') ?></option>

		<?php for ($i = 1; $i <= $number_of_tag_groups; $i++) :?>

			<option value="<?php echo $tag_group_ids[$i]; ?>"

			<?php if ($tag->term_group == $tag_group_ids[$i]) echo 'selected'; ?> ><?php echo $tag_group_labels[$i] ?></option>

		<?php endfor; ?>

		</select>
		<p><a href="edit.php?page=tag-groups"><?php _e('Edit tag groups') ?></a>. (<?php _e('Clicking will leave this page without saving.', 'tag-groups') ?>)</p>
		</td>
	</tr>

<?php
}


function tag_groups_init() {
/*
If it doesn't exist: create the default group with ID 0 that will only show up on tag pages as "unassigned".
*/

	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ($tag_group_labels === '') {

		$tag_group_labels[0] = __('not assigned', 'tag-groups');

		$tag_group_ids[0] = 0;

		$number_of_tag_groups = 0;

		$max_tag_group_id = 0;

		update_option( 'tag_group_labels', $tag_group_labels );

		update_option( 'tag_group_ids', $tag_group_ids );

		update_option( 'max_tag_group_id', $max_tag_group_id );

		$tag_group_theme = get_option( 'tag_group_theme', $tag_group_theme );

		if ($tag_group_theme == '') $tag_group_theme = TAG_GROUPS_STANDARD_THEME;

	}
}


function tag_groups() {
/*
sub-menu on the admin backend; creating, editing and deleting tag groups
*/
	$tag_group_labels = array();

	$tag_group_ids = array();

	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$max_tag_group_id = get_option( 'max_tag_group_id', $max_tag_group_id );
	
	$tag_group_theme = get_option( 'tag_group_theme', $tag_group_theme );

	$number_of_tag_groups = count($tag_group_labels) - 1;

	if ($max_tag_group_id < 0) $max_tag_group_id = 0;
	
	$default_themes = explode(',', TAG_GROUPS_BUILT_IN_THEMES);

	$label = '';
	?>
	
	<div class='wrap'>
	<h2>Tag Groups</h2>
	
	<?php

	if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];

	if (isset($_GET['id'])) $tag_groups_id = $_GET['id'];
	
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
	
			if (isset($tag_groups_id) && $tag_groups_id!='0' && $tag_groups_id!='') {
		
				$tag_group_labels[$tag_groups_id] = $label;
				
			} else {
		
				$max_tag_group_id++;

				$number_of_tag_groups++;

				$tag_group_labels[$number_of_tag_groups] = $label;

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

			$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

			$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

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
	
		<h3><?php _e('Create a new tag group') ?></h3>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<ul>
			<li><label for="label"><?php _e('Label') ?>: </label>
			<input id="label" maxlength="45" size="45" name="label" value="<?php echo $label ?>" /></li>   
		</ul>
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Create Group', 'tag-groups'); ?>' id='submitbutton' />
		<input class='button-primary' type='button' name='Cancel' value='<?php _e('Cancel'); ?>' id='cancel' onclick="location.href='edit.php?page=tag-groups'"/>
		</form>
	<?php break;
	
	case 'edit': ?>
	
		<h3><?php _e('Edit the label of an existing tag group') ?></h3>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<ul>
			<li><label for="label"><?php _e('Label') ?>: </label>
			<input id="label" maxlength="45" size="45" name="label" value="<?php echo $tag_group_labels[$tag_groups_id] ?>" /></li>   
		</ul>
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Group', 'tag-groups'); ?>' id='submitbutton' />
		<input class='button-primary' type='button' name='Cancel' value='<?php _e('Cancel'); ?>' id='cancel' onclick="location.href='edit.php?page=tag-groups'"/>
		</form>
	<?php break;
	
	case 'delete':

		$label = $tag_group_labels[$tag_groups_id];

		$id = $tag_group_ids[$tag_groups_id];
		
		unset($tag_group_labels[$tag_groups_id]);

		unset($tag_group_ids[$tag_groups_id]);

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
			<?php _e('A tag group with the id '.$id.' and the label \''.$label.'\' has been deleted.', 'tag-groups'); ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
	<?php break;

	case 'theme':

		if ($theme == 'own') $theme = $theme_name;

		update_option( 'tag_group_theme', $theme );

		?> <div class="updated fade"><p>

			<?php if ($theme != '') {

				_e('Your tag cloud theme has been updated to: '.$theme);
				
			} else {

				_e('Your tag cloud has no pre-defined theme.');

			} ?>
		</p></div><br clear="all" />
		<input class='button-primary' type='button' name='ok' value='<?php _e('OK'); ?>' id='ok' onclick="location.href='edit.php?page=tag-groups'"/>
		<?php
		
	break;

	
	default: ?>
		<p><?PHP _e('On this page you can define tag groups. Tags can be assigned to these groups on the page where you edit single tags.', 'tag-groups') ?></p>
		<h3><?php _e('List', 'tag-groups') ?></h3>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
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
			 <td><a href="edit.php?page=tag-groups&action=edit&id=<?php echo $i; ?>"><?php _e('Edit') ?></a>, <a href="#" onclick="answer = confirm('<?PHP _e('Do you really want to delete the tag group', 'tag-groups') ?> \'<?php echo $tag_group_labels[$i] ?>\'?'); if( answer ) {window.location ='edit.php?page=tag-groups&action=delete&id=<?php echo $i; ?>'}"><?php _e('Delete') ?></a></td>
			 <td>
				 <div style="overflow:hidden; position:relative;height:15px;width:27px;clear:both;">
				 <?php if ($i > 1) :?>
				 	<a href="edit.php?page=tag-groups&action=up&id=<?php echo $i; ?>">
				 	<div class="tag-groups-up"></div>
				 	</a>
				<?php endif; ?>
				</div>

				 <div style="overflow:hidden; position:relative;height:15px;width:27px;clear:both;">
				<?php if ($i < $number_of_tag_groups) :?>
				 	<a href="edit.php?page=tag-groups&action=down&id=<?php echo $i; ?>">
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
		</form>
		
		
		<p>&nbsp;</p>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<h3><?php _e('Theme', 'tag-groups') ?></h3>
		<p><?php _e('Here you can choose a theme for the tag cloud. The path is relative to the <i>uploads</i> folder of your Wordpress installation. Leave empty if you don\'t use any.</p><p>New themes can be created with the <a href="http://jqueryui.com/themeroller/" target="_blank">jQuery UI ThemeRoller</a>. Make sure that before download you open the "Advanced Theme Settings" and enter as "CSS Scope" <b>.tab-groups-cloud</b> (including the dot) and as "Theme Folder Name" the name that you wish to enter below (for example "my-theme" - avoid spaces and exotic characters). Then you unpack the downloaded zip file and open the css folder. Inside it you will find a folder with the chosen Theme Folder Name - copy it to your <i>uploads</i> folder and enter its name below.', 'tag-groups') ?></p>

		<ul>

		<?php foreach($default_themes as $theme) : ?>

			<li><input type="radio" name="theme" value="<?php echo $theme ?>" <?php if ($tag_group_theme == 'ui-gray') echo 'checked'; ?> >&nbsp;<?php echo $theme ?></li>

		<?php endforeach; ?>		

		<li><input type="radio" name="theme" value="own" <?php if (!in_array($tag_group_theme, $default_themes)) echo 'checked' ?> />&nbsp;own: /wp-content/uploads/<input type="text" id="theme-name" name="theme-name" value="<?php if (!in_array($tag_group_theme, $default_themes)) echo $tag_group_theme ?>"></li>
		<input type="hidden" id="action" name="action" value="theme">
		</ul>
		<input class='button-primary' type='submit' name='Save' value='<?php _e('Save Theme'); ?>' id='submitbutton' />
		</form>

		<p>&nbsp;</p>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<h3><?php _e('Delete Groups', 'tag-groups') ?></h3>
		<p><?php _e('Use this button to delete all tag groups and assignments. Your tags will not be changed. Check the checkbox to confirm.', 'tag-groups') ?></p>
		<input type="checkbox" id="ok" name="ok" value="yes">
		<input type="hidden" id="action" name="action" value="reset">
		<input class='button-primary' type='submit' name='delete' value='<?php _e('Delete Groups'); ?>' id='submitbutton' />
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
		<li><b>show_empty=1 or =0</b> Whether to show also tags that are not assigned to any post. Default: 0</li>
		<li><b>include=x,y,...</b> IDs of tag groups (left column in table above) that will be considered in the tag cloud. Empty or not used means that all tag groups will be used. Default: empty</li>
		<li><b>div_id=abc</b> Define an id for the enclosing '.htmlentities('<div>').' Default: tab-groups-cloud</li>
		<li><b>div_class=abc</b> Define a class for the enclosing '.htmlentities('<div>').'. Default: tab-groups-cloud</li>
		<li><b>ul_class=abc</b> Define a class for the '.htmlentities('<ul>').' that generates the tabs with the group labels. Default: empty</li>
		<li><b>show_tabs=1 or =0</b> Whether to show the tabs. Default: 1</li>
		</ul>', 'tag-groups') ?></p>
		<h4>b) PHP</h4>
		<p><?php _e('example: ', 'tag-groups'); echo htmlentities("<?php if (function_exists(tag_groups_cloud)) echo tag_groups_cloud(array( 'include' => '1,2,5,6' )); ?>") ?></p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<h4><a href="http://www.christoph-amthor.de/plugins/tag-groups/" target="_blank">Tag Groups</a>, Version: <?php echo TAG_GROUPS_VERSION ?></h4>
	
	<?php }	?>

	</div>
	
<?php
}


function tag_groups_cloud( $atts ) {
/*
Rendering of the tag cloud, usually by a shortcode [tag_groups_cloud xyz=1 ...]
*/

	$tag_group_labels = array();

	$tag_group_labels = get_option( 'tag_group_labels', $tag_group_labels );

	$tag_group_ids = get_option( 'tag_group_ids', $tag_group_ids );

	$number_of_tag_groups = count($tag_group_labels) - 1;
	
	extract( shortcode_atts( array(
		'smallest' => 12,
		'largest' => 22,
		'amount' => 40,
		'show_empty' => 0,
		'include' => '',
		'div_id' => 'tab-groups-cloud',
		'div_class' => 'tab-groups-cloud',
		'ul_class' => '',
		'show_tabs' => '1',
		), $atts ) );

	if ($smallest < 1) $smallest = 1;
	
	if ($largest < $smallest) $largest = $smallest;
	
	if ($amount < 1) $amount = 1;
	
	if ($include != '') {

		$include_groups = explode(',', $include);
	
	}

	$posttags = get_tags();


	$div_id_output = ($div_id) ? ' id="'.$div_id.'"' : '';

	$div_class_output = ($div_class) ? ' class="'.$div_class.'"' : '';

	$ul_class_output = ($ul_class) ? ' class="'.$ul_class.'"' : '';


	$html = '<div'.$div_id_output.$div_class_output.'>';


	if ($show_tabs == '1') {

		$html .= '<ul'.$ul_class_output.'>';
	
		for ($i = 1; $i <= $number_of_tag_groups; $i++) {
	
			if (($include == '') || (in_array($tag_group_ids[$i],$include_groups))) {
	
				$html .= '<li><a href="#tabs-'.$i.'" >'.$tag_group_labels[$i].'</a></li>';
	
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

			    			if (($tag->count > 0) || ($show_empty == 1)) {

			    				$tag_link = get_tag_link($tag->term_id);
				    			$html .= '<a href="'.$tag_link.'" title="'.$tag->name.', '.$tag->count.'"  class="'.$tag->slug.'"><span style="font-size:'.font_size($tag->count,$min,$max,$smallest,$largest).'px">'.$tag->name.'</span></a>&nbsp; ';
				    			$count_amount++;
				    		
							}
				    	
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

	$posttags = get_tags();
	
	foreach($posttags as $tag) {

		if (($tag->term_group == $id) || ($id == 0)) {

			$tag->term_group = 0;

			$ret = wp_update_term( $tag->term_id, 'post_tag', array( 'term_group' => $tag->term_group ) );
		}
		
	}

}


function group_tags_number_assigned($id) {

	$posttags = get_tags();
	
	$number = 0;

	foreach($posttags as $tag) {

		if ($tag->term_group == $id) $number++;
	
	}
	
	return $number;

}


function tag_group_custom_js() {

	echo '
	<!-- begin Tag Groups plugin -->
	<script type="text/javascript">
		jQuery(function() {
	
			jQuery( "#tab-groups-cloud" ).tabs();

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
 
 
function swap(&$ary,$element1,$element2) {
/*
swaps the position in an array - needed for changing the order of list items
*/

	$temp=$ary[$element1];

	$ary[$element1]=$ary[$element2];

	$ary[$element2]=$temp;

}


?>