<?php $page_title = 'ประวัติงานของฉัน'; ?>

<?php
$status_map = [
    'pending'           => ['รออนุมัติ',           'bg-amber-100 text-amber-700 border-amber-300'],
    'approved'          => ['อนุมัติแล้ว',          'bg-blue-100 text-blue-700 border-blue-300'],
    'assigned'          => ['รอรับงาน',             'bg-indigo-100 text-indigo-700 border-indigo-300'],
    'in_progress'       => ['กำลังซ่อม',            'bg-sky-100 text-sky-700 border-sky-300'],
    'waiting_parts'     => ['รออะไหล่',              'bg-rose-100 text-rose-700 border-rose-300'],
    'wait_quote'        => ['รอใบเสนอราคา',         'bg-purple-100 text-purple-700 border-purple-300'],
    'wait_review'       => ['รอตรวจสอบราคา',        'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-300'],
    'wait_confirm'      => ['รอลูกค้ายืนยัน',       'bg-pink-100 text-pink-700 border-pink-300'],
    'quote_accepted'    => ['ลูกค้ายืนยัน',         'bg-green-100 text-green-700 border-green-300'],
    'quote_rejected'    => ['ลูกค้าปฏิเสธ',         'bg-red-100 text-red-700 border-red-300'],
    'escalated'         => ['ส่งต่อแล้ว',            'bg-orange-100 text-orange-700 border-orange-300'],
    'partner_completed' => ['Partner ซ่อมเสร็จ',    'bg-teal-100 text-teal-700 border-teal-300'],
    'completed'         => ['เสร็จสิ้น',             'bg-green-100 text-green-700 border-green-300'],
    'closed'            => ['ปิด',                   'bg-slate-100 text-slate-500 border-slate-300'],
];
$weekday_labels = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

function tech_history_qs($params, $overrides)
{
    return http_build_query(array_merge($params, $overrides));
}
$base_params = ['month' => $month, 'year' => $year];
?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">ประวัติงานของฉัน — ปฏิทิน</h1>
    </div>

    <!-- ตัวเลือกเดือน -->
    <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-5 py-3 mb-5">
        <a href="?<?= tech_history_qs($base_params, ['month' => $prev_month, 'year' => $prev_year]) ?>"
            class="text-slate-500 hover:text-sky-600 px-3 py-1 rounded-lg hover:bg-slate-50">‹ เดือนก่อน</a>
        <div class="text-center">
            <p class="font-semibold text-slate-800"><?= $month_label ?></p>
            <p class="text-xs text-slate-400 mt-0.5">พบงานซ่อม <?= $total_month ?> รายการ</p>
        </div>
        <a href="?<?= tech_history_qs($base_params, ['month' => $next_month, 'year' => $next_year]) ?>"
            class="text-slate-500 hover:text-sky-600 px-3 py-1 rounded-lg hover:bg-slate-50">เดือนถัดไป ›</a>
    </div>

    <!-- ปฏิทิน -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="grid grid-cols-7 bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
            <?php foreach ($weekday_labels as $i => $wl): ?>
                <div class="px-3 py-2 text-center border-b border-slate-200 <?= $i === 0 ? 'text-red-400' : '' ?>"><?= $wl ?></div>
            <?php endforeach; ?>
        </div>
        <?php foreach ($weeks as $week):
            $bar_rows = max(1, $week['max_rows']);
        ?>
            <div class="relative" style="display:grid;grid-template-columns:repeat(7,1fr);grid-template-rows:24px repeat(<?= $bar_rows ?>,22px) 4px;">
                <!-- พื้นหลัง/เส้นขอบของแต่ละวัน (สูงเต็มความสูงของสัปดาห์นี้เสมอ) -->
                <?php foreach ($week['days'] as $i => $day): ?>
                    <div class="<?= $day['in_month'] ? '' : 'bg-slate-50/60' ?> border-b border-r border-slate-100 <?= $i === 6 ? 'border-r-0' : '' ?>"
                        style="grid-column:<?= $i + 1 ?>;grid-row:1 / -1;"></div>
                <?php endforeach; ?>

                <!-- เลขวันที่ -->
                <?php foreach ($week['days'] as $i => $day): ?>
                    <div class="px-1.5 pt-1" style="grid-column:<?= $i + 1 ?>;grid-row:1;">
                        <span class="text-xs <?= $day['is_today'] ? 'inline-flex items-center justify-center w-5 h-5 rounded-full bg-sky-600 text-white' : ($day['in_month'] ? 'text-slate-500' : 'text-slate-300') ?>">
                            <?= $day['day_num'] ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <!-- แท่งงานซ่อม: วันที่ต่อเนื่องกันจะกลืนเป็นแท่งเดียวข้ามคอลัมน์ -->
                <?php foreach ($week['bars'] as $bar):
                    $t = $bar['ticket'];
                    [$slabel, $scls] = $status_map[$t->status] ?? [$t->status, 'bg-slate-100 text-slate-500 border-slate-300'];
                    $round_l = $bar['continues_before'] ? '' : 'rounded-l';
                    $round_r = $bar['continues_after'] ? '' : 'rounded-r';
                ?>
                    <a href="<?= base_url('tech/tickets/detail/' . $t->id) ?>"
                        title="<?= addslashes($t->customer_name . ' — ' . $t->device_name) ?> (<?= $slabel ?>)"
                        class="relative mx-0.5 my-[1px] flex items-center text-[11px] leading-tight px-1.5 border-y border-l border-r <?= $scls ?> <?= $round_l ?> <?= $round_r ?> truncate text-left"
                        style="grid-column:<?= $bar['col_start'] + 1 ?> / span <?= $bar['col_span'] ?>;grid-row:<?= $bar['row'] + 2 ?>;<?= $bar['continues_before'] ? 'margin-left:0;border-left-style:dashed;' : '' ?><?= $bar['continues_after'] ? 'margin-right:0;border-right-style:dashed;' : '' ?>">
                        <?= $bar['continues_before'] ? '« ' : '' ?><?= $t->customer_name ?><?= $bar['continues_after'] ? ' »' : '' ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- คำอธิบายสี -->
    <div class="flex flex-wrap gap-3 mt-4">
        <?php foreach ($status_map as $key => [$slabel, $scls]): ?>
            <span class="flex items-center gap-1.5 text-xs text-slate-500">
                <span class="w-3 h-3 rounded border <?= $scls ?>"></span><?= $slabel ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>
