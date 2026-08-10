<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Technicians extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
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
            // สร้าง technician
            $this->db->insert('technicians', [
                'name'       => $this->input->post('name', TRUE),
                'phone'      => $this->input->post('phone', TRUE),
                'email'      => $this->input->post('email', TRUE),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $tech_id = $this->db->insert_id();

            // สร้าง user login
            $this->db->insert('users', [
                'username'      => $this->input->post('username', TRUE),
                'password_hash' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'name'          => $this->input->post('name', TRUE),
                'role'          => 'technician',
                'ref_id'        => $tech_id,
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            redirect('admin/technicians');
        }
        $this->render('technicians/form', []);
    }

    public function edit($id)
    {
        $tech = $this->db->select('t.*, u.username, u.id as user_id')
                         ->from('technicians t')
                         ->join('users u', 'u.ref_id = t.id AND u.role = "technician"', 'left')
                         ->where('t.id', $id)
                         ->get()->row();
        if (!$tech) show_404();

        if ($this->input->method() === 'post') {
            $this->db->update('technicians', [
                'name'      => $this->input->post('name', TRUE),
                'phone'     => $this->input->post('phone', TRUE),
                'email'     => $this->input->post('email', TRUE),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ], ['id' => $id]);

            // อัปเดต user
            $user_data = [
                'username'  => $this->input->post('username', TRUE),
                'name'      => $this->input->post('name', TRUE),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ];
            $password = $this->input->post('password');
            if (!empty($password)) {
                $user_data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->db->update('users', $user_data, ['ref_id' => $id, 'role' => 'technician']);

            redirect('admin/technicians');
        }
        $this->render('technicians/form', ['tech' => $tech]);
    }
}
