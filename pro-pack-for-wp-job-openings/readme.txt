=== Pro Pack for WP Job Openings ===
Contributors: awsmin
Requires at least: 4.8
Tested up to: 6.6.2
Requires PHP: 5.6
Stable tag: 3.2.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Summary ==

Converts WP Job Openings to a powerful recruitment tool by adding some of the most sought features.

== Description ==

WP Job Openings PRO is an add-on plugin with a pack of features that makes WP Job Openings a powerful recruitment tool.

The plugin helps to reduce the time spent on administrative tasks while hiring, keep track of all applicants, resumes and notes. Engage your candidates more in the process by sending notifications such as welcome and rejection emails.

= PRO Features =

* Form Builder - Make your own application form
* Shortlist, Reject and Select Applicants
* Rate and Filter Applications
* Custom Email Notifications & Templates
* Email CC option for job submission notifications
* Notes and Activity Log
* Option to Filter and Export Applications
* Attach uploaded file with email notifications
* Shortcode generator for generating customised job lists
* Use third-party forms and custom application URLs

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the `Plugins` screen in WordPress

== Changelog ==

= V 3.4.0 =
* Added: Ability to move applications between job listings.
* Added: Save original file names for uploaded attachments.
* Minor bug fixes and code improvements.

= V 3.3.1 =
* Minor bug fixes and code improvements.

= V 3.3.0 =
* Added: Functionality to re-assign applications to an alternative status upon the deletion of their current status from the settings.
* Fixed: Corrected the parsing error that modified job titles containing hyphens in email subject lines and addressed the escape character issue causing formatting anomalies with apostrophes in descriptions.
* Minor bug fixes and code improvements

= V 3.2.8 =
* Fixed: Change applicant notification from email to WordPress default email.
* Minor bug fixes and code improvements.

= V 3.2.7 =
* Fixed: Unable to print or download applications when number fields are in the application form.
* Minor bug fixes and code improvements.

= V 3.2.6 =
* Minor bug fixes and code improvements.

= V 3.2.5 =
* Fixed: Files url issue in printed application PDF.
* Fixed: Star rating issue for the applications in safari browser.
* Update: Dropzone latest version.
* Minor bug fixes.

= V 3.2.4 =
* Fixed: HTML content issue in notification mails for some installations.
* Fixed: Custom key not working for application status settings.
* Fixed: Date and time issue for the application submission email.
* Improved: Notifications customizer settings to add background color for email.
* Minor bug fixes.

= V 3.2.3 =
* Fixed: Application status not getting updated when the status label is long.
* Improved: Application status settings.
* Minor bug fixes.

= V 3.2.2 =
* Fixed: License issue with duplicated or cloned websites (Update in Freemius SDK).
* Fixed: Form submission error and empty application issue when attachments failed to generate proper metadata.
* Fixed: Drag and Drop file uploading issues in Windows systems.
* Fixed: Uploading issue with documents exported with Google.
* Improved: Filled class support for the expired job listing.
* Dev: Added functions for better debugging.
* Minor bug fixes and code improvements.

= V 3.2.1 =
* Improved: Shortcode builder. Support for job status for job listing shortcode.
* Bug fixes and code improvements.

= V 3.2.0 =
* Added: Advanced settings to manage application status (Settings > Advanced) with the option to send notification automatically on status change.
* Added: HTML editor support for notifications.
* Added: Country-based input option for phone fields.
* Added: Filled admin filter and filled post state for jobs.
* Fixed: 'Disable Form' issue in application shortcode.
* Improved: Form builder error handling.
* Improved: Elementor popup support for the application form.
* Dev: Repeater field support (programmatically) for the application form.
* Dev: Hooks to customize the export page.
* Dev: Hooks to customize job display options.
* Other minor bug fixes and style improvements.

= V 3.1.1 =
* Fixed: Drag and drop file fields conflict with other plugins.
* Security fixes and code improvements.
* Improved: Custom Button (Job Display Options > Application Form) options.

= V 3.1.0 =
* Added: Restrict Duplicate Applications support (Settings > Form > General > Application form options).
* Added: Shortcodes for Jobs count and Job Specifications (Settings > Shortcodes).
* Added: Elementor popup support for form shortcode.
* Improved: Form builder error handling.
* Dev: Added hooks to customize administrative pages.
* Code improvements.
* Minor bug fixes.

= V 3.0.0 =
* Admin UI improvements.
* Added: Multiple Forms Support (Settings > Form > Form Builder).
* Added: Order by and Order support for job listing shortcode (Settings > Shortcodes).
* Added: Applications by Status Overview widget.
* Added: Month, Week, and Day-based filters for Applications Analytics widget.
* Added: Position Filled feature.
* Added: Options to change job detail strings (Settings > Appearance > Change Strings > Job Detail Strings).
* Added: Export Applications by Job Listing or Application Form.
* Added: Unicode character support for application print feature.
* Fixed: Conflict with Yoast SEO plugin, resulting in blank or duplicate applications.
* Fixed: Placeholder not working in the application form.
* Improved: Content handling based on user capabilities.
* Dev: Improved the hook to customize the print styles for applicant details.
* Code improvements.
* Other minor bug fixes.
