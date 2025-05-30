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
git clone https://github.com/your-org/afribapay-php-checkout-seamless-modal.git
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
    'apiUser'        => 'your-public-api-user-key',     // 🔐 Public identifier for the API client
    'apiKey'         => 'your-private-api-key',         // 🔒 Secret key for authenticating API requests
    'merchantKey'    => 'your-merchant-key',            // 🧾 Merchant account identifier
    'agent_id'       => 'your-agent-id',                // 🤝 Agent or partner ID
    'environment'    => 'production',                   // 🌍 'sandbox' or 'production'
    'lang'           => 'fr',                           // 🗣 Language preference ('en' or 'fr')
    'notify_url'     => 'https://yourdomain.com/webhook/notify', // 📬 IPN endpoint
    'checkoutPath'   => '/afribapay-php-checkout-seamless-modal/checkout/', // 🧩 Frontend path to the modal
    'useCacheFolder' => true,                           // 🧠 Enables server-side caching
    'cacheDirectory' => sys_get_temp_dir(),             // 📁 Cache storage directory
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

| Property         | Type    | Description                                        |
|------------------|---------|----------------------------------------------------|
| `amount`         | Float   | Amount to be paid                                 |
| `currency`       | String  | Currency code (e.g., `XOF`, `USD`, etc.)          |
| `order_id`       | String  | Unique ID for the order                           |
| `reference_id`   | String  | Unique reference ID used for merchant tracking    |
| `notify_url`     | String  | IPN URL where payment status updates are sent     |
| `lang`           | String  | Language for the modal content                    |
| `country`        | String  | Country code (e.g., `BF`)                         |
| `showCountries`  | Boolean | Whether to allow country selection in the modal   |

---

## 📚 Documentation & Support

- Full documentation: [https://docs.afribapay.com](https://docs.afribapay.com)
- Support: contact@afribapay.com

---

## 🔐 Security Best Practices

- Never expose your `apiKey` in frontend code or public repositories.
- Use HTTPS for all webhook endpoints.
- Always set `environment` to `sandbox` for testing and switch to `production` when live.

---

## 📝 License

MIT License. © AfribaPAY inc
