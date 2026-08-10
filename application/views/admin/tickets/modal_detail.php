<?php
$status_map = [
    'pending'        => ['รออนุมัติ',         'bg-amber-100 text-amber-700'],
    'approved'       => ['อนุมัติแล้ว',        'bg-blue-100 text-blue-700'],
    'assigned'       => ['มอบหมายแล้ว',        'bg-indigo-100 text-indigo-700'],
    'in_progress'    => ['กำลังซ่อม',          'bg-sky-100 text-sky-700'],
    'waiting_parts'  => ['รออะไหล่',           'bg-rose-100 text-rose-700'],
    'wait_quote'     => ['รอใบเสนอราคา',       'bg-purple-100 text-purple-700'],
    'wait_review'    => ['รอตรวจสอบราคา',      'bg-fuchsia-100 text-fuchsia-700'],
    'wait_confirm'   => ['รอลูกค้ายืนยัน',     'bg-pink-100 text-pink-700'],
    'quote_accepted' => ['ลูกค้ายืนยัน',       'bg-green-100 text-green-700'],
    'quote_rejected' => ['ลูกค้าปฏิเสธ',       'bg-red-100 text-red-700'],
    'escalated'      => ['ส่งต่อ Partner',      'bg-orange-100 text-orange-700'],
    'completed'      => ['เสร็จสิ้น',           'bg-green-100 text-green-700'],
    'closed'         => ['ปิด',                 'bg-slate-100 text-slate-500'],
];
[$slabel, $scls] = $status_map[$ticket->status] ?? [$ticket->status, 'bg-slate-100 text-slate-500'];
?>

<!-- สถานะ -->
<div class="flex items-center gap-2 mb-4">
    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $scls ?>"><?= $slabel ?></span>
    <?php if ($ticket->ticket_type === 'hardware'): ?>
        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
    <?php else: ?>
        <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>
    <?php endif; ?>
</div>

<!-- ข้อมูลหลัก -->
<dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm mb-5">
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">ลูกค้า</dt>
        <dd class="font-medium"><?= $ticket->customer_name ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">เบอร์โทร</dt>
        <dd><?= $ticket->phone ?? '—' ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">อุปกรณ์</dt>
        <dd class="font-medium"><?= $ticket->device_name ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">Serial Number</dt>
        <dd class="font-mono text-xs"><?= $ticket->serial_number ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">ผู้รับผิดชอบ</dt>
        <dd><?= $ticket->technician_name ?? $ticket->partner_name ?? '—' ?></dd>
    </div>
    <div>
        <dt class="text-xs text-slate-400 mb-0.5">วันที่แจ้ง</dt>
        <dd><?= date('d/m/Y H:i', strtotime($ticket->created_at)) ?></dd>
    </div>
    <div class="col-span-2">
        <dt class="text-xs text-slate-400 mb-0.5">อาการ / ปัญหา</dt>
        <dd class="leading-relaxed"><?= nl2br($ticket->issue_desc) ?></dd>
    </div>
    <?php if ($ticket->tech_note): ?>
        <div class="col-span-2">
            <dt class="text-xs text-slate-400 mb-0.5">บันทึกจากช่าง</dt>
            <dd class="text-slate-600"><?= nl2br($ticket->tech_note) ?></dd>
        </div>
    <?php endif; ?>
</dl>

<!-- Quotation -->
<?php if ($admin_quotation): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <p class="text-xs font-semibold text-blue-600 mb-1">ใบเสนอราคา (ออกโดยบริษัท)</p>
        <p class="text-xl font-bold text-blue-800">฿<?= number_format($admin_quotation->total, 2) ?></p>
        <?php if ($admin_quotation->note): ?>
            <p class="text-xs text-blue-600 mt-1"><?= nl2br($admin_quotation->note) ?></p>
        <?php endif; ?>
        <a href="<?= base_url('quotation/view/' . $ticket->id) ?>" target="_blank"
            class="text-xs text-blue-600 hover:underline mt-1 inline-block">📄 ดูใบเสนอราคา</a>
    </div>
<?php elseif ($ticket->quote_amount): ?>
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
        <p class="text-xs font-semibold text-purple-600 mb-1">ใบเสนอราคาจาก Partner</p>
        <p class="text-xl font-bold text-purple-800">฿<?= number_format($ticket->quote_amount, 2) ?></p>
    </div>
<?php endif; ?>

<!-- Timeline -->
<?php if (!empty($logs)): ?>
    <div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">ประวัติดำเนินการ</p>
        <div class="space-y-2">
            <?php foreach ($logs as $log): ?>
                <div class="flex gap-3 text-sm">
                    <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-slate-700 text-xs"><?= $log->message ?></p>
                        <p class="text-slate-400 text-xs"><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>