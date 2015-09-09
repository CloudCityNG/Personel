Improved Google Analytics for Moodle.

Package name: local_googleanalytics
Copyright: 2013 Bas Brands, www.basbrands.nl
Authors: Bas Brands and Gavin Henrick.
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


ABOUT

This plugin adds the Google Analytics Javascript snippet
To each Moodle page in the page footer and modifies the page information being tracked:

Instead of sending URL's like

http://www.mymoodlesite.org/course/view.php?id=5
http://www.mymoodlesite.org/mod/forum/view.php?id=80

It sends URL's that are easy to read and recognize like:

http://www.mymoodlesite.org/CategoryA/sourcialcourse/Topic+1/myresource
http://www.mymoodlesite.org/Category1/introcourse/Topic+3/open+forum/Forum

For more information read:
http://basbrands.nl/blog/2012/04/18/google-analytics-with-moodle
and
http://www.somerandomthoughts.com/blog/2012/04/18/ireland-uk-moodlemoot-analytics-to-the-front/

INSTALLATION

Install this plugin in your /local folder
Then login as an admin and go to
Site Administration -> Notifications

This should trigger the installation process


CONFIGURATION

To track pages using Google Analytics you need to register for a google analytics account and
retreive a Google Analytics key.

Then go to:
Site Administration -> Plugins -> Local Plugins -> Google Analytics

Enable the plugin and add your Google Analytics key.