=== WP SEO Keyword Optimizer ===
Author URI: https://www.bavoko.services
Plugin URI: https://www.bavoko.services/wspo
Contributors: BAVOKO
Tags: SEO, Search Engine Optimization, Keyword Density, Rankings, Clicks, CTR, Impressions, position, positions, rankings, ranking keywords, keywords, optimize keywords, cache, wordpress seo, seo wordpress, Onpage SEO, keyword optimization, on page analysis, on page seo, on page optimization, seo rankings, Google Optimization, SEO charts, ranking pages, Suggest, Google Suggest, Google, Search Console, Google Search Console, google rankings, optimize content, content optimization, marketing, seo analysis, seo statistics, higher rankings
Tested up to: 4.7.4
Requires at least: 4.0
Stable tag: 1.2.10

Get real SEO data from Google Search Console - Analyze all charts, ranking Keywords and Pages – Optimize your Content based on your Rankings!

== Description ==

Important: This Plugin requires PHP Version 5.5+ and Wordpress 4.0+. Also your Google account needs to be the property owner of your domain in the Google Search Console.

Note: As of version 1.2.7 a caching error has been fixed. Please note that any data set from before 90 days (from your update) will be lost, if it is corrupted.

Connect your website with Google Search Console and get all important SEO data in your Wordpress Backend and more! 

**Analyze your Keywords & Pages**

Advantages

* Go around the limit of 1.000 keywords in your Search Console and get them all in your Wordpress. Save your top 5000 Keywords per day.
* Google just shows you the data of the past 3 months. Prevent losing them and make deeper analysis with a bigger set - WSKO saves all data from three months before the first install of it in it’s cache.
* The WSKO Dashboard provides a customizable time period overview with a set of useful information like Total Keywords, Keywords in Top 10, Clicks, Impressions and their changes compared to the last period. 
* View important charts for your SEO, like Ranking Keywords, Ranking Pages, Position History, Clicks History, Impressions History, Ranking Distribution and Ranking Distribution History, for a custom period of time.
* Analyze all keywords and sort by Clicks, Position, Impressions and CTR – Changes to the last period included. Keyword Search is also available.
* View and analyze all pages, the ranking keywords of them and sort by Clicks, Position, Impressions, and CTR – Changes to the last period included. Page Search is also available.


**Optimize your Pages**
Optimize your content based on your custom keywords & ranking keywords with the Content Optimizer in your posts, pages, custom post types and improve your OnPage SEO.

Advantages

* Optimize your pages based on your rankings. See all ranking keywords by editing a specific page and set the priority by targeting or focusing them. Optimize your content for the selected keywords and get higher rankings!
* Use the built-in Google Suggest tool to find long tail keywords in seconds. 
* Set Custom Keywords for new articles which don’t have rankings yet.
* Focus your most important keywords and target long tail keywords! You can read more about the differences of these two options in our [Documentation](http://www.bavoko.services/wordpress/wsko/)
* The advices are very close to the actual Data Driven Analysis of Ahrefs.com with 2M Keywords – Read more about the [on page SEO factors](https://ahrefs.com/blog/on-page-seo/)
* Don’t lose track of your keywords - Create Keyword Groups by summarizing them. Once a keyword within a group fits a criterion, like appearing in the title tag, the advice for the others won’t be shown any more. Easy examples for groups are: „Plugin“ – „Plugins“, or „On Page SEO“ – „SEO On Page“.

Note: You are able to activate, or deactivate the Content Optimizer in your plugin settings.

== Installation ==

= Installing the plugin via WordPress =
1. Sign in to WordPress with your admin account.
2. Navigate to plugins - install in your menu.
3. In the section search for plugins you can now find the WP SEO Keyword Optimizer.
4. Clicking the install button starts the WordPress installation of your plugin.
5. After the installation you can activate your new software by clicking the activate now button. You also can activate the plugin afterwards by navigating to plugins - installed plugins.

= Installing the plugin via FTP =
If you have trouble installing the plugin via your WordPress admin section, you can also try to start the installation via FTP. Therefore you need an FTP program.
You can download the zip file containing the installation folder on our website.

1. Download the installation file of the WP SEO Keyword Optimizer and save it on your PC.
2. Unpack the zip archive on your harddrive.
3. Use your FTP program to establish a connection to your web space.
4. Upload the installation file in the directory wp-content/plugins in your WordPress installation folder.
5. You can now activate the plugin with WordPress by navigating to plugins - installed plugins


== Frequently Asked Questions ==

Please read our [Documentation](https://www.bavoko.services/wordpress/wsko-plugin/) for a quick overview on our plugin pages (detailed instructions coming soon) and remember to allways have our plugin up-to-date.

Please Note: You need a configured Google Search Console Connection on your Google Account. Visit [Google Search Console](https://www.google.com/webmasters/tools/home) and add your domain if it isn't already connected.

Requirements:

PHP 5.5+
WordPress 4.0+

= Google Errors =

In some cases another plugin may load an old version of the Google Client API. This will cause an error and output a notice. If the initialization was successfull this will only result in a warning. However it is still recommended with both errors to either update your plugins and themes, or deactivate the plugins causing this error. (Right now it is not possible to show which plugin is loading an old version)

= Cache Update =

After first Installation of the plugin you need to update your Cache by clicking the 'Update Manually' button. Please click the update button as long as there are missing Keyword rows left.

= Slightly off "Position" values in Caching Mode =

Caching Mode needs too calculate an overall average from multiple data rows, because the underlying Google API only ofers values for a specific time range. This results in rounding errors in caching mode. The effect is linked to the time range you are looking at: More rows = greater rounding errors.

Usually the value is only +-1 position off in 1-5% off your keywords with ~30 days as your time range.

= Keywords in Top 10 varies in Caching/Live Mode =

Please note that in caching mode more keywords can be shown in your statistics, so this value can increase in Caching Mode if you are hiting the limit with Live Mode. Besides that please see the topic above for an explenation to rounding errors, because this value is directly linked to the positions of every keyword.

== Screenshots ==

1. WSKO Dashboard - Overview - See Overview for an overall report of your clicks, impressions, and more
2. WSKO Dashboard - Main History - Your papge progress over time
3. WSKO Dashboard - Ranking Distribution - Overall average ranking of your pages over time
4. WSKO Dashboard - Top 5's - The 5 best ranking keywords and pages by clicks
5. WSKO Dashboard - Keyword Overview - Your current ranking Keywords with corresponding information
6. WSKO Dashboard - Page Overview - Your current ranking Sites with corresponding information, linked to a WordPress-Post if possible
7. WSKO Dashboard - Page Details - Show all ranking keywords for a page
8. WSKO Dashboard - Settings - Customize the plugin, if needed
9. Content Optimizer - Post Widget - Optimize a page for specific keywords and important SEO criteria and get a brief summary of your page statistics
10. Content Optimizer - Track Keywords Modal - Select already ranking keywords, add new ones and group similar keywords, to account them for optimization

== Changelog ==

= 1.2.10 =
* Hotfix: AJAX Error - Added an error message and solved bug where it fires, allthough a notice should be shown (Note that if you got this, the update will most likly end in a more specific error message)

= 1.2.9 =
* Core Update - Some settings will be reset, please review them after updating
* Added: Permission System - You can give view access to this plugin to any user role (settings, reports and any interaction with the cache are for admins only)
* Added: Contact us overlay and link
* Added: AJAX loading system, so you don't spend to much time in front of a white screen
* Fixed: Various causes of the infamous "Query Error" and CronJob related errors
* Fixed: First and/or last day missing for a selected time range (wrong timestamps)
* Fixed: Error on some HTTPS sites (see https://wordpress.org/support/topic/wrong-protocol-http-vs-https/)
* Changed: Unstructured iCheck implementation fixed
* Changed: Standard time range was one day off to Googles last 28 days view
* Changed: Minor range and label changes for the time range picker
* Changed: Settings tabs are now more structured
* Changed: Error reporting has moved to it's own page (for performance)
* Changed: Some new notifcations and help icons were added
* Changed: Query blocks will pause 1 second between calls to maintain the 5 QPS limit set by Google
* Changed: Accidential remenants of two woocommerce files removed

= 1.2.8 =
* Hotfix - Google API loading everywhere (and thus being detected in our own check)
* Added: Time range to Keyword Details
* Fixed: Token should now be persistent through minor updates (Some versions may still require a new token)
* Fixed: Update Cache manually not working with caching disabled
* Fixed: Database option being updated instead of added if they don't exist
* Other: Changed Error API Exception output, Refreshed CronJob so 1.2.7 changes also have an effect, "Position" delta is now an absolute value

= 1.2.7 =
* Added: Error Reporting - In case of an error activate error reporting on the settings page and attach these reports to your support request
* Fixed: CronJob Error - corrupted data sets
* Other: Removed CTR field from database
* Other: Stabillity improvements, more loading icons

= 1.2.6 =
* Hotfix - Wrong PHP-sections

= 1.2.5 =
* Fixed: PHP 5.5 is the minimum requirment (instead of 5.4)

= 1.2.4 =
* Compatibility: Fixed several design and functional errors with other plugins
* Fixed: CronJob error (not running in some cases and duplicating data)
* Fixed: Track Keywords Modal opening and closing again
* Fixed: Google API conflict in init-action
* Fixed: Timestamp error (local) with custom range
* Fixed: Scroll error with Track Keywords Modal
* Other: Added several security and compatibility procedures/notices

= 1.2.3 =
* Fixed: White Dashboard error due to already loaded old Google Client API (below v2.0)

= 1.2.2 =
* Fixed: Uninstall Flag not working

= 1.2.1 =
* Hotfix - Live-Mode - Errors and CTR value wrong in Caching-Mode

= 1.2 =
* New: Toggle Caching (Active Caching is recommended)
* New: Caching Controls (Delete Cache, see database size, automatic delete time, ...)
* New: Uninstall Flag - If you are about to uninstall this plugin completely, check the option in the settings. This will also get rid of any database trace, the plugin has created.
* New: Activation Page has more info now
* New: Refreshing the page while updating the cache will show a confirmation first
* Fixed: Loading Assets on all pages reduced to plugin pages and post.php
* Fixed: Google API namespacing
* Fixed: Update Cache now has a timeout treshold to get more data rows in a big request
* Fixed: Dashboard Error View (now with logout button)
* Fixed: Authentication Token must be set before submiting the form
* Changed: Reduced WordPress Options to a single one
* Changed: Content-Length SEO criteria now has no negative effect if the page length is over 1500
* Other: Some graphs were disabled in the live-data version, because they rely on cached data
* Other: Various fixes and security related changes

= 1.1.3 =
* Hotfix - Critical Activation Error

= 1.1.2 =
* New: Documentation-Link
* Fixed: Pageination-Error on Pages-Overview
* Fixed: CronJob not working

= 1.1.1 =
* Notice changes

= 1.1 =
* New: Caching - Save all data data from first installation
* New: URL-Idenitifcation due to high load moved to a lazy loading process (may take a while)
* New: Settings-Page
* New: Keywords in Top 10 - Added progress value from mirrored time span
* New: Ranking Keyword Count on Post Page
* New: Toggle Post Widget - Setting on Login-Page
* New: Keyword and Ranking History
* New: Reload-/Submit Confirmation for post page modal (Track Keywords)
* New: Data-Limits (Can be changed in settings) for Performance (Note: Limit will allways cut the worst keywords, because it is applied on the rows sorted by clicks)
* Fixed: New URL-Resolve method to include redirected URLs
* Fixed: Suggest-Field on Post Page reloads page on "Enter" press
* Fixed: "Keyword in URL"-Criteria not working for long tail keywords
* Fixed: "Meta-Title not used"-Criteria issued for pages with only "<title/>"-element and no meta-tags
* Fixed: SEO-Criteria not working on posts with embedded scripts
* Fixed: Suggest-Tool: Language by wordpress locale
* Compatibility: Added global script/link identifiers for additional use of WSPO (Update 1.1 in queue)
* Other: Performance-/Usabillity Improvements

= 1.0.1 = 
* Hotfix - Google Error - Logout

= 1.0 = 
* Initial release
