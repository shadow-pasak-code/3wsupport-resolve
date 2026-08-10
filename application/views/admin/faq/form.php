<?php $page_title = isset($faq) ? 'แก้ไข FAQ' : 'เพิ่ม FAQ'; ?>

<div class="p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= base_url('admin/faq') ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold"><?= $page_title ?></h1>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <form method="POST" action="<?= isset($faq) ? base_url('admin/faq/edit/' . $faq->id) : base_url('admin/faq/add') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">หมวดหมู่</label>
                <select name="category" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="hardware" <?= (isset($faq) && $faq->category === 'hardware') ? 'selected' : '' ?>>Hardware</option>
                    <option value="software" <?= (isset($faq) && $faq->category === 'software') ? 'selected' : '' ?>>Software</option>
                    <option value="general"  <?= (isset($faq) && $faq->category === 'general')  ? 'selected' : '' ?>>ทั่วไป</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Keyword <span class="text-slate-400 font-normal text-xs">(คั่นด้วยจุลภาค เช่น จอดับ,เปิดไม่ติด)</span>
                </label>
                <input type="text" name="keyword" required
                    value="<?= isset($faq) ? $faq->keyword : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="จอดับ,เปิดไม่ติด,หน้าจอ">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">คำถาม</label>
                <input type="text" name="question" required
                    value="<?= isset($faq) ? $faq->question : '' ?>"
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="จอดับเปิดไม่ติดทำอย่างไร">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">คำตอบ</label>
                <textarea name="answer" rows="5" required
                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="วิธีแก้ไขเบื้องต้น..."><?= isset($faq) ? $faq->answer : '' ?></textarea>
            </div>

            <?php if (isset($faq)): ?>
            <div class="mb-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        <?= $faq->is_active ? 'checked' : '' ?>
                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="text-sm text-slate-700">เปิดใช้งาน FAQ นี้</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="flex gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-6 py-2.5 rounded-lg transition-colors">
                    <?= isset($faq) ? 'บันทึกการแก้ไข' : 'เพิ่ม FAQ' ?>
                </button>
                <a href="<?= base_url('admin/faq') ?>"
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
Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= $this->session->flashdata("error") ?>' });
<?php endif; ?>
</script>