=== Bulk NoIndex & NoFollow Toolkit ===
Contributors: MadFishDigital
Donate link: https://www.madfishdigital.com/plugins-donate/
Tags: bulk noindex nofollow, seo penalty recovery, thin content, yoast, All in One SEO (AIOSEO)
Requires PHP: 5.6
Requires at least: 4.1
Tested up to: 6.6.2
Stable tag: 2.16
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Bulk set the noindex / nofollow robots tag in for pages and categories. Easily identify thin content, and noindex it fast.

== Description ==

Developed by Mad Fish Digital, this plugin saves webmasters time when finding and removing thin pages in your website from the search engine indexes. 

This plugin has an interface that allows you to sort posts by word count, character count then bulk noindex or bulk nofollow the posts or page so that they will stop appearing in search engine indexes. 

The plugin has an interface that allows you to sort categories by their post count, and add a robots no follow and/or noindex meta tag to category and term pages.

This plugin is able to help manage a robots no index and no follow meta tag for all post types and categories.

= Please Keep in Mind =

After a page or category is noindexed, in some cases, it can take search engines up to a few weeks before the page stops appearing in the search index. The amount time will depend on how frequently a search engine crawls your website and pages. We recommend using Google Search Console to further analyze and understand how your pages will potentially appear in the search index. 

= Advantages =

1) Reduce the time it takes to Noindex/Nofollow each page, post, or category manually through the easy to use interface

2) Sort posts and pages by word count and character count to quickly identify thin content pages

3) Sort categories and tags by their number of associated posts

4) Speed up website search engine penalty recovery time by identifying and bulk noindexing large numbers of posts and pages

5) Quickly noindex content identified by web crawlers such as DeepCrawl

6) Easily control the robots meta tag directives on large numbers of pages

7) Visualize of all posts' and pages' "noindex" and "nofollow" statuses

8) Syncs with the Yoast and the All in One SEO Pack (AIOSEO) plugins to maintain and manage your existing noindex and nofollowed posts and pages

= Support =

For support related inquiries, visit the <a rel="follow" href="https://www.madfishdigital.com/wp-plugins/">Mad Fish Digital plugin support page</a> to drop us a line or ask a question. Please note that responses to specific inquiries may take up to 24 hours.

#### Why would you want to remove a bulk amount of pages from search indexes? 

At Mad Fish Digital, we use tools like Screaming Frog, LinkResearch Tools, Ahrefs, and SEM Rush to crawl and analyze web pages. Sometimes, you want to remove multiple web pages from a search engine's index that contain no longer current content, old products and services, or outdated guidelines/regulations. In many of these cases, you need the pages to be temporarily dropped from google's index today, but may want to update the content at a later date. By noindexing a post or page, you can avoid having to set the status code of those pages to 404 (or 410). 

This is where having a tool to bulk noindex/nofollow these pages can become handy. You can easily remove pages from the search index, then remove the noindex directive once the content of those post or pages has been updated.

This plugin will sync pages with your existing Yoast and All In One SEO Pack (AIOSEO) settings, and allows you to do bulk noindexing with Yoast. Syncing of category noindex/nofollow settings with YOAST and All in One SEO Pack is not yet supported.

By keeping your pages in sync with the noindex/nofollow settings from Yoast and AIOSEO, you never have to worry about duplicating efforts, or worry which plugin is managing your robots directives.


#### Fallback Protection

If you are not using Yoast or AIOSEO, this plugin is able to continue to serve the appropriate meta robots tag based on the noindex/nofollow as per the settings through the interface. 

If you do disable the Yoast or AIOSEO plugin on your site, be sure to double check the "Bulk NoIndex/NoFollow" interface (from the tools menu) to confirm that your posts and pages are still nofollowed and noindexed accordingly. Robots directives that are set directly through the WP Post editing interface may not always be tracked by this plugin, and those settings may no longer be visible to this plugin if the Yoast and AIOSEO plugins were previously enabled but are later disabled.

== Screenshots ==

1. This is a screenshot of the interface for bulk noindexing and bulk nofollowing posts and pages

2. This is a walk through of the interface and how easy it is to bulk noindex and bulk nofollow posts and pages

== Installation & Usage ==
1) Login as an administrator to your WordPress Admin account. Using the “Add New” menu option under the "Plugins" section of the navigation, you can either search for: "Bulk NoIndex & NoFollow Tool" or if you’ve downloaded the plugin already, click the "Upload" link, find the .zip file you download and then click "Install Now". Or you can unzip and FTP upload the plugin to your plugins directory.

2) Navigate to the Tools -> Bulk NoIndex/NoFollow

3) Begin noindex/nofollowing pages

== Frequently Asked Questions ==

= Will this plugin play nice if I already use Yoast for noindexing and nofollowing pages? =

Yes, this plugin will sync with Yoast's native noidexing functions

== Upgrade Notice ==

= 2.16 =
We recommend upgrading to the latest version as it includes a patch to address a rare but potential Cross-Site scripting (XSS) vulnerability

= 2.15 =
We recommend upgrading to the latest version to access categories with no posts

= 2.10 =
We recommend upgrading to the latest version as it includes a patch to address the potential for an Cross-Site scripting (XSS) vulnerability reported by users

= 2.01 =
This latest version provides additional support for managing custom post types, categories and terms. This update also changes the way the robots meta tag is implemented to avoid potential duplication.

= 1.51 =
We recommend upgrading to the latest version as it includes additional security measures

= 1.5 =
We recommend upgrading to the latest version as it includes a patch to address the potential for an Cross-Site scripting (XSS) vulnerability

= 1.42 =
We recommend upgrading to the latest version as it includes a fix for a minor warning occuring when not using either All in One SEO, or Yoast

= 1.41 =
We recommend upgrading to the latest version as it includes a fix for a minor warning occuring in PHP 7.4

= 1.4 =
We recommend upgrading to the latest version as it includes some performance enhancements for the interface

= 1.3 =
We recommend upgrading to the latest version as it now includes support for AIOSEO

= 1.2 =
We recommend upgrading to the lastest version as the latest version corrects some PHP warnings that were appearing in php.log files

= 1.1 =
We recommend upgrading to the lastest version to avoid potential issues in the case where another plugin or theme prevents the WP 'is_plugin_active' function from properly loading

== Changelog ==

= 2.16 =
Release Date: September 24, 2024

Patched a potential vulnerability

= 2.15 =
Release Date: August 24th, 2024

Minor bug fixes for filtering on searched categories, and displaying categories that have no posts

= 2.10 =
Release Date: March 9th, 2024

Patched a potential vulnerability

= 2.01 =
Release Date: February 14th, 2024

* Added support for custom post types and categories

= 1.51 =
Release Date: September 11th, 2023

* Patch that implements additional security measures

= 1.5 =
Release Date: September 11th, 2023

* Patch that addresses a potential XSS vulnerability

= 1.42 =
Release Date: June 13th, 2023

* Small patch that addresses a minor bug for non-yoast and non-AIOSEO users 

= 1.41 =
Release Date: July 28th, 2022

* Small patch that addresses a minor warning in PHP 7.4

= 1.4 =
Release Date: July 27th, 2022

* Updated the queries for the interface to use less resources

= 1.3 =
Release Date: July 26th, 2022

* This release adds support for the All in One SEO Pack plugin

= 1.2 =
Release Date: January 26th, 2021

* This release fixes some small PHP warnings that were happening in some instances of php 7.4. 

= 1.1 =
Release Date: January 11th, 2021

* This release improves the error catching if the wordpress is_plugin_active() function had not loaded prior to this plugin loading. In some instances, a PHP error was thrown due to is_plugin_active function not being available. 
