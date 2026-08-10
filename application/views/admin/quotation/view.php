<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ใบเสนอราคา #<?= $ticket->id ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Sarabun', sans-serif; }
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .page { box-shadow: none !important; margin: 0 !important; }
    }
</style>
</head>
<body class="bg-slate-100 min-h-screen py-8">

<!-- ปุ่ม Print -->
<div class="no-print text-center mb-6 flex justify-center gap-3">
    <button onclick="window.print()"
        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
        🖨️ พิมพ์ / บันทึก PDF
    </button>
    <button onclick="window.close()"
        class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2 rounded-lg text-sm">
        ปิด
    </button>
</div>

<!-- A4 Page -->
<div class="page bg-white max-w-3xl mx-auto shadow-lg rounded-lg overflow-hidden">

    <!-- Header -->
    <div class="bg-purple-700 px-8 py-6 flex justify-between items-start">
        <div>
            <h1 class="text-white text-2xl font-bold">ใบเสนอราคา</h1>
            <p class="text-purple-200 text-sm mt-1">QUOTATION</p>
        </div>
        <div class="text-right">
            <p class="text-white font-semibold text-lg">
                <?= $partner->company_name ?? 'บริษัท Partner' ?>
            </p>
            <?php if ($partner && $partner->phone): ?>
            <p class="text-purple-200 text-sm"><?= $partner->phone ?></p>
            <?php endif; ?>
            <?php if ($partner && $partner->email): ?>
            <p class="text-purple-200 text-sm"><?= $partner->email ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="px-8 py-6">

        <!-- เลขที่เอกสาร + วันที่ -->
        <div class="flex justify-between mb-6 pb-6 border-b border-slate-100">
            <div>
                <p class="text-xs text-slate-400 mb-0.5">เลขที่ใบเสนอราคา</p>
                <p class="text-slate-800 font-semibold">QT-<?= str_pad($ticket->id, 5, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">อ้างอิง Ticket</p>
                <p class="text-slate-800 font-semibold">#<?= $ticket->id ?></p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">วันที่ออกใบเสนอราคา</p>
                <p class="text-slate-800 font-semibold"><?= date('d/m/Y', strtotime($ticket->updated_at)) ?></p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">วันหมดอายุ</p>
                <p class="text-slate-800 font-semibold"><?= date('d/m/Y', strtotime($ticket->updated_at . ' +30 days')) ?></p>
            </div>
        </div>

        <!-- ข้อมูลลูกค้า + อุปกรณ์ -->
        <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b border-slate-100">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">เรียน (ลูกค้า)</p>
                <p class="font-semibold text-slate-800"><?= $ticket->customer_name ?></p>
                <?php if ($ticket->phone): ?>
                <p class="text-sm text-slate-600"><?= $ticket->phone ?></p>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">รายละเอียดอุปกรณ์</p>
                <p class="font-semibold text-slate-800"><?= $ticket->device_name ?></p>
                <p class="text-sm text-slate-500 font-mono">S/N: <?= $ticket->serial_number ?></p>
                <p class="text-sm text-slate-600 mt-1"><?= $ticket->issue_desc ?></p>
            </div>
        </div>

        <!-- รายการค่าใช้จ่าย -->
        <div class="mb-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">รายการค่าใช้จ่าย</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-2.5 text-left text-xs text-slate-500 font-medium rounded-tl-lg">รายการ</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500 font-medium">จำนวน</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500 font-medium">ราคา/หน่วย</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500 font-medium rounded-tr-lg">รวม</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // parse รายการจาก quote_detail
                $lines = explode("\n", $ticket->quote_detail ?? '');
                $has_items = false;
                foreach ($lines as $line):
                    $line = trim($line);
                    if (empty($line)) continue;

                    // format: "ชื่อ x1 @ ฿1000.00"
                    if (preg_match('/^(.+)\s+x(\d+)\s+@\s+฿([\d.]+)$/', $line, $m)):
                        $has_items = true;
                ?>
                <tr class="border-b border-slate-50">
                    <td class="px-4 py-3 text-slate-700"><?= $m[1] ?></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= $m[2] ?></td>
                    <td class="px-4 py-3 text-right text-slate-600">฿<?= number_format((float)$m[3], 2) ?></td>
                    <td class="px-4 py-3 text-right text-slate-800 font-medium">฿<?= number_format((float)$m[2] * (float)$m[3], 2) ?></td>
                </tr>
                <?php
                    else:
                        // ถ้าไม่ match format แสดงเป็นแถวเดียว
                        $has_items = true;
                ?>
                <tr class="border-b border-slate-50">
                    <td class="px-4 py-3 text-slate-700" colspan="3"><?= $line ?></td>
                    <td class="px-4 py-3 text-right text-slate-800 font-medium">—</td>
                </tr>
                <?php
                    endif;
                endforeach;

                if (!$has_items):
                ?>
                <tr>
                    <td colspan="4" class="px-4 py-3 text-slate-400 text-center">ดูรายละเอียดในเอกสารแนบ</td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ยอดรวม -->
        <div class="flex justify-end mb-6">
            <div class="w-64">
                <?php
                $vat_amount = 0;
                $subtotal   = $ticket->quote_amount;

                // ถ้าราคา include VAT แล้ว ลองคำนวณย้อนกลับ
                // แต่ถ้าไม่แน่ใจให้แสดงแค่ยอดรวมสุทธิ
                ?>
                <div class="flex justify-between py-2 border-b border-slate-100 text-sm">
                    <span class="text-slate-500">ยอดรวมก่อน VAT</span>
                    <span class="text-slate-700">฿<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100 text-sm">
                    <span class="text-slate-500">VAT</span>
                    <span class="text-slate-500">—</span>
                </div>
                <div class="flex justify-between py-3 bg-purple-50 px-3 rounded-lg mt-2">
                    <span class="font-bold text-purple-800">ยอดรวมสุทธิ</span>
                    <span class="font-bold text-purple-800 text-lg">฿<?= number_format($ticket->quote_amount, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- หมายเหตุ -->
        <?php
        $note_lines = array_filter(explode("\n", $ticket->quote_detail ?? ''), function($l) {
            return !preg_match('/^.+\s+x\d+\s+@\s+฿[\d.]+$/', trim($l)) && !empty(trim($l));
        });
        if (!empty($note_lines)):
        ?>
        <div class="bg-slate-50 rounded-lg px-5 py-4 mb-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">หมายเหตุ / เงื่อนไข</p>
            <p class="text-sm text-slate-600 leading-relaxed"><?= implode('<br>', array_map('trim', $note_lines)) ?></p>
        </div>
        <?php endif; ?>

        <!-- สถานะการยืนยัน -->
        <div class="border border-slate-200 rounded-lg px-5 py-4 mb-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">สถานะ</p>
            <?php if ($ticket->status === 'wait_confirm'): ?>
                <span class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-sm">⏳ รอลูกค้ายืนยัน</span>
            <?php elseif ($ticket->status === 'quote_accepted'): ?>
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">✅ ลูกค้ายืนยันแล้ว</span>
            <?php elseif ($ticket->status === 'quote_rejected'): ?>
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">❌ ลูกค้าปฏิเสธ</span>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="border-t border-slate-100 pt-4 text-center">
            <p class="text-xs text-slate-400">Three W Business and Solutions · ระบบบริการหลังการขาย</p>
            <p class="text-xs text-slate-300 mt-1">เอกสารนี้สร้างโดยระบบอัตโนมัติ · <?= date('d/m/Y H:i') ?></p>
        </div>

    </div>
</div>

</body>
</html>