<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสนอราคา #<?= $ticket->id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .page {
                box-shadow: none !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen py-6">

    <!-- ปุ่ม Print -->
    <div class="no-print text-center mb-4 flex justify-center gap-3 px-4">
        <button onclick="window.print()"
            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
            🖨️ พิมพ์ / บันทึก PDF
        </button>
    </div>

    <!-- A4 Page -->
    <div class="page bg-white max-w-2xl mx-auto shadow-lg rounded-lg overflow-hidden mx-4">

        <!-- Header -->
        <div class="bg-purple-700 px-6 py-5 flex justify-between items-start">
            <div>
                <h1 class="text-white text-xl font-bold">ใบเสนอราคา</h1>
                <p class="text-purple-200 text-xs mt-1">QUOTATION</p>
            </div>
            <div class="text-right">
                <p class="text-white font-semibold"><?= $this->config->item('company_name') ?></p>
                <p class="text-purple-200 text-xs"><?= $this->config->item('company_phone') ?></p>
                <p class="text-purple-200 text-xs"><?= $this->config->item('company_email') ?></p>
            </div>
        </div>

        <div class="px-6 py-5">

            <!-- เลขที่ + วันที่ -->
            <div class="flex justify-between mb-5 pb-5 border-b border-slate-100 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">เลขที่ใบเสนอราคา</p>
                    <p class="font-semibold">QT-<?= str_pad($ticket->id, 5, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 mb-0.5">วันที่ออก</p>
                    <p class="font-semibold"><?= date('d/m/Y', strtotime($ticket->updated_at)) ?></p>
                </div>
            </div>

            <!-- ข้อมูลลูกค้า + อุปกรณ์ -->
            <div class="grid grid-cols-2 gap-4 mb-5 pb-5 border-b border-slate-100 text-sm">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase mb-1">เรียน</p>
                    <p class="font-semibold"><?= $ticket->customer_name ?></p>
                    <?php if ($ticket->phone): ?>
                        <p class="text-slate-500 text-xs"><?= $ticket->phone ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase mb-1">อุปกรณ์</p>
                    <p class="font-semibold"><?= $ticket->device_name ?></p>
                    <p class="text-slate-500 font-mono text-xs">S/N: <?= $ticket->serial_number ?></p>
                </div>
            </div>

            <!-- รายการ -->
            <div class="mb-5">
                <p class="text-xs font-semibold text-slate-400 uppercase mb-3">รายการค่าใช้จ่าย</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-3 py-2 text-left text-xs text-slate-500">รายการ</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-24">จำนวน</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-28">ราคา/หน่วย</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-28">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $items = [];
                        if ($quotation) {
                            $items = json_decode($quotation->items, true) ?? [];
                        }
                        if (empty($items) && $ticket->quote_detail) {
                            // fallback: parse จาก quote_detail เดิม
                            $lines = explode("\n", $ticket->quote_detail);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (empty($line)) continue;
                                if (preg_match('/^(.+)\s+x(\d+)\s+@\s+฿([\d.]+)$/', $line, $m)) {
                                    $items[] = ['name' => $m[1], 'qty' => $m[2], 'price' => $m[3]];
                                } else {
                                    $items[] = ['name' => $line, 'qty' => 1, 'price' => 0];
                                }
                            }
                        }

                        foreach ($items as $item):
                            if (empty($item['name'])) continue;
                            $total_row = (float)$item['qty'] * (float)$item['price'];
                        ?>
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-2 text-slate-700"><?= $item['name'] ?></td>
                                <td class="px-3 py-2 text-right text-slate-600"><?= $item['qty'] ?></td>
                                <td class="px-3 py-2 text-right text-slate-600">฿<?= number_format((float)$item['price'], 2) ?></td>
                                <td class="px-3 py-2 text-right font-medium">฿<?= number_format($total_row, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ยอดรวม -->
            <?php
            $q_subtotal = $quotation ? (float)$quotation->subtotal   : (float)$ticket->quote_amount;
            $q_vat_pct  = $quotation ? (int)$quotation->vat          : 0;
            $q_vat_amt  = $quotation ? (float)$quotation->vat_amount  : 0;
            $q_total    = $quotation ? (float)$quotation->total       : (float)$ticket->quote_amount;
            ?>
            <div class="flex justify-end mb-5">
                <div class="w-56">
                    <div class="flex justify-between py-2 border-t border-slate-100 text-sm">
                        <span class="text-slate-500">ยอดก่อน VAT</span>
                        <span>฿<?= number_format($q_subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-slate-100 text-sm">
                        <span class="text-slate-500">VAT <?= $q_vat_pct ?>%</span>
                        <span>฿<?= number_format($q_vat_amt, 2) ?></span>
                    </div>
                    <div class="flex justify-between py-3 bg-blue-50 px-3 rounded-lg mt-2">
                        <span class="font-bold text-blue-800">ยอดรวมสุทธิ</span>
                        <span class="font-bold text-blue-800 text-lg">฿<?= number_format($q_total, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- สถานะ -->
            <div class="border border-slate-200 rounded-lg px-4 py-3 mb-4 text-sm">
                <p class="text-xs text-slate-400 mb-1">สถานะ</p>
                <?php if ($ticket->status === 'wait_confirm'): ?>
                    <span class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs">⏳ รอการยืนยัน</span>
                <?php elseif ($ticket->status === 'quote_accepted'): ?>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">✅ ยืนยันแล้ว</span>
                <?php elseif ($ticket->status === 'quote_rejected'): ?>
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs">❌ ปฏิเสธ</span>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-100 pt-3 text-center">
                <p class="text-xs text-slate-400">Three W Business and Solutions</p>
                <p class="text-xs text-slate-300 mt-0.5"><?= date('d/m/Y H:i') ?></p>
            </div>

        </div>
    </div>

</body>

</html>