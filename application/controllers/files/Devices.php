<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Devices extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Device_model');
        $this->load->model('Customer_model');
    }

    public function index()
    {
        $data['devices'] = $this->Device_model->get_all();
        $this->render('devices/index', $data);
    }

    public function add()
    {
        if ($this->input->method() === 'post') {
            $this->Device_model->create([
                'customer_id'   => $this->input->post('customer_id', TRUE),
                'name'          => $this->input->post('name', TRUE),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'device_type'   => $this->input->post('device_type', TRUE),
                'purchase_date' => $this->input->post('purchase_date', TRUE) ?: NULL,
                'warranty_end'  => $this->input->post('warranty_end', TRUE) ?: NULL,
                'note'          => $this->input->post('note', TRUE),
            ]);
            redirect('admin/devices');
        }
        $data['customers'] = $this->Customer_model->get_all();
        $this->render('devices/form', $data);
    }

    public function edit($id)
    {
        $device = $this->Device_model->get_by_id($id);
        if (!$device) show_404();

        if ($this->input->method() === 'post') {
            $this->Device_model->update($id, [
                'customer_id'   => $this->input->post('customer_id', TRUE),
                'name'          => $this->input->post('name', TRUE),
                'serial_number' => $this->input->post('serial_number', TRUE),
                'device_type'   => $this->input->post('device_type', TRUE),
                'purchase_date' => $this->input->post('purchase_date', TRUE) ?: NULL,
                'warranty_end'  => $this->input->post('warranty_end', TRUE) ?: NULL,
                'note'          => $this->input->post('note', TRUE),
            ]);
            redirect('admin/devices');
        }
        $data['device']    = $device;
        $data['customers'] = $this->Customer_model->get_all();
        $this->render('devices/form', $data);
    }
}
