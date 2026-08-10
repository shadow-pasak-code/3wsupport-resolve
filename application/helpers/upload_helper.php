<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Upload_helper — helper สำหรับ upload รูปภาพ
 * ใช้ CI3 Upload class
 */

/**
 * อัปโหลดรูปภาพ
 *
 * @param string $field_name  ชื่อ input file
 * @param string $upload_path โฟลเดอร์ปลายทาง เช่น 'uploads/avatars/technicians/'
 * @param int    $max_size    KB สูงสุด default 2MB
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function do_image_upload($field_name, $upload_path, $max_size = 2048)
{
    $CI =& get_instance();

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    $full_path = FCPATH . $upload_path;
    if (!is_dir($full_path)) {
        mkdir($full_path, 0755, TRUE);
    }

    $config = [
        'upload_path'   => $full_path,
        'allowed_types' => 'jpg|jpeg|png|webp',
        'max_size'      => $max_size,
        'max_width'     => 0,
        'max_height'    => 0,
        'encrypt_name'  => TRUE,
    ];

    $CI->load->library('upload', $config);

    if (!$CI->upload->do_upload($field_name)) {
        return [
            'success'  => FALSE,
            'filename' => NULL,
            'error'    => $CI->upload->display_errors('', ''),
        ];
    }

    $file_data = $CI->upload->data();
    return [
        'success'  => TRUE,
        'filename' => $file_data['file_name'],
        'error'    => NULL,
    ];
}

/**
 * ลบรูปภาพเก่า
 *
 * @param string $filename  ชื่อไฟล์
 * @param string $upload_path โฟลเดอร์
 */
function delete_image($filename, $upload_path)
{
    if (empty($filename)) return;
    $full_path = FCPATH . $upload_path . $filename;
    if (file_exists($full_path)) {
        unlink($full_path);
    }
}

/**
 * แสดง URL รูปภาพ ถ้าไม่มีให้แสดง placeholder
 *
 * @param string $filename
 * @param string $upload_path
 * @param string $placeholder URL รูป default
 */
function image_url($filename, $upload_path, $placeholder = NULL)
{
    if (empty($filename)) {
        return $placeholder ?? base_url('assets/img/placeholder.png');
    }
    return base_url($upload_path . $filename);
}
