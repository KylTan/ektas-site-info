# ektas-site-info Plugin
***
This is the plugin made by ET and AS that displays site health information that can be found in the admin tools section. You may also opt to send this data in JSON Format to an endpoint via a cURL request. Below are the version release details.

## Description

The Site Info plugin adds a "Site Info" menu item to the WordPress admin menu. When clicked, it displays various details about the site, including WordPress version, server information, active theme, installed plugins, and more.

You can also send this info to a compatible endpoint, as long as you have the proper credentials, and the proper permissions for the target site.

Enjoy 🤠

## Installation

1. Download the plugin ZIP file.
2. Extract the contents of the ZIP file to the `wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the WordPress admin dashboard.

## Usage

1. Once activated, the "Site Info" menu item will appear in the WordPress admin menu.
2. Click on the "Site Info" menu item to view the site details.
3. Type in the target endpoint, target site username, and a working application password (also in target site)
4. Click send and the page should notify you if you if it was succesful.

## Update Notes

### 0.5.7
* fixed an error that would show when activating the plugin on the plugin page
* cleaned up unused commented code
* converted some lines that echoed html code into straight html code.

### 0.5.6 
* small bug removing a line of debug code

### 0.5.5
* Fixed mising and incorrect values within the Site Info JSON to be more accurate to original Site Health.

### 0.5.4
* restored copy button functionality

### 0.5.3
* changed code so that the plugin section of the JSON reflects true slugnames so plugin 26 is compatible

### 0.5.2
* Improved page design
  * Fixed Spacing and Alignment to be consistent
  * Added titles and text for context
  * Resized some elements
  * Collapsible button for the http response

### 0.5.1
* Fixed the formatting of JSON to be compatible with the task 26 plugin
* Post title now reflects the name of the site it came from

### 0.5.0
* JSON now displays fully on newly created post
* Issue where sending to your own site causes formatting errors on JSON

### 0.4.0
* displays info but only works when sending to the site's own acf field

### 0.3.2
* fixed an issue where posting after providing proper info on all fields would yield a 401 error (not enough permissions)
* Error was caused by a lack of cURL authentication code on a certain line
  
### 0.3.1 alpha
* Added text field for login credentials and authentication
* You can now send a request by using your username and application password, which creates a new acf post of the post type, but doesn't quite include the info yet.
* `endpoint should be something like http://t-12-site.local/wp-json/wp/v2/<acf post>/`

### 0.3.0 alpha
* Added text field for sending a curl post request
* Sends back a response, but without the credentials for authentication it says you can't post

### 0.2.0: 
* View is now in JSON format
* Button added for faster copy-pasting

### 0.1.0: 
* Functionality to view site info from the admin page's sidebar 
* Includes most info with some limits (imagick, gd_formats)
