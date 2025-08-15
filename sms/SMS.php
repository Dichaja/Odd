<?php
require_once __DIR__ . '/../config/config.php';

class SMS
{
    private static $api_id = '';
    private static $api_password = '';
    private static $sender_id = '';
    private static $api_url = '';

    public static function initFromConfig()
    {
        if (defined('SMS_PROVIDER') && SMS_PROVIDER === 'speedamobile') {
            self::$api_id = defined('SPEEDMOBILE_API_ID') ? (string) SPEEDMOBILE_API_ID : '';
            self::$api_password = defined('SPEEDMOBILE_API_PASSWORD') ? (string) SPEEDMOBILE_API_PASSWORD : '';
            self::$sender_id = defined('SPEEDMOBILE_SENDER_ID') ? (string) SPEEDMOBILE_SENDER_ID : '';
            self::$api_url = defined('SPEEDMOBILE_API_URL') ? trim((string) SPEEDMOBILE_API_URL) : '';
        }
    }

    public static function send($phone_number, $message, $validate = true)
    {
        $provider = defined('SMS_PROVIDER') ? SMS_PROVIDER : '';
        if ($provider === 'collecto') {
            try {
                $mgr = new CollectoSMSManager();
                return $mgr->sendSingle($phone_number, $message);
            } catch (Throwable $e) {
                return ['success' => false, 'error' => $e->getMessage(), 'message' => null];
            }
        }

        self::initFromConfig();

        if (self::$api_url === '' || self::$api_id === '' || self::$api_password === '' || self::$sender_id === '') {
            return ['success' => false, 'error' => 'Speedamobile config incomplete', 'message' => null];
        }

        if ($validate) {
            $phone_number = self::validatePhoneNumber($phone_number);
            if (!$phone_number) {
                return ['success' => false, 'error' => 'Invalid phone number format', 'message' => null];
            }
        }

        $data = self::prepareRequestData($phone_number, $message);
        return self::sendRequest($data);
    }

    public static function sendBulk($phone_numbers, $message, $validate = true)
    {
        $results = [];
        $success_count = 0;
        $failure_count = 0;

        foreach ($phone_numbers as $phone_number) {
            $result = self::send($phone_number, $message, $validate);
            $results[] = ['phone_number' => $phone_number, 'result' => $result];
            if (!empty($result['success']))
                $success_count++;
            else
                $failure_count++;
        }

        return [
            'success' => $success_count > 0 && $failure_count === 0,
            'total' => count($phone_numbers),
            'success_count' => $success_count,
            'failure_count' => $failure_count,
            'results' => $results
        ];
    }

    public static function validatePhoneNumber($phone_number)
    {
        $phone_number = preg_replace('/\D/', '', (string) $phone_number);
        if (substr($phone_number, 0, 3) === '256') {
            if (strlen($phone_number) !== 12)
                return false;
            return '+' . $phone_number;
        }
        if (substr($phone_number, 0, 1) === '0')
            $phone_number = substr($phone_number, 1);
        if (strlen($phone_number) === 9)
            return '+256' . $phone_number;
        return false;
    }

    private static function prepareRequestData($phone_number, $message)
    {
        return [
            'api_id' => self::$api_id,
            'api_password' => self::$api_password,
            'sms_type' => 'P',
            'encoding' => 'T',
            'sender_id' => self::$sender_id,
            'phonenumber' => $phone_number,
            'textmessage' => $message,
            'templateid' => 'null',
            'V1' => 'null',
            'V2' => 'null',
            'V3' => 'null',
            'V4' => 'null',
            'V5' => 'null',
        ];
    }

    private static function sendRequest($data)
    {
        $url = self::$api_url;
        if ($url === '') {
            return ['success' => false, 'error' => 'EMPTY_URL', 'message' => null];
        }

        $payload = json_encode($data);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Zzimba/1.0',
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => $err, 'message' => null, 'http_code' => $code];
        }

        $data = json_decode((string) $resp, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $ok = isset($data['status']) ? ($data['status'] === 'S' || $data['status'] === 'success' || $data['status'] === 1) : ($code >= 200 && $code < 300);
            return ['success' => $ok, 'error' => $ok ? null : 'API returned error status', 'message' => $resp, 'data' => $data, 'http_code' => $code];
        }

        $ok = $code >= 200 && $code < 300;
        return ['success' => $ok, 'error' => $ok ? null : 'Non-JSON response', 'message' => $resp, 'http_code' => $code];
    }

    public static function setCredentials($api_id, $api_password, $sender_id)
    {
        self::$api_id = (string) $api_id;
        self::$api_password = (string) $api_password;
        self::$sender_id = (string) $sender_id;
    }

    public static function setApiUrl($url)
    {
        self::$api_url = trim((string) $url);
    }
}

SMS::initFromConfig();

class CollectoSMSManager
{
    private string $username;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->username = defined('CISSY_USERNAME') ? (string) CISSY_USERNAME : '';
        $this->apiKey = defined('CISSY_API_KEY') ? (string) CISSY_API_KEY : '';
        $this->baseUrl = defined('CISSY_COLLECTO_BASE_URL') ? rtrim((string) CISSY_COLLECTO_BASE_URL, '/') : '';
        if ($this->username === '' || $this->apiKey === '' || $this->baseUrl === '') {
            throw new Exception('Collecto config incomplete');
        }
    }

    public function sendSingle(string $phone, string $message): array
    {
        $res = $this->CollectoSendSMS([$phone], $message);
        $ok = !empty($res['success']) && !empty($res['results'][0]);
        return [
            'success' => $ok,
            'error' => $ok ? null : 'Failed sending via Collecto',
            'message' => $ok ? json_encode($res['results'][0]) : null,
            'data' => $res
        ];
    }

    public function CollectoSendSMS(array $rawPhones, string $message, int $costEach = 40): array
    {
        $valid = [];
        $invalid = [];

        foreach ($rawPhones as $p) {
            $raw = is_array($p) ? ($p['phone'] ?? '') : $p;
            $raw = is_string($raw) ? trim($raw) : '';
            $normalized = $this->normalizeUgNumber($raw);
            if ($normalized !== null) {
                $valid[] = ['phone' => $normalized, 'original' => $raw];
            } else {
                if ($raw !== '')
                    $invalid[] = $raw;
            }
        }

        if (empty($valid)) {
            $detail = $invalid ? (' Offenders: ' . implode(', ', $invalid)) : '';
            throw new Exception('No valid phone numbers found.' . $detail);
        }

        $results = [];
        foreach ($valid as $entry) {
            $payload = [
                'phone' => $entry['phone'],
                'message' => $message,
                'reference' => uniqid('', true),
            ];
            $res = $this->makeRequest('sendSingleSMS', $payload);
            $results[] = array_merge(
                ['phone' => $entry['original'], 'normalized' => $entry['phone']],
                $res['data'] ?? $res
            );
        }

        return ['success' => true, 'results' => $results, 'invalid' => $invalid];
    }

    private function normalizeUgNumber(string $input): ?string
    {
        if ($input === '')
            return null;
        $input = preg_replace('/[^\d+]/', '', $input);
        if (strpos($input, '+') === 0)
            $input = substr($input, 1);
        if (preg_match('/^2567\d{8}$/', $input))
            return $input;
        if (preg_match('/^0?7\d{8}$/', $input)) {
            if ($input[0] === '0')
                $input = substr($input, 1);
            return '256' . $input;
        }
        if (preg_match('/^7\d{8}$/', $input))
            return '256' . $input;
        return null;
    }

    private function makeRequest(string $endpoint, array $payload): array
    {
        if ($this->baseUrl === '' || $this->username === '' || $this->apiKey === '') {
            throw new Exception('Collecto config incomplete');
        }
        $endpoint = ltrim($endpoint, '/');
        $url = "{$this->baseUrl}/{$this->username}/{$endpoint}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-api-key: ' . $this->apiKey],
            CURLOPT_USERAGENT => 'Zzimba/1.0',
            CURLOPT_TIMEOUT => 30
        ]);
        $resp = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error)
            throw new Exception("cURL error: $error");
        if ($resp === false || $resp === '')
            throw new Exception('Empty response from Collecto');

        $json = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE)
            throw new Exception("Invalid JSON response: $resp");

        if ($code < 200 || $code >= 300)
            throw new Exception("Collecto HTTP $code: $resp");
        return $json;
    }
}
