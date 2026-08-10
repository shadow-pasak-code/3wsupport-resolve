<?php $page_title = 'จัดการช่าง'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">จัดการช่างเทคนิค</h1>
        <a href="<?= base_url('admin/technicians/add') ?>"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่มช่าง
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">รูป</th>
                    <th class="px-5 py-3 text-left">ชื่อ</th>
                    <th class="px-5 py-3 text-left">เบอร์โทร</th>
                    <th class="px-5 py-3 text-left">อีเมล</th>
                    <th class="px-5 py-3 text-left">Username</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($technicians)): ?>
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">ยังไม่มีช่าง</td></tr>
            <?php else: ?>
                <?php foreach ($technicians as $t): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <img src="<?= image_url($t->avatar, 'uploads/avatars/technicians/') ?>"
                             class="w-10 h-10 rounded-full object-cover bg-slate-100">
                    </td>
                    <td class="px-5 py-3 font-medium"><?= $t->name ?></td>
                    <td class="px-5 py-3 text-slate-600"><?= $t->phone ?? '—' ?></td>
                    <td class="px-5 py-3 text-slate-600"><?= $t->email ?? '—' ?></td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-500"><?= $t->username ?? '—' ?></td>
                    <td class="px-5 py-3">
                        <?php if ($t->is_active): ?>
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">ใช้งาน</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">ปิด</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 flex gap-3">
                        <a href="<?= base_url('admin/technicians/edit/' . $t->id) ?>"
                           class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                        <button onclick="confirmDelete(<?= $t->id ?>, '<?= addslashes($t->name) ?>')"
                           class="text-xs text-red-500 hover:underline">ลบ</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if ($this->session->flashdata('success')): ?>
Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $this->session->flashdata("success") ?>', timer: 2000, showConfirmButton: false });
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= $this->session->flashdata("error") ?>' });
<?php endif; ?>

function confirmDelete(id, name) {
    Swal.fire({
        title: 'ลบช่าง?',
        text: name + ' จะถูกลบออกจากระบบ',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url("admin/technicians/delete/") ?>' + id;
        }
    });
}
</script>