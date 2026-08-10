<?php $page_title = 'โปรไฟล์ของฉัน'; ?>

<div class="p-6 max-w-lg">
    <h1 class="text-xl font-semibold text-slate-800 mb-6">โปรไฟล์ของฉัน</h1>

    <div class="bg-white border border-slate-200 rounded-xl p-6">

        <!-- Avatar -->
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
            <?php
            $avatar_url = null;
            if ($current_user['role'] === 'technician' && isset($tech) && $tech->avatar) {
                $avatar_url = base_url('uploads/avatars/technicians/' . $tech->avatar);
            } elseif ($current_user['role'] === 'partner' && isset($partner) && $partner->logo) {
                $avatar_url = base_url('uploads/avatars/partners/' . $partner->logo);
            }
            ?>
            <?php if ($avatar_url): ?>
                <img src="<?= $avatar_url ?>" class="w-16 h-16 rounded-full object-cover border border-slate-200">
            <?php else: ?>
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-semibold
                <?= $current_user['role'] === 'admin' ? 'bg-blue-600' : ($current_user['role'] === 'technician' ? 'bg-sky-600' : 'bg-purple-600') ?>">
                    <?= mb_substr($current_user['name'], 0, 1) ?>
                </div>
            <?php endif; ?>
            <div>
                <p class="font-semibold text-slate-800"><?= $current_user['name'] ?></p>
                <p class="text-sm text-slate-400"><?= $user->username ?></p>
                <span class="text-xs px-2 py-0.5 rounded-full
                    <?= $current_user['role'] === 'admin' ? 'bg-blue-100 text-blue-700' : ($current_user['role'] === 'technician' ? 'bg-sky-100 text-sky-700' : 'bg-purple-100 text-purple-700') ?>">
                    <?= $current_user['role'] === 'admin' ? 'Administrator' : ($current_user['role'] === 'technician' ? 'ช่างเทคนิค' : 'Partner') ?>
                </span>
                <?php if ($current_user['role'] !== 'admin'): ?>
                    <div class="mt-2">
                        <label class="block text-xs text-slate-500 mb-1">
                            <?= $current_user['role'] === 'technician' ? 'เปลี่ยนรูปโปรไฟล์' : 'เปลี่ยนโลโก้' ?>
                        </label>
                        <input type="file" name="<?= $current_user['role'] === 'technician' ? 'avatar' : 'logo' ?>"
                            accept="image/jpeg,image/png,image/webp"
                            class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" action="<?= base_url($current_user['role'] === 'admin' ? 'admin/profile/update' : ($current_user['role'] === 'technician' ? 'tech/profile/update' : 'partner/profile/update')) ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <!-- ข้อมูลทั่วไป -->
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">ข้อมูลทั่วไป</p>

            <?php if ($current_user['role'] === 'partner'): ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อบริษัท</label>
                    <input type="text" name="company_name" required
                        value="<?= isset($partner) ? $partner->company_name : $current_user['name'] ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อผู้ติดต่อ</label>
                    <input type="text" name="contact_name"
                        value="<?= isset($partner) ? $partner->contact_name : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" required
                        value="<?= $current_user['name'] ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>

            <?php if ($current_user['role'] === 'technician'): ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">เบอร์โทร</label>
                    <input type="text" name="phone"
                        value="<?= isset($tech) ? $tech->phone : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>

            <?php if ($current_user['role'] === 'partner'): ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">เบอร์โทร</label>
                    <input type="text" name="phone"
                        value="<?= isset($partner) ? $partner->phone : '' ?>"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                <input type="text" name="username" required
                    value="<?= $user->username ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-slate-400 mt-1">Username ต้องไม่ซ้ำกับผู้ใช้คนอื่น</p>
            </div>

            <hr class="my-5 border-slate-100">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">เปลี่ยนรหัสผ่าน</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    รหัสผ่านใหม่ <span class="text-slate-400 font-normal text-xs">(เว้นว่างถ้าไม่เปลี่ยน)</span>
                </label>
                <input type="password" name="password" minlength="6"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="อย่างน้อย 6 ตัวอักษร">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" name="password_confirm" minlength="6"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="พิมพ์รหัสผ่านอีกครั้ง">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm py-2.5 rounded-lg">
                บันทึกข้อมูล
            </button>
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