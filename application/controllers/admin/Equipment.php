<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Equipment extends Admin_Controller
{
    protected $upload_path = 'uploads/equipment/';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Device_model');
        $this->load->model('Customer_model');
        $this->load->helper('upload');
        $this->partners = $this->db->where('is_active', 1)->get('partners')->result();
    }

    public function index()
    {
        $data['equipment'] = $this->db
            ->select('e.*, p.company_name as partner_name, p.id as partner_id')
            ->from('equipment e')
            ->join('partners p', 'p.id = e.partner_id', 'left')
            ->order_by('e.device_type, e.name')
            ->get()->result();

        $data['partners'] = $this->db
            ->where('is_active', 1)
            ->get('partners')->result();

        $this->render('equipment/index', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $result = do_image_upload('image', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect(base_url('admin/equipment/add'));
                }
                $image = $result['filename'];
            }

            $this->db->insert('equipment', [
                'name'        => $this->input->post('name', TRUE),
                'brand'       => $this->input->post('brand', TRUE),
                'model'       => $this->input->post('model', TRUE),
                'device_type' => $this->input->post('device_type', TRUE),
                'description' => $this->input->post('description', TRUE),
                'partner_id' => $this->input->post('partner_id') ?: NULL,
                'image'       => $image,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->session->set_flashdata('success', 'เพิ่มอุปกรณ์เรียบร้อยแล้ว');
            redirect(base_url('admin/equipment'));
        }
        $this->render('equipment/form', ['partners' => $this->partners]);
    }

    public function edit($id)
    {
        $eq = $this->db->get_where('equipment', ['id' => $id])->row();
        if (!$eq) show_404();

        if ($this->input->method() === 'post') {
            $image = $eq->image;
            if (!empty($_FILES['image']['name'])) {
                $result = do_image_upload('image', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect(base_url('admin/equipment/edit/' . $id));
                }
                delete_image($eq->image, $this->upload_path);
                $image = $result['filename'];
            }

            $this->db->update('equipment', [
                'name'        => $this->input->post('name', TRUE),
                'brand'       => $this->input->post('brand', TRUE),
                'model'       => $this->input->post('model', TRUE),
                'device_type' => $this->input->post('device_type', TRUE),
                'description' => $this->input->post('description', TRUE),
                'partner_id' => $this->input->post('partner_id') ?: NULL,
                'image'       => $image,
                'is_active'   => $this->input->post('is_active') ? 1 : 0,
            ], ['id' => $id]);

            $this->session->set_flashdata('success', 'แก้ไขเรียบร้อยแล้ว');
            redirect(base_url('admin/equipment'));
        }
        $this->render('equipment/form', ['equipment' => $eq, 'partners' => $this->partners]);
    }

    public function delete($id)
    {
        $eq = $this->db->get_where('equipment', ['id' => $id])->row();
        if ($eq) {
            delete_image($eq->image, $this->upload_path);
            $this->db->delete('equipment', ['id' => $id]);
        }
        $this->session->set_flashdata('success', 'ลบเรียบร้อยแล้ว');
        redirect(base_url('admin/equipment'));
    }

    // API สำหรับ dropdown ในหน้าเพิ่มลูกค้า
    public function get_list()
    {
        $type = $this->input->get('type');
        $query = $this->db->where('is_active', 1);
        if ($type) $query->where('device_type', $type);
        $list = $query->order_by('name')->get('equipment')->result();
        echo json_encode($list);
    }
}
