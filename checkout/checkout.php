<?php

require 'src/AfribapayConfig.inc.php';
require 'src/AfribapayClass.php';

$AfribapayClass = new AfribapayClass();
$paymentCode = null;
$params   = $AfribapayClass->formParameters;
$formInfo = $AfribapayClass->formLangData;

$cf_country  = (isset($params['country']) && ($params['country'])) ? $params['country'] : null;
$cf_currency = (isset($params['currency']) && ($params['currency'])) ? $params['currency'] : null;
$cf_showCountries = (isset($params['showCountries']) && ($params['showCountries'])) ? $params['showCountries'] : null;
$hide_with_d_none = null;
$checkoutPath = $params['checkoutPath'] ?? '/';

?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>body{background-color:#f8f9fa}.progress-container{display:flex;align-items:center;gap:10px;margin-bottom:30px}.step{flex:0 0 auto;text-align:center}.step-number{width:40px;height:40px;line-height:40px;font-size:16px;font-weight:bold;display:inline-block;text-align:center;border-radius:50%}.line{height:4px;margin:0 10px}.primary-button{background-color:#198754;color:white;border:none;padding:12px;border-radius:6px;font-size:16px;transition:background-color 0.3s ease}.primary-button:hover{background-color:#157347}.secondary-button{color:#6c757d;text-decoration:none;font-size:14px}.totals .total{font-size:18px;font-weight:bold}.summary-items .item-icon{font-size:2rem;margin-right:15px}.selection,.select2-selection{border:0!important}</style>
</head>
<body>
    
    <div class="container">
        <!-- Header -->
        <header class="py-1">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <a href="#" class="text-decoration-none text-dark mb-3 mb-md-0 d-flex align-items-center"></a>
            <!-- Progress Tracker -->
            <div class="d-flex flex-column flex-sm-row align-items-center">
                <div class="d-flex align-items-center mb-2 mb-sm-0">
                <span class="badge bg-primary text-white rounded-circle me-2" id='badgeStep1'>1</span>
                <span class="text-muted small"><?= $formInfo['PAYMENT'] ?></span>
                </div>
                <div class="line bg-secondary mx-2 d-none d-md-block" style="width: 50px; height: 2px;"></div>
                <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark rounded-circle me-2" id='badgeStep2'>2</span>
                <span class="text-muted small"><?= $formInfo['CONFIRMATION'] ?></span>
                </div>
            </div>
            <a href="#" class="text-decoration-none text-dark mb-3 mb-md-0 d-flex align-items-center"></a>
            </div>
        </div>
        </header>
        <!-- Alert Boxes -->
        <div class="container mt-4">
            <?php
            if( isset($data['error'])){
                $hide_with_d_none = ' d-none ';
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> 
                    '.$data['error'].'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
            ?>
            <div id="statusMessage" class="alert alert-success alert-dismissible fade show" role="alert" style="display:none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <!-- Main Content -->
        <div class="row <?php echo $hide_with_d_none;?> g-4">
        <!-- Left Section: Payment Details -->
        <div class="col-lg-12">
            <div class="p-4 border rounded bg-white">
            
            <form id="afribapayPaymentForm" method="POST" onsubmit="return validateAfribaPayForm()" novalidate>
                <div class="text-muted mb-3" style="font-size:0.7em"><?= $formInfo['SIMPLE_SECURE_PAYMENT']?></div>
                <div class="mb-3">
                    <label for="afribapayCountry" class="form-label"><?= $formInfo['COUNTRY'] ?></label>
                    <select id="afribapayCountry" name="country" class="form-select" required>
                        <option value=""><?= $formInfo['COUNTRY_FIRST_CHOICE'] ?></option>
                    </select>
                </div>
                <div class="mb-3" id="afribapayCurrencyBlock" style="display: none;">
                    <label for="afribapayCurrency" class="form-label"><?= $formInfo['CURRENCY'] ?></label>
                    <select id="afribapayCurrency" name="currency" class="form-select" required>
                        <option value=""><?= $formInfo['CURRENCY_FIRST_CHOICE'] ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="afribapayOperator" class="form-label"><?= $formInfo['OPERATOR'] ?></label>
                    <select id="afribapayOperator" name="operator" class="form-select" required>
                        <option value=""><?= $formInfo['OPERATOR_FIRST_CHOICE'] ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="afribapayPhone" class="form-label"><?= $formInfo['PHONE'] ?></label>
                    <div class="input-group">
                        <span class="input-group-text" id="countryCode"></span>
                        <input type="tel" id="afribapayPhone" name="phone" class="form-control" placeholder="1234567890" required>
                    </div>
                </div>
                <?php if (isset($params['amount']) && $params['amount']) { ?>
                    <input type="hidden" id="afribapayAmount" name="amount" value="<?= $params['amount'] ?>" required>
                <?php } else { ?>
                    <div class="mb-3">
                        <label for="afribapayAmount" class="form-label"><?= $formInfo['AMOUNT'] ?></label>
                        <input type="number" id="afribapayAmount" name="amount" placeholder="<?= $formInfo['AMOUNT'] ?>" class="form-control" min="1" required>
                    </div>
                <?php } ?>
                <div class="mb-3" id="afribapayOtp" style="display: none;">
                    <label for="afribapayOtp_code" class="form-label"><?= $formInfo['OTP_CODE'] ?></label>
                    <input type="text" id="afribapayOtp_code" name="otp_code" placeholder="<?= $formInfo['OTP_CODE'] ?>" class="form-control">
                    <div id="afribapayOtpMessage" class="alert alert-danger mt-2"></div>
                </div>
                <div class="mt-4 mb-3">
                <?php
                    $buttonText = (isset($params['amount'], $params['currency']) 
                        && !empty($params['amount']) && !empty($params['currency']) && $params['amount'] > 0)
                        ? $formInfo['BUTTON'] . ' ' . number_format($params['amount'], 0, ',', '.') .' '. $AfribapayClass->usualCurrency($params['currency'])
                        : $formInfo['BUTTON'];
                    ?>
                    <!-- <button type="submit" class="btn primary-button btn-success w-100" type="button" id="afribapaySubmitPayment" onclick="AfribaPayCheckout()">< ?= $buttonText ?></button> -->
                    <button type="submit" class="btn primary-button btn-success w-100" type="button" id="afribapaySubmitPayment"><?= $buttonText ?></button>
                </div>
            </form> 
            </div>
        </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        "use strict";
        let ussdText = null;
        (function(global) {
            const AfribapayClass = {
                afribapayFormParam: {},
                afribapayLang: {},
                getCheckout: async function(a) {
                    document.getElementById("afribapayPhone"),
                    document.getElementById("afribapayOtp_code"),
                    document.getElementById("afribapayOtpMessage"),
                    document.getElementById("statusMessage");
                    alertMessage(AfribapayClass.afribapayLang.PENDING_MESSAGE, "info"),
                    replaceFormWithLoadingSpinner();
                    try {
                        const e = await fetch("<?php echo $checkoutPath?>src/AfribapayClass.php?pay=<?php echo $paymentCode?>", {
                            method: "POST",
                            headers: {"Content-Type": "application/json"},
                            body: JSON.stringify({action: "checkout", ...a})
                        }),
                        t = await e.json();
                        waitResponse(t)
                    } catch(a) {
                        replaceFormWithFailledIcon("Error during payment initialization")
                    }
                },
                retryTransaction: async function(a) {
                    alertMessage(AfribapayClass.afribapayLang.RETRY_CHECK_MESSAGE, "info"),
                    replaceFormWithLoadingSpinner(),
                    checkTransactionStatus(a)
                }
            };

            let waveWindowOpened = false;

            async function waitResponse(e) {
                if ((e = e.response ?? e).error)
                    e.message ? (alertMessage(e.message, "warning"), replaceFormWithFailledIcon(e.message)) : (alertMessage(e.error.reason, "warning"), "FAILED" == e.error.status ? replaceFormWithFailledIcon(e.error.reason) : replaceFormWithFailledIcon(AfribapayClass.afribapayLang.FAILLED_MESSAGE)), switchBadge("danger");
                else if (e.data)
                    if ("PENDING" === (e = e.data).status) {
                        if (replaceFormWithLoadingSpinner(), alertMessage(AfribapayClass.afribapayLang.PENDING_MESSAGE, "info"), e?.provider_link && !waveWindowOpened) {
                            const a = e.provider_link;
                            openWaveFront(a), waveWindowOpened = !0
                        }
                        checkTransactionStatus(e.order_id)
                    } else "SUCCESS" === e.status ? (replaceFormWithSuccessIcon(), addTransactionInfo(e), switchBadge("success")) : (replaceFormWithFailledIcon(e.reason), addTransactionInfo(e), switchBadge("danger"));
                else replaceFormWithFailledIcon(AfribapayClass.afribapayLang.FAILLED_OPERATOR), switchBadge("danger")
            }

            async function checkTransactionStatus(e) {
                let r = 0;
                const t = setInterval((async () => {
                    try {
                        const a = await fetch("<?php echo $checkoutPath?>src/AfribapayClass.php?pay=<?php echo $paymentCode?>", {
                            method: "POST",
                            headers: {"Content-Type": "application/json"},
                            body: JSON.stringify({action: "status", order_id: e})
                        });
                        if (!a.ok) throw new Error("Error checking payment status");
                        const n = await a.json();
                        n.response.error ? (clearInterval(t), replaceFormWithFailledIcon(n.response.error.reason), switchBadge("danger")) : n.response.data && ("PENDING" !== n.response.data.status ? (waitResponse(n), clearInterval(t)) : (r++, r >= 3 && (replaceFormWithRetryButton(e), addTransactionInfo(n.response.data), clearInterval(t), switchBadge("warning"))))
                    } catch (e) {
                    }
                }), 1e4)
            }

            function replaceFormWithRetryButton(a) {
                alertMessage();
                document.getElementById("afribapayPaymentForm").innerHTML = `
                    <div class="text-center">
                        <div class="alert alert-warning alert-dismissible fade show afp-retrying-status" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ${AfribapayClass.afribapayLang.CKECK_BUTTON_MESSAGE}
                        <div id="transactionInfoContainer"></div>
                        </div>                  
                        <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-exclamation-triangle text-warning" viewBox="0 0 16 16">
                            <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                            <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                        </svg>
                        </div>
                        <div>
                            <button type="button" onclick="AfribapayClass.retryTransaction('${a}')" class="btn btn-primary">
                                ${AfribapayClass.afribapayLang.CKECK_BUTTON}
                            </button>
                        </div>
                        <!-- div class="mt-3 text-center">
                            <a href="javascript:void(0)" onclick="location.reload()" class="btn btn-secondary reload-btn"><i class="bi bi-arrow-return-left"></i> ${AfribapayClass.afribapayLang.RETURN_BUTTON}</a>
                        </div -->
                    </div>
                `
            }

            function replaceFormWithSuccessIcon() {
                alertMessage();
                document.getElementById("afribapayPaymentForm").innerHTML = `
                    <div class="text-center">
                        <div class="alert alert-success alert-dismissible fade show afp-success-status" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        ${AfribapayClass.afribapayLang.SUCCESS_MESSAGE}
                        <div id="transactionInfoContainer"></div>
                        </div>
                        <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-check2-circle text-success" viewBox="0 0 16 16">
                            <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                            <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
                        </svg>
                        </div>
                        <!-- div class="mt-3 text-center">
                        <a href="javascript:void(0)" onclick="location.reload()" class="btn btn-secondary reload-btn"><i class="bi bi-arrow-return-left"></i> ${AfribapayClass.afribapayLang.RETURN_BUTTON}</a>
                        </div -->
                    </div>
                `
            }

            function replaceFormWithFailledIcon(a = null) { 
            alertMessage(); 
            const n = document.getElementById("afribapayPaymentForm"); 
            a = a ?? AfribapayClass.afribapayLang.FAILLED_MESSAGE;
            
            n.innerHTML = `<div class="text-center"> 
                <div class="alert alert-danger alert-dismissible fade show afp-failed-status" role="alert" style="padding: 20px; border-radius: 8px; border-left: 5px solid #dc3545; background-color: #fff0f0;"> 
                <h4 style="font-size: 1.4rem; color: #dc3545; font-weight: bold; letter-spacing: 0.5px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; margin-bottom: 15px; text-align: center;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>${a}</strong>
                </h4>
                <div id="transactionInfoContainer" style="font-size: 0.75rem; color: #aaa; margin-top: 20px; font-style: italic; opacity: 0.6; border-top: 1px dotted #ddd; padding-top: 10px; text-align: center;"></div>
                </div> 
                <div> 
                <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-x-circle text-danger" viewBox="0 0 16 16"> 
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> 
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/> 
                </svg> 
                </div> 
                <!-- div class="mt-3 text-center"> 
                <a href="javascript:void(0)" onclick="location.reload()" class="btn btn-secondary reload-btn"><i class="bi bi-arrow-return-left"></i> ${AfribapayClass.afribapayLang.RETURN_BUTTON}</a> 
                </div --> 
            </div>`;
            }

            function replaceFormWithLoadingSpinner() {
                document.getElementById("afribapayPaymentForm").innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-info" style="width: 10rem; height: 10rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        ${ussdText ? `<div class="mt-3 fw-bold text-danger">${AfribapayClass.afribapayLang.OTP_CONFIRM_MESSAGE} <b>${ussdText}</b></div>` : ""}
                    </div>
                `
            }

            async function initPaymentForm() {
                ussdText = null;
                $('#afribapayCurrencyBlock').hide();
                try {
                    const CountriesData = <?= json_encode($AfribapayClass->fetchCountries($cf_country, $cf_currency, $cf_showCountries)) ?>;
                    if (!CountriesData || CountriesData.error) {
                        replaceFormWithFailledIcon(CountriesData?.message || "Failed to load countries data");
                        return;
                    }
                    const countryList = [];
                    for (const code in CountriesData) {
                        if (code !== 'error' && CountriesData.hasOwnProperty(code)) {
                            const country = CountriesData[code];
                            if (country && country.country_name) {
                                countryList.push({
                                    code: code,
                                    name: country.country_name,
                                    prefix: country.prefix || '',
                                    currencies: country.currencies || {},
                                    flag: `flag-icon flag-icon-${code.toLowerCase()}`
                                });
                            }
                        }
                    }
                    const $countrySelect = $("#afribapayCountry");
                    const $currencySelect = $("#afribapayCurrency");
                    const $operatorSelect = $("#afribapayOperator");
                    const $otpField = $("#afribapayOtp");
                    const $submitButton = $("#afribapaySubmitPayment");
                    if ($countrySelect.hasClass("select2-hidden-accessible")) {
                        $countrySelect.select2('destroy');
                    }
                    $countrySelect.empty();
                    $countrySelect.append(`<option value="">${AfribapayClass.afribapayLang.COUNTRY_FIRST_CHOICE}</option>`);
                    countryList.forEach(country => {
                        const $option = $(`<option value="${country.code}" data-flag="${country.code.toLowerCase()}">${country.name}</option>`);
                        $countrySelect.append($option);
                    });
                    $currencySelect.empty().append(`<option value="">${AfribapayClass.afribapayLang.CURRENCY_FIRST_CHOICE}</option>`);
                    $operatorSelect.empty().append(`<option value="">${AfribapayClass.afribapayLang.OPERATOR_FIRST_CHOICE}</option>`);
                    $countrySelect.select2({
                        placeholder: AfribapayClass.afribapayLang.COUNTRY_FIRST_CHOICE,
                        width: '100%',
                        dropdownParent: $countrySelect.parent(),
                        templateResult: formatCountryOption,
                        templateSelection: formatCountryOption
                    });
                    function formatCountryOption(country) {
                        if (!country.id) return country.text;
                        const flagCode = $(country.element).data('flag');
                        return $(`<span><i class="flag-icon flag-icon-${flagCode}"></i>   ${country.text}</span>`);
                    }
                    setTimeout(() => {
                        $countrySelect.next(".select2").addClass("form-select");
                    }, 100);
                    $countrySelect.on("change", function() {
                        const selectedCountryCode = $(this).val();
                        $currencySelect.empty().append(`<option value="">${AfribapayClass.afribapayLang.CURRENCY_FIRST_CHOICE}</option>`);
                        $operatorSelect.empty().append(`<option value="">${AfribapayClass.afribapayLang.OPERATOR_FIRST_CHOICE}</option>`);
                        $otpField.hide();
                        $("#afribapayOtp_code").removeAttr("required").val("");
                        $("#afribapayOtpMessage").hide();
                        $("#afribapayCurrencyBlock").show();
                        if (selectedCountryCode && CountriesData[selectedCountryCode]) {
                            const countryData = CountriesData[selectedCountryCode];
                            if (countryData.prefix) {
                                updatePhoneField(countryData.prefix);
                            }
                            if (countryData.currencies) {
                                const currencyKeys = Object.keys(countryData.currencies);
                                if (currencyKeys.length === 1) {
                                    const currencyCode = currencyKeys[0];
                                    const currencyName = countryData.currencies[currencyCode].currency;
                                    $("#afribapayCurrencyBlock").hide();
                                    $currencySelect.append(`<option value="${currencyCode}">${currencyName}</option>`);
                                    $currencySelect.val(currencyCode).trigger("change");
                                } else {
                                    currencyKeys.forEach(currencyCode => {
                                        const currencyName = countryData.currencies[currencyCode].currency;
                                        $currencySelect.append(`<option value="${currencyCode}">${currencyName}</option>`);
                                    });
                                }
                            }
                        }
                        validateForm();
                    });
                    $currencySelect.on("change", function() {
                        const selectedCountryCode = $countrySelect.val();
                        const selectedCurrencyCode = $(this).val();
                        $operatorSelect.empty().append(`<option value="">${AfribapayClass.afribapayLang.OPERATOR_FIRST_CHOICE}</option>`);
                        $otpField.hide();
                        $("#afribapayOtp_code").removeAttr("required").val("");
                        $("#afribapayOtpMessage").hide();
                        if (selectedCountryCode && selectedCurrencyCode && 
                            CountriesData[selectedCountryCode]?.currencies?.[selectedCurrencyCode]) {
                            const operators = CountriesData[selectedCountryCode].currencies[selectedCurrencyCode].operators;
                            if (Array.isArray(operators)) {
                                operators.forEach(operator => {
                                    $operatorSelect.append(`<option value="${operator.operator_code}">${operator.operator_name}</option>`);
                                });
                            }
                        }
                        validateForm();
                    });
                    $operatorSelect.on("change", function() {
                        const selectedCountryCode = $countrySelect.val();
                        const selectedCurrencyCode = $currencySelect.val();
                        const selectedOperatorCode = $(this).val();
                        $otpField.hide();
                        $("#afribapayOtp_code").removeAttr("required").val("");
                        $("#afribapayOtpMessage").hide();
                        if (selectedCountryCode && selectedCurrencyCode && selectedOperatorCode && 
                            CountriesData[selectedCountryCode]?.currencies?.[selectedCurrencyCode]) {
                            const operators = CountriesData[selectedCountryCode].currencies[selectedCurrencyCode].operators;
                            if (!Array.isArray(operators)) return;
                            const selectedOperator = operators.find(op => op.operator_code === selectedOperatorCode);
                            if (!selectedOperator) return;
                            if (selectedOperator.otp_required === 1 && selectedOperator.wallet !== 1) {
                                $otpField.show();
                                $("#afribapayOtpMessage")
                                    .show()
                                    .html(`${AfribapayClass.afribapayLang.OTP_MESSAGE} <b>${selectedOperator.ussd_code || ''}</b>`)
                                    .removeClass("alert-info")
                                    .addClass('alert-danger');
                                document.getElementById("afribapayOtp_code").setAttribute("required", "");
                            }
                            if (selectedOperator.otp_required === 0 && selectedOperator.ussd_code !== "") {
                                ussdText = selectedOperator.ussd_code;
                            }
                            if (selectedOperator.wallet === 1 && selectedOperator.otp_required === 1) {
                                if ($('#walletButton').length === 0) {
                                    const walletButton = $('<button>', {
                                        id: 'walletButton',
                                        class: 'btn primary-button btn-success w-100',
                                        text: AfribapayClass.afribapayLang.WALLET_OTP_BUTTON,
                                        click: function(event) {
                                            event.preventDefault();
                                            if (validateAfribaPayForm()) {
                                                const otpDataQuery = {
                                                    "operator": selectedOperatorCode.toLowerCase(),
                                                    "country": $countrySelect.val(),
                                                    "phone": $('#afribapayPhone').val(),
                                                    "amount": $('#afribapayAmount').val(),
                                                    "currency": $currencySelect.val()
                                                };
                                                sendWalletOtp(otpDataQuery)
                                                    .then(response => {
                                                        $otpField.show();
                                                        $("#afribapayOtpMessage")
                                                            .show()
                                                            .html(AfribapayClass.afribapayLang.WALLET_OTP_MESSAGE)
                                                            .removeClass("alert-danger")
                                                            .addClass('alert-info');
                                                        document.getElementById("afribapayOtp_code").setAttribute("required", "");
                                                        $('#walletButton').hide();
                                                        $submitButton.show();
                                                    })
                                                    .catch(error => {
                                                        alertMessage(error || "An error occurred. Please try again.", "danger");
                                                    });
                                            }
                                        }
                                    });
                                    $submitButton.after(walletButton);
                                    $submitButton.hide();
                                }
                            } else {
                                $('#walletButton').remove();
                                $submitButton.show();
                            }
                        }
                        validateForm();
                    });
                    if (AfribapayClass.afribapayFormParam.country) {
                        $countrySelect.val(AfribapayClass.afribapayFormParam.country).trigger("change");
                    }
                    if (AfribapayClass.afribapayFormParam.currency) {
                        $currencySelect.val(AfribapayClass.afribapayFormParam.currency).trigger("change");
                    }
                } catch (error) {
                    replaceFormWithFailledIcon("An unexpected error occurred while loading the payment form.");
                }
            }

            async function sendWalletOtp(p) {
                return new Promise((resolve, reject) => {
                    fetch("<?php echo $checkoutPath?>src/AfribapayClass.php?pay=<?php echo $paymentCode?>", {
                        method: "POST",
                        headers: {"Content-Type": "application/json"},
                        body: JSON.stringify({action: "walletOtp", data: p})
                    }).then(response => {
                        if (!response.ok) {
                            reject(new Error("Error wallet otp generation"));
                        }
                        return response.json();
                    }).then(data => {
                        if (data.httpCode !== 200) {
                            reject(new Error(data.response.error.reason || "Wallet OTP generation failed"));
                        } else {
                            resolve(data); 
                        }
                    }).catch(error => {
                        reject(error);
                    });
                });
            }
            
            function formatCountry(country) {
                if (!country.id) {
                    return country.text;
                }
                return $('<span><i class="' + country.flag + '"></i> ' + country.text + '</span>');
            }

            function updatePhoneField(indicatif) {
                const country = $('#afribapayCountry').val();
                $('#countryCode').html(`<span class="flag-icon flag-icon-${country.toLowerCase()} me-2"></span>+${indicatif}`);
            }

            function validateForm() {
                const countrySelected  = $('#afribapayCountry').val() !== "";
                const currencySelected = $('#afribapayCurrency').val() !== "";
                const operatorSelected = $('#afribapayOperator').val() !== "";
                $('#afribapaySubmitPayment').prop('disabled', !(countrySelected && currencySelected && operatorSelected));
            }

            function openWaveFront(url) {
                window.location.href = url;
            }

            function addTransactionInfo2(d) {
            const b = document.getElementById('transactionInfoContainer');
            const s = document.createElement('style');
            s.innerHTML = ``;
            document.head.appendChild(s);
            b.innerHTML = `
                <div class="alert alert-light mt-3 m-0">
                    <div>${AfribapayClass.afribapayLang.TRANSACTION_ID}: <strong>${d.transaction_id}</strong></div>
                    <div>${AfribapayClass.afribapayLang.AMOUNT}: <strong>${d.amount_total} ${d.currency}</strong></div>
                    <div>${AfribapayClass.afribapayLang.OPERATOR_LABEL}: <strong> ${d.operator || 'Unknown Operator'}-${(d.country || 'unknown').toUpperCase()}</strong></div>
                    <div>${AfribapayClass.afribapayLang.DATE}: <strong>${d.date_created}</strong></div>
                </div>
            `;
            }

            function addTransactionInfo(d) {
            const b = document.getElementById('transactionInfoContainer');
            const s = document.createElement('style');
            s.innerHTML = ``;
            document.head.appendChild(s);
            
            // Build HTML content with conditional date section
            let htmlContent = `
                <div class="alert alert-light mt-3 m-0">
                <div>${AfribapayClass.afribapayLang.TRANSACTION_ID}: <strong>${d.transaction_id}</strong></div>
                <div>${AfribapayClass.afribapayLang.AMOUNT}: <strong>${d.amount_total} ${d.currency}</strong></div>
                <div>${AfribapayClass.afribapayLang.OPERATOR_LABEL}: <strong> ${d.operator || 'Unknown Operator'}-${(d.country || 'unknown').toUpperCase()}</strong></div>`;
            
            // Only add date div if date_created exists
            if (d.date_created) {
                htmlContent += `
                <div>${AfribapayClass.afribapayLang.DATE}: <strong>${d.date_created}</strong></div>`;
            }
            
            // Close the main div
            htmlContent += `
                </div>`;
            
            b.innerHTML = htmlContent;
            }
            
            global.AfribapayClass = AfribapayClass;
            global.initPaymentForm = initPaymentForm;
        })(window);

        function AfribaPayCheckout() {
            alertMessage();
            const a = {
                country: $("#afribapayCountry").val(),
                currency: $("#afribapayCurrency").val(),
                operator: $("#afribapayOperator").val(),
                phone: $("#afribapayPhone").val(),
                amount: $("#afribapayAmount").val(),
                otp_code: $("#afribapayOtp_code").val()
            };
            validateAfribaPayForm() && AfribapayClass.getCheckout(a)
        }

        function alertMessage(e = null, a = "info") {
            const s = $("#statusMessage");
            if (s.html(e), !e) return void s.removeClass("alert-success alert-warning alert-danger alert-info").hide();
            const r = {danger: "alert-danger", warning: "alert-warning", success: "alert-success", info: "alert-info"}[a] || "";
            s.removeClass("alert-success alert-warning alert-danger alert-info").addClass(`alert ${r}`).show()
        }

        function switchBadge(e) {
            const a = $("#badgeStep1"),
                s = $("#badgeStep2");
            a.removeClass("bg-primary").addClass("bg-secondary"),
            s.removeClass("bg-light").addClass(`bg-${e} text-white`)
        }

        $(document).ready(function () {
            AfribapayClass.afribapayFormParam = <?= json_encode($params) ?>;
            AfribapayClass.afribapayLang = <?= json_encode($formInfo) ?>;
            let mbs = <?= json_encode($mbsError); ?>;
            if(mbs !== false){
                sdkFirstAuthenticationFailledIcon(mbs);
            }else{
                initPaymentForm();
            }
            // initPaymentForm();
        });

        document.querySelectorAll(".reload-btn").forEach((e => {
            e.addEventListener("click", (() => {
                location.reload()
            }))
        }));

        function sdkFirstAuthenticationFailledIcon(a = null) {
            alertMessage();
            const n = document.getElementById("afribapayPaymentForm");
            n.innerHTML = `
                <div class="text-center">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${a}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-exclamation-octagon-fill text-danger" viewBox="0 0 16 16">
                        <path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                    </svg>
                    </div>
                    <div class="mt-3 text-center">
                    <a href="javascript:void(0)" onclick="redirectToOrigin()" class="btn btn-secondary reload-btn">
                        <i class="bi bi-arrow-return-left"></i> ${AfribapayClass.afribapayLang.RETURN_BUTTON}
                    </a>
                    </div>
                </div>
            `
        }

        function redirectToOrigin() {
        if (document.referrer) {
            window.location.href = document.referrer; 
        } else {
            window.location.href = '/';
        }
        }

        function validateAfribaPayForm() {
            const e = document.getElementById("afribapayPaymentForm");
            let t = !0;
            const i = (e, t) => (e.classList.toggle("is-invalid", !t), t);
            e.querySelectorAll("select[required], input[required]").forEach((e => {
                t = i(e, "" !== e.value.trim()) && t
            }));
            const a = document.getElementById("afribapayPhone"),
                l = a.value.trim().length >= 8 && isOnlyNumber(a.value);
            t = i(a, l) && t;
            const n = document.getElementById("afribapayOtp_code"),
                r = n.value.trim(),
                s = n.hasAttribute("required");
            if (r.length >= 1 || s) {
                const e = isOnlyNumber(r) && (!s || r.length >= 4);
                t = i(n, e) && t
            } else n.classList.remove("is-invalid");
            return t
        }

        function isOnlyNumber(t) {
            return 0 !== t.trim().length && /^\d+$/.test(t.trim())
        }

        $(document).ready((function() {
            $("#afribapayPaymentForm").on("submit", (function(a) {
                a.preventDefault(),
                AfribaPayCheckout()
            }))
        }));
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 