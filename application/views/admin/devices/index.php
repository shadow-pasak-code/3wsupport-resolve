<?php $page_title = 'จัดการอุปกรณ์'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">จัดการอุปกรณ์</h1>
        <a href="<?= base_url('admin/devices/add') ?>"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่มอุปกรณ์
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">รูป</th>
                    <th class="px-5 py-3 text-left">Serial Number</th>
                    <th class="px-5 py-3 text-left">ชื่ออุปกรณ์</th>
                    <th class="px-5 py-3 text-left">ประเภท</th>
                    <th class="px-5 py-3 text-left">ลูกค้า</th>
                    <th class="px-5 py-3 text-left">Partner ประจำ</th>
                    <th class="px-5 py-3 text-left">ประกันหมด</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($devices)): ?>
                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">ยังไม่มีอุปกรณ์</td></tr>
            <?php else: ?>
                <?php foreach ($devices as $d): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <img src="<?= image_url($d->image, 'uploads/devices/') ?>"
                             class="w-10 h-10 rounded-lg object-cover bg-slate-100">
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-slate-600"><?= $d->serial_number ?></td>
                    <td class="px-5 py-3 font-medium"><?= $d->name ?></td>
                    <td class="px-5 py-3">
                        <?php if ($d->device_type === 'hardware'): ?>
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-slate-600"><?= $d->customer_name ?></td>
                    <td class="px-5 py-3 text-slate-500 text-xs"><?= $d->partner_name ?? '—' ?></td>
                    <td class="px-5 py-3 text-xs">
                        <?php if ($d->warranty_end): ?>
                            <?php $ok = $d->warranty_end >= date('Y-m-d'); ?>
                            <span class="<?= $ok ? 'text-green-600' : 'text-red-500' ?>">
                                <?= date('d/m/Y', strtotime($d->warranty_end)) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-slate-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 flex gap-3">
                        <a href="<?= base_url('admin/devices/edit/' . $d->id) ?>"
                           class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                        <button onclick="confirmDelete(<?= $d->id ?>, '<?= $d->name ?>')"
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
        title: 'ลบอุปกรณ์?',
        text: name + ' จะถูกลบออกจากระบบ',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url("admin/devices/delete/") ?>' + id;
        }
    });
}
</script>