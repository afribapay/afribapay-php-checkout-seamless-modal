<?php

return [
    // 🔐 Public identifier for the API client.
    // Used to identify the client application making requests (non-sensitive).
    'apiUser' => 'pk_sandbox_6c47d814-8647-4201-a650-a2afff6247ae',

    // 🔒 Secret key for authenticating API requests.
    // Keep this key secure—never expose it on the frontend or in public repositories.
    'apiKey' => 'sk_sandbox_NRwBOQDCunv3C1W4PK9kfH04TI6738Q3gBx',

    // 🧾 Unique merchant identifier.
    // Associates API requests with a specific merchant account in the system.
    'merchantKey' => 'mk_sandbox_8ORoB612502220',

    // 🤝 Agent or partner identifier.
    // Used to track transactions, reporting, and agent-level access control.
    'agent_id' => 'APM31923613',

    // 🌍 Current API environment: 'production' for live usage, 'sandbox' for testing and development.
    // Ensure correct mode to avoid sending test data to the live system.
    'environment' => 'sandbox',

    // 🗣 Language preference for API responses and interface content.
    // Supported values typically include 'en' (English), 'fr' (French), etc.
    'lang' => 'en',

    // 📬 Notification URL (IPN endpoint).
    // The API will send transaction status updates via POST to this URL.
    // Must be a secure and publicly accessible endpoint on your server.
    'notify_url' => "https://www.example2.com/webhook/notify", // Replace with your actual webhook URL

    // 🧩 Relative filesystem path to the embedded checkout SDK modal.
    // This should point to the directory containing the seamless modal integration files,
    // including `checkout.php`, stylesheets, and scripts needed to launch the payment interface.
    //
    // ✅ This path is relative to your web application's document root (e.g., /var/www/html).
    // ✅ Ensure the folder and its contents are accessible by your application during runtime.
    // ❗ Do NOT expose sensitive logic within this directory—this should be frontend-only assets.
    //
    // Example:
    // If your application is hosted at https://yourdomain.com/ and the checkout modal lives in
    // /var/www/html/afribapay-php-checkout-seamless-modal/checkout/,
    // then set: 'checkoutPath' => '/afribapay-php-checkout-seamless-modal/checkout/',
    'checkoutPath' => '/afribapay-sdk/afribapay-php-checkout-seamless-modal/checkout/', // ✅ Update as per your deployment path

    // 🧠 Enable server-side caching of transaction/session data.
    // Recommended for production use; disables fallback to client-side cookies.
    'useCacheFolder' => true,

    // 📁 Directory for storing cached data (used only if 'useCacheFolder' is true).
    // Defaults to the system temp directory, but can be customized.
    'cacheDirectory' => sys_get_temp_dir(),
];
