<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Faq extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Faq_model');
    }

    public function index()
    {
        $data['faqs'] = $this->Faq_model->get_all();
        $this->render('faq/index', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $this->Faq_model->create([
                'category' => $this->input->post('category', TRUE),
                'keyword'  => $this->input->post('keyword', TRUE),
                'question' => $this->input->post('question', TRUE),
                'answer'   => $this->input->post('answer', TRUE),
                'is_active' => 1,
            ]);
            $this->session->set_flashdata('success', 'เพิ่ม FAQ เรียบร้อยแล้ว');
            redirect(base_url('admin/faq'));
        }
        $this->render('faq/form', []);
    }

    public function edit($id)
    {
        $faq = $this->Faq_model->get_by_id($id);
        if (!$faq) show_404();

        if ($this->input->method() === 'post') {
            $this->Faq_model->update($id, [
                'category'  => $this->input->post('category', TRUE),
                'keyword'   => $this->input->post('keyword', TRUE),
                'question'  => $this->input->post('question', TRUE),
                'answer'    => $this->input->post('answer', TRUE),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ]);
            redirect('admin/faq');
        }
        $this->render('faq/form', ['faq' => $faq]);
    }

    public function delete($id)
    {
        $this->Faq_model->delete($id);
        $this->session->set_flashdata('success', 'ลบ FAQ เรียบร้อยแล้ว');
        redirect(base_url('admin/faq'));
    }
}
