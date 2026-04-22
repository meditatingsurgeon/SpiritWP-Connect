=== SpiritWP Connect ===
Contributors: spiritualagency
Tags: clientexec, woocommerce, sso, billing, hosting
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later

Provides deep, bidirectional integration between an xCloud-managed WordPress/WooCommerce site and a Clientexec billing/hosting installation.

== Description ==

SpiritWP Connect bridges the gap between your frontend WordPress website and your backend Clientexec billing and support portal natively. 

Built specifically to xCloud and WordPress multisite standards, the plugin utilizes the Clientexec REST API to map users, products, and sessions—without requiring the two applications to be hosted on the same server.

Key features include:
*   **User Provisioning:** Hooks into WordPress user registration and WooCommerce checkout flows to instantly create parallel client accounts within Clientexec.
*   **Purchasing & Provisioning:** Connects WooCommerce orders directly to Clientexec package structures natively. Marks orders and attaches Clientexec metadata seamlessly in your WP dashboard.
*   **AutoLogin SSO:** Secure, token-based Single Sign-On (SHA-1 HMAC). Logged-in WordPress users can jump straight to their Clientexec invoices, tickets, and packages securely without a secondary login prompt.
*   **Customer Dashboard:** Native shortcodes (`[spwp_dashboard]`, `[spwp_tickets]`, `[spwp_kb]`) bring a "lightweight client area" directly to your frontend WordPress theme, adhering strictly to the Vivid Frequency design system.
*   **Plan Sync Engine:** A secure mapping interface linking your frontend WooCommerce Product IDs directly to Clientexec Package IDs, equipped with native WP-Cron sync capabilities.

== Installation ==

1. Download the `spiritwp-connect` plugin folder as a `.zip` file.
2. Go to your WordPress Admin Dashboard -> **Plugins** -> **Add New Plugin**.
3. Upload the `.zip` file and click **Install Now**.
4. Click **Activate Plugin**. 
   *(Note: The plugin includes a built-in PSR-4 fallback autoloader, so you do NOT need to run `composer install` prior to uploading.)*

== Configuration Guide ==

**1. Generate your Clientexec API Key**
*   Log into your Clientexec Admin area.
*   Navigate to **Settings > Security > Application Key**.
*   Generate an application key and copy it to your clipboard.

**2. Configure the Plugin in WordPress**
*   Navigate to **SpiritWP > Settings** in your WordPress dashboard.
*   Enter your **Clientexec Base URL** (e.g., `https://my.spiritvm.net`). Ensure you do not add a trailing slash (though the plugin will sanitize it).
*   Paste your **Application Key**.
*   Select the modules you wish to enable (e.g., User Provisioning, SSO, Dashboard).
*   Save your settings.

**3. Configure Single Sign-On / SSO**
*   Add a link to your navigation menu pointing to `https://yoursite.com/ce-login/`.
*   You can route users deep into Clientexec by appending a destination param:
    - `/ce-login/?goto=dashboard`
    - `/ce-login/?goto=invoices`
    - `/ce-login/?goto=tickets`
    - `/ce-login/?goto=packages`

== WooCommerce Product Syncing ==

If you want front-end sales to provision Clientexec packages automatically:
1. Ensure the **Plan Sync Engine** and **WooCommerce Purchase Handler** modules are enabled in Settings.
2. Go to **SpiritWP > Plan Sync**.
3. Locate the corresponding Product ID from Clientexec (visible in CE under Settings > Products/Addons).
4. Select the matching WooCommerce product from the dropdown.
5. Save the mapping. When a user buys this product and the order hits the "Completed" status in WooCommerce, SpiritWP will use the REST API to fire `addpackage` in your CE environment automatically.

== Shortcodes Reference ==

Place these anywhere on your frontend pages (works perfectly with Gutenberg or Page Builders):

*   `[spwp_dashboard]` - Renders the top-level client area showing Recent Invoices and Open Tickets. Must be logged in.
*   `[spwp_tickets]` - Displays a full tabular interface of the user's support tickets.
*   `[spwp_kb tag="server"]` - Renders knowledge base articles. Can accept an optional `tag` parameter to filter specific CE article tags.

== Troubleshooting & Quality Control ==

**1. "Unable to locate your billing account" on SSO login:**
Verify that the current logged-in WordPress user has been properly mapped to Clientexec. You can check this by editing the User Profile in WordPress Admin; there should be a "Clientexec User ID" field populated.

**2. Provisioning isn't triggering on checkout:**
Verify that the WooCommerce order status has successfully changed to **Completed**. Provisioning skips "Pending" or "Processing" orders by default to ensure payment is captured.

**3. API Log Analyzer:**
The plugin heavily integrates with your WordPress database. If something fails, go to **SpiritWP > Settings** and enable **Log all API requests to database**. You can then inspect the `wp_spwp_api_log` SQL table to see precisely what HTTP status and JSON response Clientexec returned.

**4. 429 Too Many Requests (Rate Limiting):**
The plugin features an automated throttling capability. If Clientexec issues a 429 status code, the plugin will read the `Retry-After` header and gently pause execution (capped at 5 seconds) before automatically retrying the request seamlessly in the background up to 3 times to ensure stability.

== Changelog ==

= 1.0.0 =
* Initial Premium Release.
* Added fallback autoloader for standalone installation.
* Integrated strict Vivid Frequency frontend layout styling.
* Hardened timeout caps for API 429 rate limit adherence.
