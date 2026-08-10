<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function index()
    {
        $api_key = "AQ.Ab8RN6IcT2se2Aq2GHwIJW29ny8rWbMGTWFev7IAi7g8XW7AOA"; // ตัวเดียวกับ test.php

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=".$api_key;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Hello"
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);

        echo "<pre>";
        echo "HTTP : ".curl_getinfo($ch, CURLINFO_HTTP_CODE)."\n\n";
        echo $response;

        curl_close($ch);
    }
}