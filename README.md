# passmanage
Pasword Manage Open Source
Features
Responsive Material Design
Enjoy an attractive and usable interface based on Google's material design standards. Compatible with smartphones, tablets, and desktop.

Secure user passwords
User accounts created to access PassHub have their passwords securely hashed

Secure login storage
Logins stored inside PassHub have all fields securely encrypted using a unique key

Custom Permissions NEW
Create and assign groups to users, each with custom access permissions to pages and categories.

Private "My Logins" Category
Each user gets their own private "My Logins" category which is not viewable by other users.

Categories
Create categories and assign logins to them

Live Search
Find the right login quickly by keyword, category, or both

Copy to clipboard
On flash-enabled browsers, simply click a field to copy its value to clipboard

Multiple field types
Store any text content in a login by creating fields with either text, textarea, or password field types

CSV Export NEW
Export all login data as a backup copy

Updater NEW
Upgrade existing 1.0.X installations to 1.1.0

Specifications
Built with the Fat Free Framework
Object-oriented code
Protected against XSS and CSRF attacks
Meets PSR-1 Basic Coding Standard and PSR-2 Coding Style Guide to ensure a high level of technical interoperability between shared PHP code.
The Fat Free Framework meets PSR-4 autoloader requirements. Autoloading is tied to folder structure, and namespaces must be defined in the class file. See classes in app/models/PassHub as examples.
Web Hosting Requirements
In order for PassHub to work correctly, your web hosting must meet these requirements:

Apache web server
PHP version 5.4 or greater—check your web hosting control panel for a PHP version switcher if it doesn't meet this requirement, or contact your web host to upgrade
MySQL database support
Also, I highly recommend you install a SSL certificate and access PassHub only through https:// instead of http://. Otherwise, your login info could be sniffed out by a malicious third party... eek! SSL encrypts information sent to and from your web hosting, so your data is protected from third parties. Contact your web host about installing one.

Installation Guide
Tip: If you want to upgrade an existing installation of PassHub, see "Upgrading using the automated PassHub Updater" below.
Just follow these three steps to get up and running, quick.

Upload the contents of Web Files/ to your web host. I suggest uploading to a subfolder like "logins"
Create a new MySQL database on your web hosting
Start the web-based installer by browsing to the folder you installed PassHub in, and put "/install" on the end. For example, if your domain is mydomain.com and you installed PassHub in a "logins" subfolder, you would go to mydomain.com/logins/install
Installation complete!

Re-installing
Warning! This process will permanently delete existing data.
If you want to re-run the installer, follow these steps. Note that each time you run the installer, it saves a new encryption key. So if you want to import logins created with another key, it won't work—but if you're starting from nothing, it's ok.

Drop all the tables in the MySQL database
Edit app/config.ini, changing the ENABLEINSTALLER setting to true
Go to the install page in your web browser
Upgrading using the automated PassHub Updater
To upgrade an existing installation of PassHub from 1.0.X to 1.1.0 (latest), follow these steps:

Upload the "updater" folder (located in your downloaded PassHub files) to your webhosting in the same directory that PassHub is installed
Visit the folder in your web browser. For example, if your PassHub URL is mydomain.com/logins, you would go to mydomain.com/logins/updater
Follow the instructions on the updater page
If the updater does not work for you, please contact me using the contact form button at the top of this page. Note that attempting a manual upgrade is not recommended due to changes in the database schema in 1.1.0.

User Guide
lock Logins
All users have access to this page.

Individual login access is determined by what permissions the user's group has. Permissions are edited on the Groups page. The exception to this is logins assigned to a user's "My Logins" category - which the user always has full access to.

To copy a field to clipboard on flash-enabled devices, simply click on its value. On mobile devices, use the "Select" or "Show" button then copy the text.

Add
Use the floating action button (located at the bottom right of the screen on large displays, top left on smaller ones).

Edit
Use the context menu (three dots stacked vertically) and select Edit.

To add fields to the login, click the "+" button next to an existing field.

To remove fields from the login, click the "-" button next to an existing field.

To edit the login name, select it and type.

You can edit field labels by selecting them and typing. To switch the field type, select one of the options right below the field (text, password, textarea). To re-order a field, drag its handle on the far right.

Once you're done editing, click "Save".

Delete
Use the context menu (three dots stacked vertically) and select Delete.

label Categories
Permission to this area can be assigned on the Groups page.

Note that each user has a private "My Logins" category, and private categories can not be edited or deleted using the Categories page. However, if a user is deleted, their private category will be removed. More details in the "Users" section below.

Add
To add a category, click the "+" button next to an existing category.

Edit
To edit a category's name, select the name and type.

To re-order a category, drag its handle on the far right.

Once you're done editing, click "Save".

Delete
To remove a category, click the "-" button next to the category you wish to delete, then click "Save".

perm_identity Users
Permission to this area can be assigned on the Groups page.

Important: if a user is deleted, their private category will be removed, and all logins in their category will be released into the General category, visible to all users. To put it another way, none of their logins will be deleted, they will simply be moved.

Note that when you update a user's info, for example their password you will have to notify them - this is not done by PassHub.

Add
Use the floating action button (located at the bottom right of the screen on large displays, top left on smaller ones).

Edit
Use the context menu (three dots stacked vertically) and select Edit.

To edit the user's name, select it and type.

To edit a field value, select it and type.

The password will only be updated if a new value is entered.

Once you're done editing, click "Save".

Delete
Use the context menu (three dots stacked vertically) and select Delete.

supervisor_account Groups
Permission to this area can be assigned on the Groups page.

The Groups page contains a list of each group, and their permissions. Permissions are divided into Pages and Categories.

Page permissions control what users in the group can do on that page:

Read – view the page and its data
Create – create new items
Edit – edit existing items
Delete – delete existing items
Category permissions control what users in the group can do with logins related to the category. For example, if a group had Read permission to the "General" category, they could only view logins in that category, but not create, edit, or delete logins in that category.

Add
To add a group, click the "+" button next to an existing group.

Edit
To edit a group's name, select the name and type.

To edit the group's permissions, click the gear icon next to the group. Then use the checkboxes to toggle specific permissions.

Once you're done editing, click "Save".

Delete
To remove a group, click the "-" button next to the group you wish to delete, then click "Save".

build Tools
Permission to this area can be assigned on the Groups page.

The tools page currently contains a CSV export tool. You can download a full backup of all logins. The login fields will be exported with generic column headers, since logins can have different field names in various order. Field data will be exported in plain text - so make sure to keep any exported CSV files in a secure location!

account_circle Account Menu
This menu is available to all users.

Get Support
Opens a dialogue describing your support options.
Edit Account
Allows the user to edit their own account name, email, and password. They can also delete their account.
Sign Out
Ends the current user's session.
Design Customization
Changing the Branding
The navbar color can be changed at app/views/base.html.php line 37. Change the nav class from light-blue to a different color of your choosing. Check out all the presets you can choose from on this reference page!

If you want to replace the "PassHub" logo with your own (I won't be offended, don't worry), open app/views/base.html.php and change line 38, right inside the anchor tag with an ID of logo-container. You can edit the authentication screen logo in app/views/auth.html.php, line 15.

Editing the CSS
The main stylesheet is located at assets/css/style.css. Use this stylesheet to override and add upon styles from the Materialize Framework located at assets/css/materialize.css. By keeping changes in style.css, future updates will be much easier to perform.

Change Log
All notable changes to this project will be documented in this file. This project adheres to Semantic Versioning.

[1.1.0] - 2016-12-22
Highlights include a new groups feature to assign permissions to pages and login categories, an updater tool, and a CSV export tool.

Added
- Groups feature. Assign users to a group which allows you to specify page and category permissions.
- Add underscore JS library
Changed
- Refactored JavaScript. New functions.js file holds utility functions.
- Improve formatting on installer complete page
- New ACL structure to allow granular read, edit, update, delete permissions for pages and categories
- JS: Update Velocity and Velocity UI to 1.3.2
- Now uses JS for copy to clipboard - works on modern browsers including Microsoft Edge, Chrome, Safari, and Firefox
Fixed
- MySQL table names are all lowercase to avoid conflict with case-insensitive MySQL configurations.
- The installer now auto-dects MySQL utf8mb4 support and falls back to utf8 for MySQL versions less than 5.5.3.
- Fix action buttons overlapping textarea
- Installer - check if database is empty, fix errors not showing correctly
- Fix ampersands and other special characters being encoded as entities during installation
- JS: Remove redundant modal init calls
- Fix baseUrl and CSRF not defined when on auth pages
[1.0.1] - 2015-11-25
Added
- Favicon/app shortcut icon
Fixed
- Strings in config.ini are no longer split into arrays when commas are part of the string. Previously generated 500 internal server error after installation.
[1.0.0] - 2015-10-22
Initial release

Important Notes
Preventing Data Loss
I recommend backing up the PassHub application files and associated MySQL database regularly to prevent data loss.

The MySQL database stores encrypted field values for each login, and the config.ini file stores the encryption key, so you will need to back up both the PassHub application files and database to restore or transfer the installation.

Credits
Thank you to the open-source community, your contributions have made this project possible.

Materialize—A modern responsive front-end framework based on Material Design
Fat-Free Framework–A powerful yet easy-to-use PHP micro-framework designed to help you build dynamic and robust web applications - fast!
Burgers–contains classes for users and ACL for the Fat-Free Framework
php-passgen–A MIT-licensed library for generating cryptographically secure passwords in PHP
php-encryption–a class for doing symmetric encryption in PHP
jQuery–a fast, small, and feature-rich JavaScript library
Jen–a portable and safe Javascript password/number generator
Sortable–the JavaScript library for modern browsers and touch devices
Velocity.js–Accelerated JavaScript animation
ZeroClipboard–The ZeroClipboard library provides an easy way to copy text to the clipboard using an invisible Adobe Flash movie and a JavaScript interface
PHP Coding Standards Fixer–The PSR-1 and PSR-2 Coding Standards fixer for your code
