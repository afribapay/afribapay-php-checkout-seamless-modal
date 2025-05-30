<?php
/**
 * AfribapayClass.php
 * 
 * This class handles the integration with the Afribapay payment gateway.
 * It provides methods to fetch countries, initiate payments, and check payment status.
 * 
 */
require_once 'AfribapayConfig.inc.php';

define('CACHE_PATH_FILE', TransactionConfig::getCacheDirectory(). '/afribapay-countries.json');
define('COUNTRIES_CACHE_DURATION', 3600 * 72);  // 3 JOURS

class AfribapayClass {
    private static $config = [];
    private $token;
    private $tokenExpiration;

    public $formParameters = [];
    public $formLangData = [];

    public function __construct() {
        self::$config = TransactionConfig::getConfigurations();
        $this->formParameters = TransactionConfig::getParameters();
        $lang = !empty($this->formParameters['lang']) ? $this->formParameters['lang'] : 'fr';
        $this->formLangData = $this->language($lang);
        $this->token = null;
        $this->tokenExpiration = 0;
    }

    public function fetchCountries($country, $currency, $showCountries) {
        try {
            $listOfCountries = $this->countries();
            if (isset(self::$config['countries']) && is_array(self::$config['countries']) && !empty(self::$config['countries'])) {
                $listOfCountries = array_filter($listOfCountries, function ($key) {
                    return in_array($key, self::$config['countries']);
                }, ARRAY_FILTER_USE_KEY);
            }
            $defaultCountry  = $listOfCountries[$country] ?? null;
            $countriesToShow = $defaultCountry && !$showCountries ? [$defaultCountry] : $listOfCountries;
            if($currency){
                $optimal = [];
                $currencies = in_array($currency, ['XOF', 'XAF']) ? ['XAF', 'XOF'] : [$currency];
                foreach($countriesToShow as $country){
                    foreach ($currencies as $current) {
                        if(array_key_exists($current, $country['currencies'])){
                            $row = [];
                            $row['country_code'] = $country['country_code'];
                            $row['country_name'] = $country['country_name'];
                            $row['prefix']       = $country['prefix'];
                            $row['taxes']        = $country['taxes'];
                            $row['currencies'][$current] = $country['currencies'][$current];
                            $optimal[$country['country_code']] = $row;
                        }
                    }
                }
                $countriesToShow = $optimal;
            }
            uasort($countriesToShow, function ($a, $b) {
                return strcmp($a['country_name'], $b['country_name']);
            });
            return $countriesToShow;
        } catch (Exception $error) {
            $this->afpDebug("Error fetching countries: " . $error->getMessage());
            return ['error' => 'Failed to fetch countries', 'message' => $error->getMessage()];
        }
    }

    public function getCheckout($paymentDetails) {
        try {
            $getCheckout = $this->checkout($paymentDetails);
            echo json_encode($getCheckout);
        } catch (Exception $error) {
            $this->afpDebug("Error during payment initialization: " . $error->getMessage());
            echo json_encode(['error' => 'An error occurred during payment initialization.', 'message' => $error->getMessage()]);
        }
    }

    public function sendWalletOtp($walletDetails) {
        try {
            $sendWalletOtp = $this->walletOtp($walletDetails['data'] ?? throw new Exception('no wallet data'));
            echo json_encode($sendWalletOtp);
        } catch (Exception $error) {
            $this->afpDebug("Error during payment initialization: " . $error->getMessage());
            echo json_encode(['error' => 'An error occurred during payment initialization.', 'message' => $error->getMessage()]);
        }
    }

    public function getStatus($order_id) {
        try {
            $getStatus = $this->status($order_id);
            echo json_encode($getStatus);
        } catch (Exception $error) {
            $this->afpDebug("Error during payment initialization: " . $error->getMessage());
            echo json_encode(['error' => 'An error occurred during payment initialization.', 'message' => $error->getMessage()]);
        }
    }

    private function language($langx = 'fr') {
        $configFilePath = dirname(__FILE__, 2 )."/src/lang/{$langx}.json";
        if (!file_exists($configFilePath)) {
            throw new Exception('Language file not found for ' . $configFilePath);
        }
        $configData = file_get_contents($configFilePath);
        if ($configData === false) {
            throw new Exception('Error loading config.json file');
        }
        return json_decode($configData, true);
    }

    public function usualCurrency($currency) {
        $symbols = [
            'XOF' => 'FCFA',
            'XAF' => 'FCFA',
            'USD' => '$',
            'EUR' => '€',
        ];
        return $symbols[$currency] ?? $currency;
    }

    private function getCache() {
        if (file_exists(CACHE_PATH_FILE)) {
            $content = file_get_contents(CACHE_PATH_FILE);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    private function saveCache(array $cache) {
        file_put_contents(CACHE_PATH_FILE, json_encode($cache, JSON_PRETTY_PRINT));
    }

    private function createCurlHandle($url, $isPost = false, $headers = []) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => $isPost,
            CURLOPT_HTTPHEADER => $headers
        ]);
        return $ch;
    }

    private function executeRequest($ch) {
        try{
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ['httpCode' => $httpCode, 'response' => json_decode($response, true)];
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function checkConfigParam(): void {
        $requiredKeys = ['baseUrl', 'apiKey', 'apiUser', 'merchantKey'];
        if (!is_array(self::$config)) {
            throw new InvalidArgumentException('The configuration must be an array.');
        }
        $missingKeys = [];
        $emptyKeys = [];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, self::$config)) {
                $missingKeys[] = $key;
            } elseif (self::$config[$key] === null || trim((string)self::$config[$key]) === '') {
                $emptyKeys[] = $key;
            }
        }
        if (!empty($missingKeys) || !empty($emptyKeys)) {
            $errorMessages = [];
            if (!empty($missingKeys)) {
                $errorMessages[] = "Missing key(s) : " . implode(', ', $missingKeys);
            }
            if (!empty($emptyKeys)) {
                $errorMessages[] = "Empty or null key(s) : " . implode(', ', $emptyKeys);
            }
            throw new InvalidArgumentException(implode(' | ', $errorMessages) . ' in configuration data.');
        }
        if (!$this->isUrlStrictlyValid(self::$config['baseUrl'])) {
            throw new Exception("Invalid baseUrl format.");
        }
    }

    function isUrlStrictlyValid(string $url): bool {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'], true)) {
            return false;
        }
        if (!isset($parsedUrl['host']) || !preg_match('/^[a-z0-9\-\.]+\.[a-z]{2,}$/i', $parsedUrl['host'])) {
            return false;
        }
        if (isset($parsedUrl['port']) && ($parsedUrl['port'] < 1 || $parsedUrl['port'] > 65535)) {
            return false;
        }
        return true;
    }

    private function authenticate() {
        try{
            $this->checkConfigParam();
            if ($this->token && time() < $this->tokenExpiration) {
                return $this->token;
            }
            $credentials = base64_encode(self::$config['apiUser'] . ':' . self::$config['apiKey']);
            $ch = $this->createCurlHandle(
                self::$config['baseUrl'] . '/v1/token',
                true,
                [
                    'Authorization: Basic ' . $credentials,
                    'Content-Type: application/json'
                ]
            );
            $result = $this->executeRequest($ch);
            if (isset($result['response']['data']['access_token'])) {
                $this->token = $result['response']['data']['access_token'];
                $expires_in = $result['response']['data']['expires_in'];
                $this->tokenExpiration = time() + $expires_in;
                return $this->token;
            }
            throw new Exception("Invalid authentication credentials.");
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function refreshTokenIfNeeded() {
        try{
            if (!$this->token || time() >= $this->tokenExpiration) {
                return $this->authenticate();
            }
            return $this->token;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public function sdkFirstAuthentication() {
        try{
            $this->token = null;
            $this->tokenExpiration = 0;
            return $this->authenticate();
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }
    
    private function countries() {
        if(TransactionConfig::isUseCache()){
            $cache = $this->getCache();
            $currentTime = time();
            if (isset($cache['countries']) && ($currentTime - $cache['countries']['timestamp'] < COUNTRIES_CACHE_DURATION)) {
                return $cache['countries']['data'];
            }
            $listOfCountries = $this->fetchCountriesFromApi();
            $cache['countries'] = [
                'data' => $listOfCountries,
                'timestamp' => $currentTime
            ];
            $this->saveCache($cache);
            return $listOfCountries;
        } else {
            return $this->fetchCountriesFromApi();
        }
    }

    private function fetchCountriesFromApi() {
        try {
            $token = $this->refreshTokenIfNeeded();
            if (!$token) {
                return ['httpCode' => 401, 'response' => ['error' => 'Authentication failed']];
            }

            $ch = $this->createCurlHandle(
                self::$config['baseUrl'] . '/v1/countries',
                false,
                [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ]
            );

            $response = $this->executeRequest($ch);
            return $response['response']['data'] ?? [];

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function checkout($data) {
        try {
            $this->checkConfigParam();
            $token = $this->refreshTokenIfNeeded();
            if (!$token) {
                return ['httpCode' => 401, 'response' => ['error' => 'Authentication failed']];
            }
            $timestamp = time();
            $random = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 4)), 0, 4);
            $data = is_string($data) ? json_decode($data, true) : $data;
            // Validate notify_url if defined
            $notify_url = $this->formParameters['notify_url'] ?? self::$config['notify_url'] ?? '';
            if (!empty($notify_url)) {
                if (!$this->isUrlStrictlyValid($notify_url)) {
                    throw new Exception("Invalid notification URL format.");
                }
                // Disallow usage of example.com as a placeholder
                $parsedUrl = parse_url($notify_url);
                if (isset($parsedUrl['host']) && strpos($parsedUrl['host'], 'example.com') !== false) {
                    throw new Exception("The notify_url cannot point to example.com. Please use a valid URL.");
                }
            }
            $body = [
                "operator"     => $data['operator'],
                "country"      => $data['country'],
                "phone_number" => $data['phone'],
                "amount"       => $data['amount'],
                "currency"     => $data['currency'],
                "order_id"     => $this->formParameters['order_id'] ?? 'modalID' . md5(implode('', [$data['country'], $data['currency'], $data['amount'], $data['phone'], $random , $timestamp])),
                "merchant_key" => self::$config['merchantKey'],
                "reference_id" => $this->formParameters['reference_id'] ?? $data['reference_id'] ?? 'Modal-' . implode('-', [$data['country'], $data['currency'], $data['operator']]),
                "lang"         => self::$config['lang'],
                "notify_url"   => $notify_url
            ];
        
            if (!empty($data['otp_code'])) {
                $body["otp_code"] = $data['otp_code'];
            }

            $ch = $this->createCurlHandle(
                self::$config['baseUrl'] . '/v1/pay/payin',
                true,
                [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    
            return $this->executeRequest($ch);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function walletOtp($data) {
        try {
            $this->checkConfigParam();
        
            $token = $this->refreshTokenIfNeeded();
            if (!$token) {
                return ['httpCode' => 401, 'response' => ['error' => 'Authentication failed']];
            }
            $data = is_string($data) ? json_decode($data, true) : $data;
            $data['amount'] = floor($data['amount']);
            $body = [
                "operator"     => $data['operator'],
                "country"      => $data['country'],
                "phone_number" => $data['phone'],
                "amount"       => floor($data['amount']),
                "currency"     => $data['currency'],
                "merchant_key" => self::$config['merchantKey']
            ];
            $ch = $this->createCurlHandle(
                self::$config['baseUrl'] . '/v1/pay/otp',
                true,
                [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            return $this->executeRequest($ch);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function status($order_id) {
        try{
            $token = $this->refreshTokenIfNeeded();
            if (!$token) {
                return ['httpCode' => 401, 'response' => ['error' => 'Authentication failed']];
            }
            $ch = $this->createCurlHandle(
                self::$config['baseUrl'] . '/v1/status?order_id=' . urlencode($order_id),
                false,
                [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ]
            );
            return $this->executeRequest($ch);
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function afpDebug($message){
        error_log($message);
    }

}

try {
    $rawData = file_get_contents('php://input');
    $_POST = json_decode($rawData, true);
    TransactionConfig::initialize();
    $mbsError = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['AfpModalPost'],$_POST['baseUrl'])){
            try{
                if($f=(new AfribapayClass())->sdkFirstAuthentication()) return; 
                $mbsError = "Invalid authentication credentials.";
            }catch(Exception $e){
                throw new Exception($e->getMessage());
            }
        }else{
            $rawData = file_get_contents('php://input');
            $request = json_decode($rawData, true);
            $_POST = $request;
            if ($request === null) {
                $mbsError = 'Invalid JSON received.';
            }
            if (isset($request['action'])) {;
                switch ($request['action']) {
                    case 'checkout':
                        echo (new AfribapayClass())->getCheckout($request);
                        break;
                    case 'status':
                        echo (new AfribapayClass())->getStatus($request['order_id'] ?? throw new InvalidArgumentException('order_id is required.'));
                        break;
                    case 'walletOtp':
                        echo (new AfribapayClass())->sendWalletOtp($request);
                        break;
                    default:
                        $mbsError = 'Invalid action.';
                }
            } else {
                $mbsError = 'No action specified.'; 
            }
        }
    }
} catch(Exception $e){
    $mbsError = $e->getMessage();
}
