=== Import XML and RSS Feeds ===
Contributors: MooveAgency
Donate link: https://www.mooveagency.com/wordpress-plugins/import-xml-feed/
Tags: xml, rss, import, feed, JSON
Stable tag: 2.1.6
Requires at least: 4.3
Tested up to: 6.3
Requires PHP: 5.6
License: GPLv3

Import content from any XML or RSS file or URL. Very useful for importing content from Wix websites.

== Description ==

Import content from any XML or RSS file or URL. 

You can import the content to any post type in your WordPress install. You can also import taxonomies.

Our plugin is especailly useful for importing content from Wix websites.

### Step by Step process

* Select the source ( URL or FILE UPLOAD ).
* Select your repeated XML element you want to import - this should be the node in your XML file which will be considered a post upon import.
* Select the post type you want to import the content to.
* Match the fields from the XML node you've selected (step 2) to the corresponding fields you have available on the post type.

### Supported files and URLs

* The XML source file should be a valid XML file. [You can check your feed validation here.](https://validator.w3.org/feed/)
* JSON feeds needs to be first converted to RSS or XML file [using tools such as this.](https://www.convertjson.com/json-to-xml.htm)
* The plugin will then check if the URL source or the uploaded file is valid before the import and processing starts
* If you use  URL source for import, please ensure the URL is not password protected
* Supported formats: XML 1.0, XML 2.0, Atom 1, RSS


### Features

* **XML Preview** - After successfully uploading an XML file or reading an external URL, the plugin will present you with an XML preview of the selected node. This can be used to check if you've selected the correct node and ensure that the data are read correctly by the plugin. The preview presents one item from the selected node but it is paginated so you can navigate back and forward between the elements.

* **Linking Taxonomies to Posts** - You can import categories and taxonomies from the XML file and link the imported posts to these taxonomies. First you need to have the taxonomies created in WordPress to allow the plugin to import into these taxonomies. By default WordPress has two taxonomies: categories and tags.

* **Limit posts** - In the "Import Settings" area you can set limits for the import. You can use multiple patterns to include posts in the import. Use semicolon to separate the values, for example: 1-8;10;14-

* **Importing and linking multiple taxonomies to one post** - To import and link one post to multiple taxonomies, you need to have an XML element in your selected node with a list of categories separated by commas. These elements will be recognized and imported separately as taxonomy terms.

* **Save & Load templates** - Once the fields are matched, you can save the matching as a template, and use it again for another import.

* **Support for tag attributes** 

* **Custom Fields & ACF support**


### Demo Video

[vimeo https://vimeo.com/305452075]


### Testimonials

★★★★★
> “Excelent” - [mediadocena](https://wordpress.org/support/topic/excelent-995/)

★★★★★
> “Works as expected, smooth process” - [Sunfire](https://wordpress.org/support/topic/works-as-expected-smooth-process/)

★★★★★
> “Excellent! Finally a plugin that does exactly what I need” - [golfinsa](https://wordpress.org/support/topic/excellent-finally-a-plugin-that-does-exactly-what-i-need/)

== Screenshots ==
1. Import XML feed - Select XML/RSS feed from URL
2. Import XML feed - Select XML/RSS feed from File Upload
3. Import XML feed - Select the repeat element from feed
4. Import XML feed - Matching elements
5. Import XML feed - Import finished
6. Import XML feed - Templates

== Installation ==
1. Upload the plugin files to the plugins directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the \'Plugins\' screen in WordPress
3. Go to the 'Settings->Import Feed' to configure the plugin

== Changelog ==
= 2.1.6 = 
* Image upload fixed

= 2.1.5 = 
* Minor fixes

= 2.1.4 = 
* WP 6.3 Compatibility
* Improved template functions
* PHP 8.0 compatibility fixes

= 2.1.3 = 
* WP 6.2 Compatibility

= 2.1.2 = 
* WP 6.0 Compatibility

= 2.1.1 = 
* PHP warnings fixed
* Minor fixed

= 2.1.0 =
* New features released

= 2.0.5 =
* Admin improvements
* Plugin updater improved

= 2.0.4 =
* Bugfixes

= 2.0.3 =
* Improved reading XML from URL

= 2.0.2 =
* Improved import script

= 2.0.1 =
* Bugfixes
* Admin screen fixes
* Simple XML string entity fix

= 2.0.0 =
* Licence manager implemented
* Improved admin layout
* Bugfixes

= 1.3.3 =
* Added Atom feed support
* Bugfixes

= 1.3.2 =
* Improved AJAX actions

= 1.3.1 =
* Fixed Limit Post queue issue

= 1.3.0 =
* Queue implementation for AJAX calls

= 1.2.4 =
* Added hook to filter the_content

= 1.2.3 =
* Fixed image import if scheme is missing from URL

= 1.2.2 =
* Extended support for add-on
* Bugfixing
* Improved admin layout

= 1.2.1 =
* Updated plugin premium box

= 1.2.0 =
* Updated plugin premium box

= 1.1.9 =
* Fixed translation slugs
* PHP 7 compatibility

= 1.1.8 =
* Adding Czech translation

= 1.1.7 =
* Adding donation box

= 1.1.6 =
* Fixed PHP warnings

= 1.1.5 =
* Fixed multiple taxonomy import, comma separated list allowed

= 1.1.4 =
* Fixed post_title field, HTML tags will be removed from it

= 1.1.3 =
* Fixed PHP Warning message

= 1.1.2 =
* Fixed Date format issue

= 1.1.1 =
* Fixed ACF functions

= 1.1.0 =
* Added post limitation

= 1.0.9 =
* Fixed "Wrong or unreadable XML file!" error on file upload.

= 1.0.8 =
* Fixed "Wrong or unreadable XML file!" error appeared for Internet Explorer users.

= 1.0.7 =
* Fixed featured image import

= 1.0.6. =
* Added ability to set post_date from xml/rss feed. (thanks to metadan)

= 1.0.5. =
* Fixed Options page controller issue

= 1.0.4. =
* Rss "Atom" namespase issue fixed

= 1.0.3. =
* Third party include fixed

= 1.0.2. =
* Validated, sanitized and escaped inputs

= 1.0.1. =
* Code modified to follow WP standards

= 1.0.0. =
* Initial release of the plugin.
