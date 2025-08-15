<?php
require_once __DIR__ . '/../config/config.php';

class SMS
{
    private static $api_id;
    private static $api_password;
    private static $sender_id;
    private static $api_url;

    // Initialize with config values
    public static function initFromConfig()
    {
        if (defined('SPEEDMOBILE_API_ID')) {
            self::$api_id       = SPEEDMOBILE_API_ID;
            self::$api_password = SPEEDMOBILE_API_PASSWORD;
            self::$sender_id    = SPEEDMOBILE_SENDER_ID;
            self::$api_url      = SPEEDMOBILE_API_URL;
        }
    }

    public static function send($phone_number, $message, $validate = true)
    {
        if ($validate) {
            $phone_number = self::validatePhoneNumber($phone_number);
            if (!$phone_number) {
                return [
                    'success' => false,
                    'error'   => 'Invalid phone number format',
                    'message' => null
                ];
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
            $results[] = [
                'phone_number' => $phone_number,
                'result'       => $result
            ];

            if ($result['success']) {
                $success_count++;
            } else {
                $failure_count++;
            }
        }

        return [
            'success'        => $success_count > 0,
            'total'          => count($phone_numbers),
            'success_count'  => $success_count,
            'failure_count'  => $failure_count,
            'results'        => $results
        ];
    }

    public static function validatePhoneNumber($phone_number)
    {
        $phone_number = preg_replace('/\D/', '', $phone_number);

        if (substr($phone_number, 0, 3) === '256') {
            if (strlen($phone_number) !== 12) {
                return false;
            }
            return '+' . $phone_number;
        }

        if (substr($phone_number, 0, 1) === '0') {
            $phone_number = substr($phone_number, 1);
        }

        if (strlen($phone_number) === 9) {
            return '+256' . $phone_number;
        }

        return false;
    }

    private static function prepareRequestData($phone_number, $message)
    {
        return [
            'api_id'      => self::$api_id,
            'api_password'=> self::$api_password,
            'sms_type'    => 'P',
            'encoding'    => 'T',
            'sender_id'   => self::$sender_id,
            'phonenumber' => $phone_number,
            'textmessage' => $message,
            'templateid'  => 'null',
            'V1'          => 'null',
            'V2'          => 'null',
            'V3'          => 'null',
            'V4'          => 'null',
            'V5'          => 'null',
        ];
    }

    private static function sendRequest($data)
    {
        $data_string = json_encode($data);

        $context_options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => $data_string,
                'timeout' => 30,
            ],
        ];

        $context = stream_context_create($context_options);

        try {
            $result = @file_get_contents(self::$api_url, false, $context);

            if ($result === false) {
                return [
                    'success' => false,
                    'error'   => 'API request failed',
                    'message' => null
                ];
            }

            $response_data = json_decode($result, true);
            $is_success    = isset($response_data['status']) && $response_data['status'] === 'S';

            return [
                'success' => $is_success,
                'error'   => $is_success ? null : 'API returned error status',
                'message' => $result,
                'data'    => $response_data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => null
            ];
        }
    }

    public static function setCredentials($api_id, $api_password, $sender_id)
    {
        self::$api_id       = $api_id;
        self::$api_password = $api_password;
        self::$sender_id    = $sender_id;
    }

    public static function setApiUrl($url)
    {
        self::$api_url = $url;
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
        $this->username = CISSY_USERNAME;
        $this->apiKey = CISSY_API_KEY;
        $this->baseUrl = rtrim(CISSY_COLLECTO_BASE_URL, '/');
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
                'reference' => uniqid(),
            ];
            $res = $this->makeRequest('sendSingleSMS', $payload);
            $results[] = array_merge(
                ['phone' => $entry['original'], 'normalized' => $entry['phone']],
                $res['data'] ?? []
            );
        }

        return [
            'success' => true,
            'results' => $results,
            'invalid' => $invalid
        ];
    }

    private function normalizeUgNumber(string $input): ?string
    {
        if ($input === '') {
            return null;
        }

        $input = preg_replace('/[^\d+]/', '', $input);

        if (strpos($input, '+') === 0) {
            $input = substr($input, 1);
        }

        if (preg_match('/^2567\d{8}$/', $input)) {
            return $input;
        }

        if (preg_match('/^0?7\d{8}$/', $input)) {
            if ($input[0] === '0') {
                $input = substr($input, 1);
            }
            return '256' . $input;
        }

        if (preg_match('/^7\d{8}$/', $input)) {
            return '256' . $input;
        }

        if (preg_match('/^\d{12}$/', $input)) {
            return null;
        }

        return null;
    }

    private function makeRequest(string $endpoint, array $payload): array
    {
        $url = "{$this->baseUrl}/{$this->username}/{$endpoint}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
            ],
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $resp = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: $error");
        }
        http_response_code($code);
        $json = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: $resp");
        }
        return $json;
    }
}