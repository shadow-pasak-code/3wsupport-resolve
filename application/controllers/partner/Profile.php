<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends Partner_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $user    = $this->db->get_where('users',    ['id' => $this->current_user['id']])->row();
        $partner = $this->db->get_where('partners', ['id' => $this->current_user['ref_id']])->row();
        $this->render('profile', ['user' => $user, 'partner' => $partner]);
    }

    public function update()
    {
        if ($this->input->method() !== 'post') redirect(base_url('partner/profile'));

        $name    = $this->input->post('company_name', TRUE);
        $phone   = $this->input->post('phone', TRUE);
        $contact = $this->input->post('contact_name', TRUE);

        // เช็ค username ซ้ำ
        $username = $this->input->post('username', TRUE);
        $existing = $this->db
            ->where('username', $username)
            ->where('id !=', $this->current_user['id'])
            ->get('users')->row();
        if ($existing) {
            $this->session->set_flashdata('error', 'Username นี้ถูกใช้งานแล้ว กรุณาเลือก Username อื่น');
            redirect(base_url('partner/profile'));
        }

        // อัปโหลดโลโก้
        $partner = $this->db->get_where('partners', ['id' => $this->current_user['ref_id']])->row();
        $logo    = $partner->logo;
        if (!empty($_FILES['logo']['name'])) {
            $this->load->helper('upload');
            $result = do_image_upload('logo', 'uploads/avatars/partners/');
            if (!$result['success']) {
                $this->session->set_flashdata('error', $result['error']);
                redirect(base_url('partner/profile'));
            }
            delete_image($partner->logo, 'uploads/avatars/partners/');
            $logo = $result['filename'];
        }

        // อัปเดต users
        $update = ['name' => $name, 'username' => $username];
        $password = $this->input->post('password');
        $confirm  = $this->input->post('password_confirm');
        if (!empty($password)) {
            if ($password !== $confirm) {
                $this->session->set_flashdata('error', 'รหัสผ่านไม่ตรงกัน');
                redirect(base_url('partner/profile'));
            }
            if (strlen($password) < 6) {
                $this->session->set_flashdata('error', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                redirect(base_url('partner/profile'));
            }
            $update['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $this->db->update('users', $update, ['id' => $this->current_user['id']]);

        // อัปเดต partners
        $this->db->update('partners', [
            'company_name' => $name,
            'contact_name' => $contact,
            'phone'        => $phone,
            'logo'         => $logo,
        ], ['id' => $this->current_user['ref_id']]);

        // อัปเดต session
        $user_data = $this->session->userdata('user');
        $user_data['name'] = $name;
        $this->session->set_userdata('user', $user_data);

        $this->session->set_flashdata('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
        redirect(base_url('partner/profile'));
    }
}