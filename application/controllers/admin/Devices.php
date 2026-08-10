<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Devices extends Admin_Controller
{
    protected $upload_path = 'uploads/devices/';

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
        $data['devices'] = $this->Device_model->get_all();
        $this->render('devices/index', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $result = do_image_upload('image', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect('admin/devices/add');
                }
                $image = $result['filename'];
            }

            $this->Device_model->create([
                'customer_id'   => $this->input->post('customer_id', TRUE),
                'name'          => $this->input->post('name', TRUE),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'device_type'   => $this->input->post('device_type', TRUE),
                'purchase_date' => $this->input->post('purchase_date') ?: NULL,
                'warranty_end'  => $this->input->post('warranty_end') ?: NULL,
                'partner_id'    => $this->input->post('partner_id') ?: NULL,
                'note'          => $this->input->post('note', TRUE),
                'image'         => $image,
            ]);

            $this->session->set_flashdata('success', 'เพิ่มอุปกรณ์เรียบร้อยแล้ว');
            redirect('admin/devices');
        }

        $data['customers']      = $this->Customer_model->get_all();
        $data['partners']       = $this->partners;
        $data['equipment_list'] = $this->db->where('is_active', 1)->order_by('device_type, name')->get('equipment')->result();
        $this->render('devices/form', $data);
    }

    public function edit($id)
    {
        $device = $this->Device_model->get_by_id($id);
        if (!$device) show_404();

        if ($this->input->method() === 'post') {
            $image = $device->image;

            if (!empty($_FILES['image']['name'])) {
                $result = do_image_upload('image', $this->upload_path);
                if (!$result['success']) {
                    $this->session->set_flashdata('error', $result['error']);
                    redirect('admin/devices/edit/' . $id);
                }
                // ลบรูปเก่า
                delete_image($device->image, $this->upload_path);
                $image = $result['filename'];
            }

            $this->Device_model->update($id, [
                'customer_id'   => $this->input->post('customer_id', TRUE),
                'name'          => $this->input->post('name', TRUE),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'device_type'   => $this->input->post('device_type', TRUE),
                'purchase_date' => $this->input->post('purchase_date') ?: NULL,
                'warranty_end'  => $this->input->post('warranty_end') ?: NULL,
                'partner_id'    => $this->input->post('partner_id') ?: NULL,
                'note'          => $this->input->post('note', TRUE),
                'image'         => $image,
            ]);

            $this->session->set_flashdata('success', 'แก้ไขอุปกรณ์เรียบร้อยแล้ว');
            redirect('admin/devices');
        }

        $data['customers']      = $this->Customer_model->get_all();
        $data['partners']       = $this->partners;
        $data['equipment_list'] = $this->db->where('is_active', 1)->order_by('device_type, name')->get('equipment')->result();
        $this->render('devices/form', $data);
    }

    public function delete($id)
    {
        $device = $this->Device_model->get_by_id($id);
        if ($device) {
            // เช็คว่ามี ticket ที่ยังไม่จบอยู่มั้ย (completed/closed ไม่นับ ไม่งั้นอุปกรณ์ที่มีประวัติซ่อมจะลบไม่ได้ตลอดไป)
            $ticket_count = $this->db
                ->where('device_id', $id)
                ->where_not_in('status', ['completed', 'closed'])
                ->count_all_results('tickets');

            if ($ticket_count > 0) {
                $this->session->set_flashdata(
                    'error',
                    "ไม่สามารถลบได้ เพราะมี Ticket {$ticket_count} รายการที่ยังไม่เสร็จสิ้นผูกอยู่กับอุปกรณ์นี้\n" .
                        "กรุณาปิด Ticket ที่ยังดำเนินการอยู่ก่อนลบอุปกรณ์"
                );
                redirect(base_url('admin/devices'));
            }

            delete_image($device->image, $this->upload_path);
            $this->Device_model->delete($id);
            $this->session->set_flashdata('success', 'ลบอุปกรณ์เรียบร้อยแล้ว');
        }
        redirect(base_url('admin/devices'));
    }

    public function check_sn()
    {
        $sn  = $this->input->get('sn', TRUE);
        $id  = $this->input->get('exclude_id'); // สำหรับกรณี edit ไม่ให้เช็คตัวเอง

        $query = $this->db->where('serial_number', $sn);
        if ($id) $query->where('id !=', $id);
        $exists = $query->count_all_results('devices') > 0;

        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
    }
}
