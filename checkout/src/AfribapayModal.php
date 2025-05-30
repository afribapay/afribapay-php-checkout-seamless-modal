<?php
class AfribaPayException extends \Exception {}
enum AfpEnvironment: string {
    case Sandbox = 'sandbox';
    case Production = 'production';
}
class AfpConfiguration {
    private string $environment;
    private array $urls;
    private $params;
    
    public function __construct() {
        $this->validateConfigInc();
        $this->environment = $this->getConfigInc('environment');
        if(!AfpEnvironment::tryFrom($this->environment)){
            throw new InvalidArgumentException("Invalid environment: $this->environment");
        }
        $checkoutPath = $this->getConfigInc('checkoutPath');
        $this->urls = [
            'production' => [
                'baseURL' => 'https://api.afribapay.com',
                'checkout' => $checkoutPath.'checkout.php',
            ],
            'sandbox' => [
                'baseURL' => 'https://api-sandbox.afribapay.com',
                'checkout' => $checkoutPath.'checkout.php',
            ]
        ];
    }

    private function validateConfigInc() {
        $customer_config_path = dirname(__FILE__, 2).'/customer_config.inc.php';
        if (file_exists($customer_config_path)) {
            $params = require_once($customer_config_path);
        } else {
            throw new AfribaPayException("The customer_config.inc.php file was not found at the specified location : " . $customer_config_path);
        }   
        if (!is_array($params)) {
            throw new AfribaPayException("Invalid customer_config.inc.php file");
        }
        $this->params = $params;
        $requiredFields = [
            'apiUser', 
            'apiKey', 
            'agent_id', 
            'merchantKey', 
            'environment', 
            'lang', 
            'checkoutPath',
            'useCacheFolder',
            'cacheDirectory'
        ];
        foreach ($requiredFields as $field) {
            if (!isset($this->params[$field]) || ($field !== 'useCacheFolder' && empty($this->params[$field]))) {
                throw new AfribaPayException("Missing required field in AfribapayConfig.inc.php file: $field");
            }
        }
        if (substr($this->params['checkoutPath'], -1) !== '/') {
            throw new AfribaPayException("Field 'checkoutPath' in AfribapayConfig.inc.php must end with a '/'.");
        }
        $useCacheValue = $this->params['useCacheFolder'];
        if (!is_bool($useCacheValue) && 
            $useCacheValue !== 'true' && 
            $useCacheValue !== 'false' && 
            $useCacheValue !== 1 && 
            $useCacheValue !== 0 &&
            $useCacheValue !== '1' && 
            $useCacheValue !== '0') {
            throw new AfribaPayException("Field 'useCacheFolder' in AfribapayConfig.inc.php must be true or false");
        }
        if ($useCacheValue === 'true' || $useCacheValue === '1' || $useCacheValue === 1) {
            $this->params['useCacheFolder'] = true;
        } else if ($useCacheValue === 'false' || $useCacheValue === '0' || $useCacheValue === 0) {
            $this->params['useCacheFolder'] = false;
        }
        if (!in_array($params['lang'], ['en', 'fr'])) {
            throw new AfribaPayException("Field 'lang' in AfribapayConfig.inc.php must be 'fr' or 'en' ");
        }
    }

    public function getConfigInc($key){
        return $this->params[$key] ?? null;
    }

    public function getCheckoutUrl(): string {
        return $this->urls[$this->environment]['checkout'] 
        ?? throw new AfribaPayException("Invalid environment");
    }

    public function getBaseUrl(): string {
        return $this->urls[$this->environment]['baseURL'] 
        ?? throw new AfribaPayException("Invalid environment");
    }
}
class AfribaPayRequest {
    private $requiredFields = [];
    private $allowedFields = [
        'amount', 
        'currency', 
        'order_id', 
        'reference_id', 
        'country', 
        'lang', 
        'showCountries', 
        'notify_url'
    ];
    private $data = [];

    public function __set($name, $value) {
        if (!in_array($name, $this->allowedFields)) {
            throw new AfribaPayException("Field '$name' is not allowed");
        }
        if ($name === 'showCountries') {
            if (!is_bool($value)) {
                throw new AfribaPayException("$name must be true or false");
            }
            $this->data[$name] = $value ? 1 : 0;
            return;
        }
        if (empty($value)) {
            throw new AfribaPayException("$name cannot be empty");
        }
        if($name == 'amount' && $value < 1){
            throw new AfribaPayException("$name cannot be less than 1");
        }
        $this->data[$name] = $this->sanitizeInput($value);
    }

    public function __get($name) {
        return $this->data[$name] ?? null;
    }


    private function sanitizeInput($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
    
    public function validate() {
        foreach ($this->requiredFields as $field) {
            if (!isset($this->data[$field])) {
                throw new AfribaPayException("Missing required field: $field");
            }
        }
    }
    
    public function toArray() {
        return $this->data;
    }
}
class AfribaPayModal {
    private AfpConfiguration $config;
    private string $lang;

    public function __construct() {
        $this->config = new AfpConfiguration();
        $this->lang =  $this->config->getConfigInc('lang');
    }

    public function createCheckoutButton(
        AfribaPayRequest $request,
        string $buttonText = 'Pay',
        string $buttonColor = '#4CAF50',
        string $size = 'medium',
        array $additionnalClass = []
    ): string {
        $request->validate();
        return $this->generateCheckoutForm($request, $buttonText, $buttonColor, $size, $additionnalClass);
    }

    private function generateCheckoutForm(
        AfribaPayRequest $request,
        string $buttonText,
        string $buttonColor,
        string $size,
        array $additionnalClass
    ): string {
        $formId = $this->generateFormId($buttonText);
        $html = $this->getButtonStyles();
        $formData = array_merge(
            $request->toArray(),
            [
                'baseUrl' => $this->config->getBaseUrl(),
                'AfpModalPost' => time(),
                'showCountries' => $request->showCountries ?? true
            ]
        );
        $html .= $this->createSubmitButton($formId, $buttonText, $buttonColor, $size, $additionnalClass);
        $html  .= (new AfpModal())->generateModal($formId, $formData, $this->config->getCheckoutUrl(), $this->lang);
        return $html;
    }

    private function generateFormId(string $buttonText): string {
        return strtoupper(str_replace(' ', '_', trim($buttonText))) . '__' . random_int(0, 9999);
    }

    private function getButtonStyles(): string {
        return '
        <style>
            .afp-custom-pay-button {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 12px 24px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 8px;
                transition: background-color 0.3s, transform 0.2s, box-shadow 0.2s;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
                font-weight: bold;
            }
            .afp-custom-pay-button:hover {
                background-color: #45a049;
                transform: translateY(-2px);
                box-shadow: 0px 6px 8px rgba(0, 0, 0, 0.15);
            }
            .afp-custom-pay-button:active {
                background-color: #3e8e41;
                transform: translateY(1px);
                box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            }
            .afp-custom-pay-button.small {
                padding: 5px 10px;
                font-size: 12px;
            }
            .afp-custom-pay-button.medium {
                padding: 10px 20px;
                font-size: 16px;
            }
            .afp-custom-pay-button.large {
                padding: 15px 30px;
                font-size: 20px;
            }
        </style>';
    }

    private function createSubmitButton(
        string $formId,
        string $buttonText,
        string $buttonColor,
        string $size,
        array $additionnalClass
    ): string {
        $sizeClass = $this->getButtonSizeClass($size);
        $extraClasses = implode(' ', array_map('htmlspecialchars', $additionnalClass));
        $allClasses = trim("afp-custom-pay-button $sizeClass $extraClasses");
        $modalId = $formId;
        return sprintf(
            "<button type=\"button\" style=\"background-color: %s;\" class=\"%s\" data-bs-toggle=\"modal\" data-bs-target=\"#{$modalId}\">%s</button>",
            htmlspecialchars($buttonColor, ENT_QUOTES, 'UTF-8'),
            $allClasses,
            htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8')
        ); 
    }

    private function getButtonSizeClass(string $size): string {
        return match (strtolower($size)) {
            'small' => 'small',
            'large' => 'large',
            default => 'medium',
        };
    }
}
class AfpModal {
    private function getButtonTexts(string $lang): array {
        $translations = [
            'fr' => [
                'back' => 'Retour',
                'loading' => 'Chargement en cours ...',
                'loadingError' => 'Erreur de chargement: '
            ],
            'en' => [
                'back' => 'Back',
                'loading' => 'Loading ... ',
                'loadingError' => 'Loading error : '
            ],
        ];
        return $translations[$lang] ?? $translations['fr'];
    }     

    public function generateModal(string $modalId, array $postData = [], $contentUrl = null, $lang = 'fr'): string {
        if (empty($contentUrl)) { throw new AfribaPayException("Content URL is required"); }
        $protocol = $this->is_https() ? "https" : "http";
        $contentUrl = $protocol."://".$_SERVER['HTTP_HOST'].$contentUrl;
        $contentUrl = filter_var($contentUrl, FILTER_VALIDATE_URL);
        if ($contentUrl === false) { throw new AfribaPayException("Invalid content URL"); }
        $safeModalId = preg_replace('/[^a-zA-Z0-9_]/', '_', $modalId);
        $postDataJson = json_encode($postData);
        $buttonTexts = $this->getButtonTexts($lang);
        return "
            <div class=\"modal fade\" id=\"{$modalId}\" tabindex=\"-1\" aria-labelledby=\"{$modalId}Label\" aria-hidden=\"true\">
                <div class=\"modal-dialog modal-lg\">
                    <div class=\"modal-content\">
                        <div class=\"modal-header\">
                            <h5 class=\"modal-title\" id=\"{$modalId}Label\"></h5>
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                        </div>
                        <div class=\"modal-body\" id=\"{$modalId}_body\">
                            <div id=\"{$modalId}_content\"></div>
                        </div>
                        <div class=\"modal-footer\" style=\"display: none;\" id=\"{$modalId}_footer\">
                            <button type=\"button\" class=\"btn btn-primary\" id=\"{$modalId}_btnRetour\">{$buttonTexts['back']}</button>
                        </div>
                    </div>
                </div>
            </div>
            <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
            <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js\"></script>
            <script src=\"https://code.jquery.com/jquery-3.6.4.min.js\"></script>
            <script src=\"https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js\"></script>
            <script>
                if (!window.{$safeModalId}_data) {
                    window.{$safeModalId}_data = {};
                }
                window.{$safeModalId}_data.postData = {$postDataJson};
                window.{$safeModalId}_data.language = '{$lang}';
                window.{$safeModalId}_data.translations = {
                    '{$lang}': {
                        'back': '{$buttonTexts['back']}',
                        'loading': '{$buttonTexts['loading']}'
                    }
                };
                document.addEventListener('DOMContentLoaded', function() {
                    var modal = document.getElementById('{$modalId}');
                    var contentDiv = document.getElementById('{$modalId}_content');
                    var footerDiv = document.getElementById('{$modalId}_footer');
                    var isLoaded = false;
                    if (typeof window.validateAfribaPayForm !== 'function') {
                        window.validateAfribaPayForm = function(form) { return false;};
                    }
                    modal.addEventListener('shown.bs.modal', function () {
                        if (document.activeElement) { document.activeElement.blur();}
                        if (!isLoaded) { loadContent();}
                    });
                    modal.addEventListener('hide.bs.modal', function () {
                        const focusedElement = modal.querySelector(':focus');
                        if (focusedElement) {focusedElement.blur();}
                    });
                    function loadContent() {
                        footerDiv.style.display = 'none';
                        contentDiv.innerHTML = '<div class=\"text-center\"><div class=\"spinner-border\" role=\"status\"><span class=\"visually-hidden\">{$buttonTexts['loading']}</span></div><p class=\"mt-2\">{$buttonTexts['loading']}</p></div>';
                        fetch('{$contentUrl}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(window.{$safeModalId}_data.postData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok: ' + response.status);
                            }
                            return response.text();
                        })
                        .then(html => {
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            processUrls(tempDiv);
                            while (contentDiv.firstChild) {contentDiv.removeChild(contentDiv.firstChild);}
                            Array.from(tempDiv.childNodes).forEach(node => {
                                if (node.nodeName !== 'SCRIPT') {
                                    contentDiv.appendChild(node.cloneNode(true));
                                }
                            });
                            handleScripts(tempDiv, '{$safeModalId}');
                            if (typeof $.fn.select2 === 'function') {
                                $(contentDiv).find('select.select2').select2();
                            }
                            isLoaded = true;
                            if (window.{$safeModalId}_data.formData) {
                                setTimeout(restoreFormData, 100);
                            }
                            const contentHtml = contentDiv.innerHTML;
                            handleTransactionStatus(contentHtml);
                            setupStatusObserver();
                        })
                        .catch(error => {
                            contentDiv.innerHTML = '<div class=\"alert alert-danger\"> {$buttonTexts['loadingError']} ' + error.message + '</div>';
                            footerDiv.style.display = 'flex';
                        });
                    }
                    function handleTransactionStatus(contentHtml) {
                        if (contentHtml.includes('afp-success-status')) {
                            footerDiv.style.display = 'flex';
                            document.getElementById('{$modalId}_btnRetour').style.display = 'none';
                            window.{$safeModalId}_data.transactionStatus = 'success';
                        } else if (contentHtml.includes('afp-failed-status')) {
                            footerDiv.style.display = 'flex';
                            document.getElementById('{$modalId}_btnRetour').style.display = 'block';
                            window.{$safeModalId}_data.transactionStatus = 'failed';
                        } else if (contentHtml.includes('afp-retrying-status')) {
                            footerDiv.style.display = 'flex';
                            document.getElementById('{$modalId}_btnRetour').style.display = 'block';
                            window.{$safeModalId}_data.transactionStatus = 'retrying';
                        } else {
                            window.{$safeModalId}_data.transactionStatus = 'unknown';
                        }
                        const statusEvent = new CustomEvent('afp-status-change', { 
                            detail: { 
                                status: window.{$safeModalId}_data.transactionStatus,
                                modalId: '{$modalId}'
                            } 
                        });
                        document.dispatchEvent(statusEvent);
                    }
                    function setupStatusObserver() {
                        const observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                                    const currentContent = contentDiv.innerHTML;
                                    const previousStatus = window.{$safeModalId}_data.transactionStatus || 'unknown';
                                    if (window.{$safeModalId}_data.lastContent !== currentContent) {
                                        window.{$safeModalId}_data.lastContent = currentContent;
                                        handleTransactionStatus(currentContent);
                                    }
                                }
                            });
                        });
                        const config = { attributes: true, childList: true, subtree: true };
                        observer.observe(contentDiv, config);
                        window.{$safeModalId}_data.statusInterval = setInterval(function() {
                            const currentContent = contentDiv.innerHTML;
                            if (window.{$safeModalId}_data.lastPolledContent !== currentContent) {
                                window.{$safeModalId}_data.lastPolledContent = currentContent;
                                handleTransactionStatus(currentContent);
                            }
                        }, 1000);
                        modal.addEventListener('hidden.bs.modal', function() {
                            if (window.{$safeModalId}_data.statusInterval) {
                                clearInterval(window.{$safeModalId}_data.statusInterval);
                            }
                            observer.disconnect();
                        });
                    }
                    function processUrls(container) {
                        var links = container.querySelectorAll('a[href]');
                        links.forEach(link => {
                            var href = link.getAttribute('href');
                            if (href && !href.startsWith('http') && !href.startsWith('//') && !href.startsWith('#')) {
                                link.href = new URL(href, '{$contentUrl}').href;
                            }
                        });
                        var srcs = container.querySelectorAll('[src]');
                        srcs.forEach(srcElem => {
                            var src = srcElem.getAttribute('src');
                            if (src && !src.startsWith('http') && !src.startsWith('//')) {
                                srcElem.src = new URL(src, '{$contentUrl}').href;
                            }
                        });
                    }
                    function handleScripts(container, safeId) {
                        var scripts = container.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.src) {
                                var scriptSrc = script.src;
                                var existingScripts = document.querySelectorAll('script[src=\"' + scriptSrc + '\"]');
                                if (existingScripts.length === 0) {
                                    var newScript = document.createElement('script');
                                    newScript.src = scriptSrc;
                                    newScript.defer = true;
                                    document.body.appendChild(newScript);
                                }
                            } else {
                                var scriptContent = script.textContent;
                                var wrappedScript = document.createElement('script');
                                wrappedScript.textContent = '(function(window, document, $) { ' +
                                    '// Create a unique context for each script execution\\n' +
                                    'var ' + safeId + '_context_' + index + ' = {};\\n' +
                                    scriptContent +
                                    '})(window, document, jQuery);';
                                document.body.appendChild(wrappedScript);
                            }
                        });
                    }
                    document.getElementById('{$modalId}_btnRetour').addEventListener('click', function() {
                        document.activeElement.blur();
                        saveFormData();
                        isLoaded = false;
                        loadContent();
                    });
                    function saveFormData() {
                        const forms = contentDiv.querySelectorAll('form');
                        if (!forms.length) return;
                        if (!window.{$safeModalId}_data.formData) {
                            window.{$safeModalId}_data.formData = {};
                        }
                        forms.forEach((form, formIndex) => {
                            const formData = {};
                            const inputs = form.querySelectorAll('input, select, textarea');
                            inputs.forEach(input => {
                                if (input.name && input.type !== 'button' && input.type !== 'submit' && input.type !== 'reset') {
                                    if (input.type === 'checkbox' || input.type === 'radio') {
                                        formData[input.name] = input.checked;
                                    }else {
                                        formData[input.name] = input.value;
                                    }
                                }
                            });
                            window.{$safeModalId}_data.formData['form_' + formIndex] = formData;
                        });
                    }
                    function restoreFormData() {
                        if (!window.{$safeModalId}_data.formData) return;
                        const forms = contentDiv.querySelectorAll('form');
                        if (!forms.length) return;
                        forms.forEach((form, formIndex) => {
                            const formData = window.{$safeModalId}_data.formData['form_' + formIndex];
                            if (!formData) return;
                            const inputs = form.querySelectorAll('input, select, textarea');
                            inputs.forEach(input => {
                                if (input.name && formData.hasOwnProperty(input.name)) {
                                    if (input.type === 'checkbox' || input.type === 'radio') {
                                        input.checked = formData[input.name];
                                    } else if (input.type !== 'file') {
                                        input.value = formData[input.name];
                                        if (input.tagName.toLowerCase() === 'select' && typeof $.fn.select2 === 'function') {
                                            $(input).trigger('change');
                                        }
                                    }
                                }
                            });
                        });
                    }
                });
            </script>
        ";
    }

    private function is_https() {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }
} 
