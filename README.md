# Afribapay PHP Checkout Seamless Modal

Afribapay's PHP Checkout Seamless Modal enables developers and merchants to integrate a simple and secure payment button directly into their websites. This SDK is designed for ease of use, offering a lightweight embedded modal that supports payments in multiple currencies and African countries.

## 🌍 Overview

This SDK provides:
- An embeddable modal payment button
- Support for multiple African currencies and countries
- Easy integration via PHP
- Secure backend configuration

For full API documentation, refer to: [docs.afribapay.com](https://docs.afribapay.com)

---

## 🛠️ Installation

1. **Clone the repository**  
```bash
git clone https://github.com/afribapay/afribapay-php-checkout-seamless-modal.git
```

2. **Include the SDK in your project**  
Make sure the following file path is valid:
```php
require_once __DIR__ . '/src/AfribapayModal.php';
```

3. **Create your `customer_config.inc.php` file**  
This config file contains your API credentials and settings:

```php
return [

    // 🔐 Public identifier for your API integration.
    // Used to identify your application when initiating transactions.
    'apiUser' => 'your-public-api-user-key',

    // 🔒 Private secret key for authenticating API requests.
    // This key must remain confidential—never expose it in client-side code.
    'apiKey' => 'your-private-api-key',

    // 🧾 Unique identifier for your merchant account.
    // Associates all transactions with your AfribaPay merchant profile.
    'merchantKey' => 'your-merchant-key',

    // 🤝 Identifier for the agent or partner handling transactions.
    // Useful for affiliate tracking and account-level segmentation.
    'agent_id' => 'your-agent-id',

    // 🌍 Environment mode for the API.
    // Use 'sandbox' for testing and 'production' for live transactions.
    'environment' => 'production',

    // 🗣 Language for the checkout modal and system messages.
    // Supported values: 'fr' for French, 'en' for English.
    'lang' => 'fr',

    // 📬 URL that AfribaPay will use to send payment status notifications (IPN).
    // This should be a public endpoint on your server that can handle POST requests.
    'notify_url' => 'https://yourdomain.com/webhook/notify',

    // 🧩 Path to the embedded checkout modal.
    // This must match the accessible URL path to the modal on your web server.
    // Example: if your files are hosted at example.com/checkout/, set this to '/checkout/'
    'checkoutPath' => '/checkout/',

    // 🧠 Enable or disable server-side caching of session/payment data.
    // Recommended: true (fallbacks to cookies if false).
    'useCacheFolder' => true,

    // 📁 Filesystem path where cache files will be stored (used only if 'useCacheFolder' is true).
    // Defaults to the system temp directory, but can be customized if needed.
    'cacheDirectory' => sys_get_temp_dir(),

];

```

---

## 🚀 Quick Usage

```php
require_once __DIR__ . '/src/AfribapayModal.php';

$AfribaPayButton = new AfribapayModal($config);
$request = new AfribapayRequest();

$request->amount        = 10000;
$request->currency      = 'XOF';
$request->order_id      = uniqid('ord_', true);
$request->reference_id  = uniqid('ref_', true);
$request->notify_url    = $config['notify_url'];
$request->lang          = $config['lang'];
$request->showCountries = true;

echo $AfribaPayButton->createCheckoutButton($request, '💳 Payer maintenant', '#2ECC71', 'large');
```

---

## ✅ Supported Currencies

| Currency Code | Description                     |
|---------------|---------------------------------|
| **XOF**       | West African CFA Franc         |
| **XAF**       | Central African CFA Franc      |
| **CDF**       | Congolese Franc                |
| **GNF**       | Guinean Franc                  |
| **KES**       | Kenyan Shilling                |
| **MWK**       | Malawian Kwacha                |
| **RWF**       | Rwandan Franc                  |
| **SLE**       | Sierra Leonean Leone           |
| **UGX**       | Ugandan Shilling               |
| **ZMW**       | Zambian Kwacha                 |

---

## 🌐 Supported Countries

| Country Code | Currency | Country Name         |
|--------------|----------|----------------------|
| **BJ**       | XOF      | Benin                |
| **BF**       | XOF      | Burkina Faso         |
| **CI**       | XOF      | Côte d’Ivoire       |
| **GW**       | XOF      | Guinea-Bissau        |
| **ML**       | XOF      | Mali                 |
| **NE**       | XOF      | Niger                |
| **SN**       | XOF      | Senegal              |
| **TG**       | XOF      | Togo                 |
| **CM**       | XAF      | Cameroon             |
| **CF**       | XAF      | Central African Rep. |
| **TD**       | XAF      | Chad                 |
| **CG**       | XAF      | Republic of Congo    |
| **GQ**       | XAF      | Equatorial Guinea    |
| **GA**       | XAF      | Gabon                |
| **CD**       | CDF      | DR Congo             |
| **GN**       | GNF      | Guinea               |
| **KE**       | KES      | Kenya                |
| **MW**       | MWK      | Malawi               |
| **RW**       | RWF      | Rwanda               |
| **SL**       | SLE      | Sierra Leone         |
| **UG**       | UGX      | Uganda               |
| **ZM**       | ZMW      | Zambia               |

---

## 📦 AfribapayRequest Object


| Property        | Type    | Required   | Description                                                                       |
| --------------- | ------- | ---------- | --------------------------------------------------------------------------------- |
| `amount`        | float   | ✅ Yes      | Amount to be paid by the user. Must be greater than zero.                         |
| `currency`      | string  | ✅ Yes      | 3-letter ISO currency code (e.g., `XOF`, `USD`, `GNF`).                           |
| `country`       | string  | ✅ Yes      | ISO 2-letter country code (e.g., `BF` for Burkina Faso).                          |
| `notify_url`    | string  | ✅ Yes      | Fully-qualified URL where AfribaPay will send payment status notifications (IPN). |
| `order_id`      | string  | ❌ Optional | Unique ID for the order. If not set, it will be auto-generated.                   |
| `reference_id`  | string  | ❌ Optional | Reference string for merchant-side tracking and reconciliation.                   |
| `lang`          | string  | ❌ Optional | Language code for the modal (`en`, `fr`, etc.). Defaults to `en`.                 |
| `showCountries` | boolean | ❌ Optional | Whether to allow the user to select a country in the modal. Defaults to `true`.   |

---

## 📚 Documentation & Support

- Full documentation: [https://docs.afribapay.com](https://docs.afribapay.com)
- Support: support@afribapay.com

---

## 🔐 Security Best Practices

- Never expose your `apiKey` in frontend code or public repositories.
- Use HTTPS for all webhook endpoints.
- Always set `environment` to `sandbox` for testing and switch to `production` when live.

---

## 📝 License

MIT License. © AfribaPAY inc
