<?php

define("CACHE_EXPIRY", 900); // 15 minutes
define("DATA_COOKIE_NAME", 'afpData');
define("TRANSID_COOKIE_NAME", 'afpId');
define("CACHE_EXTENSION", '.afp.cache'); // 15 minutes
define("AFP_ROOTPATH", dirname(__FILE__, 2)); 
define("CUSTOMER_CONFIG_PATH", dirname(__FILE__, 2) . '/customer_config.inc.php');

class TransactionConfig {
    private static $configurations = [];
    private static $parameters = [];
    private static $transactionId = null;
    private static $memoryMode = false;
    private static $getCacheDirectory = null;

    private static function isNewTransaction(): bool {
        $isNew = (!empty($_POST['baseUrl']) && !empty($_POST['AfpModalPost']));
        return $isNew;
    }

    public static function initialize(): void {
        self::$getCacheDirectory = self::getCacheDirectory();
        self::$transactionId = $_COOKIE[TRANSID_COOKIE_NAME] ?? null;
        self::$memoryMode = self::memoryMode();
        if (self::isNewTransaction()) {
            self::clearCacheAndCookies(self::$memoryMode);
            self::$transactionId = uniqid();
            self::$configurations = [
                "baseUrl"     => !empty($_POST['baseUrl']) ? urldecode($_POST['baseUrl']) : null
            ];
            self::$parameters = [
                'country'       => !empty($_POST['country']) ? urldecode($_POST['country']) : null,
                'currency'      => !empty($_POST['currency']) ? urldecode($_POST['currency']) : null,
                'amount'        => (!empty($_POST['amount']) && $_POST['amount'] > 0) ? floor($_POST['amount']) : null,
                'order_id'      => !empty($_POST['order_id']) ? urldecode($_POST['order_id']) : null,
                'reference_id'  => !empty($_POST['reference_id']) ? urldecode($_POST['reference_id']) : null,
                'showCountries' => isset($_POST['showCountries']) ? $_POST['showCountries'] : true,
                'notify_url'    => isset($_POST['notify_url']) ? $_POST['notify_url'] : null
            ];
            $dataToCache = ['configurations' => self::$configurations, 'parameters' =>  self::$parameters];
            if (!self::saveCachedConfig($dataToCache, self::$transactionId, self::$memoryMode)) {
            }
            setcookie(TRANSID_COOKIE_NAME, self::$transactionId, time() + CACHE_EXPIRY, '/');
         } else {
            if (!self::$transactionId) {
                return;
            }
            $cachedConfig = self::loadCachedConfig(self::$transactionId, self::$memoryMode);
            if ($cachedConfig) {
                self::$configurations = $cachedConfig['configurations'] ?? [];
                self::$parameters = $cachedConfig['parameters'] ?? [];
             } else {
            }
        }
    }

    private static function loadCachedConfig(string $encryptionKey, bool $useCache = false): ?array {
        if ($useCache && $encryptionKey !== '') {
            $cacheFile = self::$getCacheDirectory . '/' . $encryptionKey . CACHE_EXTENSION;
            if (file_exists($cacheFile)) {
                $encryptedData = file_get_contents($cacheFile);
            } else {
                return null;
            }
        } else {
            if (isset($_COOKIE[DATA_COOKIE_NAME]) && $_COOKIE[DATA_COOKIE_NAME] !== null) {
                $encryptedData = $_COOKIE[DATA_COOKIE_NAME];
            } else {
                return null;
            }
        }
        $configData = self::decryptData($encryptedData, $encryptionKey);
        if ($configData === false) {
            return null;
        }
        $config = json_decode($configData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $config;
    }

    private static function saveCachedConfig(array $config, string $encryptionKey, bool $useCache = false): bool {
        $configData = json_encode($config, JSON_PRETTY_PRINT);
        if ($configData === false) {
            return false;
        }
        $encryptedData = self::encryptData($configData, $encryptionKey);
        if ($encryptedData === false) {
            return false;
        }
        if ($useCache && $encryptionKey !== '') {
            $cacheDir = self::$getCacheDirectory;
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            $cacheFile = $cacheDir . '/' . $encryptionKey . CACHE_EXTENSION;
            return file_put_contents($cacheFile, $encryptedData) !== false;
        } else {
            setcookie(DATA_COOKIE_NAME, $encryptedData, time() + CACHE_EXPIRY, '/');
            return true;
        }
    }

    private static function encryptData(string $data, string $encryptionKey): string|false {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $options = 0;
        $encrypted = openssl_encrypt($data, $cipher, $encryptionKey, $options, $iv);
        if ($encrypted === false) {
            return false;
        }
        return base64_encode($iv . $encrypted);
    }

    private static function decryptData(string $encryptedData, string $encryptionKey): string|false {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $options = 0;
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, $ivlen);
        $encrypted = substr($data, $ivlen);
        $decrypted = openssl_decrypt($encrypted, $cipher, $encryptionKey, $options, $iv);
        if ($decrypted === false) {
            return false;
        }
        return $decrypted;
    }

    private static function clearCacheAndCookies(bool $useCache = false): void {
        $cookiesToDelete = [TRANSID_COOKIE_NAME, DATA_COOKIE_NAME];
        foreach ($cookiesToDelete as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                unset($_COOKIE[$cookieName]);
                setcookie($cookieName, '', time() - CACHE_EXPIRY, '/');
            }
        }
        if($useCache){
            $filePath = self::$getCacheDirectory . "/".self::$transactionId. CACHE_EXTENSION;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            self::cleanExpiredCaches();
        }

    }

    public static function getConfigurations(): array {
        return array_merge(self::$configurations, self::extraData('config'));
    }

    public static function getParameters(): array {
        return array_merge(self::$parameters, self::extraData('params'));
    }

    public static function getCacheDirectory(): string {
        return self::extraData('cache')['cacheDirectory'];
    }

    public static function isUseCache():bool {
        return self::$memoryMode;
    }

    protected static function extraData(string $param): array {
        $customer_config_path = CUSTOMER_CONFIG_PATH;
        if (!file_exists($customer_config_path)) {
            throw new AfribaPayException("The customer_config.inc.php file was not found at: " . $customer_config_path);
        }
        $params = require $customer_config_path;
        if (!is_array($params)) {
            throw new AfribaPayException("Invalid customer_config.inc.php file");
        }
        return match ($param) { 
            'config' => [
                'apiUser'     => $params['apiUser'] ?? '',
                'apiKey'      => $params['apiKey'] ?? '',
                'merchantKey' => $params['merchantKey'] ?? '',
                'lang'        => $params['lang'] ?? 'fr',
                'notify_url'  => $params['notify_url'] ?? '',
            ],
            'params' => [
                'agent_id'    => $params['agent_id'] ?? '',
                'lang'        => $params['lang'] ?? 'fr',
                'checkoutPath'=> $params['checkoutPath'] ?? '',
            ],
            'cache' => [
                'cacheDirectory' => $params['cacheDirectory'] ?? sys_get_temp_dir(),
            ],
            default => throw new AfribaPayException("Invalid parameter requested in extraData"),
        };
    }

    protected static function memoryMode(): string {
        $customer_config_path = CUSTOMER_CONFIG_PATH;
        if (!file_exists($customer_config_path)) {
            throw new AfribaPayException("The customer_config.inc.php file was not found at: " . $customer_config_path);
        }
        $params = require $customer_config_path;
        if (!is_array($params)) {
            throw new AfribaPayException("Invalid customer_config.inc.php file");
        }
        return ($params['useCacheFolder'] == 'true') ? true : false;
    }

    private static function cleanExpiredCaches(): void {
        foreach (glob(self::$getCacheDirectory . "/*".CACHE_EXTENSION) as $file) {
            if (filemtime($file) < time() - CACHE_EXPIRY) {
                unlink($file);
            }
        }
    }
}
