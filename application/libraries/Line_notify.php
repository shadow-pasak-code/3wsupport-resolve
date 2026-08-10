<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Line_notify
{
    protected $CI;
    protected $token;
    protected $api_url;

    public function __construct()
    {
        $this->CI      = &get_instance();
        $this->token   = $this->CI->config->item('line_channel_access_token');
        $this->api_url = $this->CI->config->item('line_api_url');
    }

    /**
     * Push text message ไปยัง line_uid ของลูกค้า
     */
    public function push($line_uid, $message)
    {
        if (empty($line_uid)) return false;

        $body = json_encode([
            'to'       => $line_uid,
            'messages' => [
                ['type' => 'text', 'text' => $message]
            ]
        ]);

        $ch = curl_init($this->api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        log_message('info', "LINE push to {$line_uid}: HTTP {$http_code}");
        return $http_code === 200;
    }

    /**
     * Push ข้อความอัพเดทความคืบหน้า พร้อมรูปภาพ (ถ้ามี) ในการ push ครั้งเดียว
     * $image_urls ต้องเป็น URL ที่ LINE server เข้าถึงได้จริง (https สาธารณะ) — บนเครื่อง dev local ข้อความจะส่งได้ แต่รูปอาจโหลดไม่ขึ้นเพราะ localhost เข้าถึงจากภายนอกไม่ได้
     */
    public function push_update($line_uid, $text, $image_urls = [])
    {
        if (empty($line_uid)) return false;

        $messages = [];
        if (!empty($text)) {
            $messages[] = ['type' => 'text', 'text' => $text];
        }
        // LINE จำกัดสูงสุด 5 ข้อความต่อการ push 1 ครั้ง กันพื้นที่ไว้ให้ข้อความตัวหนังสือ 1 ที่แล้ว
        foreach (array_slice($image_urls, 0, 4) as $url) {
            $messages[] = [
                'type'               => 'image',
                'originalContentUrl' => $url,
                'previewImageUrl'    => $url,
            ];
        }
        if (empty($messages)) return false;

        $body = json_encode(['to' => $line_uid, 'messages' => $messages]);

        $ch = curl_init($this->api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        log_message('info', "LINE push_update to {$line_uid}: HTTP {$http_code}");
        return $http_code === 200;
    }

    /**
     * Push Flex Message (สำหรับ Quotation / สรุปงาน)
     */
    public function push_flex($line_uid, $alt_text, $flex_contents)
    {
        if (empty($line_uid)) return false;

        $body = json_encode([
            'to'       => $line_uid,
            'messages' => [
                [
                    'type'     => 'flex',
                    'altText'  => $alt_text,
                    'contents' => $flex_contents,
                ]
            ]
        ]);

        $ch = curl_init($this->api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        log_message('error', 'PUSH_FLEX HTTP=' . $http_code);
        log_message('error', 'PUSH_FLEX BODY=' . $body);
        log_message('error', 'PUSH_FLEX RESPONSE=' . $response);
        if ($curl_err) log_message('error', 'PUSH_FLEX CURL_ERR=' . $curl_err);

        return $http_code === 200;
    }
}
