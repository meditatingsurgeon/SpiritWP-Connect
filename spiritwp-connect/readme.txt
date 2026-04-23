=== SpiritWP Connect ===
Contributors: spiritualagency
Tags: clientexec, woocommerce, sso, billing, hosting
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.1.0
License: GPLv2 or later

Provides deep, bidirectional integration between an xCloud-managed WordPress site and a Clientexec billing/hosting installation.

== Description ==

SpiritWP Connect bridges the gap between your frontend WordPress website and your backend Clientexec billing and support portal natively. 

Built specifically to xCloud and WordPress multisite standards, the plugin utilizes the Clientexec REST API to map users, products, and sessions—without requiring the two applications to be hosted on the same server.

Key features include:
*   **User Provisioning:** Hooks into WordPress user registration and optional WooCommerce flows to instantly create parallel client accounts within Clientexec.
*   **AutoLogin SSO:** Secure, token-based Single Sign-On (SHA-1 HMAC). Logged-in WordPress users can jump straight to their Clientexec invoices, tickets, and packages securely without a secondary login prompt.
*   **Customer Dashboard:** Native shortcodes (`[spwp_dashboard]`, `[spwp_tickets]`, `[spwp_kb]`) bring a "lightweight client area" directly to your frontend WordPress theme, adhering strictly to the Vivid Frequency design system.
*   **Plan Sync Engine:** A secure mapping interface linking your frontend products directly to Clientexec Package IDs, equipped with native WP-Cron sync capabilities.

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

== Shortcodes Reference ==

Place these anywhere on your frontend pages (works perfectly with Gutenberg or Page Builders):

*   `[spwp_dashboard]` - Renders the top-level client area showing Recent Invoices and Open Tickets. Must be logged in.
*   `[spwp_tickets]` - Displays a full tabular interface of the user's support tickets.
*   `[spwp_kb tag="server"]` - Renders knowledge base articles. Can accept an optional `tag` parameter to filter specific CE article tags.

== Changelog ==

= 1.1.0 =
* FIXED: CE REST API URL construction (missing /api/ prefix).
* FIXED: Missing get_packages() method caused dashboard fatal error.
* FIXED: Users registered before plugin activation weren't linked to CE.
* FIXED: Plugin failed on sites without WooCommerce.
* FIXED: Missing is_wp_error() guards caused PHP warnings under API failure.
* FIXED: 429 rate-limit responses now retried up to 3 times with backoff.
* FIXED: Purchase handler idempotency prevents double-provisioning.
* ADDED: Spirit Webinars packages separated in dashboard.
* ADDED: Transient caching layer for GET requests (5min default TTL).
* ADDED: PSR-4 fallback autoloader for composerless installs.
* ADDED: Comprehensive uninstall cleanup (tables, options, transients).

= 1.0.0 =
* Initial Release.
