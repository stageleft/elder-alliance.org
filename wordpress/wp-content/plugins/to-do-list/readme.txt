=== To Do List ===
Contributors: dgwyer
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5YY6QK2LA377J
Tags: to do, list, tasks, admin
Requires at least: 2.7
Tested up to: 3.4
Stable tag: 2.0

Finally, a simple way to keep track of important tasks and activities! Every registered user can maintain an individual to-do list using the built-in WordPress rich text editor.

== Description ==

Update: As of May 2012 the To Do List Plugin has undergone a complete rewrite and is now fully compatible with the latest version of WordPress (3.4).

Maintain an active to-do list for every registered user on your site! Each list is unique and is automatically displayed for the currently logged in user via the WordPress dashboard.

Administrators have direct access to ALL to-do lists.

Please rate the Plugin if you find it useful, thanks.

See our <a href="http://www.presscoders.com" target="_blank">site</a> for more Plugins and themes.

== Installation ==

Instructions for installing:

1. In your WordPress admin go to Plugins -> Add New.
2. Enter To Do List in the text box and click Search Plugins.
3. In the list of Plugins click Install Now next to the To Do List Plugin.
4. Once installed click to activate.
5. Start creating your to-do list!

== Screenshots ==

1. The to-do list (for the current user) is displayed in the WordPress dashboard.
2. Edit the to-do list on your user profile page.

== Changelog ==

= 2.0 =

* Complete rewrite! Now fully compatible with WordPress 3.4.
* Each to-do list is now edited on a users profile page rather than directly on the WordPress dashboard. However, the current to-do list is still displayed on the dashboard.

= 1.3.1 =

* Text no longer coloured by default when TinyMCE editor is 'active' but the html tab is selected.

= 1.3 =

* Users can now use the TinyMCE editor included with WordPress to format their To-Do List! There is an option to use the plain and simple editor from the previous version. Infact, this is still the default. If you wish to drag the To-Do List to a new position in the dashboard you MUST enable the simple editor first. Otherwise it will not work properly.
* Many minor updates to the plug-in code, mainly just tidying things up and updating some links.

= 1.21 =

* Fixed bug that caused an error for some users: removed call to unefined function 'gc_todo_admin_actions()'.

= 1.2 =

* Support for multiple users. Every user of the blog can maintain their own list! Plugin automatically monitors who is logged in and displays the appropriate to do list.
* To Do List is now available as a dashboard widget for convenience, and accessibility.
* List saved in the database not as a text file.
* When a user is deleted from the blog, their to do list is deleted too, automatically.

= 1.02 =

* To Do List now located under the Dashboard top level menu. Makes more sense to put it here.
* Changed the way the Plugin gets the path to the To Do List folder. Now using 'WP_PLUGIN_DIR' constant rather than 'PLUGINDIR'.

= 1.01 =

* Changed message when update button clicked. Used to display 'Options Saved', now displays 'To Do List Updated'.
* Removed test code that shows magic quotes status.