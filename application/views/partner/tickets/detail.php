<?php $page_title = 'รายละเอียดงาน #' . $ticket->id; ?>

<div class="p-6 max-w-6xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('partner/tickets') ?>" class="text-slate-400 hover:text-slate-600">
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
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">ข้อมูลงานซ่อม</h2>
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">อุปกรณ์</dt>
                <dd class="font-medium"><?= $ticket->device_name ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 mb-0.5">Serial Number</dt>
                <dd class="font-mono text-xs"><?= $ticket->serial_number ?></dd>
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
        <?php $ticket_images = $ticket->images ? json_decode($ticket->images, true) : []; ?>
        <?php if (!empty($ticket_images)): ?>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-xs text-slate-400 mb-2">รูปภาพจากช่าง (<?= count($ticket_images) ?>) — ดูก่อนรับงานได้</dt>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($ticket_images as $img): ?>
                        <a href="<?= base_url('uploads/tickets/' . $img) ?>" target="_blank">
                            <img src="<?= base_url('uploads/tickets/' . $img) ?>"
                                class="w-20 h-20 object-cover rounded-lg border border-slate-200 hover:opacity-80">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $today = date('Y-m-d');
    $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;
    ?>

    <!-- ================================================= -->
    <!-- กรณีอยู่ในประกัน: ทำงานเหมือนช่าง (assigned/in_progress) -->
    <!-- ================================================= -->

    <!-- รับงาน (assigned) -->
    <?php if ($ticket->status === 'assigned'): ?>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-5">
            <h2 class="text-sm font-semibold text-indigo-700 mb-4">รับงานและกำหนดวัน</h2>
            <form method="POST" action="<?= base_url('partner/tickets/accept/' . $ticket->id) ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">วันเริ่มซ่อม <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required
                            min="<?= date('Y-m-d') ?>"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">คาดว่าเสร็จ <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required
                            min="<?= date('Y-m-d') ?>"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
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

    <!-- กำลังซ่อม (in_progress) -->
    <?php if (in_array($ticket->status, ['in_progress', 'waiting_parts'])): ?>

        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 mb-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-sky-600">กำหนดการซ่อม</p>
                <button onclick="document.getElementById('edit-date-form').classList.toggle('hidden')"
                    class="text-xs text-sky-600 hover:underline">✏️ แก้ไขวัน</button>
            </div>
            <?php if ($ticket->tech_start_date): ?>
                <p class="text-sm font-medium text-sky-800">
                    <?= date('d/m/Y', strtotime($ticket->tech_start_date)) ?>
                    — <?= date('d/m/Y', strtotime($ticket->tech_end_date)) ?>
                </p>
            <?php else: ?>
                <p class="text-sm text-sky-400">ยังไม่ได้กำหนดวัน</p>
            <?php endif; ?>

            <div id="edit-date-form" class="hidden mt-3 pt-3 border-t border-sky-200">
                <form method="POST" action="<?= base_url('partner/tickets/update_date/' . $ticket->id) ?>">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">วันเริ่มซ่อม</label>
                            <input type="date" name="start_date" required
                                value="<?= $ticket->tech_start_date ?>"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">คาดว่าเสร็จ</label>
                            <input type="date" name="end_date" required
                                value="<?= $ticket->tech_end_date ?>"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white text-sm py-2 rounded-lg">
                        บันทึกวัน
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-5">
            <h2 class="text-sm font-semibold text-green-700 mb-4">บันทึกผลการซ่อม</h2>
            <form method="POST" action="<?= base_url('partner/tickets/complete/' . $ticket->id) ?>">
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
                    <label class="block text-xs text-slate-600 mb-1">สรุปสิ่งที่ซ่อม/เปลี่ยน <span class="text-red-500">*</span></label>
                    <textarea name="tech_note" rows="4" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="สรุปอะไหล่ที่เปลี่ยน/ซ่อม..."></textarea>
                </div>
                <button type="submit"
                    onclick="return confirm('ยืนยันว่าซ่อมเสร็จแล้ว? ระบบจะแจ้งลูกค้าทันที')"
                    class="w-full bg-green-600 hover:bg-green-700 text-white text-sm py-2.5 rounded-lg">
                    ✅ ยืนยันเสร็จสิ้น (แจ้ง Line ลูกค้าอัตโนมัติ)
                </button>
            </form>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-orange-700 mb-3">ส่งต่อ Admin (เกินความสามารถ)</h2>
            <form method="POST" action="<?= base_url('partner/tickets/escalate/' . $ticket->id) ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="mb-3">
                    <label class="block text-xs text-slate-600 mb-1">ระบุเหตุผล <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="2" required
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="เช่น ต้องเปลี่ยนอะไหล่นอก stock..."></textarea>
                </div>
                <button type="submit"
                    onclick="return confirm('ยืนยันส่งต่อ Admin?')"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm py-2.5 rounded-lg">
                    ส่งต่อ Admin
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ================================================= -->
    <!-- กรณีหมดประกัน: flow ใบเสนอราคา -->
    <!-- ================================================= -->

    <?php if ($ticket->status === 'wait_quote' && $in_warranty): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-5">
            <p class="text-sm font-semibold text-green-700">✅ อุปกรณ์นี้ยังอยู่ในประกัน</p>
            <p class="text-xs text-green-600 mt-1">ไม่สามารถแนบใบเสนอราคาได้ การซ่อมอยู่ภายใต้การรับประกัน กรุณาติดต่อ Admin</p>
        </div>
    <?php elseif ($ticket->status === 'wait_quote' && !$in_warranty): ?>
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-5">
            <div class="bg-purple-600 px-6 py-4">
                <h2 class="text-white font-semibold text-lg">ใบเสนอราคา</h2>
                <p class="text-purple-200 text-sm">Ticket #<?= $ticket->id ?> · <?= date('d/m/Y') ?></p>
            </div>

            <form method="POST" action="<?= base_url('partner/tickets/quote/' . $ticket->id) ?>"
                enctype="multipart/form-data" id="quote-form">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700">รายการค่าใช้จ่าย</h3>
                            <button type="button" onclick="addRow()"
                                class="text-xs bg-purple-50 hover:bg-purple-100 text-purple-700 px-3 py-1.5 rounded-lg">
                                + เพิ่มรายการ
                            </button>
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
                                            <input type="text" name="items[0][name]" required
                                                placeholder="เช่น ค่าแรง, อะไหล่ drum unit"
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
                                        <td class="px-4 py-2.5 text-right">
                                            <span class="row-total text-slate-700">0.00</span>
                                        </td>
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
                            placeholder="เช่น ราคานี้ยังไม่รวมค่าขนส่ง / ระยะเวลารับประกัน 30 วัน"></textarea>
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
    <?php if (in_array($ticket->status, ['wait_review', 'wait_confirm'])): ?>
        <div class="bg-pink-50 border border-pink-200 rounded-xl p-5 mb-5">
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
            <p class="text-xs text-pink-400 mt-2">* ราคาที่ Admin เสนอต่อลูกค้าอาจแตกต่างจากราคานี้</p>
        </div>
    <?php endif; ?>

    <!-- ลูกค้ายืนยันแล้ว → กำหนดวันคาดว่าจะเสร็จ (เหมือนบริบทในประกัน) -->
    <?php if ($ticket->status === 'quote_accepted'): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-5">
            <p class="text-sm font-semibold text-green-700">✅ ลูกค้ายืนยันแล้ว — ดำเนินการซ่อมได้เลย</p>
            <p class="text-xl font-bold text-green-800 mt-1">฿<?= number_format($ticket->partner_quote_amount ?? $ticket->quote_amount, 2) ?></p>
            <p class="text-xs text-green-500 mt-1">* ราคาที่คุณเสนอ</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-5">
            <h2 class="text-sm font-semibold text-indigo-700 mb-4">รับงานและกำหนดวัน</h2>
            <form method="POST" action="<?= base_url('partner/tickets/accept/' . $ticket->id) ?>">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">วันเริ่มซ่อม <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required
                            min="<?= date('Y-m-d') ?>"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">คาดว่าเสร็จ <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required
                            min="<?= date('Y-m-d') ?>"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-slate-600 mb-1">หมายเหตุ (ถ้ามี)</label>
                    <textarea name="note" rows="2"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="เช่น นัดลูกค้าส่งเครื่องวันที่..."></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2.5 rounded-lg">
                    ✅ กำหนดวันและแจ้งลูกค้า
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ลูกค้าปฏิเสธ -->
    <?php if ($ticket->status === 'quote_rejected'): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-5">
            <p class="text-sm font-semibold text-red-700">❌ ลูกค้าปฏิเสธใบเสนอราคา</p>
            <p class="text-sm text-red-600 mt-1">กรุณาติดต่อ Admin เพื่อดำเนินการต่อ</p>
        </div>
    <?php endif; ?>

    <!-- partner ซ่อมเสร็จ รอ admin แจ้งลูกค้า -->
    <?php if ($ticket->status === 'partner_completed'): ?>
        <div class="bg-teal-50 border border-teal-200 rounded-xl p-5 mb-5">
            <p class="text-sm font-semibold text-teal-700">⚡ ซ่อมเสร็จแล้ว — รอ Admin แจ้งลูกค้า</p>
        </div>
    <?php endif; ?>

    <!-- เสร็จสิ้น -->
    <?php if ($ticket->status === 'completed'): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="text-sm font-semibold text-green-700">✅ งานเสร็จสิ้นแล้ว</p>
            <?php if ($ticket->tracking_no): ?>
                <p class="text-sm text-green-600 mt-1">เลขพัสดุ: <span class="font-mono"><?= $ticket->tracking_no ?></span></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    </div>
    <div class="lg:col-span-1">

    <!-- Timeline -->
    <?php if (!empty($logs)): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">ประวัติการดำเนินการ</h2>
            <div class="space-y-3">
                <?php foreach ($logs as $log): ?>
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full bg-purple-400 mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-slate-700"><?= $log->message ?></p>
                            <p class="text-xs text-slate-400 mt-0.5"><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></p>
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
        'assigned'          => ['รอรับงาน',       'bg-indigo-100 text-indigo-700'],
        'in_progress'       => ['กำลังซ่อม',      'bg-sky-100 text-sky-700'],
        'waiting_parts'     => ['รออะไหล่',        'bg-rose-100 text-rose-700'],
        'wait_quote'        => ['รอกรอก Quotation', 'bg-purple-100 text-purple-700'],
        'wait_review'       => ['รอ Admin ตรวจสอบราคา', 'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'      => ['รอลูกค้ายืนยัน',   'bg-pink-100 text-pink-700'],
        'quote_accepted'    => ['ลูกค้ายืนยันแล้ว',  'bg-green-100 text-green-700'],
        'quote_rejected'    => ['ลูกค้าปฏิเสธ',      'bg-red-100 text-red-700'],
        'escalated'         => ['ส่งต่อ Admin',      'bg-orange-100 text-orange-700'],
        'partner_completed' => ['ซ่อมเสร็จ รอแจ้งลูกค้า', 'bg-teal-100 text-teal-700'],
        'completed'         => ['เสร็จสิ้น',          'bg-green-100 text-green-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2.5 py-1 rounded-full text-xs font-medium $cls'>$label</span>";
}
?>