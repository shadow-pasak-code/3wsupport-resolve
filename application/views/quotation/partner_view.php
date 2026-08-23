<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $issuer_label ?> #<?= $ticket->id ?></title>
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
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen py-6">

    <div class="no-print text-center mb-4">
        <button onclick="window.print()"
            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm">
            🖨️ พิมพ์
        </button>
    </div>

    <div class="bg-white max-w-2xl mx-auto shadow-lg rounded-lg overflow-hidden">
        <div class="bg-purple-700 px-6 py-5 flex justify-between items-start">
            <div>
                <h1 class="text-white text-xl font-bold"><?= $issuer_label ?></h1>
                <p class="text-purple-200 text-xs mt-1"><?= $issuer_label_en ?></p>
            </div>
            <div class="text-right">
                <p class="text-white font-semibold"><?= $issuer_name ?></p>
                <?php if ($issuer_phone): ?>
                    <p class="text-purple-200 text-xs"><?= $issuer_phone ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="flex justify-between mb-5 pb-5 border-b border-slate-100 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">อ้างอิง Ticket</p>
                    <p class="font-semibold">#<?= $ticket->id ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 mb-0.5">วันที่</p>
                    <p class="font-semibold"><?= date('d/m/Y', strtotime($ticket->updated_at)) ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5 pb-5 border-b border-slate-100 text-sm">
                <div>
                    <p class="text-xs text-slate-400 mb-1">ลูกค้า</p>
                    <p class="font-semibold"><?= $ticket->customer_name ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-1">อุปกรณ์</p>
                    <p class="font-semibold"><?= $ticket->device_name ?></p>
                    <p class="font-mono text-xs text-slate-400">S/N: <?= $ticket->serial_number ?></p>
                </div>
            </div>

            <!-- รายการ parse จาก quote_detail -->
            <div class="mb-5">
                <p class="text-xs font-semibold text-slate-400 uppercase mb-3">รายการ</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-3 py-2 text-left text-xs text-slate-500">รายการ</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-20">จำนวน</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-28">ราคา/หน่วย</th>
                            <th class="px-3 py-2 text-right text-xs text-slate-500 w-28">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $lines = explode("\n", $ticket->partner_quote_detail ?? $ticket->quote_detail ?? '');
                        $has_items = false;
                        foreach ($lines as $line):
                            $line = trim($line);
                            if (empty($line)) continue;
                            $has_items = true;
                            if (preg_match('/^(.+)\s+x(\d+)\s+@\s+฿([\d.]+)$/', $line, $m)):
                        ?>
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-2"><?= $m[1] ?></td>
                                    <td class="px-3 py-2 text-right"><?= $m[2] ?></td>
                                    <td class="px-3 py-2 text-right">฿<?= number_format((float)$m[3], 2) ?></td>
                                    <td class="px-3 py-2 text-right font-medium">฿<?= number_format((float)$m[2] * (float)$m[3], 2) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-2 text-slate-700" colspan="4"><?= nl2br($line) ?></td>
                                </tr>
                            <?php
                            endif;
                        endforeach;
                        if (!$has_items):
                            ?>
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-slate-400 text-xs">ไม่มีรายการ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-5">
                <div class="w-48">
                    <div class="flex justify-between py-3 bg-purple-50 px-3 rounded-lg">
                        <span class="font-bold text-purple-800">ยอดรวม</span>
                        <span class="font-bold text-purple-800 text-lg">฿<?= number_format($ticket->partner_quote_amount ?? $ticket->quote_amount, 2) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($ticket->quote_file): ?>
                <div class="border border-slate-200 rounded-lg px-4 py-3 mb-4">
                    <p class="text-xs text-slate-400 mb-1">เอกสารแนบ</p>
                    <a href="<?= base_url('uploads/quotations/' . $ticket->quote_file) ?>" target="_blank"
                        class="text-sm text-blue-600 hover:underline">📎 ดาวน์โหลดไฟล์แนบ</a>
                </div>
            <?php endif; ?>

            <div class="border-t border-slate-100 pt-3 text-center">
                <p class="text-xs text-slate-400"><?= $issuer_name ?></p>
            </div>
        </div>
    </div>

</body>

</html>