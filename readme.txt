=== Chamber Dashboard Member Manager ===
Contributors: gwendydd, jpkay, cguntur, LisaK.social
Tags: Chamber of Commerce, business directory, businesses, membership, membership fees
Donate link: http://chamberdashboard.com/donate
Requires at least: 4.2
Tested up to: 4.7.5
Stable tag: 2.1.9
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Manage the membership levels and payments for your chamber of commerce or other membership-based organization.

== Description ==
Chamber Dashboard Member Manager is a part of the Chamber Dashboard collection of plugins and themes designed to meet the needs of chambers of commerce.

= With Chamber Dashboard Member Manager, you can: =
*   create different membership levels 
*   describe the membership levels' prices and perks
*   let organizations sign up for membership on your website
*   collect payment with PayPal
*   track when membership payments are due

The Chamber Dashboard Member Manager requires that you have the [Chamber Dashboard Business Directory](https://wordpress.org/plugins/chamber-dashboard-business-directory/) installed.
You can learn more at [chamberdashboard.com](http://chamberdashboard.com)

= Basic Usage =

Go to Businesses->Membership Levels to enter in your membership levels, perks, and prices.
To display the membership levels and perks on your site, use the shortcode [membership_levels]
Enter your PayPal email address on the Member Manager settings page.
Use the [membership_form] shortcode to display a form that will let people sign up and pay for membership.

For full instructions about how to use the plugin, go to [Chamber Dashboard Documentation](https://chamberdashboard.com/document/install-and-setup-member-manager/)

If you want to track the people associated with businesses in your organization, check out [the Chamber Dashboard CRM](https://wordpress.org/plugins/chamber-dashboard-crm/) plugin!

To display an event calendar, you can use the [the Chamber Dashboard Events Calendar(https://wordpress.org/plugins/chamber-dashboard-events-calendar/) plugin!

More features coming soon!

== Installation ==

= Using The WordPress Dashboard =
1. Navigate to the \'Add New\' in the plugins dashboard
2. Search for \'chamber dashboard member manager\'
3. Click \'Install Now\'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =
1. Navigate to the \'Add New\' in the plugins dashboard
2. Navigate to the \'Upload\' area
3. Select `chamber-dashboard-member-manager.zip` from your computer
4. Click \'Install Now\'
5. Activate the plugin in the Plugin dashboard

= Using FTP =
1. Download `chamber-dashboard-member-manager.zip`
2. Extract the `chamber-dashboard-member-manager` directory to your computer
3. Upload the `chamber-dashboard-business-directory` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= What payment methods are accepted? =
For now, PayPal is the only accepted payment method.  Soon, extensions will be available for other payment gateways.

== Screenshots ==
1. The membership form
2. Public invoice view
3. Entering an invoice in the dashboard
4. Payment report

== Changelog ==

= 2.1.9 =
* Some minor changes done to fix the error messages

= 2.1.8 =
* The next and previous invoice links on the single invoice have been removed
* The titles in the recent post widget on the single invoice are now showing up correctly

= 2.1.7 =
* Fixed the invoice total calculation issue

= 2.1.6 =
* Removed Freemius integration

= 2.1.5 =
* Fixed the plugin to work with WordPress 4.6

= 2.1.4 =
* Fixed the issue of taxes not being added to PayPal correctly

= 2.1.3 =
* Fixed the recurring invoices being sent out with out taxes

= 2.1.2 =
* add option to hide lapsed members from the site
* fixed bug where membership price would double if donation amount was altered

= 2.1.1 =
* fixed email subject
* premium version compatibility
* fixed bug that caused membership cost to be doubled if taxes are turned off

= 2.1 =
* improved PayPal error reporting
* added optional tax field to membership form
* Freemius integration

= 2.0.5 =
* fixed issue with membership form and recurring payments

= 2.0.4 =
* improved security
* processing fee now shows up on invoice
* donation does not show up on invoice if there is no donation

= 2.0.3 =
* fixed bug that caused some invoices to be marked as "unpaid" when they have been paid

= 2.0.2 =
* fixed bug that caused invoice notification history to sometimes have the wrong date

= 2.0.1 =
* fixed bug that caused some invoices to have the same URL

= 2.0 =
* added an option to remove the donation field from the membership form
* added an optional processing fee to the membership form
* changed H2s to H1s to comply with new accessibility recommendations
* added membership level to recent payment report

= 1.9.0 =
* stop escaping quotes on invoice notifications sent through the invoice page sidebar
* billing email field can now accept comma-separated list of emails
* receipt reply-to email from settings page is included in list of cc emails for invoice notification

= 1.8.9 =
* make sure only invoices are not indexed by robots

= 1.8.8 =
* prevent email "from" field from cutting off last character

= 1.8.7 =
* more HTML tags allowed in editor fields on options page
* make HTML emails retain line breaks

= 1.8.6 =
* made payment report recognize draft businesses

= 1.8.5 = 
* updates to improve compatibility with recurring payments

= 1.8.4 =
* remove invoice archive page

= 1.8.3 =
* don't allow negative donations
* HTML emails

= 1.8.2 =
* British and Canadian English translations added
* option to accept only checks, not PayPal

= 1.8.1 =
* changes to language files to facilitate translation

= 1.8 =
* fixed PHP error in notification meta box
* revamped settings page to use settings API better
* added option to automatically lapse membership when membership dues are not paid
* automatically mark invoices as "unpaid" after 4 months
* other changes to work with new recurring payments plugin
* added due date column to invoices overview page
* made columns on invoices overview page sortable
* further fixes to PayPal IPN that should prevent multiple notifications from being sent
* improvements to Payment Report
* fixed bug that caused invoice number to increment if you use Quick Edit
* fixed bug that caused paid invoices to be marked as overdue
* added ability to download CSV of recent payments
* invoices default to invoice status "open" if no other invoice status is selected

= 1.7 =

* fixed bug where incorrect business would get inserted into membership form
* fixed bug so that invoices connected to draft businesses displayed properly
* added optional parameter to membership form to limit membership level

= 1.6 =
* added option to membership form to pay with check instead of PayPal
* added "print" button to single invoice view that prints just the invoice

= 1.5.3 =
* improved membership form validation

= 1.5.2 =
* fixed bug where invoices couldn't calculate total if numbers had commas

= 1.5.1 = 
* improved compatibility

= 1.5 =
* added payment button to invoice view
* added ability to add as many items as needed to invoices
* improved validation on the membership form
* improved error-checking on PayPal IPN
* ensure no duplicate invoice numbers

= 1.4 =
* Increased compatibility with Chamber Dashboard Business Directory

= 1.3 =
* Added "orderby" and "exclude" parameters to membership_levels shortcode

= 1.2 =
* Invoices are automatically marked as overdue

= 1.1.1 =
* Stopped the plugin from generating fatal errors if Business Directory isn't installed

= 1.1 = 
* Added "email invoice" button to add/edit invoice page

= 1.0 =
* First release