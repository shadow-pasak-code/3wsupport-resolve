<?php $page_title = 'ออกใบเสนอราคา Ticket #' . $ticket->id; ?>

<div class="p-6 max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/tickets/detail/' . $ticket->id) ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold">
            <?= $quotation ? 'แก้ไขใบเสนอราคา' : 'ตรวจสอบใบเสนอราคา' ?> Ticket #<?= $ticket->id ?>
        </h1>
    </div>

    <?php if ($ticket->partner_quote_amount && !$is_partner_quote): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5">
            <p class="text-sm font-semibold text-amber-800">🔒 ยอดรวมถูกล็อกไว้ที่ ฿<?= number_format($ticket->partner_quote_amount, 2) ?></p>
            <p class="text-xs text-amber-700 mt-1">
                ใบนี้ออกโดยช่างในบริษัท แอดมินตรวจสอบและแก้ไขรายละเอียด/ข้อความได้ แต่ปรับยอดรวมไม่ได้
                — หากราคาไม่ถูกต้อง กรุณาให้ช่างแก้ใบเสนอราคามาใหม่
            </p>
        </div>
    <?php elseif ($ticket->partner_quote_amount && $is_partner_quote): ?>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-5">
            <p class="text-sm font-semibold text-purple-800">Partner เสนอราคามาที่ ฿<?= number_format($ticket->partner_quote_amount, 2) ?></p>
            <p class="text-xs text-purple-700 mt-1">ปรับยอดขึ้นได้ตามส่วนต่างที่บริษัทกำหนด แต่ต้องไม่ต่ำกว่าราคานี้</p>
        </div>
    <?php endif; ?>

    <!-- ข้อมูล Ticket -->
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-5 text-sm">
        <div class="grid grid-cols-2 gap-3">
            <div><span class="text-slate-400">ลูกค้า:</span> <span class="font-medium"><?= $ticket->customer_name ?></span></div>
            <div><span class="text-slate-400">เบอร์:</span> <?= $ticket->phone ?? '—' ?></div>
            <div><span class="text-slate-400">อุปกรณ์:</span> <span class="font-medium"><?= $ticket->device_name ?></span></div>
            <div><span class="text-slate-400">S/N:</span> <span class="font-mono text-xs"><?= $ticket->serial_number ?></span></div>
            <div class="col-span-2"><span class="text-slate-400">อาการ:</span> <?= $ticket->issue_desc ?></div>
        </div>
        <?php if ($ticket->partner_quote_amount): ?>
            <?php
            // ช่างในบริษัทและ Partner ใช้คอลัมน์ partner_quote_amount ร่วมกัน ต้องเช็ค partner_id เพื่อบอกที่มาให้ถูก
            $quote_submitter = !empty($ticket->partner_id)
                ? 'Partner: ' . ($ticket->partner_name ?? '-')
                : ($ticket->technician_name ?? 'ช่าง');
            ?>
            <div class="mt-3 pt-3 border-t border-slate-200">
                <p class="text-xs text-slate-400">ใบเสนอราคาจาก <?= $quote_submitter ?>: <span class="text-purple-700 font-semibold">฿<?= number_format($ticket->partner_quote_amount, 2) ?></span>
                    <?php if ($ticket->quote_file): ?>
                        · <a href="<?= base_url('uploads/quotations/' . $ticket->quote_file) ?>" target="_blank" class="text-blue-600 hover:underline">📎 ดูไฟล์</a>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Form ใบเสนอราคา -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="bg-blue-700 px-6 py-4">
            <h2 class="text-white font-semibold">ใบเสนอราคา</h2>
            <p class="text-blue-200 text-xs mt-0.5"><?= $this->config->item('company_name') ?></p>
        </div>

        <form method="POST" action="<?= base_url('admin/tickets/save_quotation/' . $ticket->id) ?>" id="quote-form">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <input type="hidden" name="subtotal" id="subtotal-input">
            <input type="hidden" name="vat_amount" id="vat-amount-input">
            <input type="hidden" name="total" id="total-input">

            <div class="p-6">
                <!-- รายการ -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-700">รายการค่าใช้จ่าย</h3>
                        <button type="button" onclick="addRow()"
                            class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg">
                            + เพิ่มรายการ
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-xs text-slate-500">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">รายการ</th>
                                    <th class="px-4 py-2.5 text-center w-20">จำนวน</th>
                                    <th class="px-4 py-2.5 text-right w-36">ราคา/หน่วย</th>
                                    <th class="px-4 py-2.5 text-right w-36">รวม</th>
                                    <th class="px-4 py-2.5 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                <?php
                                $existing_items = [];
                                if ($quotation) {
                                    $existing_items = json_decode($quotation->items, true) ?? [];
                                } elseif (!empty($prefill_items)) {
                                    $existing_items = $prefill_items;
                                }
                                if (empty($existing_items)) {
                                    $existing_items = [['name' => '', 'qty' => 1, 'price' => 0]];
                                }
                                foreach ($existing_items as $i => $item):
                                ?>
                                    <tr class="item-row border-t border-slate-100">
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="items[<?= $i ?>][name]" required
                                                value="<?= $item['name'] ?>"
                                                placeholder="เช่น ค่าแรง, อะไหล่"
                                                class="w-full text-sm border-0 focus:ring-0 bg-transparent focus:outline-none">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" name="items[<?= $i ?>][qty]"
                                                value="<?= $item['qty'] ?>" min="1"
                                                onchange="calcRow(this)" oninput="calcRow(this)"
                                                class="w-full text-sm text-center border-0 focus:ring-0 bg-transparent focus:outline-none">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" name="items[<?= $i ?>][price]"
                                                value="<?= $item['price'] ?>" min="0" step="0.01"
                                                onchange="calcRow(this)" oninput="calcRow(this)"
                                                class="w-full text-sm text-right border-0 focus:ring-0 bg-transparent focus:outline-none">
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <span class="row-total"><?= number_format($item['qty'] * $item['price'], 2) ?></span>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400">✕</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-slate-50">
                                <tr class="border-t border-slate-200">
                                    <td colspan="3" class="px-4 py-2 text-right text-xs text-slate-500">ยอดรวมก่อน VAT</td>
                                    <td class="px-4 py-2 text-right font-medium" id="subtotal-display">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-right text-xs text-slate-500">VAT</td>
                                    <td class="px-4 py-2">
                                        <select name="vat" id="vat-select" onchange="calcTotal()"
                                            class="w-full text-xs border border-slate-300 rounded px-2 py-1 focus:outline-none">
                                            <option value="0" <?= (!$quotation || $quotation->vat == 0) ? 'selected' : '' ?>>ไม่มี VAT</option>
                                            <option value="7" <?= ($quotation && $quotation->vat == 7) ? 'selected' : '' ?>>VAT 7%</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2 text-right font-medium" id="vat-display">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="border-t-2 border-slate-300">
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-slate-800">ยอดรวมสุทธิ</td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-700 text-lg" id="total-display">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php if ($ticket->partner_quote_amount): ?>
                        <p class="text-xs text-amber-600 mt-2 text-right">
                            <?php if ($is_partner_quote): ?>
                                ⚠️ ห้ามตั้งยอดรวมต่ำกว่าราคาที่ Partner เสนอ (฿<?= number_format($ticket->partner_quote_amount, 2) ?>)
                            <?php else: ?>
                                🔒 ยอดรวมต้องเท่ากับราคาที่ช่างเสนอ (฿<?= number_format($ticket->partner_quote_amount, 2) ?>)
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- หมายเหตุ -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">หมายเหตุ / เงื่อนไข</label>
                    <textarea name="note" rows="3"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="เช่น ราคานี้รวมค่าแรงแล้ว / รับประกัน 30 วัน"><?= $quotation ? $quotation->note : '' ?></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" onclick="return prepareSubmit()"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                        <?= $quotation ? 'บันทึกการแก้ไข' : 'ออกใบเสนอราคา' ?>
                    </button>
                    <a href="<?= base_url('admin/tickets/detail/' . $ticket->id) ?>"
                        class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if ($this->session->flashdata('error')): ?>
    Swal.fire({ icon: 'error', title: 'ทำรายการไม่สำเร็จ', text: '<?= addslashes($this->session->flashdata('error')) ?>' });
<?php endif; ?>

    const ORIGINAL_QUOTE = <?= $ticket->partner_quote_amount ? (float)$ticket->partner_quote_amount : 0 ?>;
    // ช่างในบริษัท → ยอดรวมต้องเท่าต้นฉบับเป๊ะ / Partner → เป็นแค่ราคาขั้นต่ำ บวกส่วนต่างขึ้นได้
    const QUOTE_IS_LOCKED = <?= $is_partner_quote ? 'false' : 'true' ?>;
    let rowIndex = <?= count($existing_items) ?>;

    function addRow() {
        const tbody = document.getElementById('items-body');
        const tr = document.createElement('tr');
        tr.className = 'item-row border-t border-slate-100';
        tr.innerHTML = `
        <td class="px-4 py-2.5">
            <input type="text" name="items[${rowIndex}][name]" required
                placeholder="รายการ..."
                class="w-full text-sm border-0 focus:ring-0 bg-transparent focus:outline-none">
        </td>
        <td class="px-4 py-2.5">
            <input type="number" name="items[${rowIndex}][qty]" value="1" min="1"
                onchange="calcRow(this)" oninput="calcRow(this)"
                class="w-full text-sm text-center border-0 focus:ring-0 bg-transparent focus:outline-none">
        </td>
        <td class="px-4 py-2.5">
            <input type="number" name="items[${rowIndex}][price]" value="0" min="0" step="0.01"
                onchange="calcRow(this)" oninput="calcRow(this)"
                class="w-full text-sm text-right border-0 focus:ring-0 bg-transparent focus:outline-none">
        </td>
        <td class="px-4 py-2.5 text-right"><span class="row-total">0.00</span></td>
        <td class="px-4 py-2.5 text-center">
            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400">✕</button>
        </td>`;
        tbody.appendChild(tr);
        rowIndex++;
        calcTotal();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'ต้องมีอย่างน้อย 1 รายการ',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }
        btn.closest('tr').remove();
        calcTotal();
    }

    function calcRow(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
        row.querySelector('.row-total').textContent = (qty * price).toFixed(2);
        calcTotal();
    }

    function calcTotal() {
        let subtotal = 0;
        document.querySelectorAll('.row-total').forEach(el => {
            subtotal += parseFloat(el.textContent) || 0;
        });
        const vatRate = parseFloat(document.getElementById('vat-select').value) || 0;
        const vatAmt = subtotal * vatRate / 100;
        const total = subtotal + vatAmt;

        document.getElementById('subtotal-display').textContent = subtotal.toFixed(2);
        document.getElementById('vat-display').textContent = vatAmt.toFixed(2);
        document.getElementById('total-display').textContent = total.toFixed(2);
    }

    function prepareSubmit() {
        const total = parseFloat(document.getElementById('total-display').textContent) || 0;
        if (total <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'ยอดรวมต้องมากกว่า 0',
                text: 'กรุณากรอกรายการและราคา'
            });
            return false;
        }
        if (ORIGINAL_QUOTE > 0) {
            if (QUOTE_IS_LOCKED && Math.abs(total - ORIGINAL_QUOTE) >= 0.01) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยอดรวมต้องเท่ากับราคาที่ช่างเสนอ',
                    text: 'ยอดต้องเป็น ฿' + ORIGINAL_QUOTE.toFixed(2) + ' เท่านั้น แอดมินแก้ไขได้เฉพาะรายละเอียด/ข้อความ '
                        + 'หากราคาไม่ถูกต้องกรุณาให้ช่างแก้ใบเสนอราคามาใหม่'
                });
                return false;
            }
            if (!QUOTE_IS_LOCKED && total < ORIGINAL_QUOTE) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยอดรวมต่ำกว่าราคาที่ Partner เสนอ',
                    text: 'ห้ามตั้งราคาต่ำกว่า ฿' + ORIGINAL_QUOTE.toFixed(2) + ' ที่ Partner เสนอมา'
                });
                return false;
            }
        }
        const subtotal = parseFloat(document.getElementById('subtotal-display').textContent) || 0;
        const vatAmt = parseFloat(document.getElementById('vat-display').textContent) || 0;
        document.getElementById('subtotal-input').value = subtotal.toFixed(2);
        document.getElementById('vat-amount-input').value = vatAmt.toFixed(2);
        document.getElementById('total-input').value = total.toFixed(2);
        return true;
    }

    // คำนวณครั้งแรก
    calcTotal();
</script>