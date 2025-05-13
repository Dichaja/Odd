<?php
class SMS
{
    private static $api_id = 'API57063841167';
    private static $api_password = 'sample123';
    private static $sender_id = 'bulksms';

    private static $api_url = 'http://apidocs.speedamobile.com/api/SendSMS';

    public static function send($phone_number, $message, $validate = true)
    {
        if ($validate) {
            $phone_number = self::validatePhoneNumber($phone_number);
            if (!$phone_number) {
                return [
                    'success' => false,
                    'error' => 'Invalid phone number format',
                    'message' => null
                ];
            }
        }

        $data = self::prepareRequestData($phone_number, $message);

        $response = self::sendRequest($data);

        return $response;
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
                'result' => $result
            ];

            if ($result['success']) {
                $success_count++;
            } else {
                $failure_count++;
            }
        }

        return [
            'success' => $success_count > 0,
            'total' => count($phone_numbers),
            'success_count' => $success_count,
            'failure_count' => $failure_count,
            'results' => $results
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
        $data_string = json_encode($data);

        $context_options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
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
                    'error' => 'API request failed',
                    'message' => null
                ];
            }

            $response_data = json_decode($result, true);

            $is_success = isset($response_data['status']) && $response_data['status'] === 'S';

            return [
                'success' => $is_success,
                'error' => $is_success ? null : 'API returned error status',
                'message' => $result,
                'data' => $response_data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => null
            ];
        }
    }

    public static function setCredentials($api_id, $api_password, $sender_id)
    {
        self::$api_id = $api_id;
        self::$api_password = $api_password;
        self::$sender_id = $sender_id;
    }

    public static function setApiUrl($url)
    {
        self::$api_url = $url;
    }
}