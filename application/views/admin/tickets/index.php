<?php $page_title = 'จัดการ Ticket'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">จัดการ Ticket</h1>
            <?php if ($hide_done): ?>
            <p class="text-xs text-slate-400 mt-0.5">แสดงเฉพาะ Ticket ที่ยังดำเนินการอยู่</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" class="flex gap-3 mb-5 flex-wrap">
        <input type="hidden" name="filtered" value="1">
        <select name="status"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกสถานะ (รวมเสร็จแล้ว)</option>
            <?php
            $statuses = [
                'pending'            => 'รออนุมัติ',
                'approved'           => 'อนุมัติแล้ว',
                'assigned'           => 'มอบหมายแล้ว',
                'in_progress'        => 'กำลังซ่อม',
                'waiting_parts'      => 'รออะไหล่',
                'wait_quote'         => 'รอใบเสนอราคา',
                'wait_review'        => 'รอตรวจสอบราคา',
                'wait_confirm'       => 'รอลูกค้ายืนยัน',
                'quote_accepted'     => 'ลูกค้ายืนยันแล้ว',
                'quote_rejected'     => 'ลูกค้าปฏิเสธ',
                'escalated'          => 'ส่งต่อ Partner',
                'partner_completed'  => 'Partner ซ่อมเสร็จ',
                'completed'          => 'เสร็จสิ้น',
                'closed'             => 'ปิด',
            ];
            foreach ($statuses as $val => $label):
                $sel = ($filters['status'] ?? '') === $val ? 'selected' : '';
            ?>
            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <select name="type"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกประเภท</option>
            <option value="hardware" <?= ($filters['type'] ?? '') === 'hardware' ? 'selected' : '' ?>>Hardware</option>
            <option value="software" <?= ($filters['type'] ?? '') === 'software' ? 'selected' : '' ?>>Software</option>
        </select>

        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            กรอง
        </button>
        <a href="<?= base_url('admin/tickets') ?>"
           class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">ล้างตัวกรอง</a>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">ลูกค้า</th>
                    <th class="px-5 py-3 text-left">อุปกรณ์ / S/N</th>
                    <th class="px-5 py-3 text-left">ประเภท</th>
                    <th class="px-5 py-3 text-left">ผู้รับผิดชอบ</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3 text-left">วันที่</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($tickets)): ?>
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">ไม่พบ Ticket</td></tr>
            <?php else: ?>
                <?php
                $prev_customer = null;
                foreach ($tickets as $t):
                    $is_new = ($prev_customer !== $t->customer_name);
                    $prev_customer = $t->customer_name;
                ?>
                <tr class="hover:bg-slate-50 <?= $is_new && $prev_customer !== null ? 'border-t-2 border-slate-200' : '' ?>">
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">#<?= $t->id ?></td>
                    <td class="px-5 py-3">
                        <?php if ($is_new): ?>
                        <p class="font-medium text-slate-800"><?= $t->customer_name ?></p>
                        <?php else: ?>
                        <p class="text-slate-300 text-xs">↳</p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-slate-700"><?= $t->device_name ?></p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5"><?= $t->serial_number ?></p>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($t->ticket_type === 'hardware'): ?>
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-slate-600 text-xs">
                        <?= $t->technician_name ?? ($t->partner_name ?? '<span class="text-slate-300">—</span>') ?>
                    </td>
                    <td class="px-5 py-3"><?= status_badge($t->status) ?></td>
                    <td class="px-5 py-3 text-slate-400 text-xs"><?= date('d/m/y', strtotime($t->created_at)) ?></td>
                    <td class="px-5 py-3">
                        <a href="<?= base_url('admin/tickets/detail/' . $t->id) ?>"
                           class="text-xs text-blue-600 hover:underline">จัดการ</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if ($this->session->flashdata('success')): ?>
Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $this->session->flashdata("success") ?>', timer: 2000, showConfirmButton: false });
<?php endif; ?>
</script>

<?php
function status_badge($status) {
    $map = [
        'pending'        => ['รออนุมัติ',         'bg-amber-100 text-amber-700'],
        'partner_completed' => ['Partner ซ่อมเสร็จ ⚡', 'bg-teal-100 text-teal-700'],
        'approved'       => ['อนุมัติแล้ว',        'bg-blue-100 text-blue-700'],
        'assigned'       => ['มอบหมายแล้ว',        'bg-indigo-100 text-indigo-700'],
        'in_progress'    => ['กำลังซ่อม',          'bg-sky-100 text-sky-700'],
        'waiting_parts'  => ['รออะไหล่',           'bg-rose-100 text-rose-700'],
        'wait_quote'     => ['รอใบเสนอราคา',       'bg-purple-100 text-purple-700'],
        'wait_review'    => ['รอตรวจสอบราคา',      'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'   => ['รอลูกค้ายืนยัน',     'bg-pink-100 text-pink-700'],
        'quote_accepted' => ['ลูกค้ายืนยันแล้ว',   'bg-green-100 text-green-700'],
        'quote_rejected' => ['ลูกค้าปฏิเสธ',       'bg-red-100 text-red-700'],
        'escalated'      => ['ส่งต่อ Partner',      'bg-orange-100 text-orange-700'],
        'completed'      => ['เสร็จสิ้น',           'bg-green-100 text-green-700'],
        'closed'         => ['ปิด',                 'bg-slate-100 text-slate-500'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2 py-0.5 rounded text-xs $cls'>$label</span>";
}
?>