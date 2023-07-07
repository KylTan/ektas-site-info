# ektas-site-plugin
***
This is the plugin made by ET and AS that displays site health information that can be found in the admin tools section. Below are the version release details.
# Site Info Plugin

The Site Info plugin displays site details in the WordPress admin page.

## Description

The Site Info plugin adds a "Site Info" menu item to the WordPress admin menu. When clicked, it displays various details about the site, including WordPress version, server information, active theme, installed plugins, and more.

## Installation

1. Download the plugin ZIP file.
2. Extract the contents of the ZIP file to the `wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the WordPress admin dashboard.

## Usage

1. Once activated, the "Site Info" menu item will appear in the WordPress admin menu.
2. Click on the "Site Info" menu item to view the site details.

## Update Notes

### 0.1.0: 
* Functionality to view site info from the admin page's sidebar 
* Includes most info with some limits (imagick, gd_formats)

### 0.2.0: 
* View is now in JSON format
* Button added for faster copy-pasting

### 0.3.0 alpha
* Added text field for sending a curl post request
* Sends back a response, but without the credentials for authentication it says you can't post
