<?php $page_title = 'หมวดหมู่การซ่อม'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">หมวดหมู่การซ่อม</h1>
            <p class="text-xs text-slate-400 mt-1">กำหนดระยะเวลาซ่อมสูงสุดต่อหมวดหมู่ — ช่างจะเลือกจากรายการนี้แทนการกำหนดวันเสร็จเอง</p>
        </div>
        <a href="<?= base_url('admin/repair_categories/add') ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่มหมวดหมู่
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">ชื่อหมวดหมู่</th>
                    <th class="px-5 py-3 text-left">คำสำคัญของอาการ</th>
                    <th class="px-5 py-3 text-left">ระยะเวลาสูงสุด</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400">ยังไม่มีหมวดหมู่การซ่อม</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $c): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800"><?= $c->name ?></td>
                            <td class="px-5 py-3 text-xs text-slate-500 max-w-xs">
                                <?php if ($c->keywords): ?>
                                    <?= $c->keywords ?>
                                <?php else: ?>
                                    <span class="italic text-slate-400">— สำรอง (โผล่เมื่อไม่มีหมวดหมู่ไหนตรงอาการ)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-slate-600">ไม่เกิน <?= $c->max_days ?> วัน</td>
                            <td class="px-5 py-3">
                                <?php if ($c->is_active): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">ปิด</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 flex gap-3">
                                <a href="<?= base_url('admin/repair_categories/edit/' . $c->id) ?>"
                                    class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                                <button onclick="confirmToggle(<?= $c->id ?>, '<?= addslashes($c->name) ?>', <?= $c->is_active ? 'true' : 'false' ?>)"
                                    class="text-xs <?= $c->is_active ? 'text-red-500' : 'text-green-600' ?> hover:underline">
                                    <?= $c->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>
                                </button>
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

    function confirmToggle(id, name, isActive) {
        Swal.fire({
            title: (isActive ? 'ปิดใช้งาน' : 'เปิดใช้งาน') + 'หมวดหมู่นี้?',
            text: name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isActive ? '#ef4444' : '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isActive ? 'ปิดใช้งาน' : 'เปิดใช้งาน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url("admin/repair_categories/toggle_active/") ?>' + id;
            }
        });
    }
</script>
