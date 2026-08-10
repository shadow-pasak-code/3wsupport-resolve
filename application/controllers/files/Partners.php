<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partners extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
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
            $this->db->insert('partners', [
                'company_name' => $this->input->post('company_name', TRUE),
                'contact_name' => $this->input->post('contact_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
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

            redirect('admin/partners');
        }
        $this->render('partners/form', []);
    }

    public function edit($id)
    {
        $partner = $this->db->select('p.*, u.username, u.id as user_id')
                            ->from('partners p')
                            ->join('users u', 'u.ref_id = p.id AND u.role = "partner"', 'left')
                            ->where('p.id', $id)
                            ->get()->row();
        if (!$partner) show_404();

        if ($this->input->method() === 'post') {
            $this->db->update('partners', [
                'company_name' => $this->input->post('company_name', TRUE),
                'contact_name' => $this->input->post('contact_name', TRUE),
                'phone'        => $this->input->post('phone', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'is_active'    => $this->input->post('is_active') ? 1 : 0,
            ], ['id' => $id]);

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

            redirect('admin/partners');
        }
        $this->render('partners/form', ['partner' => $partner]);
    }
}
