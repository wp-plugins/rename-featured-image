=== Rename Featured Image ===
Contributors: hrishiv90
Tags: rename image, rename post attachment, rename post thumbnail, featured image rename, rename thumbnail
Requires at least: 4.1
Tested up to: 4.2
Stable tag: 1.0
License: GPLv2 or later (of-course)
License URI: http://www.gnu.org/licenses/gpl-2.0.html


This plugin uses WordPress hooks and updates the featured image title and file name.

== Description ==

This plugin updates the image title (post title) and file name which is set as featured image when publishing the post.

= Bulk Rename =
This plugin has settings page through which you can rename the title and file name of all the post featured images to their respective post title. It has filter to check if already same file name (from the attachment url) as of post title.
The post title is sanitized before applying as new name to attached image.

It has an "Force Rename button" for renaming all images forcefully. It helps to update partially renamed images and disable the filter mentioned above.

== Installation ==
Install Rename Featured Image plugin from the 'Plugins' section in your dashboard (Plugins > Add New > Search for Rename Featured Image).

Activate it through the 'Plugins' section.

Use Plugin feature from the plugin settings page.

== Screenshots ==

1. Rename Featured Image bulk update page

== Frequently Asked Questions ==

= Does this plugin actually renames the image file? =

Yes, plugin renames the actual file in the uploads folder.


= Does this plugin rename the attachment url and guid both? =

Yes, plugin renames the file name in both the attachment url and guid.


= Can we revert the previous name of file to it? =

No, once file is renamed then previous file name can't be brought back.

== Changelog ==

= 1.0 =
* First Release