=== Tag Groups ===
Contributors: camthor
Donate link: http://www.burma-center.org/donate/
Tags: tag, tags, term_group, tag cloud, tag-cloud, WPML, category, categories, category cloud
Requires at least: 3.1
Tested up to: 3.7
Stable tag: 0.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin enables you to organize tags (or categories, or custom terms) in groups. The group membership is saved in the native WP table field term_group, so it won't create additional tables in the database. The plugin comes with a configurable tag cloud where tags are displayed in tabs sorted by the groups. It is possible to upload own themes (advanced usage).

Possible applications are:

* Display your tags grouped by language or by topic.
* Display your tags in any order, independently from their names or slugs.
* Choose which tags to display in different sections of your blog.
* Link from you posts and pages to other ones that have the same tags.

Please find more information [here](http://www.christoph-amthor.de/software/tag-groups/ "plugin website").

== Installation ==

1. Find the plugin in the list at the backend and click to install it. Or, upload the ZIP file through the admin backend. Or, upload the unzipped tag-groups folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.

The plugin will create two new submenus in the Post section where you find the tag groups and related settings. After you have created some groups, you can edit your tags (or other terms) and assign them to one of these groups.

Other taxonomies than post tags and categories may need further customization beyond this plugin.

The tabbed tag cloud can be inserted with a shortcode. Options are listed on the Settings page.


== Frequently Asked Questions ==

= When I use the shortcode I can see the content but they are not displayed in tabs. =

Make sure you have "Use jQuery" checked on the settings page. If you use a plugin for caching pages, purge their caches and see if that helps. If you use plugins for minifying scripts or style sheets, turn them off and purge their caches.


== Screenshots ==

1. The edit screen
2. Tag cloud (custom theme)
3. Tag cloud (custom theme)

== Changelog ==

= 0.12 =

FEATURES

* multiple taxonomies
* new parameter to select taxonomies for tag cloud

BUG FIXES

* function post_in_tag_group was wrong for other taxonomies that tags

= 0.11 =

FEATURES

* multiple shortcodes/clouds per page by using 'div_id' with own values
* general settings for 'collapsible' and 'mouseover' can be overridden per cloud
* new parameters to prepend and append text to tags

= 0.10 =

FEATURES

* added two parameters for shortcode and function to filter tags or groups depending on the tags of a particular post
* added parameter to optionally hide empty tabs

BUG FIXES

* updated uninstall routine

= 0.9.1 =

BUG FIXES

* fixed filter not showing up in some settings

= 0.9 =

FEATURES

* filter posts on the back end and show only posts that have tags belonging to particular tag groups
* diversified permissions: now editors can edit tag groups, while vital settings can only by changed by administrators

= 0.8.2.3 =

BUG FIXES

* improved instructions for installing own theme
* fixed deprecated file names - please check cloud tags if you use custom theme

= 0.8.2.2 =

BUG FIXES

* theme images lost during plugin evolution
* fixed css of jquery-ui

= 0.8.2 =

* svn missed files, new version is 0.8.2.2

= 0.8.1 =

BUG FIXES

* fixed warning for adding term that is not managed by this plugin

= 0.8 =

FEATURES

* new parameters for shortcode to display a separator

BUG FIXES

* fixed potential problem when flushing the cache

= 0.7.2 =

BUG FIXES

* fixed shortcode not working in widgets; can now be enabled in settings

= 0.7.1 =

BUG FIXES

* fixed wrong stripping of html in term descriptions (thanks to Ahni for reporting)
* uninstallation now removes plugin settings
* fixed typo

= 0.7 =

FEATURES

* supports now other taxonomies than post_tag
* user-friendlier settings page

BUG FIXES

* fixed wrong group displayed on quick edit

= 0.6.2 =

BUG FIXES

* fixed 'foreach' warning (thanks to IOTI for reporting)

= 0.6.1 =

BUG FIXES

* wrong code in instructions

= 0.6 =

FEATURES

* optional output as array for theme developers
* tags can now be sorted

BUG FIXES

* counting tags

= 0.5.1 =

BUG FIXES

* escaping
* saving of menus

= 0.5 =

FEATURES

* improved inline editing in tag list (still problems with Opera browser)
* hardened security with 'nonce'

BUG FIXES

* with WPML installed, inline editing of tag groups showed up at posts


= 0.4.1 =

BUG FIXES

* faulty default settings after plugin activation

= 0.4 =

FEATURES

* tabs on tag cloud: support for mousover and collapsible
* optionally not enqueuing jQuery for custom themes

BUG FIXES

* problem saving themes

= 0.3 =

FEATURES

* support for WPML string translation of tag group labels

BUG FIXES

* incomplete deletion of tag groups
* no display of unused tags
* wrong counting of tags
* obsolete JS

= 0.2.1 =

BUG FIXES

* Wrong label introduced in last version.

= 0.2 =

FEATURES

* Introduced an option to show a tag cloud without tabs - useful when displaying tags of just one group.
* Tag groups are now visible as new column in the tag list.
* Tag groups can now be assigned upon tag creation and changed directly in the tag list.

BUG FIXES

* Showing wrong tag group in single tag view.

= 0.1 =
* initial release


== Upgrade Notice ==

Nothing yet.


== Other Notes ==

Styling created by jQuery UI who also provided the JavaScript that is used for the tabs to do their magic. Find their license in the package.