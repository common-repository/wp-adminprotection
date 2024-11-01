=== WP Adminprotection ===
Contributors: Horttcore
Donate link: http://horttcore.de/plugin/wp-adminprotection/
Tags: security, login, administration, ip block
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 2.0.2

The WP-Backend is secured by an IP Blocker.

== Description ==

The WP-Backend is secured by an IP Blocker. You can add as many IPs as you want, each one of them is allowed to log in. The rest have no access to the WP-Backend.

== Installation ==

Upload the plugin into the plugin folder and activate it in the WP-Backend.

Goto the Manage->Adminprotection panel and enter one or more IPs (note: 1 IP per line),
Bellow you can see if your current IP would be accepted or not.
Then activate the protection checkbox

For Backwards compability please deactivate and reactivate the Plugin!


== Frequently Asked Questions ==

Can I define IP ranges?
Not exactly but you can enter sth like '192.168.*.*' or '*.*.*.666' or '*' (free for all)
* is used as a Wildcard.

I entered the wrong IP adress and can't enter the backend anymore, what can I do?
You have no chance to log in again, so the best way to get back to it, is to rename the plugin. So the plugin is inactive and you can login again.

== Changelog ==

=== 2.0.2 ===
*   Added trim function to remove whitespaces - props Mateusz

=== 2.0.1 ===
*   Bugfix, there was an error that only the first IP was checked

=== 2.0 ===
*    Multilanguage support
*    Hookable with filters
*    Code cleanup

…