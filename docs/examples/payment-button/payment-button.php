<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement AfribaPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .payment-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            padding: 32px;
            width: 100%;
            max-width: 400px;
        }
        .payment-card h1 {
            font-size: 1.5rem;
            color: #111827;
            margin-bottom: 12px;
        }
        .payment-card p {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .button-wrapper {
            display: flex;
            justify-content: center;
        }
        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    
    <div class="payment-card" style="max-width: 400px; margin: 40px auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden; font-family: Arial, sans-serif;">

        <div class="card-header" style="text-align: center; padding: 20px; background-color: #fff;">
            <img src="https://cdn-icons-png.flaticon.com/512/4290/4290854.png" alt="Carte de crédit" style="width: 80px; height: auto; margin-bottom: 10px;">
            <h1 style="margin: 10px 0; font-size: 24px; color: #333;">Paiement sécurisé</h1>
            <p style="margin: 5px 0; font-size: 16px; color: #666;">Utilisez AfribaPay pour finaliser votre paiement</p>
        </div>
        <div class="amount-box" style="background-color: #f2f2f2; text-align: center; padding: 15px; font-size: 18px; font-weight: bold; color: #000;">
            Montant à payer : 5000 FCFA
        </div>

        <div class="button-wrapper" style="padding: 20px; background-color: #fff;"> 
        
            <?php
            
            /**
             * 🔗 Load AfribaPayModal SDK
             * 
             * Locate the AfribapayModal SDK PHP class located 4 levels up relative to this file,
             * under the `/checkout/src/` directory.
             */
            $modalPath = dirname(__FILE__, 4) . '/checkout/src/AfribapayModal.php';
            
            // ✅ Check if SDK file exists before including
            if (file_exists($modalPath)) {
                require_once($modalPath); // Load SDK: AfribaPayModal & AfribaPayRequest classes
            } else {
                // ❌ Stop execution with clear error if the SDK is not found
                die("The MODAL file was not found at the specified location: " . $modalPath);
            }
            
            /**
             * 🌍 AfribaPay supports two major CFA Franc currency zones:
             * 
             * 💱 XOF – Franc CFA BCEAO (West Africa - UEMOA)
             *   Countries using XOF:
             *     🇧🇯 Benin (BJ)
             *     🇧🇫 Burkina Faso (BF)
             *     🇨🇮 Côte d'Ivoire (CI)
             *     🇬🇼 Guinea-Bissau (GW)
             *     🇲🇱 Mali (ML)
             *     🇳🇪 Niger (NE)
             *     🇸🇳 Senegal (SN)
             *     🇹🇬 Togo (TG)
             * 
             * 💱 XAF – Franc CFA BEAC (Central Africa - CEMAC)
             *   Countries using XAF:
             *     🇨🇲 Cameroon (CM)
             *     🇨🇫 Central African Republic (CF)
             *     🇹🇩 Chad (TD)
             *     🇨🇬 Congo-Brazzaville (CG)
             *     🇬🇶 Equatorial Guinea (GQ)
             *     🇬🇦 Gabon (GA)
             * 
             * 📘 For full details, visit the AfribaPay Developer Documentation: https://docs.afribapay.com
             */
            
            try {
                // 🎛️ Create an instance of the SDK modal class
                $AfribaPayButton = new AfribaPayModal();
            
                // 🧾 Create a payment request object and configure it
                $request = new AfribaPayRequest();
            
                // 💰 Amount to charge the customer (in FCFA)
                $request->amount = 5000;
            
                // 💱 Currency: Use 'XOF' for West Africa or 'XAF' for Central Africa
                $request->currency = 'XOF';
            
                // 🆔 Unique order ID for internal system tracking
                $request->order_id = uniqid("sing_ord_", true);
            
                // 🔖 Reference ID used by the merchant for reconciliation/logging
                $request->reference_id = 'Test-Ref';
            
                // 🌐 Language setting for the modal (e.g., 'en' or 'fr')
                $request->lang = 'en';
            
                // 🌍 Optionally specify the customer's country (commented out here)
                // $request->country = 'BF'; // Burkina Faso
            
                // 🔄 Optionally disable country selector in the modal (default is true)
                // $request->showCountries = false;
            
                // 📬 Webhook URL to receive payment status updates (IPN-style)
                $request->notify_url = "https://api.afribapay.com/ajax.php?view=afribapay&r_payment=1&webhook=1";
            
                // 🧩 Render the payment button
                echo $AfribaPayButton->createCheckoutButton(
                    $request,
                    '💳 Payer maintenant', // Button label
                    '#2ECC71',             // Button color (green)
                    'large'                // Button size
                );
            
            } catch (Exception $e) {
                // ⚠️ Catch and display any exceptions encountered during setup
                echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            ?>
            
        </div>
        <div class="footer" style="background-color: #f9f9f9; padding: 10px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #eee;">
            <img src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="Certificat SSL" style="width: 18px; vertical-align: middle; margin-right: 5px;">
            Paiement sécurisé via AfribaPay • Certificat SSL 256-bit
        </div>
    </div>
</body>
</html>
