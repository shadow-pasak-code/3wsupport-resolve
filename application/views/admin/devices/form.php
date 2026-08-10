<?php $page_title = isset($device) ? 'แก้ไขอุปกรณ์' : 'เพิ่มอุปกรณ์'; ?>

<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/devices') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST" enctype="multipart/form-data"
            action="<?= isset($device) ? base_url('admin/devices/edit/' . $device->id) : base_url('admin/devices/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <!-- รูปอุปกรณ์ -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">รูปอุปกรณ์</label>
                <?php if (isset($device) && $device->image): ?>
                    <div class="mb-2">
                        <img src="<?= image_url($device->image, 'uploads/devices/') ?>"
                            class="w-24 h-24 rounded-lg object-cover border border-slate-200">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP ขนาดไม่เกิน 2MB</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ลูกค้า <span class="text-red-500">*</span></label>
                <select name="customer_id" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— เลือกลูกค้า —</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c->id ?>" <?= (isset($device) && $device->customer_id == $c->id) ? 'selected' : '' ?>>
                            <?= $c->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่ออุปกรณ์ <span class="text-red-500">*</span></label>
                <select name="equipment_id" id="equipment-select" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— เลือกอุปกรณ์ —</option>
                    <?php foreach ($equipment_list as $eq): ?>
                        <option value="<?= $eq->id ?>"
                            data-name="<?= $eq->name ?>"
                            data-type="<?= $eq->device_type ?>"
                            data-partner="<?= $eq->partner_id ?>"
                            <?= (isset($device) && $device->name === $eq->name) ? 'selected' : '' ?>>
                            <?= $eq->name ?> <?= $eq->brand ? '(' . $eq->brand . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="name" id="device-name-hidden"
                    value="<?= isset($device) ? $device->name : '' ?>">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Serial Number <span class="text-red-500">*</span></label>
                <input type="text" name="serial_number" required
                    value="<?= isset($device) ? $device->serial_number : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                    placeholder="SN-2024-00001">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ประเภท</label>
                <select name="device_type" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="hardware" <?= (isset($device) && $device->device_type === 'hardware') ? 'selected' : '' ?>>Hardware</option>
                    <option value="software" <?= (isset($device) && $device->device_type === 'software') ? 'selected' : '' ?>>Software</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">วันที่ซื้อ</label>
                    <input type="date" name="purchase_date"
                        value="<?= isset($device) ? $device->purchase_date : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ประกันหมดวันที่</label>
                    <input type="date" name="warranty_end"
                        value="<?= isset($device) ? $device->warranty_end : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Partner ประจำ</label>
                <select name="partner_id"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— ไม่ระบุ —</option>
                    <?php foreach ($partners as $p): ?>
                        <option value="<?= $p->id ?>" <?= (isset($device) && $device->partner_id == $p->id) ? 'selected' : '' ?>>
                            <?= $p->company_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">หมายเหตุ</label>
                <textarea name="note" rows="2"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= isset($device) ? $device->note : '' ?></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                    <?= isset($device) ? 'บันทึก' : 'เพิ่มอุปกรณ์' ?>
                </button>
                <a href="<?= base_url('admin/devices') ?>"
                    class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('#equipment-select').select2({
        placeholder: '— เลือกหรือพิมพ์ค้นหา —',
        width: '100%'
    }).on('change', function() {
        const selected = $(this).find(':selected');
        $('#device-name-hidden').val(selected.data('name') || '');
        // auto fill device_type
        const type = selected.data('type') || '';
        $('select[name="device_type"]').val(type);
        // auto fill partner
        const partner = selected.data('partner') || '';
        $('select[name="partner_id"]').val(partner);
    });

    // init ถ้ามีค่าอยู่แล้ว (กรณี edit)
    $('#equipment-select').trigger('change');

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
</script>