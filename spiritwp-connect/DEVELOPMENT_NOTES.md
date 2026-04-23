# Development Notes: SpiritWP Connect

## Architecture Overview
This plugin connects WordPress to Clientexec via the Clientexec REST API.

### Extension Points & Hooks
The plugin exposes several hooks for developers wishing to extend its functionality:

**Actions:**
*   `spwp_wc_product_updated` (args: `$wc_product_id, $ce_product_id`): Fires when a mapped WC product is updated. Ideal for adding custom syncing logic (e.g., syncing prices).

**Note on AutoLogin:**
AutoLogin hash generation relies on the Application Key from CE. Ensure your CE instance server time and WP server time are in sync (NTP) to prevent token expiry issues, as the token is valid for a maximum of 15 minutes from creation.

### Clientexec API Limitations
*   **Product Listing:** Clientexec API does not currently expose a bulk `getproducts` list endpoint. Therefore, mapping must be done by manually entering the CE Product ID into the Sync Page UI, rather than a dropdown.
*   **Package Retrieval:** The Dashboard module simulates fetching packages via an assumed API structure. If CE implements a `getpackages` endpoint, update `Dashboard.php` to fetch them.

### Security
*   The Application key is stored in `wp_options` safely.
*   Database calls use `$wpdb->prepare` strictly.
*   The API client handles the `429 Too Many Requests` HTTP status gracefully applying a sleep based on the `Retry-After` header.
