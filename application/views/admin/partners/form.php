<?php $page_title = isset($partner) ? 'แก้ไข Partner' : 'เพิ่ม Partner'; ?>

<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/partners') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST" enctype="multipart/form-data"
              action="<?= isset($partner) ? base_url('admin/partners/edit/' . $partner->id) : base_url('admin/partners/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <!-- Logo -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">โลโก้บริษัท</label>
                <?php if (isset($partner) && $partner->logo): ?>
                <div class="mb-2">
                    <img src="<?= image_url($partner->logo, 'uploads/avatars/partners/') ?>"
                         class="w-20 h-20 rounded-lg object-cover border border-slate-200">
                </div>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, WEBP ขนาดไม่เกิน 2MB</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อบริษัท / ร้าน <span class="text-red-500">*</span></label>
                <input type="text" name="company_name" required
                    value="<?= isset($partner) ? $partner->company_name : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อผู้ติดต่อ</label>
                <input type="text" name="contact_name"
                    value="<?= isset($partner) ? $partner->contact_name : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">เบอร์โทร</label>
                <input type="text" name="phone"
                    value="<?= isset($partner) ? $partner->phone : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">อีเมล</label>
                <input type="email" name="email"
                    value="<?= isset($partner) ? $partner->email : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <hr class="my-5 border-slate-100">
            <p class="text-sm font-semibold text-slate-700 mb-4">ข้อมูล Login (Partner Portal)</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" <?= isset($partner) ? '' : 'required' ?>
                    value="<?= isset($partner) ? $partner->username : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Password <?= isset($partner) ? '<span class="text-slate-400 font-normal text-xs">(เว้นว่างถ้าไม่เปลี่ยน)</span>' : '<span class="text-red-500">*</span>' ?>
                </label>
                <input type="password" name="password" <?= isset($partner) ? '' : 'required' ?>
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <?php if (isset($partner)): ?>
            <div class="mb-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= $partner->is_active ? 'checked' : '' ?>
                        class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-sm text-slate-700">เปิดใช้งานบัญชีนี้</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg">
                    <?= isset($partner) ? 'บันทึก' : 'เพิ่ม Partner' ?>
                </button>
                <a href="<?= base_url('admin/partners') ?>"
                   class="text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if ($this->session->flashdata('success')): ?>
Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $this->session->flashdata("success") ?>', timer: 2000, showConfirmButton: false });
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= addslashes($this->session->flashdata("error")) ?>' });
<?php endif; ?>
</script>