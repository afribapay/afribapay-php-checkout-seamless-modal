<?php

/**
 * 🔗 Locate and load the AfribaPayModal SDK
 *
 * Go up 4 directory levels from the current file and locate the SDK in `/checkout/src/`.
 * This file must contain the `AfribaPayModal` and `AfribaPayRequest` classes.
 */
$modalPath = dirname(__FILE__, 4) . '/checkout/src/AfribapayModal.php';

// ✅ Check if SDK file exists before loading
if (file_exists($modalPath)) {
    require_once($modalPath);
} else {
    // ❌ Stop and notify developer if SDK file is missing
    die("The MODAL file was not found at the specified location : " . $modalPath);
}

/**
 * 🎯 Reusable function to create an AfribaPay checkout button
 *
 * @param int $amount          Amount in FCFA
 * @param string $currency     Currency code: 'XOF' (West Africa) or 'XAF' (Central Africa)
 * @param string $order_id     Unique merchant order ID
 * @param string $reference_id Transaction reference ID
 * @param string|null $country Optional country code (e.g., 'BF' for Burkina Faso)
 * @return string              HTML output of the payment button
 */
function createButton($amount, $currency, $order_id, $reference_id, $country = null) {
    $AfribaPayButton = new AfribaPayModal();
    $request = new AfribaPayRequest();

    // 🧾 Set the transaction details
    $request->amount = $amount;         // Amount to be paid in local currency
    $request->currency = $currency;     // 'XOF' or 'XAF'

    // 🌍 Optionally specify the country (e.g., 'BF' for Burkina Faso)
    if (!is_null($country)) {
        $request->country = $country;
    }

    // 🆔 Unique identifiers for tracking
    $request->order_id = $order_id;
    $request->reference_id = $reference_id;

    // 🌐 Show country selection in modal UI
    $request->showCountries = true;

    // 🔘 Return the styled checkout button
    return $AfribaPayButton->createCheckoutButton($request, '💳 Payer maintenant', '#2ECC71', 'large');
}

/**
 * 🌍 Supported Currency Zones
 *
 * 💱 XOF – Franc CFA BCEAO (West Africa)
 *   Countries: 🇧🇯 BJ, 🇧🇫 BF, 🇨🇮 CI, 🇬🇼 GW, 🇲🇱 ML, 🇳🇪 NE, 🇸🇳 SN, 🇹🇬 TG
 *
 * 💱 XAF – Franc CFA BEAC (Central Africa)
 *   Countries: 🇨🇲 CM, 🇨🇫 CF, 🇹🇩 TD, 🇨🇬 CG, 🇬🇶 GQ, 🇬🇦 GA
 *
 * 📘 Visit https://docs.afribapay.com for updated country/operator lists.
 */

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement AfribaPay</title>

    <!-- Google Font: Inter for modern UI -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>*{box-sizing:border-box}body{margin:0;font-family:'Inter',sans-serif;background-color:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh}.payment-card{background:#fff;border-radius:14px;box-shadow:0 12px 24px rgba(0,0,0,0.08);padding:32px;width:100%;max-width:400px}.payment-card h1{font-size:1.5rem;color:#111827;margin-bottom:12px}.payment-card p{font-size:14px;color:#4b5563;margin-bottom:24px}.button-wrapper{display:flex;justify-content:center}.footer{margin-top:24px;font-size:12px;color:#9ca3af}</style>
</head>
<body>

    <!-- 💳 Payment UI Card -->
    <div class="payment-card">

        <!-- Card Header -->
        <div class="card-header" style="text-align: center; padding: 20px;">
            <img src="https://cdn-icons-png.flaticon.com/512/4290/4290854.png" alt="Carte de crédit"
                 style="width: 80px; height: auto; margin-bottom: 10px;">
            <h1>Paiement sécurisé</h1>
            <p>Utilisez AfribaPay pour finaliser votre paiement</p>
        </div>

        <!-- Payment Amount -->
        <div class="amount-box" style="background-color: #f2f2f2; text-align: center; padding: 15px;
                                        font-size: 18px; font-weight: bold; color: #000;">
            Montant à payer : 10000 FCFA
        </div>

        <!-- Checkout Button -->
        <div class="button-wrapper" style="padding: 20px; background-color: #fff;">
            <?php
            try {
                // ✅ Generate and display AfribaPay checkout button using the reusable function
                echo createButton(
                    amount: 10000,                                // Amount in FCFA
                    currency: 'XOF',                              // Currency (XOF for West Africa)
                    country: 'BF',                                // Country code (e.g., Burkina Faso)
                    order_id: uniqid("func_ord_", true),         // Order ID
                    reference_id: uniqid("func_ref_", true)      // Reference ID
                );
            } catch (Exception $e) {
                // ❌ Handle and display SDK or integration errors
                echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>

        <!-- Footer Security Notice -->
        <div class="footer" style="background-color: #f9f9f9; padding: 10px; text-align: center;
                                   font-size: 13px; color: #666; border-top: 1px solid #eee;">
            <img src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="SSL"
                 style="width: 18px; vertical-align: middle; margin-right: 5px;">
            Paiement sécurisé via AfribaPay • Certificat SSL 256-bit
        </div>
    </div>
</body>
</html>
