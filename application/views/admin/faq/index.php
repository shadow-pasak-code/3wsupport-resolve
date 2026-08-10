<?php $page_title = 'จัดการ FAQ (Chatbot)'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">จัดการ FAQ (Chatbot)</h1>
        <a href="<?= base_url('admin/faq/add') ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่ม FAQ
        </a>
    </div>

    <!-- Search + Filter -->
    <div class="flex gap-3 mb-5 flex-wrap">
        <input type="text" id="search-input" placeholder="🔍 ค้นหาคำถาม คำตอบ keyword..."
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 w-72 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select id="filter-category"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกหมวดหมู่</option>
            <option value="hardware">Hardware</option>
            <option value="software">Software</option>
            <option value="general">ทั่วไป</option>
        </select>

        <select id="filter-status"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกสถานะ</option>
            <option value="1">ใช้งาน</option>
            <option value="0">ปิด</option>
        </select>

        <select id="per-page"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="10">10 / หน้า</option>
            <option value="20">20 / หน้า</option>
            <option value="50">50 / หน้า</option>
        </select>

        <button onclick="resetFilter()"
            class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">ล้าง</button>
    </div>

    <p class="text-xs text-slate-400 mb-3" id="summary-text"></p>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left w-24">หมวดหมู่</th>
                    <th class="px-5 py-3 text-left">คำถาม / คำตอบ</th>
                    <th class="px-5 py-3 text-left w-48">Keyword</th>
                    <th class="px-5 py-3 text-left w-28">สถานะ</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody id="faq-list" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-5">
        <p class="text-xs text-slate-400" id="page-info"></p>
        <div class="flex gap-2" id="pagination-btns"></div>
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

    const ALL_FAQS = <?= json_encode(array_values($faqs), JSON_UNESCAPED_UNICODE) ?>;
    let filtered = [...ALL_FAQS];
    let currentPage = 1;

    function getPerPage() {
        return parseInt(document.getElementById('per-page').value);
    }

    const CAT_MAP = {
        hardware: {
            label: 'Hardware',
            cls: 'bg-orange-100 text-orange-700'
        },
        software: {
            label: 'Software',
            cls: 'bg-sky-100 text-sky-700'
        },
        general: {
            label: 'ทั่วไป',
            cls: 'bg-slate-100 text-slate-600'
        },
    };

    function render() {
        const perPage = getPerPage();
        const total = filtered.length;
        const totalPages = Math.ceil(total / perPage) || 1;
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const items = filtered.slice(start, start + perPage);

        document.getElementById('summary-text').textContent =
            `แสดง ${total === 0 ? 0 : start + 1}–${Math.min(start + perPage, total)} จาก ${total} รายการ`;

        const tbody = document.getElementById('faq-list');
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">ไม่พบ FAQ</td></tr>`;
        } else {
            tbody.innerHTML = items.map(f => {
                const cat = CAT_MAP[f.category] || {
                    label: f.category,
                    cls: 'bg-slate-100 text-slate-500'
                };
                const status = parseInt(f.is_active) ?
                    `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">ใช้งาน</span>` :
                    `<span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">ปิด</span>`;
                const keywords = (f.keyword || '').split(',').map(k => k.trim()).filter(Boolean)
                    .map(k => `<span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-xs">${k}</span>`)
                    .join(' ');
                const answer = f.answer.length > 80 ? f.answer.slice(0, 80) + '...' : f.answer;

                return `<tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded text-xs ${cat.cls}">${cat.label}</span>
                </td>
                <td class="px-5 py-3">
                    <p class="font-medium text-slate-800 mb-0.5">${f.question}</p>
                    <p class="text-xs text-slate-400 leading-relaxed">${answer}</p>
                </td>
                <td class="px-5 py-3">
                    <div class="flex flex-wrap gap-1">${keywords || '—'}</div>
                </td>
                <td class="px-5 py-3">${status}</td>
                <td class="px-5 py-3 flex gap-3">
                    <a href="<?= base_url('admin/faq/edit/') ?>${f.id}"
                       class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                    <button onclick="confirmDelete(${f.id}, '${f.question.replace(/'/g, "\\'")}')"
                       class="text-xs text-red-500 hover:underline">ลบ</button>
                </td>
            </tr>`;
            }).join('');
        }

        // pagination
        document.getElementById('page-info').textContent = `หน้า ${currentPage} / ${totalPages}`;
        const btns = document.getElementById('pagination-btns');
        btns.innerHTML = '';
        if (totalPages <= 1) return;

        const prev = document.createElement('button');
        prev.textContent = '← ก่อนหน้า';
        prev.className = `text-xs px-3 py-1.5 rounded-lg border ${currentPage === 1 ? 'text-slate-300 border-slate-200 cursor-not-allowed' : 'text-slate-600 border-slate-300 hover:bg-slate-50'}`;
        prev.disabled = currentPage === 1;
        prev.onclick = () => {
            currentPage--;
            render();
        };
        btns.appendChild(prev);

        for (let i = 1; i <= totalPages; i++) {
            if (totalPages > 7 && Math.abs(i - currentPage) > 2 && i !== 1 && i !== totalPages) {
                if (i === 2 || i === totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.className = 'text-xs px-2 py-1.5 text-slate-400';
                    btns.appendChild(dots);
                }
                continue;
            }
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `text-xs px-3 py-1.5 rounded-lg border ${i === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'text-slate-600 border-slate-300 hover:bg-slate-50'}`;
            btn.onclick = (page => () => {
                currentPage = page;
                render();
            })(i);
            btns.appendChild(btn);
        }

        const next = document.createElement('button');
        next.textContent = 'ถัดไป →';
        next.className = `text-xs px-3 py-1.5 rounded-lg border ${currentPage === totalPages ? 'text-slate-300 border-slate-200 cursor-not-allowed' : 'text-slate-600 border-slate-300 hover:bg-slate-50'}`;
        next.disabled = currentPage === totalPages;
        next.onclick = () => {
            currentPage++;
            render();
        };
        btns.appendChild(next);
    }

    function applyFilter() {
        const search = document.getElementById('search-input').value.toLowerCase();
        const category = document.getElementById('filter-category').value;
        const status = document.getElementById('filter-status').value;

        filtered = ALL_FAQS.filter(f => {
            if (search) {
                const hay = `${f.question} ${f.answer} ${f.keyword || ''}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }
            if (category && f.category !== category) return false;
            if (status !== '' && String(f.is_active) !== status) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function resetFilter() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('per-page').value = '10';
        filtered = [...ALL_FAQS];
        currentPage = 1;
        render();
    }

    document.getElementById('search-input').addEventListener('input', applyFilter);
    document.getElementById('filter-category').addEventListener('change', applyFilter);
    document.getElementById('filter-status').addEventListener('change', applyFilter);
    document.getElementById('per-page').addEventListener('change', () => {
        currentPage = 1;
        render();
    });

    render();

    function confirmDelete(id, question) {
        Swal.fire({
            title: 'ลบ FAQ?',
            text: question,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url("admin/faq/delete/") ?>' + id;
            }
        });
    }
</script>