<?php

/**
 * 🔗 Load AfribaPayModal SDK
 * 
 * Determine the absolute path to the AfribapayModal class by going up 4 directory levels
 * from this file and locating it under the `/checkout/src/` directory.
 */
$modalPath = dirname(__FILE__, 4) . '/checkout/src/AfribapayModal.php';

// ✅ Verify SDK file exists before including
if (file_exists($modalPath)) {
    require_once($modalPath); // Load SDK classes: AfribaPayModal and AfribaPayRequest
} else {
    die("The MODAL file was not found at the specified location: " . $modalPath);
}

// 🌍 AfribaPay currently supports two major CFA Franc zones across West and Central Africa:
// -------------------------------------------------------------------------------
// 💱 XOF – Franc CFA BCEAO (UEMOA - West Africa)
// Countries using XOF: 
//   🇧🇯 Benin (BJ), 🇧🇫 Burkina Faso (BF), 🇨🇮 Côte d'Ivoire (CI), 🇬🇼 Guinea-Bissau (GW), 
//   🇲🇱 Mali (ML), 🇳🇪 Niger (NE), 🇸🇳 Senegal (SN), 🇹🇬 Togo (TG)
//
// 💱 XAF – Franc CFA BEAC (CEMAC - Central Africa)
// Countries using XAF: 
//   🇨🇲 Cameroon (CM), 🇨🇫 Central African Republic (CF), 🇹🇩 Chad (TD), 🇨🇬 Congo-Brazzaville (CG), 
//   🇬🇶 Equatorial Guinea (GQ), 🇬🇦 Gabon (GA)
//
// 🔎 For an up-to-date list of supported countries, operators, and currencies,
// please visit the AfribaPay Developer Documentation: https://docs.afribapay.com

try {
    // 🧩 Instantiate the SDK modal class
    $AfribaPayButton = new AfribaPayModal();

    // 🧾 Create a new payment request object
    $request = new AfribaPayRequest();

    // 💰 Define transaction amount (in local currency)
    $request->amount = 500;

    // 💱 Specify currency: 'XOF' for West Africa (you can use 'XAF' for Central Africa)
    $request->currency = 'XOF';

    // 🌍 Optionally specify the country (e.g., Burkina Faso = 'BF')
    // $request->country = 'BF';

    // 🆔 Unique order ID (generated dynamically)
    $request->order_id = uniqid("test_ord_", true);

    // 🔖 Unique reference ID for merchant-side reconciliation
    $request->reference_id = uniqid("test_ref_", true);

    // 📬 URL to receive webhook callbacks (transaction status updates)
    $request->notify_url = "https://www.example.com/webhook/notify";

    // 🌐 Enable country selection dropdown in the modal interface
    $request->showCountries = true;

    // 🔘 Render the AfribaPay payment button with provided config
    echo $AfribaPayButton->createCheckoutButton(
        $request,
        'Pay Now 500 FCFA',
        '#2ECC71', // Button color (green)
        'small'    // Button size
    );

} catch (Exception $e) {
    // ⚠️ Display any error encountered during button generation
    echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
