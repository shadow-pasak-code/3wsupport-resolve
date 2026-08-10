<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Technicians extends Admin_Controller
{
    protected $upload_path = 'uploads/avatars/technicians/';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('upload');
    }

    public function index()
    {
        $technicians = $this->db
            ->select('t.*, u.username')
            ->from('technicians t')
            ->join('users u', 'u.ref_id = t.id AND u.role = "technician"', 'left')
            ->get()->result();

        $this->render('technicians/index', ['technicians' => $technicians]);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $avatar = null;
            if (!empty($_FILES['avatar']['name'])) {
                $result = do_image_upload('avatar', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect('admin/technicians/add');
                }
                $avatar = $result['filename'];
            }

            $this->db->insert('technicians', [
                'name'       => $this->input->post('name', TRUE),
                'phone'      => $this->input->post('phone', TRUE),
                'email'      => $this->input->post('email', TRUE),
                'avatar'     => $avatar,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $tech_id = $this->db->insert_id();

            $this->db->insert('users', [
                'username'      => $this->input->post('username', TRUE),
                'password_hash' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'name'          => $this->input->post('name', TRUE),
                'role'          => 'technician',
                'ref_id'        => $tech_id,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            $this->session->set_flashdata('success', 'เพิ่มช่างเรียบร้อยแล้ว');
            redirect('admin/technicians');
        }

        $this->render('technicians/form', []);
    }

    public function edit($id)
    {
        $tech = $this->db
            ->select('t.*, u.username, u.id as user_id')
            ->from('technicians t')
            ->join('users u', 'u.ref_id = t.id AND u.role = "technician"', 'left')
            ->where('t.id', $id)
            ->get()->row();
        if (!$tech) show_404();

        if ($this->input->method() === 'post') {

            // เช็ค username ซ้ำก่อนทำอะไรทั้งนั้น
            $new_username = $this->input->post('username', TRUE);
            $dup = $this->db
                ->where('username', $new_username)
                ->where('id !=', $tech->user_id)
                ->count_all_results('users');
            if ($dup > 0) {
                $this->session->set_flashdata('error', "Username '{$new_username}' ถูกใช้งานแล้ว กรุณาเลือก Username อื่น");
                redirect(base_url('admin/technicians/edit/' . $id));
            }

            $avatar = $tech->avatar;
            if (!empty($_FILES['avatar']['name'])) {
                $result = do_image_upload('avatar', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect(base_url('admin/technicians/edit/' . $id));
                }
                delete_image($tech->avatar, $this->upload_path);
                $avatar = $result['filename'];
            }

            $this->db->update('technicians', [
                'name'      => $this->input->post('name', TRUE),
                'phone'     => $this->input->post('phone', TRUE),
                'email'     => $this->input->post('email', TRUE),
                'avatar'    => $avatar,
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ], ['id' => $id]);

            $user_data = [
                'username'  => $new_username,
                'name'      => $this->input->post('name', TRUE),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ];
            $password = $this->input->post('password');
            if (!empty($password)) {
                $user_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->db->update('users', $user_data, ['ref_id' => $id, 'role' => 'technician']);

            $this->session->set_flashdata('success', 'แก้ไขข้อมูลช่างเรียบร้อยแล้ว');
            redirect(base_url('admin/technicians'));
        }

        $this->render('technicians/form', ['tech' => $tech]);
    }

    public function delete($id)
    {
        $ticket_count = $this->db
            ->where('technician_id', $id)
            ->where_not_in('status', ['completed', 'closed'])
            ->count_all_results('tickets');

        if ($ticket_count > 0) {
            $this->session->set_flashdata('error', "ไม่สามารถลบได้ เพราะมี Ticket {$ticket_count} รายการที่ยังดำเนินการอยู่ กรุณาโอนงานให้ช่างคนอื่นก่อน");
            redirect(base_url('admin/technicians'));
        }

        // ลบ user account ของช่างด้วย
        $this->db->delete('users', ['ref_id' => $id, 'role' => 'technician']);
        $this->db->delete('technicians', ['id' => $id]);
        $this->session->set_flashdata('success', 'ลบช่างเรียบร้อยแล้ว');
        redirect(base_url('admin/technicians'));
    }
}
