<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Partners extends Admin_Controller
{
    protected $upload_path = 'uploads/avatars/partners/';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('upload');
    }

    public function index()
    {
        $partners = $this->db
            ->select('p.*, u.username')
            ->from('partners p')
            ->join('users u', 'u.ref_id = p.id AND u.role = "partner"', 'left')
            ->get()->result();

        $this->render('partners/index', ['partners' => $partners]);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $logo = null;
            if (!empty($_FILES['logo']['name'])) {
                $result = do_image_upload('logo', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect(base_url('admin/partners/add'));
                }
                $logo = $result['filename'];
            }

            $this->db->insert('partners', [
                'company_name' => $this->input->post('company_name', TRUE),
                'contact_name' => $this->input->post('contact_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'logo'         => $logo,
                'is_active'    => 1,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $partner_id = $this->db->insert_id();

            $this->db->insert('users', [
                'username'      => $this->input->post('username', TRUE),
                'password_hash' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'name'          => $this->input->post('company_name', TRUE),
                'role'          => 'partner',
                'ref_id'        => $partner_id,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            $this->session->set_flashdata('success', 'เพิ่ม Partner เรียบร้อยแล้ว');
            redirect(base_url('admin/partners'));
        }

        $this->render('partners/form', []);
    }

    public function edit($id)
    {
        $partner = $this->db
            ->select('p.*, u.username, u.id as user_id')
            ->from('partners p')
            ->join('users u', 'u.ref_id = p.id AND u.role = "partner"', 'left')
            ->where('p.id', $id)
            ->get()->row();
        if (!$partner) show_404();

        if ($this->input->method() === 'post') {
            $logo = $partner->logo;
            if (!empty($_FILES['logo']['name'])) {
                $result = do_image_upload('logo', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect(base_url('admin/partners/edit/' . $id));
                }
                delete_image($partner->logo, $this->upload_path);
                $logo = $result['filename'];
            }

            $this->db->update('partners', [
                'company_name' => $this->input->post('company_name', TRUE),
                'contact_name' => $this->input->post('contact_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'logo'         => $logo,
                'is_active'    => $this->input->post('is_active') ? 1 : 0,
            ], ['id' => $id]);

            // เช็ค username ซ้ำ
            $new_username = $this->input->post('username', TRUE);
            $dup = $this->db
                ->where('username', $new_username)
                ->where('id !=', $partner->user_id)
                ->count_all_results('users');
            if ($dup > 0) {
                $this->session->set_flashdata('error', "Username '{$new_username}' ถูกใช้งานแล้ว กรุณาเลือก Username อื่น");
                redirect(base_url('admin/partners/edit/' . $id));
            }
            
            $user_data = [
                'username'  => $this->input->post('username', TRUE),
                'name'      => $this->input->post('company_name', TRUE),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ];
            $password = $this->input->post('password');
            if (!empty($password)) {
                $user_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->db->update('users', $user_data, ['ref_id' => $id, 'role' => 'partner']);

            $this->session->set_flashdata('success', 'แก้ไข Partner เรียบร้อยแล้ว');
            redirect(base_url('admin/partners'));
        }

        $this->render('partners/form', ['partner' => $partner]);
    }

    public function delete($id)
    {
        // เช็ค devices ที่ผูกอยู่
        $device_count = $this->db->where('partner_id', $id)->count_all_results('devices');
        if ($device_count > 0) {
            $this->session->set_flashdata('error', "ไม่สามารถลบได้ เพราะมีอุปกรณ์ {$device_count} รายการผูกอยู่กับ Partner นี้ กรุณาเปลี่ยน Partner ของอุปกรณ์ก่อน");
            redirect(base_url('admin/partners'));
        }

        // เช็ค tickets ที่ผูกอยู่
        $ticket_count = $this->db->where('partner_id', $id)->count_all_results('tickets');
        if ($ticket_count > 0) {
            $this->session->set_flashdata('error', "ไม่สามารถลบได้ เพราะมี Ticket {$ticket_count} รายการผูกอยู่กับ Partner นี้");
            redirect(base_url('admin/partners'));
        }

        $this->db->delete('partners', ['id' => $id]);
        $this->session->set_flashdata('success', 'ลบเรียบร้อยแล้ว');
        redirect(base_url('admin/partners'));
    }
}
