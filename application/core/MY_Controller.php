<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('app_config');
        $this->load->library('session');
        $this->current_user = $this->session->userdata('user');
    }

    protected function render($view, $data = [], $layout = 'admin/layout/main')
    {
        // กันเบราว์เซอร์เก็บหน้า dashboard ไว้ใน cache — ไม่งั้นกดเมนูซ้ำ (เช่น "จัดการ Ticket") แล้วเจอข้อมูลเก่าค้าง
        // ต้องกดปุ่มกรอง/ค้นหาถึงจะยิง request ใหม่จริงๆ เพราะ query string เปลี่ยนไปบังคับให้ไม่ใช้ cache
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');

        $data['current_user'] = $this->current_user;
        $data['content_view'] = $view;
        $this->load->view($layout, $data);
    }
}

class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->current_user || $this->current_user['role'] !== 'admin') {
            redirect(base_url('login'));
            exit;
        }
    }

    protected function render($view, $data = [], $layout = null)
    {
        parent::render('admin/' . $view, $data, 'admin/layout/main');
    }
}

class Tech_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->current_user || $this->current_user['role'] !== 'technician') {
            redirect(base_url('login'));
            exit;
        }
    }

    protected function render($view, $data = [], $layout = null)
    {
        parent::render('technician/' . $view, $data, 'admin/layout/main');
    }
}

class Partner_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->current_user || $this->current_user['role'] !== 'partner') {
            redirect(base_url('login'));
            exit;
        }
    }

    protected function render($view, $data = [], $layout = null)
    {
        parent::render('partner/' . $view, $data, 'admin/layout/main');
    }
} 