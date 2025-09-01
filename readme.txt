Secure ShareLink

Contributors: irabbi360
Tags: share secure, link, file sharing, temporary access, password protection
Requires at least: 5.2
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Description

Secure ShareLink is a WordPress plugin that allows you to generate secure, time-limited sharing links for files, routes, or model data. It includes advanced security features such as password protection, IP filtering, burn-after-read, and usage limits, along with comprehensive auditing. Perfect for sharing sensitive content or providing temporary access to resources.

Key Features

🔗 Multiple Resource Types – Share files, URLs, or text/data seamlessly

⏰ Time-Limited Access – Set expiration dates and usage limits

🔒 Password Protection – Protect links with a secure password

🚫 IP Filtering – Restrict access to specific IP addresses

🔥 Burn After Reading – One-time access links that self-destruct

📊 Comprehensive Auditing – Track access patterns and usage

🎯 Flexible Delivery – Supports file downloads and URL redirection

💻 Admin UI – Create, manage, and monitor links from WordPress admin

🧪 Secure – Passwords are hashed, preventing exposure in the database

Installation

Upload the secure-sharelink folder to the /wp-content/plugins/ directory.

Activate the plugin through the “Plugins” menu in WordPress.

Go to Settings → Permalinks and click Save Changes to flush rewrite rules.

Access the admin UI from ShareLink in the WordPress admin menu.

Usage
Creating a Link

Go to ShareLink → Add New.

Choose the resource type: File, URL, or Text.

If selecting a file, use the Media Library picker.

Optionally configure:

Password protection

Expiration date/time

Maximum uses

IP whitelist

Burn after reading

Click Create Link. You will get a secure URL like:

http://yourdomain.com/shareurl?sharelink=82ddae9522c4dd0a40bcdfd036c5f56f

Accessing a Link

Visiting the link will:

Prompt for a password if set.

Enforce IP whitelist and max uses.

Expire after the specified date or after the configured number of uses.

Automatically delete if burn-after-reading is enabled.

Users can download files or view content directly depending on the resource type.

Frequently Asked Questions

Q: Can I use this to share any file from my WordPress Media Library?
A: Yes, simply pick the file when creating a link.

Q: Is the password stored securely?
A: Yes, passwords are hashed using WordPress’s secure password functions.

Q: Can I restrict access by IP?
A: Yes, you can whitelist specific IP addresses or ranges when creating a link.

Changelog
1.0.0

Initial release with full link management, password protection, expiration, IP filters, and burn-after-read.

Admin UI for creating and managing links.

Media Library integration for file selection.

Upgrade Notice

N/A — first release.

License

GPLv2 or later: https://www.gnu.org/licenses/gpl-2.0.html