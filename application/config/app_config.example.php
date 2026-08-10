<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| LINE Messaging API
|--------------------------------------------------------------------------
| คัดลอกไฟล์นี้เป็น app_config.php แล้วใส่ค่าจริง — ห้าม commit app_config.php (อยู่ใน .gitignore แล้ว)
*/
$config['line_channel_access_token'] = '';
$config['line_channel_secret']        = '';
$config['line_api_url']               = 'https://api.line.me/v2/bot/message/push';

/*
|--------------------------------------------------------------------------
| OpenRouter API (Gemini)
|--------------------------------------------------------------------------
*/
$config['openrouter_api_key'] = '';
$config['openrouter_api_url'] = 'https://openrouter.ai/api/v1/chat/completions';
$config['openrouter_model'] = 'google/gemini-2.5-flash';

/*
|--------------------------------------------------------------------------
| Session / Auth
|--------------------------------------------------------------------------
*/
$config['session_expiration'] = 7200; // 2 ชั่วโมง

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/
$config['role_admin']       = 'admin';
$config['role_technician']  = 'technician';
$config['role_partner']     = 'partner';

/*
|--------------------------------------------------------------------------
| ข้อมูลบริษัท (สำหรับใบเสนอราคา)
|--------------------------------------------------------------------------
*/
$config['company_name']    = '';
$config['company_address'] = '';
$config['company_phone']   = '';
$config['company_email']   = '';
$config['company_tax_id']  = '';
