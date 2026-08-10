<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงาน Ticket</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Sarabun', sans-serif; }
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
    }
</style>
</head>
<body class="bg-slate-100 min-h-screen py-6 px-4">

<!-- Filter + Print -->
<div class="no-print max-w-5xl mx-auto mb-5">
    <div class="bg-white border border-slate-200 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">🔍 กรองรายงาน</h2>
        <form method="GET" class="flex gap-3 flex-wrap items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">วันที่เริ่ม</label>
                <input type="date" name="date_from" value="<?= $date_from ?>"
                    class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">วันที่สิ้นสุด</label>
                <input type="date" name="date_to" value="<?= $date_to ?>"
                    class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">สถานะ</label>
                <select name="status" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                    <option value="">ทุกสถานะ</option>
                    <?php
                    $statuses = ['pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว','assigned'=>'มอบหมายแล้ว',
                        'in_progress'=>'กำลังซ่อม','waiting_parts'=>'รออะไหล่','wait_quote'=>'รอใบเสนอราคา','wait_review'=>'รอตรวจสอบราคา',
                        'wait_confirm'=>'รอลูกค้ายืนยัน',
                        'quote_accepted'=>'ลูกค้ายืนยัน','quote_rejected'=>'ลูกค้าปฏิเสธ',
                        'escalated'=>'ส่งต่อ Partner','completed'=>'เสร็จสิ้น','closed'=>'ปิด'];
                    foreach ($statuses as $val => $lbl):
                    ?>
                    <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">ประเภท</label>
                <select name="type" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                    <option value="">ทุกประเภท</option>
                    <option value="hardware" <?= $type === 'hardware' ? 'selected' : '' ?>>Hardware</option>
                    <option value="software" <?= $type === 'software' ? 'selected' : '' ?>>Software</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">กรอง</button>
            <button type="button" onclick="window.print()"
                class="bg-slate-700 hover:bg-slate-800 text-white text-sm px-4 py-2 rounded-lg">
                🖨️ พิมพ์ / บันทึก PDF
            </button>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">

    <!-- Header -->
    <div class="bg-slate-800 px-8 py-5 flex justify-between items-center">
        <div>
            <h1 class="text-white text-lg font-bold">รายงานสรุป Ticket</h1>
            <p class="text-slate-400 text-xs mt-0.5">
                ช่วงวันที่ <?= date('d/m/Y', strtotime($date_from)) ?>
                — <?= date('d/m/Y', strtotime($date_to)) ?>
            </p>
        </div>
        <div class="text-right">
            <p class="text-white font-semibold">Three W Business and Solutions</p>
            <p class="text-slate-400 text-xs">พิมพ์เมื่อ <?= date('d/m/Y H:i') ?></p>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-4 gap-0 border-b border-slate-100">
        <div class="px-6 py-4 border-r border-slate-100 text-center">
            <p class="text-2xl font-bold text-slate-800"><?= $total ?></p>
            <p class="text-xs text-slate-400 mt-0.5">Ticket ทั้งหมด</p>
        </div>
        <div class="px-6 py-4 border-r border-slate-100 text-center">
            <p class="text-2xl font-bold text-orange-600"><?= $hw_count ?></p>
            <p class="text-xs text-slate-400 mt-0.5">Hardware</p>
        </div>
        <div class="px-6 py-4 border-r border-slate-100 text-center">
            <p class="text-2xl font-bold text-sky-600"><?= $sw_count ?></p>
            <p class="text-xs text-slate-400 mt-0.5">Software</p>
        </div>
        <div class="px-6 py-4 text-center">
            <p class="text-2xl font-bold text-green-600">
                <?= count(array_filter((array)$tickets, fn($t) => in_array($t->status, ['completed','closed']))) ?>
            </p>
            <p class="text-xs text-slate-400 mt-0.5">เสร็จสิ้น</p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">ลูกค้า</th>
                    <th class="px-5 py-3 text-left">อุปกรณ์ / S/N</th>
                    <th class="px-5 py-3 text-left">ประเภท</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3 text-left">ผู้รับผิดชอบ</th>
                    <th class="px-5 py-3 text-left">วันที่แจ้ง</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($tickets)): ?>
                <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">ไม่พบข้อมูล</td></tr>
            <?php else: ?>
                <?php
                $status_labels = [
                    'pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว','assigned'=>'มอบหมายแล้ว',
                    'in_progress'=>'กำลังซ่อม','waiting_parts'=>'รออะไหล่','wait_quote'=>'รอใบเสนอราคา','wait_review'=>'รอตรวจสอบราคา',
                    'wait_confirm'=>'รอลูกค้ายืนยัน','quote_accepted'=>'ลูกค้ายืนยัน',
                    'quote_rejected'=>'ลูกค้าปฏิเสธ','escalated'=>'ส่งต่อ Partner',
                    'completed'=>'เสร็จสิ้น','closed'=>'ปิด',
                ];
                foreach ($tickets as $t):
                ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-400 font-mono text-xs">#<?= $t->id ?></td>
                    <td class="px-5 py-3">
                        <p class="font-medium"><?= $t->customer_name ?></p>
                        <p class="text-xs text-slate-400"><?= $t->phone ?></p>
                    </td>
                    <td class="px-5 py-3">
                        <p><?= $t->device_name ?></p>
                        <p class="text-xs font-mono text-slate-400"><?= $t->serial_number ?></p>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($t->ticket_type === 'hardware'): ?>
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
                        <?php else: ?>
                        <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-xs"><?= $status_labels[$t->status] ?? $t->status ?></td>
                    <td class="px-5 py-3 text-xs text-slate-600">
                        <?= $t->technician_name ?? $t->partner_name ?? '—' ?>
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-400">
                        <?= date('d/m/Y', strtotime($t->created_at)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="border-t border-slate-100 px-8 py-4 text-center">
        <p class="text-xs text-slate-400">Three W Business and Solutions · ระบบบริการหลังการขาย · เอกสารนี้สร้างอัตโนมัติ</p>
    </div>

</div>

</body>
</html>