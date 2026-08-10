<?php $page_title = 'งานของฉัน'; ?>

<div class="p-6">
    <h1 class="text-xl font-semibold mb-6">งานที่ได้รับมอบหมาย</h1>

    <?php if (empty($tickets)): ?>
    <div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-slate-400">
        ยังไม่มีงานที่ได้รับมอบหมาย
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($tickets as $t): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-sky-300 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs text-slate-400 font-mono">#<?= $t->id ?></span>
                        <?= status_badge($t->status) ?>
                        <?php if ($t->ticket_type === 'hardware'): ?>
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>
                        <?php endif; ?>
                    </div>
                    <p class="font-medium text-slate-800"><?= $t->device_name ?></p>
                    <p class="text-xs text-slate-400 mt-0.5"><?= $t->customer_name ?> · <?= $t->serial_number ?></p>
                    <p class="text-sm text-slate-600 mt-2 line-clamp-2"><?= $t->issue_desc ?></p>
                </div>
                <div class="shrink-0 text-right">
                    <?php if ($t->tech_end_date): ?>
                    <p class="text-xs text-slate-400 mb-1">คาดว่าเสร็จ</p>
                    <p class="text-sm font-medium"><?= date('d/m/y', strtotime($t->tech_end_date)) ?></p>
                    <?php endif; ?>
                    <a href="<?= base_url('tech/tickets/detail/' . $t->id) ?>"
                       class="mt-3 inline-block bg-sky-600 hover:bg-sky-700 text-white text-xs px-4 py-2 rounded-lg">
                        จัดการ
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
        'assigned'    => ['รอรับงาน',   'bg-indigo-100 text-indigo-700'],
        'wait_quote'  => ['รอกรอก Quotation', 'bg-purple-100 text-purple-700'],
        'in_progress' => ['กำลังซ่อม',  'bg-sky-100 text-sky-700'],
        'waiting_parts' => ['รออะไหล่', 'bg-rose-100 text-rose-700'],
        'wait_review' => ['รอ Admin ตรวจสอบราคา', 'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'=> ['รอลูกค้ายืนยัน', 'bg-pink-100 text-pink-700'],
        'escalated'   => ['ส่งต่อแล้ว', 'bg-orange-100 text-orange-700'],
        'completed'   => ['เสร็จสิ้น',  'bg-green-100 text-green-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2 py-0.5 rounded text-xs $cls'>$label</span>";
}
?>