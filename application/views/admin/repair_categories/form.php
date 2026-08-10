<?php $page_title = isset($category) ? 'แก้ไขหมวดหมู่การซ่อม' : 'เพิ่มหมวดหมู่การซ่อม'; ?>

<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/repair_categories') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST"
            action="<?= isset($category) ? base_url('admin/repair_categories/edit/' . $category->id) : base_url('admin/repair_categories/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อหมวดหมู่ <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                    value="<?= isset($category) ? $category->name : '' ?>"
                    placeholder="เช่น เปลี่ยนตลับหมึก / เปลี่ยน ROM"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ระยะเวลาซ่อมสูงสุด (วัน) <span class="text-red-500">*</span></label>
                <input type="number" name="max_days" required min="1"
                    value="<?= isset($category) ? $category->max_days : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-slate-400 mt-1">ช่างเลือกหมวดหมู่นี้ตอนรับงาน ระบบจะคำนวณวันครบกำหนดให้อัตโนมัติจากจำนวนวันนี้</p>
            </div>

            <?php if (isset($category)): ?>
                <div class="mb-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= $category->is_active ? 'checked' : '' ?>
                            class="w-4 h-4 text-blue-600 rounded">
                        <span class="text-sm text-slate-700">เปิดใช้งานหมวดหมู่นี้</span>
                    </label>
                </div>
            <?php endif; ?>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                    <?= isset($category) ? 'บันทึก' : 'เพิ่มหมวดหมู่' ?>
                </button>
                <a href="<?= base_url('admin/repair_categories') ?>"
                    class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if ($this->session->flashdata('success')): ?>
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= addslashes($this->session->flashdata("success")) ?>', timer: 2000, showConfirmButton: false });
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= addslashes($this->session->flashdata("error")) ?>' });
    <?php endif; ?>
</script>
