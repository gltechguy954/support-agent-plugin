=== WP Ultimo: Support Agents ===
Contributors: aanduque
Requires at least: 5.1
Tested up to: 5.8.3
Requires PHP: 7.1.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and manage a special kind of WordPress user that has access to the Network Admin without being a super-admin (Support Agents), complete with granular permission controls.

== Description ==

WP Ultimo: Support Agents

Create and manage a special kind of WordPress user that has access to the Network Admin without being a super-admin (Support Agents), complete with granular permission controls.

== Installation ==

1. Upload 'wp-ultimo-support-agents' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the step by step Wizard to set the plugin up

== Changelog ==

Version 1.0.8 - Released on 2022-01-21

* Internal: Added hooks and filters generator;
* Internal: Added WP Ultimo stubs for developer quality of life;
* Added: Checkout Form capabilities;
* Fixed: Capability name mismatch between WP Ultimo forms and this add-on options;

Version 1.0.7 - Released on 2021-12-02

* Fixed: Delete Support Agents not working;
* Fixed: Bulk deleting Support Agents not working;

Version 1.0.6 - Released on 2021-11-22

* Fixed: Add new modal not working after performance optimizations on core;
* Fixed: Grid-item view present on core and not on the add-on;

Version 1.0.5 - Released on 2021-10-31

* Fixed: small incompatibilities with PHP 8;
* Improved: wp_ultimo_skip_network_active_check now uses wu_is_must_use();

Version 1.0.4 - 2021-09-24

* Added: filter wp_ultimo_skip_network_active_check for mu-plugins based setups;

Version 1.0.3 - Released on 2021-08-29

* Added: Support to REST API - support agents can be created via REST;

Version 1.0.2 - Released on 2021-07-26

* Fix: Updating agent permissions not working;

Version 1.0.1 - Released on 2021-05-28

* Added: Notice when user is already a super admin;
* Added: Permission to allow user switching;
* Improved: Better form fields;

Version 1.0.0 - Initial Release
