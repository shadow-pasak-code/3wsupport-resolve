<?php $page_title = 'รายละเอียดงาน #' . $ticket->id; ?>

<div class="p-6 max-w-6xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('tech/tickets') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold">งาน #<?= $ticket->id ?></h1>
        <?= status_badge($ticket->status) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2">

    <!-- ข้อมูลงาน -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-4">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
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
                <dt class="text-xs text-slate-400 mb-0.5">Serial</dt>
                <dd class="font-mono text-xs"><?= $ticket->serial_number ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">ประเภท</dt>
                <dd>
                    <?php if ($ticket->ticket_type === 'software'): ?>
                        <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software (Remote)</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">ประกัน</dt>
                <dd>
                    <?php if ($ticket->warranty_end): ?>
                        <?php $in_w_badge = $ticket->warranty_end >= date('Y-m-d'); ?>
                        <span class="<?= $in_w_badge ? 'text-green-600' : 'text-red-500' ?> text-xs">
                            <?= $in_w_badge ? 'อยู่ในประกัน' : 'หมดประกัน' ?>
                        </span>
                        <?php else: ?>—<?php endif; ?>
                </dd>
            </div>
            <div class="col-span-2">
                <dt class="text-xs text-slate-400 mb-0.5">อาการ / ปัญหา</dt>
                <dd class="text-slate-700 leading-relaxed"><?= nl2br($ticket->issue_desc) ?></dd>
            </div>
            <?php if ($ticket->note): ?>
                <div class="col-span-2">
                    <dt class="text-xs text-slate-400 mb-0.5">หมายเหตุจากลูกค้า</dt>
                    <dd class="text-slate-600"><?= nl2br($ticket->note) ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>

    <?php
    $today = date('Y-m-d');
    $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;
    // ถ้าเคยส่งต่อให้ Partner แล้ว (partner_id ถูกตั้งไว้) งานนี้ไม่ใช่ของช่างอีกต่อไป
    $owned_by_tech = empty($ticket->partner_id);
    ?>

    <?php if (!$owned_by_tech && in_array($ticket->status, ['wait_quote', 'wait_review', 'wait_confirm', 'quote_accepted', 'assigned', 'in_progress', 'waiting_parts'])): ?>
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-slate-600">📤 งานนี้ถูกส่งต่อให้ Partner ดำเนินการแล้ว</p>
            <p class="text-xs text-slate-400 mt-1">ระบบจะแจ้งเตือนเมื่อมีความคืบหน้า ไม่ต้องดำเนินการเพิ่มเติม</p>
        </div>
    <?php endif; ?>

    <!-- ================================================= -->
    <!-- หมดประกัน + wait_quote → ต้องทำใบเสนอราคาก่อน -->
    <!-- ================================================= -->
    <?php if ($ticket->status === 'wait_quote' && $in_warranty): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-green-700">✅ อุปกรณ์นี้ยังอยู่ในประกัน</p>
            <p class="text-xs text-green-600 mt-1">ไม่สามารถแนบใบเสนอราคาได้ การซ่อมอยู่ภายใต้การรับประกัน กรุณาติดต่อ Admin</p>
        </div>
    <?php elseif ($ticket->status === 'wait_quote' && !$in_warranty && $owned_by_tech): ?>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-4">
            <div class="bg-purple-600 px-6 py-4">
                <h2 class="text-white font-semibold text-lg">ใบเสนอราคา (หมดประกัน)</h2>
                <p class="text-purple-200 text-sm">Ticket #<?= $ticket->id ?> · <?= date('d/m/Y') ?></p>
                <?php if ($ticket->repair_category_name): ?>
                    <p class="text-purple-100 text-xs mt-1">หมวดหมู่ที่เลือกไว้: <?= $ticket->repair_category_name ?> (ไม่เกิน <?= $ticket->repair_category_max_days ?> วัน)</p>
                <?php endif; ?>
            </div>
            <form method="POST" action="<?= base_url('tech/tickets/quote/' . $ticket->id) ?>"
                enctype="multipart/form-data" id="quote-form">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700">รายการค่าใช้จ่าย</h3>
                            <button type="button" onclick="addRow()"
                                class="text-xs bg-purple-50 hover:bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg">+ เพิ่มรายการ</button>
                        </div>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-sm" id="items-table">
                                <thead class="bg-slate-50 text-xs text-slate-500">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">รายการ</th>
                                        <th class="px-4 py-2.5 text-center w-20">จำนวน</th>
                                        <th class="px-4 py-2.5 text-right w-36">ราคา/หน่วย</th>
                                        <th class="px-4 py-2.5 text-right w-36">รวม</th>
                                        <th class="px-4 py-2.5 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    <tr class="item-row border-t border-slate-100">
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="items[0][name]" required placeholder="เช่น ค่าแรง, อะไหล่"
                                                class="w-full text-sm border-0 focus:ring-0 focus:outline-none bg-transparent">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" name="items[0][qty]" value="1" min="1"
                                                onchange="calcRow(this)" oninput="calcRow(this)"
                                                class="w-full text-sm text-center border-0 focus:ring-0 focus:outline-none bg-transparent">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" name="items[0][price]" value="0" min="0" step="0.01"
                                                onchange="calcRow(this)" oninput="calcRow(this)"
                                                class="w-full text-sm text-right border-0 focus:ring-0 focus:outline-none bg-transparent">
                                        </td>
                                        <td class="px-4 py-2.5 text-right"><span class="row-total text-slate-700">0.00</span></td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400">✕</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-slate-50">
                                    <tr class="border-t border-slate-200">
                                        <td colspan="3" class="px-4 py-2.5 text-right text-xs text-slate-500">ยอดรวมก่อน VAT</td>
                                        <td class="px-4 py-2.5 text-right font-medium" id="subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="px-4 py-2.5 text-right text-xs text-slate-500">VAT</td>
                                        <td class="px-4 py-2.5">
                                            <select name="vat" id="vat-select" onchange="calcTotal()"
                                                class="w-full text-xs border border-slate-300 rounded px-2 py-1 focus:outline-none">
                                                <option value="0">ไม่มี VAT</option>
                                                <option value="7">VAT 7%</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-medium" id="vat-amount">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="border-t-2 border-slate-300">
                                        <td colspan="3" class="px-4 py-3 text-right font-semibold text-slate-800">ยอดรวมสุทธิ</td>
                                        <td class="px-4 py-3 text-right font-bold text-purple-700 text-base" id="grand-total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <input type="hidden" name="quote_amount" id="quote-amount-input">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">หมายเหตุ / เงื่อนไข</label>
                        <textarea name="quote_detail" rows="3"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="เช่น รับประกัน 30 วัน"></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">แนบเอกสารเพิ่มเติม (ถ้ามี)</label>
                        <input type="file" name="quote_file" accept=".pdf,.doc,.docx"
                            class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        <p class="text-xs text-slate-400 mt-1">PDF, DOC, DOCX ขนาดไม่เกิน 5MB</p>
                    </div>
                    <button type="submit" onclick="return prepareSubmit()"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm py-3 rounded-lg font-medium">
                        ส่งใบเสนอราคาให้ Admin
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- ส่งใบเสนอราคาแล้ว รอ Admin ตรวจสอบ / รอลูกค้ายืนยัน -->
    <?php if (in_array($ticket->status, ['wait_review', 'wait_confirm']) && $owned_by_tech): ?>
        <div class="bg-pink-50 border border-pink-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-pink-700 mb-2">
                <?= $ticket->status === 'wait_review' ? '🕓 ส่งใบเสนอราคาแล้ว รอ Admin ตรวจสอบ' : '⏳ รอลูกค้ายืนยันราคา' ?>
            </p>
            <p class="text-2xl font-bold text-pink-800">฿<?= number_format($ticket->partner_quote_amount ?? $ticket->quote_amount, 2) ?></p>
            <?php if ($ticket->partner_quote_detail ?? $ticket->quote_detail): ?>
                <p class="text-sm text-pink-600 mt-2"><?= nl2br($ticket->partner_quote_detail ?? $ticket->quote_detail) ?></p>
            <?php endif; ?>
            <?php if ($ticket->quote_file): ?>
                <a href="<?= base_url('uploads/quotations/' . $ticket->quote_file) ?>" target="_blank"
                    class="inline-flex items-center gap-1 text-sm text-pink-700 hover:underline mt-2">
                    📄 ดูเอกสารแนบ
                </a>
            <?php endif; ?>
            <p class="text-xs text-pink-400 mt-2">* ราคาที่คุณเสนอ</p>
        </div>
    <?php endif; ?>

    <!-- ลูกค้ายืนยันแล้ว → เริ่มซ่อมจริง (หมวดหมู่เลือกไว้ตั้งแต่ตอนรับงานแล้ว ไม่ต้องเลือกซ้ำ) -->
    <?php if ($ticket->status === 'quote_accepted' && $owned_by_tech): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-green-700">✅ ลูกค้ายืนยันแล้ว — ดำเนินการซ่อมได้เลย</p>
            <p class="text-xl font-bold text-green-800 mt-1">฿<?= number_format($ticket->partner_quote_amount ?? $ticket->quote_amount, 2) ?></p>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-4">
            <h2 class="text-sm font-semibold text-indigo-700 mb-3">เริ่มดำเนินการซ่อม</h2>
            <?php if ($ticket->repair_category_name): ?>
                <p class="text-sm text-indigo-700 mb-4">
                    หมวดหมู่ที่เลือกไว้: <span class="font-medium"><?= $ticket->repair_category_name ?></span>
                    (ไม่เกิน <?= $ticket->repair_category_max_days ?> วัน — นับจากวันนี้)
                </p>
                <form method="POST" action="<?= base_url('tech/tickets/start_repair/' . $ticket->id) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg">
                        ✅ เริ่มซ่อมและแจ้งลูกค้า
                    </button>
                </form>
            <?php else: ?>
                <!-- เผื่อกรณี ticket เก่าที่ยังไม่เคยเลือกหมวดหมู่มาก่อน -->
                <p class="text-xs text-amber-600 mb-3">⚠️ ยังไม่เคยเลือกหมวดหมู่การซ่อมสำหรับ ticket นี้ กรุณาเลือกก่อน</p>
                <form method="POST" action="<?= base_url('tech/tickets/accept/' . $ticket->id) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="mb-3">
                        <label class="block text-xs text-slate-600 mb-1">หมวดหมู่การซ่อม <span class="text-red-500">*</span></label>
                        <p class="text-xs mb-1 <?= $categories_auto_matched ? 'text-emerald-600' : 'text-amber-600' ?>">
                            <?= $categories_auto_matched ? '🔍 กรองตามอาการที่ลูกค้าแจ้งให้อัตโนมัติ' : '⚠️ ไม่พบหมวดหมู่ที่ตรงกับอาการที่แจ้ง แสดงตัวเลือกทั่วไปแทน' ?>
                        </p>
                        <select name="repair_category_id" required
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— เลือกหมวดหมู่ —</option>
                            <?php foreach ($repair_categories as $rc): ?>
                                <option value="<?= $rc->id ?>">
                                    <?= $rc->name ?> (ไม่เกิน <?= $rc->max_days ?> วัน)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg">
                        ✅ กำหนดหมวดหมู่และแจ้งลูกค้า
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ลูกค้าปฏิเสธ -->
    <?php if ($ticket->status === 'quote_rejected'): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-red-700">❌ ลูกค้าปฏิเสธใบเสนอราคา</p>
            <p class="text-sm text-red-600 mt-1">กรุณาติดต่อ Admin เพื่อดำเนินการต่อ</p>
        </div>
    <?php endif; ?>

    <!-- ================================================= -->
    <!-- รับงาน (assigned) — ทั้งในและหมดประกัน เลือกหมวดหมู่ก่อนเสมอ ใบเสนอราคา (ถ้าหมดประกัน) จะโผล่หลังจากนี้ -->
    <!-- ================================================= -->
    <?php if ($ticket->status === 'assigned' && $owned_by_tech): ?>
        <div class="bg-slate-100 border border-slate-200 rounded-xl px-5 py-3 mb-3">
            <p class="text-sm font-semibold text-slate-700">เลือกดำเนินการอย่างใดอย่างหนึ่ง</p>
            <p class="text-xs text-slate-500 mt-0.5">ประเมินหน้างานแล้วเลือกว่าจะซ่อมเอง หรือส่งต่อให้ Partner</p>
        </div>
        <div class="bg-indigo-50 border-2 border-indigo-200 rounded-xl p-5 mb-3">
            <h2 class="text-sm font-semibold text-indigo-700 mb-1">✅ ตัวเลือกที่ 1 — ซ่อมเอง</h2>
            <p class="text-xs text-indigo-500 mb-4">รับงานไว้ทำเอง เลือกหมวดหมู่เพื่อให้ระบบคำนวณวันครบกำหนด</p>
            <form method="POST" action="<?= base_url('tech/tickets/accept/' . $ticket->id) ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="mb-3">
                    <label class="block text-xs text-slate-600 mb-1">หมวดหมู่การซ่อม <span class="text-red-500">*</span></label>
                    <p class="text-xs mb-1 <?= $categories_auto_matched ? 'text-emerald-600' : 'text-amber-600' ?>">
                        <?= $categories_auto_matched ? '🔍 กรองตามอาการที่ลูกค้าแจ้งให้อัตโนมัติ' : '⚠️ ไม่พบหมวดหมู่ที่ตรงกับอาการที่แจ้ง แสดงตัวเลือกทั่วไปแทน' ?>
                    </p>
                    <select name="repair_category_id" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— เลือกหมวดหมู่ —</option>
                        <?php foreach ($repair_categories as $rc): ?>
                            <option value="<?= $rc->id ?>">
                                <?= $rc->name ?> (ไม่เกิน <?= $rc->max_days ?> วัน)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">ระบบจะคำนวณวันครบกำหนดให้อัตโนมัติจากหมวดหมู่ที่เลือก เริ่มนับจากวันนี้</p>
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-slate-600 mb-1">หมายเหตุ (ถ้ามี)</label>
                    <textarea name="note" rows="2"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="เช่น นัดลูกค้าส่งเครื่องวันที่..."></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg">
                    ✅ รับงานและแจ้งลูกค้า
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ส่งต่อให้ Partner (ซ่อมเองไม่ได้) — เห็นได้ตั้งแต่หน้ารับงาน (assigned) ไปจนถึงระหว่างกำลังซ่อม -->
    <?php if (in_array($ticket->status, ['assigned', 'in_progress', 'waiting_parts']) && $owned_by_tech): ?>
        <div class="bg-orange-50 <?= $ticket->status === 'assigned' ? 'border-2' : 'border' ?> border-orange-200 rounded-xl p-5 mb-4">
            <?php if ($ticket->status === 'assigned'): ?>
                <h2 class="text-sm font-semibold text-orange-700 mb-1">🔧 ตัวเลือกที่ 2 — ซ่อมเองไม่ได้ ส่งต่อให้ Partner</h2>
                <p class="text-xs text-orange-500 mb-4">เกินขอบเขต/ไม่มีอะไหล่ ส่งงานให้ Partner ภายนอกดำเนินการแทน</p>
            <?php else: ?>
                <h2 class="text-sm font-semibold text-orange-700 mb-3">ส่งต่อให้ Partner (ซ่อมเองไม่ได้)</h2>
            <?php endif; ?>
            <form method="POST" action="<?= base_url('tech/tickets/escalate/' . $ticket->id) ?>"
                enctype="multipart/form-data" id="escalate-form">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="mb-3">
                    <label class="block text-xs text-slate-600 mb-1">เลือก Partner <span class="text-red-500">*</span></label>
                    <select name="partner_id" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">— เลือก Partner —</option>
                        <?php foreach ($partners as $p): ?>
                            <option value="<?= $p->id ?>"><?= $p->company_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs text-slate-600 mb-1">ระบุเหตุผล <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="2" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="เช่น ต้องเปลี่ยนอะไหล่นอก stock / ปัญหาเกินขอบเขต..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-slate-600 mb-1">แนบรูปภาพ (ถ้ามี — ถ่ายรูปอุปกรณ์/ปัญหาให้ Partner ดูก่อน)</label>
                    <input type="file" name="images[]" accept="image/*" capture="environment" multiple
                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP ไฟล์ละไม่เกิน 8MB เลือกได้หลายรูป</p>
                </div>
                <button type="submit" onclick="return confirmEscalate()"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm py-2.5 rounded-lg">
                    ส่งต่อให้ Partner
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- กำลังซ่อม (in_progress) -->
    <?php if (in_array($ticket->status, ['in_progress', 'waiting_parts']) && $owned_by_tech): ?>

        <!-- แสดงกำหนดการซ่อม + ปรับหมวดหมู่ได้ -->
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-sky-600">กำหนดการซ่อม</p>
                <button onclick="document.getElementById('edit-date-form').classList.toggle('hidden')"
                    class="text-xs text-sky-600 hover:underline">✏️ ปรับหมวดหมู่</button>
            </div>
            <?php if ($ticket->status === 'waiting_parts'): ?>
                <p class="text-xs font-medium text-rose-600 mb-1.5">⏳ เกินกำหนดเดิมแล้ว ระบบเปลี่ยนสถานะเป็น "รออะไหล่" — เลือกหมวดหมู่ใหม่เพื่อประเมินวันเสร็จอีกครั้ง</p>
            <?php endif; ?>
            <?php if ($ticket->tech_start_date): ?>
                <p class="text-sm font-medium text-sky-800">
                    <?= $ticket->repair_category_name ? $ticket->repair_category_name . ' — ' : '' ?>
                    เริ่ม <?= date('d/m/Y', strtotime($ticket->tech_start_date)) ?>
                    ครบกำหนด <?= date('d/m/Y', strtotime($ticket->tech_end_date)) ?>
                </p>
            <?php else: ?>
                <p class="text-sm text-sky-400">ยังไม่ได้กำหนดวัน</p>
            <?php endif; ?>

            <div id="edit-date-form" class="hidden mt-3 pt-3 border-t border-sky-200">
                <form method="POST" action="<?= base_url('tech/tickets/update_date/' . $ticket->id) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="mb-3">
                        <label class="block text-xs text-slate-600 mb-1">หมวดหมู่การซ่อม</label>
                        <p class="text-xs mb-1 <?= $categories_auto_matched ? 'text-emerald-600' : 'text-amber-600' ?>">
                            <?= $categories_auto_matched ? '🔍 กรองตามอาการที่ลูกค้าแจ้งให้อัตโนมัติ' : '⚠️ ไม่พบหมวดหมู่ที่ตรงกับอาการที่แจ้ง แสดงตัวเลือกทั่วไปแทน' ?>
                        </p>
                        <select name="repair_category_id" required
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="">— เลือกหมวดหมู่ —</option>
                            <?php foreach ($repair_categories as $rc): ?>
                                <option value="<?= $rc->id ?>" <?= $ticket->repair_category_id == $rc->id ? 'selected' : '' ?>>
                                    <?= $rc->name ?> (ไม่เกิน <?= $rc->max_days ?> วัน)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-slate-400 mt-1">วันครบกำหนดใหม่จะนับจากวันเริ่มซ่อมเดิม (<?= date('d/m/Y', strtotime($ticket->tech_start_date)) ?>)</p>
                    </div>
                    <button type="submit"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white text-sm py-2 rounded-lg">
                        บันทึก
                    </button>
                </form>
            </div>
        </div>

        <!-- ส่งอัพเดทความคืบหน้าให้ลูกค้า (เฉพาะช่วงกำลังซ่อมจริง ยังไม่เกินกำหนด) -->
        <?php if ($ticket->status === 'in_progress'): ?>
            <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-5 mb-4">
                <h2 class="text-sm font-semibold text-cyan-700 mb-3">📸 ส่งอัพเดทความคืบหน้าให้ลูกค้า</h2>
                <form method="POST" action="<?= base_url('tech/tickets/send_update/' . $ticket->id) ?>"
                    enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="mb-3">
                        <label class="block text-xs text-slate-600 mb-1">รายละเอียด</label>
                        <textarea name="note" rows="2"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                            placeholder="เช่น ถอดฝาเครื่องแล้ว พบว่าลูกกลิ้งสึก กำลังเปลี่ยน..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs text-slate-600 mb-1">แนบรูปภาพ (ถ้ามี)</label>
                        <input type="file" name="images[]" accept="image/*" capture="environment" multiple
                            class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cyan-100 file:text-cyan-700 hover:file:bg-cyan-200">
                    </div>
                    <button type="submit"
                        class="w-full bg-cyan-600 hover:bg-cyan-700 text-white text-sm py-2.5 rounded-lg">
                        ส่งอัพเดทให้ลูกค้าทาง Line
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- ซ่อมเสร็จ (ใช้ได้ทั้งในประกันและหมดประกัน) -->
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
            <h2 class="text-sm font-semibold text-green-700 mb-4">
                <?= $ticket->ticket_type === 'software' ? 'บันทึกผล Remote Support' : 'บันทึกผลการซ่อม' ?>
            </h2>
            <form method="POST" action="<?= base_url('tech/tickets/complete/' . $ticket->id) ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <?php if (!$in_warranty): ?>
                <div class="mb-3">
                    <label class="block text-xs text-slate-600 mb-1">เลขพัสดุที่จัดส่งคืนลูกค้า (ถ้ามี)</label>
                    <input type="text" name="tracking_no"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="TH123456789">
                    <p class="text-xs text-slate-400 mt-1">เลขติดตามพัสดุจากขนส่ง (เช่น ไปรษณีย์ไทย/Kerry/Flash) สำหรับให้ลูกค้าเช็คสถานะจัดส่ง — ไม่ใช่เลข S/N ของเครื่อง</p>
                </div>
                <?php endif; ?>
                <div class="mb-4">
                    <label class="block text-xs text-slate-600 mb-1">สรุปสิ่งที่แก้ไข <span class="text-red-500">*</span></label>
                    <textarea name="tech_note" rows="4" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="<?= $ticket->ticket_type === 'software' ? 'สรุปปัญหาและวิธีแก้ไข...' : 'สรุปอะไหล่ที่เปลี่ยน/ซ่อม...' ?>"></textarea>
                </div>
                <button type="submit"
                    onclick="return confirm('ยืนยันว่าซ่อมเสร็จแล้ว? ระบบจะแจ้งลูกค้าทันที')"
                    class="w-full bg-green-600 hover:bg-green-700 text-white text-sm py-2.5 rounded-lg">
                    ✅ ยืนยันเสร็จสิ้น (แจ้ง Line ลูกค้าอัตโนมัติ)
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ช่างซ่อมเสร็จ (หมดประกัน) รอ admin แจ้งลูกค้า -->
    <?php if ($ticket->status === 'partner_completed'): ?>
        <div class="bg-teal-50 border border-teal-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-teal-700">⚡ ซ่อมเสร็จแล้ว — รอ Admin แจ้งลูกค้า</p>
        </div>
    <?php endif; ?>

    <!-- เสร็จแล้ว -->
    <?php if ($ticket->status === 'completed'): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
            <p class="text-sm font-semibold text-green-700 mb-2">✅ งานเสร็จสิ้นแล้ว</p>
            <?php if ($ticket->tech_note): ?>
                <p class="text-sm text-green-600"><?= nl2br($ticket->tech_note) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    </div>
    <div class="lg:col-span-1">

    <!-- Timeline: รวมประวัติการดำเนินการ + อัพเดทรูป/ข้อความที่ส่งลูกค้า เรียงตามเวลาเดียวกัน -->
    <?php if (!empty($timeline)): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">ประวัติการดำเนินการ</h2>
            <div class="space-y-4">
                <?php foreach ($timeline as $t): ?>
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 <?= $t->type === 'update' ? 'bg-cyan-400' : 'bg-sky-400' ?>"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-700"><?= $t->type === 'update' ? '📸 ' : '' ?><?= nl2br($t->message) ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></p>
                            <?php if (!empty($t->images)): ?>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <?php foreach ($t->images as $img): ?>
                                        <a href="<?= base_url('uploads/tickets/' . $img) ?>" target="_blank">
                                            <img src="<?= base_url('uploads/tickets/' . $img) ?>"
                                                class="w-16 h-16 object-cover rounded-lg border border-slate-200 hover:opacity-80">
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if ($this->session->flashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: '<?= addslashes($this->session->flashdata("success")) ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: '<?= addslashes($this->session->flashdata("error")) ?>'
        });
    <?php endif; ?>

    function confirmEscalate() {
        const form = document.getElementById('escalate-form');
        const partner = form.querySelector('select[name="partner_id"]');
        if (partner && !partner.value) {
            Swal.fire({ icon: 'warning', title: 'กรุณาเลือก Partner', timer: 1800, showConfirmButton: false });
            return false;
        }
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันส่งต่อให้ Partner?',
            text: 'Partner ที่เลือกจะรับผิดชอบงานนี้ต่อทันที',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันส่งต่อ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#f97316'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
        return false;
    }

    let rowIndex = 1;

    function addRow() {
        const tbody = document.getElementById('items-body');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.className = 'item-row border-t border-slate-100';
        tr.innerHTML = `
        <td class="px-4 py-2.5">
            <input type="text" name="items[${rowIndex}][name]" required
                placeholder="รายการ..."
                class="w-full text-sm border-0 focus:ring-0 focus:outline-none bg-transparent">
        </td>
        <td class="px-4 py-2.5">
            <input type="number" name="items[${rowIndex}][qty]" value="1" min="1"
                onchange="calcRow(this)" oninput="calcRow(this)"
                class="w-full text-sm text-center border-0 focus:ring-0 focus:outline-none bg-transparent">
        </td>
        <td class="px-4 py-2.5">
            <input type="number" name="items[${rowIndex}][price]" value="0" min="0" step="0.01"
                onchange="calcRow(this)" oninput="calcRow(this)"
                class="w-full text-sm text-right border-0 focus:ring-0 focus:outline-none bg-transparent">
        </td>
        <td class="px-4 py-2.5 text-right">
            <span class="row-total text-slate-700">0.00</span>
        </td>
        <td class="px-4 py-2.5 text-center">
            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-400">✕</button>
        </td>`;
        tbody.appendChild(tr);
        rowIndex++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) return;
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
        const vatSelect = document.getElementById('vat-select');
        if (!vatSelect) return;
        const vatRate = parseFloat(vatSelect.value) || 0;
        const vatAmt = subtotal * vatRate / 100;
        const grand = subtotal + vatAmt;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('vat-amount').textContent = vatAmt.toFixed(2);
        document.getElementById('grand-total').textContent = grand.toFixed(2);
    }

    function prepareSubmit() {
        const grand = parseFloat(document.getElementById('grand-total').textContent) || 0;
        if (grand <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'ยอดรวมต้องมากกว่า 0',
                text: 'กรุณากรอกรายการและราคา'
            });
            return false;
        }
        document.getElementById('quote-amount-input').value = grand.toFixed(2);

        let details = [];
        document.querySelectorAll('.item-row').forEach(row => {
            const name = row.querySelector('input[name*="[name]"]').value;
            const qty = row.querySelector('input[name*="[qty]"]').value;
            const price = row.querySelector('input[name*="[price]"]').value;
            if (name) details.push(`${name} x${qty} @ ฿${parseFloat(price).toFixed(2)}`);
        });

        const detailField = document.querySelector('textarea[name="quote_detail"]');
        if (detailField && !detailField.value.trim()) {
            detailField.value = details.join('\n');
        }
        return true;
    }
</script>

<?php
function status_badge($status)
{
    $map = [
        'assigned'          => ['รอรับงาน',              'bg-indigo-100 text-indigo-700'],
        'wait_quote'        => ['รอกรอก Quotation',      'bg-purple-100 text-purple-700'],
        'in_progress'       => ['กำลังซ่อม',             'bg-sky-100 text-sky-700'],
        'waiting_parts'     => ['รออะไหล่',               'bg-rose-100 text-rose-700'],
        'wait_review'       => ['รอ Admin ตรวจสอบราคา',  'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'      => ['รอลูกค้ายืนยัน',        'bg-pink-100 text-pink-700'],
        'quote_accepted'    => ['ลูกค้ายืนยันแล้ว',      'bg-green-100 text-green-700'],
        'quote_rejected'    => ['ลูกค้าปฏิเสธ',          'bg-red-100 text-red-700'],
        'escalated'         => ['ส่งต่อแล้ว',            'bg-orange-100 text-orange-700'],
        'partner_completed' => ['ซ่อมเสร็จ รอแจ้งลูกค้า', 'bg-teal-100 text-teal-700'],
        'completed'         => ['เสร็จสิ้น',              'bg-green-100 text-green-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2.5 py-1 rounded-full text-xs font-medium $cls'>$label</span>";
}
?>