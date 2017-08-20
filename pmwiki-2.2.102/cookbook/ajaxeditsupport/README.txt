INTRODUCTION
============

This recipe adds 5 AJAX-powered browsers for:

- Existing groups/pages
- Attachments
- Categories
- Given elements in the current page
- Templates defined site-wide, group-wide or in the current user's profile.

Just install the recipe, and add (:ajaxeditsupport:) on your site's EditForm. From then on, the
user will be able to easily browse the files to insert links, attachments, etc...

INSTALLATION
============
Place the following command somewhere in your local/config.php
configuration file:

  @include_once("$FarmD/cookbook/ajaxeditsupport.php");


CHANGELOG
=========

1.0a
- Solved a problem with Internet Explorer 8 which caused all the source of the page to appear as a single
  line in the textarea if the "Current page" browser was enabled.
  Thanks to Ricard Nàcher for finding the problem and the solution.

1.0
 - Added the "current page" browser
 - Added the templates browser.
 - Click anywhere to close any browser.
 - New config options.

0.6
- Solved a problem when direct download was enabled.
- Added config options for:
	- $AjaxEditSupportPageBrowser (0|1): Enables page browser. Defaults to 1 (enabled).
	- $AjaxEditSupportAttachmentBrowser (0|1): Enables attachments browser. Defaults to 1 (enabled).
	- $AjaxEditSupportCategoryBrowser (0|1): Enables categories browser. Defaults to 1 (enabled).
	- $AjaxEditSupportBrowserWidth: Sets the width in pixels of the browsers. Defaults to 450.
	- $AjaxEditSupportBrowserHeight: Sets the height in pixels of the browsers. Defaults to 450.

0.5
- First release


TODO
====

- Per-group or sitewide attachments support


