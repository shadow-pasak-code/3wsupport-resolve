<?php

$api_key = "AQ.Ab8RN6IcT2se2Aq2GHwIJW29ny8rWbMGTWFev7IAi7g8XW7AOA";

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

$ch = curl_init($url);

curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>[
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS=>json_encode($data)
]);

echo curl_exec($ch);

curl_close($ch);