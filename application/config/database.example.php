<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// คัดลอกไฟล์นี้เป็น database.php แล้วใส่ค่าจริงของเครื่อง/เซิร์ฟนี้ — ห้าม commit database.php (อยู่ใน .gitignore แล้ว)

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'      => '',
    'hostname' => 'localhost',
    'username' => '',       // ใส่ username จริง
    'password' => '',       // ใส่ password จริง
    'database' => '',       // ใส่ชื่อ database จริง
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE,
);
