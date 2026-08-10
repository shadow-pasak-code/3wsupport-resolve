<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Repair_categories extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Repair_category_model');
    }

    public function index()
    {
        $categories = $this->Repair_category_model->get_all();
        $this->render('repair_categories/index', ['categories' => $categories]);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $name     = $this->input->post('name', TRUE);
            $max_days = (int) $this->input->post('max_days');

            if (!$name || $max_days < 1) {
                $this->session->set_flashdata('error', 'กรุณากรอกชื่อหมวดหมู่และจำนวนวัน (อย่างน้อย 1 วัน)');
                redirect(base_url('admin/repair_categories/add'));
            }

            $this->Repair_category_model->create([
                'name'     => $name,
                'max_days' => $max_days,
            ]);

            $this->session->set_flashdata('success', 'เพิ่มหมวดหมู่การซ่อมเรียบร้อยแล้ว');
            redirect(base_url('admin/repair_categories'));
        }

        $this->render('repair_categories/form', []);
    }

    public function edit($id)
    {
        $category = $this->Repair_category_model->get_by_id($id);
        if (!$category) show_404();

        if ($this->input->method() === 'post') {
            $name     = $this->input->post('name', TRUE);
            $max_days = (int) $this->input->post('max_days');

            if (!$name || $max_days < 1) {
                $this->session->set_flashdata('error', 'กรุณากรอกชื่อหมวดหมู่และจำนวนวัน (อย่างน้อย 1 วัน)');
                redirect(base_url('admin/repair_categories/edit/' . $id));
            }

            $this->Repair_category_model->update($id, [
                'name'      => $name,
                'max_days'  => $max_days,
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ]);

            $this->session->set_flashdata('success', 'แก้ไขหมวดหมู่การซ่อมเรียบร้อยแล้ว');
            redirect(base_url('admin/repair_categories'));
        }

        $this->render('repair_categories/form', ['category' => $category]);
    }

    // ปิด/เปิดการใช้งาน แทนการลบจริง เพราะ ticket เก่าอาจอ้างอิงหมวดหมู่นี้อยู่
    public function toggle_active($id)
    {
        $category = $this->Repair_category_model->get_by_id($id);
        if (!$category) show_404();

        $this->Repair_category_model->set_active($id, !$category->is_active);

        $this->session->set_flashdata('success', $category->is_active
            ? 'ปิดการใช้งานหมวดหมู่เรียบร้อยแล้ว'
            : 'เปิดการใช้งานหมวดหมู่เรียบร้อยแล้ว');
        redirect(base_url('admin/repair_categories'));
    }
}
