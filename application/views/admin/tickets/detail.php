<?php $page_title = 'รายละเอียด Ticket #' . $ticket->id; ?>

<div class="p-6 max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/tickets') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold">Ticket #<?= $ticket->id ?></h1>
        <?= status_badge($ticket->status) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Left -->
        <div class="lg:col-span-2 space-y-5">

            <!-- ข้อมูลหลัก -->
            <div class="bg-white border border-slate-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">ข้อมูล Ticket</h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">ลูกค้า</dt>
                        <dd class="font-medium"><?= $ticket->customer_name ?></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">เบอร์โทร</dt>
                        <dd><?= $ticket->phone ?? '—' ?></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">อุปกรณ์</dt>
                        <dd class="font-medium"><?= $ticket->device_name ?></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">Serial Number</dt>
                        <dd class="font-mono text-xs"><?= $ticket->serial_number ?></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">ประเภท</dt>
                        <dd><?= $ticket->ticket_type === 'hardware' ? 'Hardware' : 'Software' ?></dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">Partner ประจำอุปกรณ์</dt>
                        <dd>
                            <?php
                            $device_partner = $this->db
                                ->select('p.company_name')
                                ->from('devices d')
                                ->join('partners p', 'p.id = d.partner_id', 'left')
                                ->where('d.id', $ticket->device_id)
                                ->get()->row();
                            echo $device_partner->company_name ?? '<span class="text-slate-400">—</span>';
                            ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-xs mb-0.5">ประกันหมด</dt>
                        <dd>
                            <?php if ($ticket->warranty_end): ?>
                                <?php $in_w = $ticket->warranty_end >= date('Y-m-d'); ?>
                                <span class="<?= $in_w ? 'text-green-600' : 'text-red-500' ?>">
                                    <?= date('d/m/Y', strtotime($ticket->warranty_end)) ?>
                                    (<?= $in_w ? 'อยู่ในประกัน' : 'หมดประกัน' ?>)
                                </span>
                                <?php else: ?>—<?php endif; ?>
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-400 text-xs mb-0.5">อาการ / ปัญหา</dt>
                        <dd class="text-slate-700 leading-relaxed"><?= nl2br($ticket->issue_desc) ?></dd>
                    </div>
                    <?php if ($ticket->note): ?>
                        <div class="col-span-2">
                            <dt class="text-slate-400 text-xs mb-0.5">หมายเหตุจากลูกค้า</dt>
                            <dd class="text-slate-600"><?= nl2br($ticket->note) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
                <?php $ticket_images = $ticket->images ? json_decode($ticket->images, true) : []; ?>
                <?php if (!empty($ticket_images)): ?>
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <dt class="text-slate-400 text-xs mb-2">รูปภาพที่ช่างแนบมา (<?= count($ticket_images) ?>)</dt>
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

            <!-- Quotation -->
            <?php
            // ดึงใบเสนอราคาของ Admin (ถ้ามี)
            $admin_quotation = $this->db->get_where('quotations', ['ticket_id' => $ticket->id])->row();
            ?>

            <!-- ใบเสนอราคาจาก Partner (read only) -->
            <!-- ใบเสนอราคาจาก Partner (แสดงเสมอถ้ามี) -->
            <?php
            $today = date('Y-m-d');
            $in_warranty = !empty($ticket->warranty_end) && $ticket->warranty_end >= $today;
            ?>
            <?php if ($in_warranty): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-green-700">✅ อุปกรณ์นี้ยังอยู่ในประกัน ไม่มีค่าใช้จ่ายสำหรับการซ่อม</p>
                </div>
            <?php elseif ($ticket->quote_amount): ?>
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-purple-700 mb-2">ใบเสนอราคาจาก Partner</h2>
                    <p class="text-2xl font-semibold text-purple-800 mb-1">฿<?= number_format($ticket->partner_quote_amount ?? $ticket->quote_amount, 2) ?></p>
                    <p class="text-sm text-purple-600 mb-3"><?= nl2br($ticket->quote_detail) ?></p>
                    <div class="flex gap-3 mb-3">
                        <?php if ($ticket->quote_file): ?>
                            <a href="<?= base_url('uploads/quotations/' . $ticket->quote_file) ?>" target="_blank"
                                class="inline-flex items-center gap-1 text-sm text-purple-700 hover:underline">
                                📎 ดูไฟล์แนบ
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('quotation/partner/' . $ticket->id) ?>" target="_blank"
                            class="inline-flex items-center gap-1 text-sm text-purple-700 hover:underline">
                            📄 ดูใบเสนอราคา Partner
                        </a>
                    </div>
                    <div class="mt-3 pt-3 border-t border-purple-200 flex gap-3">
                        <a href="<?= base_url('quotation/partner/' . $ticket->id) ?>" target="_blank"
                            class="inline-block bg-white border border-purple-300 hover:bg-purple-50 text-purple-700 text-sm px-4 py-2 rounded-lg">
                            📄 ดูใบ Partner
                        </a>
                        <?php if (!$admin_quotation && !$in_warranty): ?>
                            <a href="<?= base_url('admin/tickets/quotation/' . $ticket->id) ?>"
                                class="inline-block bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">
                                ✏️ ออกใบเสนอราคาในนามบริษัท
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ใบเสนอราคาของ Admin -->
            <?php if ($admin_quotation): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-blue-700">ใบเสนอราคา (ออกโดยบริษัท)</h2>
                        <a href="<?= base_url('admin/tickets/quotation/' . $ticket->id) ?>"
                            class="text-xs text-blue-600 hover:underline">✏️ แก้ไข</a>
                    </div>
                    <p class="text-2xl font-semibold text-blue-800 mb-1">฿<?= number_format($admin_quotation->total, 2) ?></p>
                    <?php if ($admin_quotation->note): ?>
                        <p class="text-sm text-blue-600 mb-3"><?= nl2br($admin_quotation->note) ?></p>
                    <?php endif; ?>

                    <div class="flex gap-3 mt-3 pt-3 border-t border-blue-200">
                        <a href="<?= base_url('quotation/view/' . $ticket->id) ?>" target="_blank"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
                            📄 ดูใบเสนอราคา
                        </a>
                        <?php if (in_array($ticket->status, ['wait_review', 'wait_confirm'])): ?>
                            <a href="<?= base_url('admin/tickets/send_quote/' . $ticket->id) ?>"
                                onclick="return confirm('ส่งใบเสนอราคาให้ลูกค้าผ่าน Line OA?')"
                                class="inline-block bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg">
                                📨 ส่งให้ลูกค้าผ่าน Line
                            </a>
                        <?php elseif ($ticket->status === 'quote_accepted'): ?>
                            <span class="text-sm text-green-600 font-medium self-center">✅ ลูกค้ายืนยันแล้ว</span>
                        <?php elseif ($ticket->status === 'quote_rejected'): ?>
                            <span class="text-sm text-red-500 font-medium self-center">❌ ลูกค้าปฏิเสธ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ถ้ายังไม่มีใบเสนอราคาเลย -->
            <?php elseif (!$ticket->quote_amount): ?>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <p class="text-sm text-slate-500 mb-3">ยังไม่มีใบเสนอราคา</p>
                    <a href="<?= base_url('admin/tickets/quotation/' . $ticket->id) ?>"
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
                        📄 ออกใบเสนอราคา
                    </a>
                </div>
            <?php endif; ?>

            <!-- Tech note (ถ้ามี) -->
            <?php if ($ticket->tech_note): ?>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-700 mb-2">บันทึกจากช่าง</h2>
                    <p class="text-sm text-slate-600"><?= nl2br($ticket->tech_note) ?></p>
                </div>
            <?php endif; ?>

            <!-- Timeline: รวมประวัติการดำเนินการ + อัพเดทรูป/ข้อความที่ส่งลูกค้า เรียงตามเวลาเดียวกัน -->
            <?php if (!empty($timeline)): ?>
                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-slate-700 mb-4">ประวัติการดำเนินการ</h2>
                    <div class="space-y-4">
                        <?php foreach ($timeline as $t): ?>
                            <div class="flex gap-3 text-sm">
                                <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 <?= $t->type === 'update' ? 'bg-cyan-400' : 'bg-blue-400' ?>"></div>
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

        <!-- Right: Actions -->
        <div class="space-y-4">

            <!-- PENDING: อนุมัติ / ปฏิเสธ -->
            <?php if ($ticket->status === 'pending'): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-amber-700 mb-3">รออนุมัติ</p>
                    <a href="<?= base_url('admin/tickets/approve/' . $ticket->id) ?>"
                        onclick="return confirm('ยืนยันการอนุมัติ Ticket นี้?')"
                        class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-white text-sm py-2 rounded-lg mb-2">
                        ✅ อนุมัติ
                    </a>
                    <button onclick="showRejectModal()"
                        class="block w-full text-center bg-white hover:bg-red-50 border border-red-300 text-red-500 text-sm py-2 rounded-lg">
                        ❌ ไม่อนุมัติ
                    </button>
                </div>
            <?php endif; ?>

            <!-- APPROVED / ESCALATED (Partner ส่งกลับ) / QUOTE_ACCEPTED: Assign (จ่ายงานอัตโนมัติจากคิวช่างว่าง) -->
            <?php if (in_array($ticket->status, ['approved', 'escalated', 'quote_accepted'])): ?>
                <?php $suggested = null; foreach ($tech_queue as $q) { if ($q['id'] == $suggested_tech_id) { $suggested = $q; break; } } ?>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-3">
                        <?= $ticket->status === 'escalated' ? 'Partner ส่งกลับ — จ่ายงานให้ช่างใหม่' : ($ticket->status === 'quote_accepted' ? 'มอบหมายงานซ่อม' : 'มอบหมายงาน') ?>
                    </p>

                    <?php if ($suggested): ?>
                        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                            <img src="<?= image_url($suggested['avatar'], 'uploads/avatars/technicians/') ?>"
                                class="w-9 h-9 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-blue-600">ช่างที่แนะนำ (คิวว่างสุด)</p>
                                <p class="text-sm font-medium text-slate-800 truncate"><?= $suggested['name'] ?></p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 shrink-0">ว่างวันนี้</span>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3">
                            ⚠️ ช่างทุกคนติดงานอยู่ในขณะนี้ — เลือกช่างที่จะว่างเร็วที่สุดในรายการได้
                        </p>
                    <?php endif; ?>

                    <button type="button" onclick="showAssignModal()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-lg">
                        <?= $suggested ? 'ยืนยัน/เปลี่ยนช่างที่จ่ายงาน' : 'เลือกช่างที่จ่ายงาน' ?>
                    </button>
                </div>
            <?php endif; ?>

            <!-- ผู้รับผิดชอบ -->
            <?php if ($ticket->technician_name || $ticket->partner_name): ?>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <p class="text-xs text-slate-400 mb-1">ผู้รับผิดชอบปัจจุบัน</p>
                    <p class="text-sm font-medium"><?= $ticket->technician_name ?? $ticket->partner_name ?></p>
                    <?php if ($ticket->tech_start_date): ?>
                        <p class="text-xs text-slate-400 mt-1">
                            <?= date('d/m/y', strtotime($ticket->tech_start_date)) ?>
                            — <?= date('d/m/y', strtotime($ticket->tech_end_date)) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Tracking -->
            <?php if ($ticket->tracking_no): ?>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <p class="text-xs text-slate-400 mb-1">เลขพัสดุ</p>
                    <p class="text-sm font-mono font-medium"><?= $ticket->tracking_no ?></p>
                </div>
            <?php endif; ?>

            <!-- ปิด Ticket -->
            <!-- ซ่อมเสร็จ → Admin แจ้งลูกค้าก่อน แล้วค่อยปิด -->
            <?php if (in_array($ticket->status, ['completed', 'partner_completed'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 space-y-3">
                    <p class="text-sm font-semibold text-green-700">✅ ซ่อมเสร็จแล้ว</p>

                    <!-- ฟอร์มแจ้งลูกค้า -->
                    <form method="POST" action="<?= base_url('admin/tickets/notify_complete/' . $ticket->id) ?>">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                        <label class="block text-xs text-slate-600 mb-1">ข้อความแจ้งลูกค้า</label>
                        <textarea name="message" rows="3" required
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="เช่น เครื่องซ่อมเสร็จแล้วครับ สามารถมารับได้ที่บริษัทในวันจันทร์-ศุกร์ เวลา 9:00-17:00 น."><?php
                                                                                                                                    $default_msg = "✅ เครื่องของคุณซ่อมเสร็จแล้วครับ Ticket #{$ticket->id}\n";
                                                                                                                                    if ($ticket->tracking_no) {
                                                                                                                                        $default_msg .= "เลขพัสดุ: {$ticket->tracking_no}\n";
                                                                                                                                    }
                                                                                                                                    $default_msg .= "กรุณาติดต่อบริษัทเพื่อนัดรับเครื่องคืนได้เลยครับ\nโทร: " . $this->config->item('company_phone');
                                                                                                                                    echo $default_msg;
                                                                                                                                    ?></textarea>
                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white text-sm py-2 rounded-lg mb-2">
                            📱 แจ้งลูกค้าผ่าน Line
                        </button>
                    </form>

                    <!-- ปิด Ticket -->
                    <a href="<?= base_url('admin/tickets/close/' . $ticket->id) ?>"
                        onclick="return confirm('ปิด Ticket นี้?')"
                        class="block w-full text-center border border-slate-300 hover:bg-slate-50 text-slate-600 text-sm py-2 rounded-lg">
                        ปิด Ticket
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal ปฏิเสธ -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm mx-4 shadow-xl">
        <h3 class="text-sm font-semibold text-slate-800 mb-3">ระบุเหตุผลที่ไม่อนุมัติ</h3>
        <form method="POST" action="<?= base_url('admin/tickets/reject/' . $ticket->id) ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <textarea name="reason" rows="3" required
                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-red-500"
                placeholder="เช่น อาการไม่ครบถ้วน / ไม่อยู่ในขอบเขตบริการ"></textarea>
            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm py-2 rounded-lg">
                    ยืนยันไม่อนุมัติ
                </button>
                <button type="button" onclick="hideRejectModal()"
                    class="flex-1 bg-white border border-slate-300 text-slate-600 text-sm py-2 rounded-lg">
                    ยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (in_array($ticket->status, ['approved', 'escalated', 'quote_accepted'])): ?>
<!-- Modal จ่ายงานอัตโนมัติ: คิวช่าง -->
<div id="assign-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl w-full max-w-md shadow-xl max-h-[85vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">คิวงานช่าง — เลือกผู้รับผิดชอบ</h3>
            <p class="text-xs text-slate-400 mt-0.5">ระบบจัดอันดับช่างว่างไว้บนสุดให้แล้ว เลือกคนอื่นได้ถ้าต้องการ</p>
        </div>
        <form method="POST" action="<?= base_url('admin/tickets/assign/' . $ticket->id) ?>" class="overflow-y-auto flex-1">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <div class="p-3 space-y-2">
                <?php foreach ($tech_queue as $q): ?>
                    <label class="flex items-center gap-3 border rounded-lg p-3 cursor-pointer <?= $q['id'] == $suggested_tech_id ? 'border-blue-400 bg-blue-50' : 'border-slate-200 hover:bg-slate-50' ?>">
                        <input type="radio" name="technician_id" value="<?= $q['id'] ?>"
                            <?= $q['id'] == $suggested_tech_id ? 'checked' : '' ?> class="shrink-0">
                        <img src="<?= image_url($q['avatar'], 'uploads/avatars/technicians/') ?>"
                            class="w-9 h-9 rounded-full object-cover shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-800 truncate"><?= $q['name'] ?></p>
                                <?php if ($q['id'] == $suggested_tech_id): ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-600 text-white shrink-0">แนะนำ</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($q['busy']): ?>
                                <p class="text-xs text-orange-600 truncate">
                                    ติดงาน: <?= $q['busy_customer'] ?> · ว่างวันที่ <?= date('d/m/Y', strtotime($q['busy_until'])) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-xs text-green-600">ว่างวันนี้ · งานค้าง <?= $q['active_count'] ?> งาน</p>
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full shrink-0 <?= $q['busy'] ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' ?>">
                            <?= $q['busy'] ? 'ไม่ว่าง' : 'ว่าง' ?>
                        </span>
                    </label>
                <?php endforeach; ?>
                <?php if (empty($tech_queue)): ?>
                    <p class="text-sm text-slate-400 text-center py-6">ยังไม่มีช่างในระบบ</p>
                <?php endif; ?>
            </div>
            <div class="p-4 border-t border-slate-100 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-lg">
                    ยืนยันจ่ายงาน
                </button>
                <button type="button" onclick="hideAssignModal()"
                    class="flex-1 bg-white border border-slate-300 text-slate-600 text-sm py-2 rounded-lg">
                    ยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if ($this->session->flashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: '<?= $this->session->flashdata("success") ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: '<?= $this->session->flashdata("error") ?>'
        });
    <?php endif; ?>

    function showRejectModal() {
        document.getElementById('reject-modal').classList.remove('hidden');
    }

    function hideRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
    }

    function showAssignModal() {
        document.getElementById('assign-modal').classList.remove('hidden');
    }

    function hideAssignModal() {
        document.getElementById('assign-modal').classList.add('hidden');
    }
</script>

<?php
function status_badge($status)
{
    $map = [
        'pending'        => ['รออนุมัติ',         'bg-amber-100 text-amber-700'],
        'approved'       => ['อนุมัติแล้ว',        'bg-blue-100 text-blue-700'],
        'assigned'       => ['มอบหมายแล้ว',        'bg-indigo-100 text-indigo-700'],
        'in_progress'    => ['กำลังซ่อม',          'bg-sky-100 text-sky-700'],
        'waiting_parts'  => ['รออะไหล่',           'bg-rose-100 text-rose-700'],
        'wait_quote'     => ['รอใบเสนอราคา',       'bg-purple-100 text-purple-700'],
        'wait_review'    => ['รอตรวจสอบราคา',      'bg-fuchsia-100 text-fuchsia-700'],
        'wait_confirm'   => ['รอลูกค้ายืนยัน',     'bg-pink-100 text-pink-700'],
        'quote_accepted' => ['ลูกค้ายืนยันแล้ว',   'bg-green-100 text-green-700'],
        'quote_rejected' => ['ลูกค้าปฏิเสธ',       'bg-red-100 text-red-700'],
        'escalated'      => ['ส่งต่อ Partner',      'bg-orange-100 text-orange-700'],
        'completed'      => ['เสร็จสิ้น',           'bg-green-100 text-green-700'],
        'closed'         => ['ปิด',                 'bg-slate-100 text-slate-500'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-500'];
    return "<span class='px-2.5 py-1 rounded-full text-xs font-medium $cls'>$label</span>";
}
?>