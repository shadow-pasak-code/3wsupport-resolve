<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user = $this->db->get_where('users', ['id' => $this->current_user['id']])->row();
        $this->render('profile', ['user' => $user]);
    }

    public function update()
    {
        if ($this->input->method() !== 'post') redirect(base_url('admin/profile'));

        $name = $this->input->post('name', TRUE);
        $username = $this->input->post('username', TRUE);
        $existing = $this->db->where('username', $username)->where('id !=', $this->current_user['id'])->get('users')->row();
        if ($existing) {
            $this->session->set_flashdata('error', 'Username นี้ถูกใช้งานแล้ว');
            redirect(base_url('admin/profile'));
        }
        $update = ['name' => $name, 'username' => $username];

        $password = $this->input->post('password');
        $confirm  = $this->input->post('password_confirm');

        if (!empty($password)) {
            if ($password !== $confirm) {
                $this->session->set_flashdata('error', 'รหัสผ่านไม่ตรงกัน');
                redirect(base_url('admin/profile'));
            }
            if (strlen($password) < 6) {
                $this->session->set_flashdata('error', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                redirect(base_url('admin/profile'));
            }
            $update['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db->update('users', $update, ['id' => $this->current_user['id']]);

        // อัปเดต session name
        $user_data = $this->session->userdata('user');
        $user_data['name'] = $name;
        $this->session->set_userdata('user', $user_data);

        $this->session->set_flashdata('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
        redirect(base_url('admin/profile'));
    }
}
