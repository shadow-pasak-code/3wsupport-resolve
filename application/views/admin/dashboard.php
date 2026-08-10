<?php $page_title = 'แดชบอร์ด'; ?>

<div class="p-6">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">แดชบอร์ด</h1>
            <p class="text-xs text-slate-400 mt-0.5"><?= date('l, d F Y') ?></p>
        </div>
        <a href="<?= base_url('admin/dashboard/report') ?>" target="_blank"
            class="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm px-4 py-2 rounded-lg">
            🖨️ สร้างรายงาน
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-xs text-slate-400 mb-1">Ticket ทั้งหมด</p>
            <p class="text-3xl font-bold text-slate-800"><?= $total_tickets ?></p>
            <div class="flex gap-2 mt-2">
                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded">รอ <?= $pending_tickets ?></span>
                <span class="text-xs text-sky-600 bg-sky-50 px-2 py-0.5 rounded">ดำเนินการ <?= $active_tickets ?></span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-xs text-slate-400 mb-1">เสร็จสิ้นแล้ว</p>
            <p class="text-3xl font-bold text-green-600"><?= $completed_tickets ?></p>
            <p class="text-xs text-slate-400 mt-2">
                <?= $total_tickets > 0 ? round($completed_tickets / $total_tickets * 100) : 0 ?>% ของทั้งหมด
            </p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-xs text-slate-400 mb-1">ลูกค้าทั้งหมด</p>
            <p class="text-3xl font-bold text-slate-800"><?= $total_customers ?></p>
            <div class="flex gap-2 mt-2">
                <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded">ผูก Line <?= $line_linked ?></span>
                <span class="text-xs text-slate-400 bg-slate-50 px-2 py-0.5 rounded">ยังไม่ผูก <?= $total_customers - $line_linked ?></span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-xs text-slate-400 mb-1">อุปกรณ์ทั้งหมด</p>
            <p class="text-3xl font-bold text-slate-800"><?= $total_devices ?></p>
            <div class="flex gap-2 mt-2">
                <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded">ในประกัน <?= $warranty_valid ?></span>
                <span class="text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded">หมดประกัน <?= $warranty_expired ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

        <!-- Ticket by Status (Donut) -->
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Ticket ตามสถานะ</h2>
            <canvas id="chart-status" height="200"></canvas>
        </div>

        <!-- Ticket Monthly (Bar) -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Ticket รายเดือน (12 เดือนล่าสุด)</h2>
            <canvas id="chart-monthly" height="120"></canvas>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

        <!-- Hardware vs Software -->
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">ประเภท Ticket</h2>
            <canvas id="chart-type" height="160"></canvas>
        </div>

        <!-- Ticket by Partner -->
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">งานแยกตาม Partner</h2>
            <?php if (empty($ticket_by_partner)): ?>
                <p class="text-slate-400 text-sm text-center py-8">ยังไม่มีข้อมูล</p>
            <?php else: ?>
                <canvas id="chart-partner" height="160"></canvas>
            <?php endif; ?>
        </div>

    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Partner ซ่อมเสร็จ รอแจ้งลูกค้า -->
        <?php if (!empty($partner_done_list)): ?>
            <div class="bg-teal-50 border border-teal-200 rounded-xl overflow-hidden mb-5">
                <div class="flex items-center justify-between px-5 py-4 border-b border-teal-100">
                    <h2 class="text-sm font-semibold text-teal-700">⚡ Partner ซ่อมเสร็จ — รอแจ้งลูกค้า (<?= count($partner_done_list) ?>)</h2>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-teal-100">
                        <?php foreach ($partner_done_list as $t): ?>
                            <tr class="hover:bg-teal-50/50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800"><?= $t->customer_name ?></p>
                                    <p class="text-xs text-slate-400"><?= $t->device_name ?></p>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="<?= base_url('admin/tickets/detail/' . $t->id) ?>"
                                        class="text-xs bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg">
                                        แจ้งลูกค้า
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- Pending Tickets -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">🔔 รออนุมัติ (<?= $pending_tickets ?>)</h2>
                <a href="<?= base_url('admin/tickets?status=pending') ?>"
                    class="text-xs text-blue-600 hover:underline">ดูทั้งหมด</a>
            </div>
            <?php if (empty($pending_list)): ?>
                <p class="text-slate-400 text-sm text-center py-8">ไม่มี Ticket รออนุมัติ ✅</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($pending_list as $t): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800"><?= $t->customer_name ?></p>
                                    <p class="text-xs text-slate-400"><?= $t->device_name ?></p>
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-400">
                                    <?= date('d/m/Y', strtotime($t->created_at)) ?>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="<?= base_url('admin/tickets/detail/' . $t->id) ?>"
                                        class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg">
                                        อนุมัติ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Active Tickets -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">🔧 กำลังดำเนินการ (<?= $active_tickets ?>)</h2>
                <a href="<?= base_url('admin/tickets?status=in_progress') ?>"
                    class="text-xs text-blue-600 hover:underline">ดูทั้งหมด</a>
            </div>
            <?php if (empty($active_list)): ?>
                <p class="text-slate-400 text-sm text-center py-8">ไม่มี Ticket กำลังดำเนินการ</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $status_labels = [
                            'assigned'       => ['มอบหมายแล้ว',      'bg-indigo-100 text-indigo-700'],
                            'in_progress'    => ['กำลังซ่อม',         'bg-sky-100 text-sky-700'],
                            'waiting_parts'  => ['รออะไหล่',          'bg-rose-100 text-rose-700'],
                            'wait_quote'     => ['รอใบเสนอราคา',      'bg-purple-100 text-purple-700'],
                            'wait_review'    => ['รอตรวจสอบราคา',     'bg-fuchsia-100 text-fuchsia-700'],
                            'wait_confirm'   => ['รอลูกค้ายืนยัน',    'bg-pink-100 text-pink-700'],
                            'quote_accepted' => ['ลูกค้ายืนยันแล้ว',  'bg-green-100 text-green-700'],
                            'escalated'      => ['ส่งต่อ Partner',     'bg-orange-100 text-orange-700'],
                        ];
                        foreach ($active_list as $t):
                            [$slabel, $scls] = $status_labels[$t->status] ?? [$t->status, 'bg-slate-100 text-slate-500'];
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800"><?= $t->customer_name ?></p>
                                    <p class="text-xs text-slate-400"><?= $t->device_name ?></p>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs <?= $scls ?>"><?= $slabel ?></span>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        <?= $t->technician_name ?? $t->partner_name ?? '—' ?>
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="<?= base_url('admin/tickets/detail/' . $t->id) ?>"
                                        class="text-xs text-blue-600 hover:underline">จัดการ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ข้อมูลจาก PHP
    const statusData = <?php
                        $labels = [
                            'pending' => 'รออนุมัติ',
                            'approved' => 'อนุมัติแล้ว',
                            'assigned' => 'มอบหมายแล้ว',
                            'in_progress' => 'กำลังซ่อม',
                            'wait_quote' => 'รอใบเสนอราคา',
                            'wait_review' => 'รอตรวจสอบราคา',
                            'wait_confirm' => 'รอลูกค้ายืนยัน',
                            'quote_accepted' => 'ลูกค้ายืนยัน',
                            'quote_rejected' => 'ลูกค้าปฏิเสธ',
                            'escalated' => 'ส่งต่อ Partner',
                            'completed' => 'เสร็จสิ้น',
                            'closed' => 'ปิด',
                        ];
                        $sd = [];
                        foreach ($ticket_by_status as $r) {
                            $sd[] = ['label' => $labels[$r->status] ?? $r->status, 'value' => (int)$r->cnt];
                        }
                        echo json_encode($sd);
                        ?>;

    const monthlyData = <?php
                        $md = [];
                        foreach ($ticket_monthly as $r) {
                            $md[] = ['month' => $r->month, 'cnt' => (int)$r->cnt];
                        }
                        echo json_encode($md);
                        ?>;

    const COLORS = [
        '#f59e0b', '#3b82f6', '#6366f1', '#0ea5e9',
        '#a855f7', '#ec4899', '#22c55e', '#ef4444',
        '#f97316', '#10b981', '#64748b'
    ];

    // Chart: Ticket by Status (Donut)
    new Chart(document.getElementById('chart-status'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(d => d.label),
            datasets: [{
                data: statusData.map(d => d.value),
                backgroundColor: COLORS,
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        boxWidth: 12
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Chart: Monthly (Bar)
    new Chart(document.getElementById('chart-monthly'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'จำนวน Ticket',
                data: monthlyData.map(d => d.cnt),
                backgroundColor: '#3b82f6',
                borderRadius: 6
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Chart: Hardware vs Software
    new Chart(document.getElementById('chart-type'), {
        type: 'pie',
        data: {
            labels: ['Hardware', 'Software'],
            datasets: [{
                data: [<?= $hw_count ?>, <?= $sw_count ?>],
                backgroundColor: ['#f97316', '#0ea5e9'],
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12
                        },
                        boxWidth: 14
                    }
                }
            }
        }
    });

    <?php if (!empty($ticket_by_partner)): ?>
        // Chart: Partner
        new Chart(document.getElementById('chart-partner'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(fn($r) => $r->company_name ?? 'ไม่ระบุ', $ticket_by_partner)) ?>,
                datasets: [{
                    label: 'จำนวน Ticket',
                    data: <?= json_encode(array_map(fn($r) => (int)$r->cnt, $ticket_by_partner)) ?>,
                    backgroundColor: ['#a855f7', '#6366f1', '#3b82f6'],
                    borderRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                indexAxis: 'y'
            }
        });
    <?php endif; ?>
</script>