<?php $page_title = isset($tech) ? 'แก้ไขข้อมูลช่าง' : 'เพิ่มช่าง'; ?>

<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/technicians') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST" enctype="multipart/form-data"
              action="<?= isset($tech) ? base_url('admin/technicians/edit/' . $tech->id) : base_url('admin/technicians/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <!-- Avatar -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">รูปโปรไฟล์</label>
                <?php if (isset($tech) && $tech->avatar): ?>
                <div class="mb-2">
                    <img src="<?= image_url($tech->avatar, 'uploads/avatars/technicians/') ?>"
                         class="w-16 h-16 rounded-full object-cover border border-slate-200">
                </div>
                <?php endif; ?>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP ขนาดไม่เกิน 2MB</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                    value="<?= isset($tech) ? $tech->name : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">เบอร์โทร</label>
                <input type="text" name="phone"
                    value="<?= isset($tech) ? $tech->phone : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">อีเมล</label>
                <input type="email" name="email"
                    value="<?= isset($tech) ? $tech->email : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <hr class="my-5 border-slate-100">
            <p class="text-sm font-semibold text-slate-700 mb-4">ข้อมูล Login</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" <?= isset($tech) ? '' : 'required' ?>
                    value="<?= isset($tech) ? $tech->username : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Password <?= isset($tech) ? '<span class="text-slate-400 font-normal text-xs">(เว้นว่างถ้าไม่เปลี่ยน)</span>' : '<span class="text-red-500">*</span>' ?>
                </label>
                <input type="password" name="password" <?= isset($tech) ? '' : 'required' ?>
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <?php if (isset($tech)): ?>
            <div class="mb-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= $tech->is_active ? 'checked' : '' ?>
                        class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-slate-700">เปิดใช้งานบัญชีนี้</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                    <?= isset($tech) ? 'บันทึก' : 'เพิ่มช่าง' ?>
                </button>
                <a href="<?= base_url('admin/technicians') ?>"
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