=== DanP Bitly URLs ===
Contributors: danpoulton
Donate link: https://dan-p.net/donate
Tags: short URLs, Bitly, short links
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 1.0.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync pageview data from Google Analytics to your WordPress Database, enabling you to sort posts, view pageview data in the WordPress Dashboard, and output pageviews to your visitors.

== Description ==

Export the Google Analytics pageviews for all your pages and posts, and copy the data into your WordPress database.

See your pageviews in the WordPress Admin screen next to your pages and posts!

You can then order your pages and posts by pageviews, using the "danp-dot-net-ga-page-views" meta key.

Plus, you can output the number of pageviews to your visitors, by using the shortcode like this:

* Automatically detect the ID of the page you're using the shortcode on: \[danp-ga-pageviews\]
* Set the page/post ID and output that anywhere you like \[danp-ga-pageviews id=999\]

[Getting Started Guide](https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync)

== Frequently Asked Questions ==

= How do I get a Google Analytics account? =

You can easily set up a Google Analytics account with any Google Account. See this [support article](https://support.google.com/analytics/answer/1008015?hl=en) from Google.

= How do I get a Google API token? =

You will need a JSON API Token from [Google Cloud Platform](https://console.cloud.google.com/apis/). See the [quick start guide](https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync).

= How do I grant access to my Google Analytics profile? =

You need to grant your Google Cloud Platform Service Account with permissions in Google Analytics. See the [quick start guide](https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync).

= How do I get a Google Analytics "Profile ID"? =

Login to Google Analytics and click Admin. See the [quick start guide](https://dan-p.net/wordpress-plugins/danp-google-analytics-pageview-sync) for more information.

= Where do I enter my API token? =

A new page is created in the WordPress Dashboard called 'Google Analytics Pageview Sync'.

= How do I change plugin settings? =

A new page is created in the WordPress Dashboard called 'Google Analytics Pageview Sync'. You can enter your Profile ID and upload your JSON API Key there.

= How do I start syncing data? =

A new page is created in the WordPress Dashboard called 'Google Analytics Pageview Sync'. You can start a bulk update there and update the settings to automatically sync pageviews either daily or weekly.


== Screenshots ==

1. This is where you can add your Bitly API token and generate short URLs for all posts and pages. Location: Dashboard > Settings > Reading.

== Changelog ==

= 1.0 =
* This is the first version!

== Upgrade Notice ==

= 1.0 =
* This is the first version!
