<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('app_config');
        $this->load->library('session');
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $current_user = $this->session->userdata('user');

        if ($current_user) {
            $this->_redirect_by_role($current_user['role']);
        }

        if ($this->input->method() === 'post') {
            $username = $this->input->post('username', TRUE);
            $password = $this->input->post('password', TRUE);

            $user = $this->User_model->login($username, $password);

            if ($user) {
                $this->session->set_userdata('user', [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'role'     => $user->role,
                    'ref_id'   => $user->ref_id,
                ]);
                $this->_redirect_by_role($user->role);
            } else {
                $data['error'] = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                $this->load->view('login', $data);
            }
        } else {

            $this->load->view('login');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect(base_url('login'));
    }

    private function _redirect_by_role($role)
    {
        switch ($role) {
            case 'admin':
                redirect(base_url('admin/dashboard'));
                break;
            case 'technician':
                redirect(base_url('tech/tickets'));
                break;
            case 'partner':
                redirect(base_url('partner/tickets'));
                break;
            default:
                redirect(base_url('login'));
        }
    }
}
