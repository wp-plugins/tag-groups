=== Tag Groups ===
Contributors: camthor
Donate link: https://flattr.com/thing/721303/Tag-Groups-plugin
Tags: tag, tags, term_group, tag cloud, tag-cloud, WPML
Requires at least: 2.9
Tested up to: 3.4.1
Stable tag: 0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin enables you to define groups for tags and then assign tags to them. The group membership is saved in the native WP table field term_group, so it won’t create additional tables in the database. The plugin comes with a configurable tag cloud where tags are displayed in tabs sorted by groups.

Possible applications are:

* Display your tags grouped by language or by topic.
* Display your tags in any order, independently from their names or slugs.
* Choose which tags to display in different sections of your blog.

Please find more information [here](http://www.christoph-amthor.de/software/tag-groups/ "plugin website").

== Installation ==

1. Find the plugin in the list at the backend and click to install it. Or, upload the ZIP file through the admin backend. Or, upload the unzipped tag-groups folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.

The plugin will create a new submenu in the Post section where you edit the tag groups. After you have created some groups, you can edit your (post) tags and assign them to one of these groups.


== Frequently Asked Questions ==

No questions yet.

== Screenshots ==

1. The edit screen
2. Tag cloud (custom theme)
3. Tag cloud (custom theme)

== Changelog ==

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