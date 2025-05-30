<?php

// 📍 Define the path to the AfribaPay SDK modal class by going up 4 levels
$modalPath = dirname(__FILE__, 4) . '/checkout/src/AfribapayModal.php';

// ✅ Load the SDK if it exists, or throw a fatal error
if (file_exists($modalPath)) {
    require_once($modalPath);
} else {
    // ❌ Stop execution if SDK file is missing
    die("Le fichier MODAL est introuvable : " . $modalPath);
}

// 🍪 Retrieve the user's cart stored in a cookie named 'cart'
$cartJson = $_COOKIE['cart'] ?? null;

// 🧾 Initialize an empty array to store the cart data
$cartArray = [];

// ✅ Decode and parse the cart JSON if available
if ($cartJson) {
    $cartArray = json_decode(urldecode($cartJson), true);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement AfribaPay</title>

    <!-- Load Google Fonts for a modern interface -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Basic layout and styling for the payment interface -->
    <style>body{margin:0;font-family:'Inter',sans-serif;background-color:#f0f2f5;display:flex;justify-content:center;align-items:start;padding:50px 20px;min-height:100vh}.payment-card{background:#fff;border-radius:14px;box-shadow:0 12px 24px rgba(0,0,0,0.08);padding:32px;max-width:500px;width:100%}.card-header{text-align:center}.card-header img{width:80px;margin-bottom:10px}.cart-summary{margin:20px 0;border-top:1px solid #ccc;padding-top:15px}table{width:100%;border-collapse:collapse;font-size:15px}th,td{padding:8px 6px;text-align:left}th{background-color:#f9f9f9;color:#333}tr:nth-child(even){background-color:#f7f7f7}.total-row{font-weight:bold;border-top:2px solid #333}.amount-box{text-align:center;padding:15px;background-color:#f2f2f2;font-size:18px;font-weight:bold}.button-wrapper{padding:20px;display:flex;justify-content:center}.footer{margin-top:20px;text-align:center;font-size:13px;color:#666}</style>
</head>
<body>

<!-- 💳 Payment container -->
<div class="payment-card">

    <!-- 🧾 Header with payment intro -->
    <div class="card-header">
        <img src="https://cdn-icons-png.flaticon.com/512/4290/4290854.png" alt="Paiement">
        <h1>Paiement sécurisé</h1>
        <p>Utilisez AfribaPay pour finaliser votre paiement</p>
    </div>

    <!-- 🛒 Display cart summary if available -->
    <?php if (!empty($cartArray)): ?>
        <div class="cart-summary">
            <h4>Détail de votre commande</h4>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>PU</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;

                    // 🧮 Loop through each cart item and calculate subtotal
                    foreach ($cartArray as $item) {
                        $name = htmlspecialchars($item['name'] ?? 'Produit');
                        $qty = intval($item['quantity'] ?? 1);
                        $price = floatval($item['price'] ?? 0);
                        $subtotal = $qty * $price;
                        $total += $subtotal;

                        // 🖨 Display product row
                        echo "<tr>
                                <td>{$name}</td>
                                <td>{$qty}</td>
                                <td>" . number_format($price, 0, ',', ' ') . " FCFA</td>
                                <td>" . number_format($subtotal, 0, ',', ' ') . " FCFA</td>
                            </tr>";
                    }
                    ?>
                    <!-- 🧾 Final total row -->
                    <tr class="total-row">
                        <td colspan="3">Total à payer</td>
                        <td><?= number_format($total, 0, ',', ' ') ?> FCFA</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- ⚠ Fallback if cart is empty -->
        <p>Votre panier est vide.</p>
    <?php endif; ?>

    <!-- 💰 Display final amount to pay -->
    <div class="amount-box">
        Montant à payer : <?= isset($total) ? number_format($total, 0, ',', ' ') : '0' ?> FCFA
    </div>

    <!-- 🟢 Payment button rendered via AfribaPay SDK -->
    <div class="button-wrapper">
        <?php
        try {
            $AfribaPayButton = new AfribaPayModal();
            $request = new AfribaPayRequest();

            // 💵 Configure payment request
            $request->amount = $total ?? 0;
            $request->currency = 'XOF'; // XOF = Franc CFA BCEAO (West Africa)
            // $request->country = 'BF'; // Optional: Specify country (e.g., Burkina Faso)
            $request->reference_id = uniqid("paiement_ref_", true);
            $request->order_id = uniqid("paiement_ord_", true);

            // 🚀 Render the modal payment button
            echo $AfribaPayButton->createCheckoutButton($request, '💳 Payer maintenant', '#202942', 'large');
        } catch (Exception $e) {
            // ❌ Display errors in red
            echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>

    <!-- 🔙 Link to continue shopping -->
    <p class="text-center mt-4">
        <a href="javascript:void(0);" onclick="goBackOrHome();" class="text-indigo-600 hover:text-indigo-800">
            Continuez vos achats
        </a>
    </p>

    <!-- 🛡 Security footer -->
    <div class="footer">
        <img src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="SSL" style="width: 18px; vertical-align: middle; margin-right: 5px;">
        Paiement sécurisé via AfribaPay • Certificat SSL 256-bit
    </div>
</div>

<!-- 🔁 Back navigation logic -->
<script>
    function goBackOrHome() {
        if (window.history.length > 1) {
            window.history.back(); // Go back if history exists
        } else {
            window.location.href = 'index.html'; // Otherwise, redirect to homepage
        }
    }
</script>

</body>
</html>

