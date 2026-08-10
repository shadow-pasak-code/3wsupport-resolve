<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class History extends Admin_Controller
{
    private $month_names = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $today = date('Y-m-d');
        $year  = (int) ($this->input->get('year') ?: date('Y'));
        $month = (int) ($this->input->get('month') ?: date('n'));
        $tech_id = $this->input->get('tech_id') ?: 'all';

        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }

        $month_start = sprintf('%04d-%02d-01', $year, $month);
        $month_end   = date('Y-m-t', strtotime($month_start));

        // รายชื่อช่าง (ที่ยังใช้งานอยู่)
        $technicians = $this->db
            ->select('id, name, avatar')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('technicians')->result();

        // นับจำนวนงานต่อช่างในเดือนนี้ (สำหรับ badge บนแท็บ)
        $counts = $this->db
            ->select('technician_id, COUNT(*) as cnt')
            ->from('tickets')
            ->where('technician_id IS NOT NULL', null, false)
            ->where('partner_id IS NULL', null, false)
            ->where('tech_start_date IS NOT NULL', null, false)
            ->where('tech_start_date <=', $month_end)
            ->where('COALESCE(tech_end_date, tech_start_date) >=', $month_start)
            ->group_by('technician_id')
            ->get()->result();
        $count_map = [];
        foreach ($counts as $c) {
            $count_map[$c->technician_id] = (int) $c->cnt;
        }

        // ดึงรายการซ่อมของเดือนนี้ (ของช่างที่เลือก หรือทั้งหมด)
        $query = $this->db
            ->select('t.id, t.status, t.ticket_type, t.issue_desc, t.tech_start_date, t.tech_end_date,
                      t.technician_id, c.company_name as customer_name, d.name as device_name,
                      d.serial_number, tech.name as technician_name')
            ->from('tickets t')
            ->join('customers c', 'c.id = t.customer_id', 'left')
            ->join('devices d', 'd.id = t.device_id', 'left')
            ->join('technicians tech', 'tech.id = t.technician_id', 'left')
            ->where('t.technician_id IS NOT NULL', null, false)
            ->where('t.partner_id IS NULL', null, false)
            ->where('t.tech_start_date IS NOT NULL', null, false)
            ->where('t.tech_start_date <=', $month_end)
            ->where('COALESCE(t.tech_end_date, t.tech_start_date) >=', $month_start);

        if ($tech_id !== 'all') {
            $query->where('t.technician_id', $tech_id);
        }

        $tickets = $query->order_by('t.tech_start_date', 'ASC')->get()->result();

        // สร้างตารางปฏิทิน (แถวสัปดาห์ x 7 วัน) พร้อมจัด ticket ที่วันติดกันให้เป็น "แท่งเดียว" ยาวข้ามวัน
        $first_dow = (int) date('w', strtotime($month_start));
        $grid_start = strtotime("-$first_dow day", strtotime($month_start));

        $weeks = [];
        $cursor = $grid_start;
        for ($w = 0; $w < 6; $w++) {
            $week_days = [];
            for ($d = 0; $d < 7; $d++) {
                $date_key = date('Y-m-d', $cursor);
                $week_days[] = [
                    'date'     => $date_key,
                    'day_num'  => (int) date('j', $cursor),
                    'in_month' => ((int) date('n', $cursor) === $month),
                    'is_today' => ($date_key === $today),
                ];
                $cursor = strtotime('+1 day', $cursor);
            }
            $week_start = $week_days[0]['date'];
            $week_end   = $week_days[6]['date'];

            // หา ticket ที่ช่วงวันงานทับกับสัปดาห์นี้ แล้วตัดให้เหลือเฉพาะช่วงในสัปดาห์นี้ (1 ticket = 1 แท่ง ต่อสัปดาห์)
            $bars = [];
            foreach ($tickets as $t) {
                $t_end = $t->tech_end_date ?: $t->tech_start_date;
                if ($t->tech_start_date > $week_end || $t_end < $week_start) {
                    continue;
                }
                $seg_start = max($t->tech_start_date, $week_start);
                $seg_end   = min($t_end, $week_end);
                $bars[] = [
                    'ticket'           => $t,
                    'col_start'        => (int) round((strtotime($seg_start) - strtotime($week_start)) / 86400),
                    'col_span'         => (int) round((strtotime($seg_end) - strtotime($seg_start)) / 86400) + 1,
                    'continues_before' => $t->tech_start_date < $week_start,
                    'continues_after'  => $t_end > $week_end,
                ];
            }

            // จัดแถวไม่ให้แท่งที่วันซ้อนกันทับกัน (แบบเดียวกับ Google Calendar month view)
            usort($bars, fn($a, $b) => $a['col_start'] <=> $b['col_start']);
            $row_ends = [];
            foreach ($bars as &$bar) {
                $placed = false;
                foreach ($row_ends as $r => $end_col) {
                    if ($bar['col_start'] >= $end_col) {
                        $bar['row'] = $r;
                        $row_ends[$r] = $bar['col_start'] + $bar['col_span'];
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $bar['row'] = count($row_ends);
                    $row_ends[] = $bar['col_start'] + $bar['col_span'];
                }
            }
            unset($bar);

            $weeks[] = [
                'days'     => $week_days,
                'bars'     => $bars,
                'max_rows' => count($row_ends),
            ];

            // หยุดถ้าสัปดาห์นี้เลยสิ้นเดือนไปแล้ว และครบอย่างน้อย 4 แถว
            if ($w >= 3 && strtotime($week_end) >= strtotime($month_end)) {
                break;
            }
        }

        $data['technicians'] = $technicians;
        $data['count_map']   = $count_map;
        $data['tech_id']     = $tech_id;
        $data['year']        = $year;
        $data['month']       = $month;
        $data['month_label'] = $this->month_names[$month] . ' ' . ($year + 543);
        $data['weeks']       = $weeks;
        $data['total_month'] = count($tickets);

        // สำหรับปุ่มเดือนก่อนหน้า / ถัดไป
        $prev = $month - 1; $prev_year = $year;
        if ($prev < 1) { $prev = 12; $prev_year--; }
        $next = $month + 1; $next_year = $year;
        if ($next > 12) { $next = 1; $next_year++; }
        $data['prev_month'] = $prev; $data['prev_year'] = $prev_year;
        $data['next_month'] = $next; $data['next_year'] = $next_year;

        $this->render('history/index', $data);
    }
}
