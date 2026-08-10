<?php $page_title = isset($equipment) ? 'แก้ไขอุปกรณ์' : 'เพิ่มอุปกรณ์'; ?>

<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/equipment') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST" enctype="multipart/form-data"
            action="<?= isset($equipment) ? base_url('admin/equipment/edit/' . $equipment->id) : base_url('admin/equipment/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <!-- รูปภาพ -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">รูปอุปกรณ์</label>
                <?php if (isset($equipment) && $equipment->image): ?>
                    <div class="mb-2">
                        <img src="<?= image_url($equipment->image, 'uploads/equipment/') ?>"
                            class="w-24 h-24 rounded-lg object-cover border border-slate-200">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP ขนาดไม่เกิน 2MB</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่ออุปกรณ์ <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                    value="<?= isset($equipment) ? $equipment->name : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="เช่น Epson L3210 เครื่องพิมพ์">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">แบรนด์</label>
                    <input type="text" name="brand"
                        value="<?= isset($equipment) ? $equipment->brand : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="เช่น Epson, HP, Adobe">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">รุ่น</label>
                    <input type="text" name="model"
                        value="<?= isset($equipment) ? $equipment->model : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="เช่น L3210, M404n">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ประเภท</label>
                <select name="device_type" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="hardware" <?= (isset($equipment) && $equipment->device_type === 'hardware') ? 'selected' : '' ?>>Hardware</option>
                    <option value="software" <?= (isset($equipment) && $equipment->device_type === 'software') ? 'selected' : '' ?>>Software</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Partner ประจำ</label>
                <select name="partner_id"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— ไม่ระบุ —</option>
                    <?php foreach ($partners as $p): ?>
                        <option value="<?= $p->id ?>" <?= (isset($equipment) && $equipment->partner_id == $p->id) ? 'selected' : '' ?>>
                            <?= $p->company_name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">รายละเอียด</label>
                <textarea name="description" rows="3"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="รายละเอียดเพิ่มเติม..."><?= isset($equipment) ? $equipment->description : '' ?></textarea>
            </div>

            <?php if (isset($equipment)): ?>
                <div class="mb-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            <?= $equipment->is_active ? 'checked' : '' ?>
                            class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-slate-700">เปิดใช้งาน</span>
                    </label>
                </div>
            <?php endif; ?>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                    <?= isset($equipment) ? 'บันทึก' : 'เพิ่มอุปกรณ์' ?>
                </button>
                <a href="<?= base_url('admin/equipment') ?>"
                    class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

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
</script>