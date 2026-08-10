<?php $page_title = 'งานที่ได้รับ'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">งานที่ได้รับมอบหมาย</h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">อุปกรณ์</th>
                    <th class="px-5 py-3 text-left">อาการ</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3 text-left">ใบเสนอราคา</th>
                    <th class="px-5 py-3 text-left">วันที่รับงาน</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            ยังไม่มีงานที่ได้รับมอบหมาย
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-400">#<?= $t->id ?></td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800"><?= $t->device_name ?></p>
                                <p class="text-xs text-slate-400 mt-0.5"><?= $t->serial_number ?></p>
                            </td>
                            <td class="px-5 py-3 text-slate-600 max-w-xs">
                                <p class="truncate"><?= $t->issue_desc ?></p>
                            </td>
                            <td class="px-5 py-3"><?= status_badge($t->status) ?></td>
                            <td class="px-5 py-3">
                                <?php if ($t->partner_quote_amount): ?>
                                    <span class="text-sm font-semibold text-purple-700">
                                        ฿<?= number_format($t->partner_quote_amount, 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">ยังไม่มี</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-400">
                                <?= date('d/m/Y', strtotime($t->created_at)) ?>
                            </td>
                            <td class="px-5 py-3">
                                <a href="<?= base_url('partner/tickets/detail/' . $t->id) ?>"
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
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: '<?= $this->session->flashdata("success") ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
</script>

<?php
function status_badge($status)
{
    $map = [
        'assigned'       => ['รอรับงาน',         'bg-indigo-100 text-indigo-700'],
        'wait_quote'     => ['รอกรอก Quotation', 'bg-purple-100 text-purple-700'],
        'wait_review'    => ['รอ Admin ตรวจสอบราคา', 'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'   => ['รอลูกค้ายืนยัน',   'bg-pink-100 text-pink-700'],
        'quote_accepted' => ['ลูกค้ายืนยันแล้ว',  'bg-green-100 text-green-700'],
        'quote_rejected' => ['ลูกค้าปฏิเสธ',      'bg-red-100 text-red-700'],
        'in_progress'    => ['กำลังซ่อม',          'bg-sky-100 text-sky-700'],
        'waiting_parts'  => ['รออะไหล่',           'bg-rose-100 text-rose-700'],
        'completed'      => ['เสร็จสิ้น',           'bg-green-100 text-green-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2 py-0.5 rounded text-xs $cls'>$label</span>";
}
?>