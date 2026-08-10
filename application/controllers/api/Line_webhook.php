<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Line_webhook extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('app_config');
        $this->load->model(['Ticket_model', 'Customer_model', 'Device_model', 'Faq_model']);
        $this->load->library('Line_notify');
    }

    public function handle()
    {
        // -----------------------------
        // รับข้อมูลจาก LINE
        // -----------------------------
        $raw_body = file_get_contents('php://input');

        // -----------------------------
        // Debug Log
        // -----------------------------
        log_message('error', '========== LINE WEBHOOK ==========');
        log_message('error', 'DATE : ' . date('Y-m-d H:i:s'));
        log_message('error', 'BODY : ' . $raw_body);
        log_message('error', 'HEADER SIGNATURE : ' . ($_SERVER['HTTP_X_LINE_SIGNATURE'] ?? 'NONE'));

        // -----------------------------
        // Verify Signature
        // -----------------------------
        if (!$this->_verify_signature($raw_body)) {

            log_message('error', 'VERIFY RESULT : FAILED');

            http_response_code(401);
            echo 'Unauthorized';
            return;
        }

        log_message('error', 'VERIFY RESULT : PASS');

        // -----------------------------
        // Decode JSON
        // -----------------------------
        $json = json_decode($raw_body, true);

        if (!$json) {
            log_message('error', 'JSON Decode Error');

            http_response_code(400);
            echo 'Bad Request';
            return;
        }

        $events = $json['events'] ?? [];

        if (empty($events)) {

            log_message('error', 'No Event');

            http_response_code(200);
            echo 'OK';
            return;
        }

        foreach ($events as $event) {

            log_message('error', 'EVENT TYPE : ' . ($event['type'] ?? 'NULL'));

            if (($event['type'] ?? '') != 'message') {
                continue;
            }

            if (($event['message']['type'] ?? '') != 'text') {
                continue;
            }

            $line_uid = $event['source']['userId'] ?? '';
            $text     = trim($event['message']['text'] ?? '');

            log_message('error', 'LINE UID : ' . $line_uid);
            log_message('error', 'TEXT : ' . $text);

            // เรียก Logic เดิมของคุณ
            $this->_process_message($line_uid, $text);
        }

        http_response_code(200);
        echo 'OK';
    }

    private function _process_message($line_uid, $text)
    {
        $customer = $this->Customer_model->get_by_line_uid($line_uid);

        // ============================================================
        // ยังไม่ผูก line_uid
        // ============================================================
        if (!$customer) {

            // เช็ค pending verify (รอยืนยันเบอร์)
            $pending = $this->db->get_where('line_verify', ['line_uid' => $line_uid])->row();
            if ($pending) {
                $customer_check = $this->Customer_model->get_by_id($pending->customer_id);
                $input_phone    = preg_replace('/[\s\-]/', '', $text);
                $stored_phone   = preg_replace('/[\s\-]/', '', $customer_check->phone ?? '');

                if ($input_phone === $stored_phone) {
                    $this->Customer_model->bind_line_uid($pending->customer_id, $line_uid);
                    $this->db->delete('line_verify', ['line_uid' => $line_uid]);
                    $this->line_notify->push(
                        $line_uid,
                        "✅ ยืนยันตัวตนสำเร็จครับ\n" .
                            "สวัสดีครับ {$customer_check->company_name}\n\n" .
                            "ตอนนี้คุณสามารถใช้บริการได้แล้วครับ\n" .
                            "กด Rich Menu เพื่อเลือกบริการได้เลย"
                    );
                } else {
                    $this->line_notify->push(
                        $line_uid,
                        "❌ เบอร์โทรไม่ตรงครับ กรุณาลองใหม่\n" .
                            "หรือติดต่อแอดมินหากต้องการความช่วยเหลือ"
                    );
                }
                return;
            }

            // เช็ค S/N เพื่อเริ่มผูกบัญชี
            preg_match('/[A-Z0-9\-]{5,}/i', $text, $matches);
            $serial = $matches[0] ?? null;

            if ($serial) {
                $device = $this->Device_model->get_by_serial($serial);
                if ($device) {
                    // เช็คว่า S/N นี้ถูกผูกกับคนอื่นแล้วมั้ย
                    $already = $this->Customer_model->get_by_line_uid($line_uid);
                    if (!$already) {
                        $this->db->replace('line_verify', [
                            'line_uid'    => $line_uid,
                            'customer_id' => $device->customer_id,
                            'created_at'  => date('Y-m-d H:i:s'),
                        ]);
                        $this->line_notify->push(
                            $line_uid,
                            "🔍 พบข้อมูลอุปกรณ์แล้วครับ\n" .
                                "กรุณายืนยันตัวตนด้วยเบอร์โทรศัพท์ที่ลงทะเบียนไว้\n\n" .
                                "📱 พิมพ์เบอร์โทรของคุณได้เลยครับ"
                        );
                    }
                } else {
                    $this->line_notify->push(
                        $line_uid,
                        "❌ ไม่พบ Serial Number นี้ในระบบครับ\n" .
                            "กรุณาตรวจสอบและลองใหม่อีกครั้ง"
                    );
                }
                return;
            }

            // ไม่รู้จัก
            $this->line_notify->push(
                $line_uid,
                "สวัสดีครับ 👋\n" .
                    "กรุณายืนยันตัวตนก่อนใช้งานระบบครับ\n\n" .
                    "📌 พิมพ์ Serial Number ของอุปกรณ์ที่ซื้อจากเรา\n" .
                    "ตัวอย่าง: SN-2024-00001"
            );
            return;
        }

        // ============================================================
        // ผูกแล้ว
        // ============================================================

        // เช็ค pending repair (รอรับอาการ)
        // เช็คว่ากำลังรอ S/N อยู่มั้ย
        $waiting_sn = $this->db->get_where('line_waiting_sn', ['line_uid' => $line_uid])->row();
        if ($waiting_sn) {
            $device_by_sn = $this->Device_model->get_by_serial(strtoupper(trim($text)));
            if ($device_by_sn && $device_by_sn->customer_id == $customer->id) {
                $this->db->delete('line_waiting_sn', ['line_uid' => $line_uid]);
                $this->_handle_repair_by_serial($line_uid, $customer, $device_by_sn);
                return;
            }

            // ไม่ใช่ S/N → เช็คว่าเป็น keyword อื่นมั้ย
            if (!preg_match('/^[A-Z0-9\-]{5,}$/i', trim($text))) {
                // ข้อความทั่วไปหรือ keyword → ยกเลิก state แล้วไป route ปกติ
                $this->db->delete('line_waiting_sn', ['line_uid' => $line_uid]);
                // ไม่ return → วิ่งต่อ route keyword ด้านล่าง
            } else {
                // ดูเหมือน S/N แต่หาไม่เจอ
                $this->line_notify->push(
                    $line_uid,
                    "❌ ไม่พบ Serial Number นี้ในระบบครับ\n" .
                        "กรุณาตรวจสอบและลองพิมพ์ใหม่"
                );
                return;
            }
        }
        $repair_pending = $this->db->get_where('line_repair_pending', ['line_uid' => $line_uid])->row();
        if ($repair_pending) {

            // ลูกค้าพิมพ์ keyword อื่น → ยกเลิก state แล้วไป route ปกติ
            if ($this->_contains($text, ['ยกเลิก', 'เมนูหลัก', 'หยุด', 'cancel'])) {
                $this->db->delete('line_repair_pending', ['line_uid' => $line_uid]);
                $this->line_notify->push($line_uid, "↩️ ยกเลิกการแจ้งซ่อมแล้วครับ กำลังดำเนินการต่อ...");
                // ไม่ return → วิ่งต่อ route keyword ด้านล่าง
            } else {
                $device = $this->Device_model->get_by_id($repair_pending->device_id);
                $this->db->delete('line_repair_pending', ['line_uid' => $line_uid]);

                $today = date('Y-m-d');
                $in_w  = !empty($device->warranty_end) && $device->warranty_end >= $today;

                $ticket_id = $this->Ticket_model->create([
                    'customer_id' => $customer->id,
                    'device_id'   => $device->id,
                    'ticket_type' => $device->device_type,
                    'issue_desc'  => $text,
                    'status'      => 'pending',
                ]);

                $this->line_notify->push(
                    $line_uid,
                    "✅ รับแจ้งซ่อมเรียบร้อยแล้วครับ\n\n" .
                        "🎫 Ticket #{$ticket_id}\n" .
                        "อุปกรณ์: {$device->name}\n" .
                        "S/N: {$device->serial_number}\n" .
                        "อาการ: {$text}\n\n" .
                        ($in_w
                            ? "📋 อยู่ในประกัน เจ้าหน้าที่จะตรวจสอบและแจ้งกลับโดยเร็วครับ"
                            : "📋 หมดประกันแล้ว เจ้าหน้าที่จะประเมินราคาและแจ้งกลับครับ")
                );
                return;
            }
        }

        // เช็ค quote response (ยืนยัน/ปฏิเสธ)
        // เช็ค quote response — รองรับทั้งแบบ "ยืนยัน:1" และ "ยืนยันใบเสนอราคา"
        if (preg_match('/^(ยืนยัน|ปฏิเสธ):(\d+)$/', $text, $m)) {
            $this->_handle_quote_response($line_uid, $customer, $m[1], (int)$m[2]);
            return;
        }

        // กรณีลูกค้าพิมพ์ "ยืนยันใบเสนอราคา" หรือ "ปฏิเสธใบเสนอราคา" โดยไม่รู้เลข ticket
        if ($this->_contains($text, ['ยืนยันใบเสนอราคา', 'ยืนยันราคา', 'ยืนยันการซ่อม'])) {
            $this->_handle_confirm_quote($line_uid, $customer, 'ยืนยัน');
            return;
        }
        if ($this->_contains($text, ['ปฏิเสธใบเสนอราคา', 'ปฏิเสธราคา', 'ไม่ยืนยัน', 'ไม่ซ่อม'])) {
            $this->_handle_confirm_quote($line_uid, $customer, 'ปฏิเสธ');
            return;
        }

        // เช็ค S/N จากลูกค้าที่ผูกแล้ว
        $device_by_sn = $this->Device_model->get_by_serial(strtoupper(trim($text)));
        if ($device_by_sn && $device_by_sn->customer_id == $customer->id) {
            $this->_handle_repair_by_serial($line_uid, $customer, $device_by_sn);
            return;
        }

        // route ตาม keyword
        // route ตาม keyword
        if ($this->_contains($text, ['แจ้งซ่อม', 'ซ่อม', 'แจ้งปัญหา'])) {
            $this->_handle_repair_request($line_uid, $customer, $text);
        } elseif ($this->_contains($text, ['ตรวจสอบประกัน', 'เช็คประกัน', 'ประกัน'])) {
            $this->_handle_warranty_check($line_uid, $customer, $text);
        } elseif ($this->_contains($text, ['สถานะ', 'ติดตาม', 'เช็คสถานะ'])) {
            $this->_handle_status_check($line_uid, $customer);
        } elseif ($this->_contains($text, ['ติดต่อแอดมิน', 'ติดต่อเจ้าหน้าที่', 'admin', 'แอดมิน'])) {
            $this->line_notify->push(
                $line_uid,
                "📞 ติดต่อฝ่ายสนับสนุน 3W Business and Solutions\n\n" .
                    "☎️ โทร: 02-108-1866\n" .
                    "📧 อีเมล: nance3w@gmail.com\n" .
                    "🌐 Facebook: https://www.facebook.com/p/3w-Business-And-Solutions-Coltd-100077655579893/\n" .
                    "📍 ที่อยู่: 7 ซอยรามอินทรา 18 แขวงท่าแร้ง เขตบางเขน กรุงเทพฯ 10220\n\n" .
                    "⏰ วันจันทร์ - ศุกร์ เวลา 08:30 - 17:30 น."
            );
        } else {
            $this->_handle_chatbot($line_uid, $text);
        }
    }

    private function _handle_repair_by_serial($line_uid, $customer, $device)
    {
        $today = date('Y-m-d');
        $in_w  = !empty($device->warranty_end) && $device->warranty_end >= $today;

        // เช็คว่า ticket นี้กำลังซ่อมอยู่มั้ย
        $active_tickets = $this->Ticket_model->get_all([
            'customer_id' => $customer->id,
        ]);

        $active = null;
        foreach ((array)$active_tickets as $t) {
            if (
                $t->device_id == $device->id &&
                in_array($t->status, ['pending', 'approved', 'assigned', 'in_progress', 'wait_quote', 'wait_review', 'wait_confirm', 'quote_accepted', 'escalated'])
            ) {
                $active = $t;
                break;
            }
        }

        // มี ticket ที่กำลังดำเนินการอยู่
        if ($active) {
            $this->line_notify->push(
                $line_uid,
                "⚠️ อุปกรณ์นี้มี Ticket ที่กำลังดำเนินการอยู่แล้วครับ\n\n" .
                    "🎫 Ticket #{$active->id}\n" .
                    "อุปกรณ์: {$device->name}\n" .
                    "สถานะ: " . $this->_status_label($active->status) . "\n\n" .
                    "หากต้องการสอบถามเพิ่มเติม กรุณาติดต่อแอดมินโดยตรงครับ"
            );
            return;
        }

        // ไม่มี active ticket → เริ่ม flow แจ้งซ่อม
        $warranty_text = $in_w
            ? "✅ อยู่ในประกัน (หมดอายุ: " . date('d/m/Y', strtotime($device->warranty_end)) . ")"
            : "❌ หมดประกันแล้ว (หมดเมื่อ: " . date('d/m/Y', strtotime($device->warranty_end)) . ")";

        $this->line_notify->push(
            $line_uid,
            "🔧 แจ้งซ่อมอุปกรณ์\n\n" .
                "อุปกรณ์: {$device->name}\n" .
                "S/N: {$device->serial_number}\n" .
                "สถานะประกัน: {$warranty_text}\n\n" .
                "📝 กรุณาพิมพ์อธิบายอาการหรือปัญหาที่พบครับ"
        );

        // เก็บ state รอรับอาการ
        $this->db->replace('line_repair_pending', [
            'line_uid'   => $line_uid,
            'device_id'  => $device->id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function _handle_repair_request($line_uid, $customer, $text)
    {
        $devices = $this->Device_model->get_by_customer($customer->id);

        if (empty($devices)) {
            $this->line_notify->push(
                $line_uid,
                "ไม่พบข้อมูลอุปกรณ์ในระบบครับ\n" .
                    "กรุณาติดต่อแอดมินโดยตรง"
            );
            return;
        }

        if (count($devices) === 1) {
            // มีอุปกรณ์เครื่องเดียว → เข้า flow แจ้งซ่อมเลย
            $this->_handle_repair_by_serial($line_uid, $customer, $devices[0]);
            return;
        }

        // มีหลายเครื่อง → ให้เลือก โดยพิมพ์ S/N
        $msg = "🔧 แจ้งซ่อมครับ\n\n";
        $msg .= "คุณมีอุปกรณ์ในระบบดังนี้ กรุณาพิมพ์ S/N ของอุปกรณ์ที่ต้องการแจ้งซ่อมครับ\n\n";
        foreach ($devices as $d) {
            $today = date('Y-m-d');
            $in_w  = !empty($d->warranty_end) && $d->warranty_end >= $today;
            $msg  .= "📌 {$d->name}\n";
            $msg  .= "   S/N: {$d->serial_number}\n";
            $msg  .= "   ประกัน: " . ($in_w ? "✅ อยู่ในประกัน" : "❌ หมดประกัน") . "\n\n";
        }
        $msg .= "พิมพ์ S/N ที่ต้องการแจ้งซ่อมได้เลยครับ";
        $this->line_notify->push($line_uid, $msg);

        // เก็บ state รอรับ S/N
        $this->db->replace('line_waiting_sn', [
            'line_uid'   => $line_uid,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function _handle_warranty_check($line_uid, $customer, $text)
    {
        // ดึงอุปกรณ์ทั้งหมดของลูกค้า
        $devices = $this->Device_model->get_by_customer($customer->id);

        if (empty($devices)) {
            $this->line_notify->push(
                $line_uid,
                "ไม่พบข้อมูลอุปกรณ์ในระบบครับ\n" .
                    "กรุณาติดต่อแอดมินโดยตรง"
            );
            return;
        }

        // เช็คว่ามี S/N ใน text มั้ย ถ้ามีแสดงเฉพาะอันนั้น
        preg_match('/[A-Z0-9\-]{5,}/i', $text, $matches);
        $serial = $matches[0] ?? null;

        if ($serial) {
            // หา device ตาม S/N ที่พิมพ์มา
            $device = $this->Device_model->get_by_serial($serial);
            if (!$device || $device->customer_id != $customer->id) {
                $this->line_notify->push(
                    $line_uid,
                    "ไม่พบ Serial Number {$serial} ในระบบครับ"
                );
                return;
            }
            $devices = [$device]; // แสดงเฉพาะอันที่ระบุ
        }

        // แสดงสถานะประกันทุกเครื่องเลย ไม่ต้องให้พิมพ์ S/N อีก
        $today = date('Y-m-d');
        $msg   = "🛡 สถานะประกันอุปกรณ์ของคุณ\n\n";

        foreach ($devices as $d) {
            $in_w        = !empty($d->warranty_end) && $d->warranty_end >= $today;
            $status_icon = $in_w ? '✅' : '❌';
            $warranty_text = !empty($d->warranty_end)
                ? ($in_w
                    ? "อยู่ในประกัน\nหมดอายุ: " . date('d/m/Y', strtotime($d->warranty_end))
                    : "หมดประกันแล้ว\nหมดเมื่อ: " . date('d/m/Y', strtotime($d->warranty_end)))
                : "ไม่มีข้อมูลประกัน";

            $msg .= "{$status_icon} {$d->name}\n";
            $msg .= "S/N: {$d->serial_number}\n";
            $msg .= "{$warranty_text}\n\n";
        }

        $this->line_notify->push($line_uid, $msg);
    }

    private function _handle_status_check($line_uid, $customer)
    {
        $active_statuses = ['pending', 'approved', 'assigned', 'in_progress', 'wait_quote', 'wait_review', 'wait_confirm', 'quote_accepted', 'escalated', 'completed'];

        $tickets = $this->db
            ->select('t.*, d.name as device_name, d.serial_number, d.warranty_end')
            ->from('tickets t')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->where('t.customer_id', $customer->id)
            ->where_in('t.status', $active_statuses)
            ->order_by('t.created_at', 'DESC')
            ->limit(5)
            ->get()->result();

        if (empty($tickets)) {
            $tickets = $this->db
                ->select('t.*, d.name as device_name, d.serial_number')
                ->from('tickets t')
                ->join('devices d', 'd.id = t.device_id', 'left')
                ->where('t.customer_id', $customer->id)
                ->where('t.status', 'closed')
                ->order_by('t.created_at', 'DESC')
                ->limit(3)
                ->get()->result();
        }

        if (empty($tickets)) {
            $this->line_notify->push(
                $line_uid,
                "📦 ไม่พบรายการแจ้งซ่อมในระบบครับ\n" .
                    "กดปุ่ม 'แจ้งซ่อม' เพื่อเปิด Ticket ใหม่ได้เลย"
            );
            return;
        }

        $msg = "📦 รายการแจ้งซ่อมของคุณ\n";
        foreach ((array)$tickets as $t) {
            $msg .= "\n🎫 Ticket #{$t->id}\n";
            $msg .= "อุปกรณ์: {$t->device_name}\n";
            $msg .= "สถานะ: " . $this->_status_label($t->status) . "\n";
            $msg .= "วันที่แจ้ง: " . date('d/m/Y', strtotime($t->created_at)) . "\n";

            // วันที่คาดว่าเสร็จ
            if (!empty($t->tech_start_date) && !empty($t->tech_end_date)) {
                $msg .= "📅 วันเริ่มซ่อม: " . date('d/m/Y', strtotime($t->tech_start_date)) . "\n";
                $msg .= "📅 คาดว่าเสร็จ: " . date('d/m/Y', strtotime($t->tech_end_date)) . "\n";
            }

            // ข้อมูลเพิ่มเติมตามสถานะ
            if ($t->status === 'wait_confirm' && $t->quote_amount) {
                $msg .= "💰 ใบเสนอราคา: ฿" . number_format($t->quote_amount, 2) . "\n";
                $msg .= "👉 พิมพ์ 'ยืนยัน:{$t->id}' เพื่อยืนยัน\n";
                $msg .= "👉 พิมพ์ 'ปฏิเสธ:{$t->id}' เพื่อปฏิเสธ\n";
            } elseif ($t->status === 'quote_accepted') {
                $msg .= "✅ ยืนยันแล้ว รอดำเนินการซ่อม\n";
            } elseif ($t->status === 'completed') {
                $msg .= "✅ ซ่อมเสร็จแล้ว กรุณาติดต่อเพื่อรับเครื่องคืน\n";
                if (!empty($t->tracking_no)) {
                    $msg .= "📦 เลขพัสดุ: {$t->tracking_no}\n";
                }
            } elseif ($t->status === 'in_progress') {
                $msg .= "🔧 กำลังดำเนินการซ่อม\n";
            } elseif ($t->status === 'escalated') {
                $msg .= "🔄 อยู่ระหว่างดำเนินการครับ\n";
            } elseif ($t->status === 'wait_quote') {
                $msg .= "⏳ กำลังประเมินราคา จะแจ้งให้ทราบเร็วๆ นี้ครับ\n";
            } elseif ($t->status === 'wait_review') {
                $msg .= "⏳ ได้รับใบเสนอราคาแล้ว กำลังตรวจสอบ จะแจ้งให้ทราบเร็วๆ นี้ครับ\n";
            }

            $this->line_notify->push($line_uid, $msg);

            // ถ้ามี ticket รอยืนยัน → ส่ง Flex Message ด้วย
            foreach ((array)$tickets as $t) {
                if ($t->status === 'wait_confirm' && $t->quote_amount) {
                    $pdf_url = !empty($t->quote_file) ? base_url('uploads/quotations/' . $t->quote_file) : null;
                    $flex    = $this->_build_quote_flex($t, $pdf_url);
                    $this->line_notify->push_flex(
                        $line_uid,
                        "ใบเสนอราคา Ticket #{$t->id}",
                        $flex
                    );
                }
            }
        }
    }

    private function _handle_chatbot($line_uid, $text)
    {
        $faqs    = $this->Faq_model->get_active_all();
        $context = "คุณเป็น AI ผู้ช่วยงาน After-Sales Service ของบริษัท Three W Business and Solutions\n";
        $context .= "หน้าที่คือตอบคำถามเกี่ยวกับการแก้ปัญหาเบื้องต้นของอุปกรณ์ hardware และ software\n\n";
        $context .= "ข้อมูล FAQ ในระบบ:\n";
        foreach ($faqs as $f) {
            $context .= "Q: {$f->question}\nA: {$f->answer}\n\n";
        }
        $context .= "กฎการตอบ:\n";
        $context .= "1. ตอบเป็นภาษาไทยเท่านั้น\n";
        $context .= "2. กระชับ ไม่เกิน 4-5 ประโยค\n";
        $context .= "3. ถ้าคำถามอยู่นอกเหนือ FAQ ให้ใช้ความรู้ทั่วไปตอบได้ทุกเรื่อง\n";
        $context .= "4. ตอบอย่างเป็นมิตร กระชับ และเป็นประโยชน์ที่สุด\n";
        $context .= "5. ห้ามพูดถึง Partner หรือบริษัทภายนอก ให้พูดในนามบริษัท Three W Business and Solutions เท่านั้น";

        $reply = $this->_call_openrouter($context, $text);
        $this->line_notify->push($line_uid, "🤖 " . $reply);
    }

    private function _call_openrouter($system_context, $user_text)
    {
        $api_key = trim($this->config->item('openrouter_api_key'));
        $url     = trim($this->config->item('openrouter_api_url'));
        $model   = trim($this->config->item('openrouter_model'));

        $body = [
            "model" => $model,
            "messages" => [
                [
                    "role" => "system",
                    "content" => $system_context
                ],
                [
                    "role" => "user",
                    "content" => $user_text
                ]
            ],
            "temperature" => 0.3,
            "max_tokens" => 512
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: application/json',
                'HTTP-Referer: https://3wsupport.rtafsar.com',
                'X-Title: 3W After Sale AI'
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            log_message('error', 'OpenRouter CURL : ' . curl_error($ch));

            curl_close($ch);

            return 'ขออภัยครับ ระบบ AI ไม่สามารถเชื่อมต่อได้';
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        log_message('error', 'OpenRouter HTTP : ' . $http_code);

        if ($http_code != 200) {

            log_message('error', 'OpenRouter Response : ' . $response);

            $json = json_decode($response, true);

            return $json['error']['message']
                ?? 'AI ตอบกลับผิดพลาด (' . $http_code . ')';
        }

        $json = json_decode($response, true);

        if (isset($json['error'])) {

            log_message('error', json_encode($json['error'], JSON_UNESCAPED_UNICODE));

            return $json['error']['message'];
        }

        $reply = $json['choices'][0]['message']['content'] ?? '';

        if ($reply == '') {

            log_message('error', 'OpenRouter Invalid Response : ' . $response);

            return 'ขออภัยครับ AI ไม่สามารถสร้างคำตอบได้';
        }

        return trim($reply);
    }

    private function _verify_signature($raw_body)
    {
        $secret    = $this->config->item('line_channel_secret');
        $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
        $hash      = base64_encode(hash_hmac('sha256', $raw_body, $secret, true));
        return hash_equals($hash, $signature);
    }

    private function _contains($text, $keywords)
    {
        foreach ($keywords as $kw) {
            if (mb_strpos($text, $kw) !== false) return true;
        }
        return false;
    }

    private function _status_label($status)
    {
        $labels = [
            'pending'        => 'รออนุมัติ',
            'approved'       => 'อนุมัติแล้ว',
            'assigned'       => 'มอบหมายช่างแล้ว',
            'in_progress'    => 'กำลังซ่อม',
            'wait_quote'     => 'รอใบเสนอราคา',
            'wait_review'    => 'กำลังตรวจสอบราคา',
            'wait_confirm'   => 'รอลูกค้ายืนยันราคา',
            'quote_accepted' => 'ลูกค้ายืนยันแล้ว',
            'quote_rejected' => 'ลูกค้าปฏิเสธ',
            'escalated'      => 'ส่งต่อ Partner',
            'completed'      => 'เสร็จสิ้น',
            'closed'         => 'ปิด',
        ];
        return $labels[$status] ?? $status;
    }

    private function _handle_quote_response($line_uid, $customer, $action, $ticket_id)
    {
        $ticket = $this->Ticket_model->get_by_id($ticket_id);

        // เช็คว่า ticket นี้เป็นของลูกค้าคนนี้จริงมั้ย
        if (!$ticket || $ticket->customer_id != $customer->id) {
            $this->line_notify->push($line_uid, "ไม่พบ Ticket นี้ในระบบครับ");
            return;
        }

        // ต้องเป็นใบเสนอราคาที่ Admin ส่งให้ลูกค้าแล้วจริงๆ (wait_confirm) เท่านั้น
        // ป้องกันลูกค้ายืนยัน/ปฏิเสธข้ามขั้นตอน ก่อน Admin ตรวจสอบ/ดีดราคา หรือหลัง ticket เปลี่ยนสถานะไปแล้ว
        if ($ticket->status !== Ticket_model::STATUS_WAIT_CONFIRM) {
            $this->line_notify->push($line_uid, "ไม่พบใบเสนอราคาที่รอการยืนยันสำหรับ Ticket #{$ticket_id} ครับ");
            return;
        }

        if ($action === 'ยืนยัน') {
            $this->Ticket_model->update_status($ticket_id, 'quote_accepted');
            $this->line_notify->push(
                $line_uid,
                "✅ ยืนยันการซ่อมเรียบร้อยแล้วครับ\n" .
                    "Ticket #{$ticket_id}\n" .
                    "ทีมงานจะดำเนินการซ่อมและแจ้งความคืบหน้าให้ทราบครับ"
            );
        } else {
            $this->Ticket_model->update_status($ticket_id, 'quote_rejected');
            $this->line_notify->push(
                $line_uid,
                "รับทราบครับ Ticket #{$ticket_id}\n" .
                    "ขอบคุณที่แจ้งให้ทราบ หากต้องการสอบถามเพิ่มเติมกรุณาติดต่อแอดมินโดยตรงครับ"
            );
        }

        // บันทึก log
        $this->db->insert('ticket_logs', [
            'ticket_id'  => $ticket_id,
            'user_id'    => NULL,
            'old_status' => 'wait_confirm',
            'new_status' => $action === 'ยืนยัน' ? 'quote_accepted' : 'quote_rejected',
            'message'    => 'ลูกค้า' . $action . 'ใบเสนอราคาผ่าน Line OA',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function _handle_confirm_quote($line_uid, $customer, $action)
    {
        // หา ticket ล่าสุดที่รอยืนยันของลูกค้าคนนี้
        $ticket = $this->db
            ->where('customer_id', $customer->id)
            ->where('status', 'wait_confirm')
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get('tickets')->row();

        if (!$ticket) {
            $this->line_notify->push(
                $line_uid,
                "ไม่พบใบเสนอราคาที่รอการยืนยันครับ\n" .
                    "พิมพ์ 'สถานะ' เพื่อดูรายการทั้งหมด"
            );
            return;
        }

        $this->_handle_quote_response($line_uid, $customer, $action, $ticket->id);
    }
}
