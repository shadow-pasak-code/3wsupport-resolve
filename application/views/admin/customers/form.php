<?php $page_title = isset($customer) ? 'แก้ไขลูกค้า' : 'เพิ่มลูกค้า'; ?>

<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/customers') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <form method="POST" enctype="multipart/form-data"
        action="<?= isset($customer) ? base_url('admin/customers/edit/' . $customer->id) : base_url('admin/customers/add') ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

        <!-- ข้อมูลลูกค้า -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 mb-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">ข้อมูลลูกค้า</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อบริษัท / ร้านค้า <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" required
                        value="<?= isset($customer) ? ($customer->company_name ?? '') : '' ?>"
                        placeholder="เช่น บริษัท ABC จำกัด, ร้านตัวอย่าง"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">เบอร์โทร</label>
                    <input type="text" name="phone"
                        value="<?= isset($customer) ? $customer->phone : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">อีเมล</label>
                    <input type="email" name="email"
                        value="<?= isset($customer) ? $customer->email : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ที่อยู่</label>
                    <input type="text" name="address"
                        value="<?= isset($customer) ? ($customer->address ?? '') : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="ที่อยู่สำหรับจัดส่ง">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">หมายเหตุ</label>
                    <input type="text" name="note"
                        value="<?= isset($customer) ? ($customer->note ?? '') : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <?php if (isset($customer) && $customer->line_uid): ?>
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-700 font-medium">✅ ผูก Line OA แล้ว</p>
                        <p class="text-xs text-green-600 font-mono mt-0.5"><?= $customer->line_uid ?></p>
                    </div>
                    <a href="<?= base_url('admin/customers/reset_line/' . $customer->id) ?>"
                        onclick="return confirm('รีเซ็ต Line UID? ลูกค้าต้องผูกใหม่')"
                        class="text-xs text-red-500 hover:underline">รีเซ็ต</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- รายการอุปกรณ์ -->
        <?php if (!isset($customer)): // แสดงเฉพาะตอน add ใหม่ 
        ?>
            <div class="bg-white border border-slate-200 rounded-xl p-6 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-700">อุปกรณ์ / ซอฟต์แวร์ที่ซื้อ</h2>
                    <button type="button" onclick="addDevice()"
                        class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg">
                        + เพิ่มรายการ
                    </button>
                </div>

                <div class="border border-slate-200 rounded-lg overflow-x-auto">
                    <table class="text-sm" style="min-width:900px; width:100%" id="items-table">
                        <thead class="bg-slate-50 text-xs text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 text-left" style="min-width:220px">ชื่ออุปกรณ์ / ซอฟต์แวร์</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:180px">Serial Number</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:100px">ประเภท</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:140px">วันที่ซื้อ</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:140px">ประกันหมด</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:160px">Partner ประจำ</th>
                                <th class="px-4 py-2.5" style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="device-list">
                            <tr class="device-row border-t border-slate-100">
                                <td class="px-4 py-2.5" style="min-width:220px">
                                    <select name="devices[0][equipment_id]" class="equipment-select w-full text-sm"
                                        data-index="0">
                                        <option value="">-- เลือกหรือพิมพ์ค้นหา --</option>
                                        <?php foreach ($equipment_list as $eq): ?>
                                            <option value="<?= $eq->id ?>"
                                                data-type="<?= $eq->device_type ?>"
                                                data-name="<?= $eq->name ?>"
                                                data-partner="<?= $eq->partner_id ?>">
                                                <?= $eq->name ?> <?= $eq->brand ? '(' . $eq->brand . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="devices[0][name]" class="device-name-input">
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-1" style="min-width:180px">
                                        <span class="text-xs font-mono text-slate-500 whitespace-nowrap">SN-</span>
                                        <input type="text" name="devices[0][serial_suffix]"
                                            placeholder="HW-2024-001"
                                            maxlength="20"
                                            oninput="checkSN(this)"
                                            class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono sn-input">
                                        <input type="hidden" name="devices[0][serial_number]" class="sn-hidden">
                                        <span class="sn-status text-xs ml-1 whitespace-nowrap"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="text" name="devices[0][device_type]" class="device-type-input
                w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500" readonly>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="date" name="devices[0][purchase_date]"
                                        class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="date" name="devices[0][warranty_end]"
                                        class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-2.5">
                                    <select disabled
                                        class="partner-select w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500 cursor-not-allowed">
                                        <option value="">— auto จากอุปกรณ์ —</option>
                                        <?php foreach ($partners as $p): ?>
                                            <option value="<?= $p->id ?>"><?= $p->company_name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="devices[0][partner_id]" class="partner-hidden">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" onclick="removeDevice(this)"
                                        class="text-slate-300 hover:text-red-400">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-slate-400 mt-2">* S/N ใช้สำหรับให้ลูกค้ายืนยันตัวตนใน Line OA</p>
            </div>
        <?php endif; ?>

        <!-- ถ้าเป็น edit แสดงอุปกรณ์ปัจจุบัน -->
        <?php if (isset($customer)): ?>
            <div class="bg-white border border-slate-200 rounded-xl p-6 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-slate-700">อุปกรณ์ของลูกค้า</h2>
                    <button type="button" onclick="addDevice()"
                        class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg">
                        + เพิ่มอุปกรณ์ใหม่
                    </button>
                </div>

                <!-- อุปกรณ์ที่มีอยู่แล้ว — แก้ไขได้ -->
                <?php if (!empty($devices)): ?>
                    <div class="border border-slate-200 rounded-lg overflow-x-auto mb-4">
                        <table class="text-sm" style="min-width:900px; width:100%">
                            <thead class="bg-slate-50 text-xs text-slate-500">
                                <tr>
                                    <th class="px-4 py-2.5 text-left" style="min-width:220px">ชื่ออุปกรณ์</th>
                                    <th class="px-4 py-2.5 text-left" style="min-width:160px">Serial Number</th>
                                    <th class="px-4 py-2.5 text-left" style="min-width:100px">ประเภท</th>
                                    <th class="px-4 py-2.5 text-left" style="min-width:140px">วันที่ซื้อ</th>
                                    <th class="px-4 py-2.5 text-left" style="min-width:140px">ประกันหมด</th>
                                    <th class="px-4 py-2.5 text-left" style="min-width:160px">Partner ประจำ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($devices as $d): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2.5">
                                            <select name="existing_devices[<?= $d->id ?>][name]"
                                                class="equipment-select-edit w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5"
                                                data-selected="<?= $d->name ?>">
                                                <option value="<?= $d->name ?>" selected><?= $d->name ?></option>
                                                <?php foreach ($equipment_list as $eq): ?>
                                                    <option value="<?= $eq->name ?>"
                                                        data-type="<?= $eq->device_type ?>"
                                                        data-partner="<?= $eq->partner_id ?>"
                                                        <?= $eq->name === $d->name ? 'selected' : '' ?>>
                                                        <?= $eq->name ?> <?= $eq->brand ? '(' . $eq->brand . ')' : '' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="existing_devices[<?= $d->id ?>][serial_number]"
                                                value="<?= $d->serial_number ?>"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="existing_devices[<?= $d->id ?>][device_type]"
                                                value="<?= $d->device_type === 'hardware' ? 'Hardware' : 'Software' ?>"
                                                class="w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500" readonly>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="date" name="existing_devices[<?= $d->id ?>][purchase_date]"
                                                value="<?= $d->purchase_date ?>"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="date" name="existing_devices[<?= $d->id ?>][warranty_end]"
                                                value="<?= $d->warranty_end ?>"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <select disabled
                                                class="partner-select w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500 cursor-not-allowed">
                                                <option value="">— auto จากอุปกรณ์ —</option>
                                                <?php foreach ($partners as $p): ?>
                                                    <option value="<?= $p->id ?>" <?= $d->partner_id == $p->id ? 'selected' : '' ?>>
                                                        <?= $p->company_name ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="existing_devices[<?= $d->id ?>][partner_id]"
                                                class="partner-hidden" value="<?= $d->partner_id ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- อุปกรณ์ใหม่ที่เพิ่ม -->
                <div class="border border-slate-200 rounded-lg overflow-x-auto" id="new-devices-wrap" style="display:none">
                    <table class="text-sm" style="min-width:900px; width:100%">
                        <thead class="bg-blue-50 text-xs text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 text-left" style="min-width:220px">ชื่ออุปกรณ์ใหม่</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:160px">Serial Number</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:100px">ประเภท</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:140px">วันที่ซื้อ</th>
                                <th class="px-4 py-2.5 text-left" style="min-width:140px">ประกันหมด</th>
                                <th class="px-4 py-2.5 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="device-list"></tbody>
                    </table>
                </div>
                <p class="text-xs text-slate-400 mt-2">* S/N ใช้สำหรับให้ลูกค้ายืนยันตัวตนใน Line OA</p>
            </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                <?= isset($customer) ? 'บันทึก' : 'เพิ่มลูกค้า + อุปกรณ์' ?>
            </button>
            <a href="<?= base_url('admin/customers') ?>"
                class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
        </div>
    </form>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    // equipment options จาก PHP
    const equipmentOptions = <?= json_encode(array_values($equipment_list), JSON_UNESCAPED_UNICODE) ?>;
    const partnersData = <?= json_encode(array_values($partners), JSON_UNESCAPED_UNICODE) ?>;

    let deviceIndex = 1;

    // init Select2 ทุกตัวที่มีอยู่
    function initSelect2(selector) {
        $(selector).select2({
            placeholder: '-- เลือกหรือพิมพ์ค้นหา --',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return 'ไม่พบรายการ';
                },
                searching: function() {
                    return 'กำลังค้นหา...';
                }
            }
        }).on('change', function() {
            const selected = $(this).find(':selected');
            const row = $(this).closest('tr');
            const name = selected.data('name') || '';
            const type = selected.data('type') || '';
            const partnerId = selected.data('partner') || '';

            row.find('.device-name-input').val(name);
            row.find('.device-type-input').val(
                type === 'hardware' ? 'Hardware' : (type === 'software' ? 'Software' : '')
            );

            // auto fill partner + readonly
            // auto fill partner
            const partnerSelect = row.find('.partner-select');
            const partnerHidden = row.find('.partner-hidden');
            if (partnerId) {
                partnerSelect.val(partnerId);
                partnerHidden.val(partnerId);
            } else {
                partnerSelect.val('');
                partnerHidden.val('');
            }
        });
    }

    // init แถวแรก
    initSelect2('.equipment-select');
    // init existing devices (edit mode)
    $('.equipment-select-edit').each(function() {
        $(this).select2({
            width: '100%',
            language: {
                noResults: function() {
                    return 'ไม่พบรายการ';
                },
                searching: function() {
                    return 'กำลังค้นหา...';
                }
            }
        }).on('change', function() {
            const selected = $(this).find(':selected');
            const row = $(this).closest('tr');
            const type = selected.data('type') || '';
            const partnerId = selected.data('partner') || '';
            row.find('input[name*="[device_type]"]').val(
                type === 'hardware' ? 'Hardware' : (type === 'software' ? 'Software' : '')
            );
            row.find('.partner-select').val(partnerId);
            row.find('.partner-hidden').val(partnerId);
        });
    });

    function addDevice() {
        const wrap = document.getElementById('new-devices-wrap');
        if (wrap) wrap.style.display = 'block';
        const tbody = document.getElementById('device-list');
        const tr = document.createElement('tr');
        tr.className = 'device-row border-t border-slate-100';

        // สร้าง option HTML
        let opts = '<option value="">-- เลือกหรือพิมพ์ค้นหา --</option>';
        equipmentOptions.forEach(eq => {
            opts += `<option value="${eq.id}" data-type="${eq.device_type}" data-name="${eq.name}" data-partner="${eq.partner_id || ''}">
            ${eq.name}${eq.brand ? ' (' + eq.brand + ')' : ''}
        </option>`;
        });

        // สร้าง partner options
        let partnerOpts = '<option value="">— auto จากอุปกรณ์ —</option>';
        partnersData.forEach(p => {
            partnerOpts += `<option value="${p.id}">${p.company_name}</option>`;
        });

        tr.innerHTML = `
    <td class="px-4 py-2.5" style="min-width:220px">
        <select name="devices[${deviceIndex}][equipment_id]"
            class="equipment-select w-full text-sm" data-index="${deviceIndex}">
            ${opts}
        </select>
        <input type="hidden" name="devices[${deviceIndex}][name]" class="device-name-input">
    </td>
    <td class="px-4 py-2.5">
        <div class="flex items-center gap-1" style="min-width:180px">
            <span class="text-xs font-mono text-slate-500 whitespace-nowrap">SN-</span>
            <input type="text" name="devices[${deviceIndex}][serial_suffix]"
                placeholder="HW-2024-001"
                maxlength="20"
                oninput="checkSN(this)"
                class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono sn-input">
            <input type="hidden" name="devices[${deviceIndex}][serial_number]" class="sn-hidden">
            <span class="sn-status text-xs ml-1 whitespace-nowrap"></span>
        </div>
    </td>
    <td class="px-4 py-2.5">
        <input type="text" name="devices[${deviceIndex}][device_type]"
            class="device-type-input w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500" readonly>
    </td>
    <td class="px-4 py-2.5">
        <input type="date" name="devices[${deviceIndex}][purchase_date]"
            class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </td>
    <td class="px-4 py-2.5">
        <input type="date" name="devices[${deviceIndex}][warranty_end]"
            class="w-full text-sm border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </td>
    <td class="px-4 py-2.5">
        <select class="partner-select w-full text-sm border border-slate-200 rounded-lg px-2 py-1.5 bg-slate-50 text-slate-500 cursor-not-allowed" disabled>
            ${partnerOpts}
        </select>
        <input type="hidden" name="devices[${deviceIndex}][partner_id]" class="partner-hidden">
    </td>
    <td class="px-4 py-2.5 text-center">
        <button type="button" onclick="removeDevice(this)"
            class="text-slate-300 hover:text-red-400">✕</button>
    </td>`;

        tbody.appendChild(tr);
        initSelect2($(tr).find('.equipment-select'));
        deviceIndex++;
    }

    function removeDevice(btn) {
        const rows = document.querySelectorAll('.device-row');
        if (rows.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'ต้องมีอย่างน้อย 1 รายการ',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }
        Swal.fire({
            title: 'ลบรายการนี้?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const row = btn.closest('tr');
                $(row).find('.equipment-select').select2('destroy');
                row.remove();
            }
        });
    }

    // S/N check ซ้ำ real-time
    const SN_CHECK_URL = '<?= base_url("admin/devices/check_sn") ?>';
    let snTimers = {};

    function checkSN(input) {
        const suffix = input.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        input.value = suffix;

        const row = input.closest('tr') || input.closest('div').closest('tr');
        const hidden = input.closest('td, div').querySelector('.sn-hidden') ||
            input.parentElement.querySelector('.sn-hidden');
        const status = input.parentElement.querySelector('.sn-status');

        const full = suffix ? 'SN-' + suffix : '';
        if (hidden) hidden.value = full;

        if (!suffix) {
            input.classList.remove('border-green-400', 'border-red-400');
            input.classList.add('border-slate-300');
            if (status) status.textContent = '';
            return;
        }

        // เช็คซ้ำในฟอร์มเดียวกันก่อน
        const allSuffixes = document.querySelectorAll('.sn-input');
        let dupInForm = false;
        allSuffixes.forEach(el => {
            if (el !== input && el.value.toUpperCase() === suffix) dupInForm = true;
        });

        if (dupInForm) {
            input.classList.remove('border-slate-300', 'border-green-400');
            input.classList.add('border-red-400');
            if (status) status.innerHTML = '<span class="text-red-500">❌ ซ้ำในฟอร์ม</span>';
            if (hidden) hidden.value = '';
            return;
        }

        // เช็คซ้ำกับ DB
        if (status) status.textContent = '...';
        clearTimeout(snTimers[suffix]);
        snTimers[suffix] = setTimeout(() => {
            fetch(SN_CHECK_URL + '?sn=' + encodeURIComponent(full))
                .then(r => r.json())
                .then(data => {
                    if (data.exists) {
                        input.classList.remove('border-slate-300', 'border-green-400');
                        input.classList.add('border-red-400');
                        if (status) status.innerHTML = '<span class="text-red-500">❌ ซ้ำในระบบ</span>';
                        if (hidden) hidden.value = '';
                    } else {
                        input.classList.remove('border-slate-300', 'border-red-400');
                        input.classList.add('border-green-400');
                        if (status) status.innerHTML = '<span class="text-green-600">✅ ใช้ได้</span>';
                        if (hidden) hidden.value = full;
                    }
                })
                .catch(() => {
                    if (status) status.textContent = '';
                    if (hidden) hidden.value = full;
                });
        }, 500);
    }
</script>