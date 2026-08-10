<?php $page_title = 'จัดการอุปกรณ์ในร้าน'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">อุปกรณ์ / ซอฟต์แวร์ในร้าน</h1>
        <a href="<?= base_url('admin/equipment/add') ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่มอุปกรณ์
        </a>
    </div>

    <!-- Search + Filter -->
    <div class="flex gap-3 mb-5 flex-wrap">
        <input type="text" id="search-input" placeholder="🔍 ค้นหาชื่อ แบรนด์ รุ่น..."
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select id="filter-type"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกประเภท</option>
            <option value="hardware">Hardware</option>
            <option value="software">Software</option>
        </select>

        <select id="filter-partner"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุก Partner</option>
            <?php foreach ($partners as $p): ?>
                <option value="<?= $p->id ?>"><?= $p->company_name ?></option>
            <?php endforeach; ?>
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
                    <th class="px-5 py-3 text-left">รูป</th>
                    <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" onclick="sortBy('name')">
                        ชื่ออุปกรณ์ <span id="sort-name"></span>
                    </th>
                    <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" onclick="sortBy('brand')">
                        แบรนด์ <span id="sort-brand"></span>
                    </th>
                    <th class="px-5 py-3 text-left">รุ่น</th>
                    <th class="px-5 py-3 text-left">ประเภท</th>
                    <th class="px-5 py-3 text-left">Partner ประจำ</th>
                    <th class="px-5 py-3 text-left">สถานะ</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="equipment-list" class="divide-y divide-slate-100">
            </tbody>
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
    <?php if ($this->session->flashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: '<?= $this->session->flashdata("error") ?>'
        });
    <?php endif; ?>

    const ALL_EQUIPMENT = <?= json_encode(array_values($equipment), JSON_UNESCAPED_UNICODE) ?>;
    let filtered = [...ALL_EQUIPMENT];
    let currentPage = 1;
    let sortKey = 'name';
    let sortDir = 'asc';

    function getPerPage() {
        return parseInt(document.getElementById('per-page').value);
    }

    function render() {
        const perPage = getPerPage();
        const total = filtered.length;
        const totalPages = Math.ceil(total / perPage) || 1;
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const items = filtered.slice(start, start + perPage);

        document.getElementById('summary-text').textContent =
            `แสดง ${start + 1}–${Math.min(start + perPage, total)} จาก ${total} รายการ`;

        const tbody = document.getElementById('equipment-list');
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">ไม่พบอุปกรณ์</td></tr>`;
        } else {
            tbody.innerHTML = items.map(eq => {
                const typeBadge = eq.device_type === 'hardware' ?
                    `<span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>` :
                    `<span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>`;
                const statusBadge = parseInt(eq.is_active) ?
                    `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">ใช้งาน</span>` :
                    `<span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">ปิด</span>`;
                const img = eq.image ?
                    `<img src="<?= base_url('uploads/equipment/') ?>${eq.image}" class="w-10 h-10 rounded-lg object-cover bg-slate-100">` :
                    `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 text-lg">📦</div>`;

                return `<tr class="hover:bg-slate-50">
                <td class="px-5 py-3">${img}</td>
                <td class="px-5 py-3 font-medium">${eq.name}</td>
                <td class="px-5 py-3 text-slate-600">${eq.brand || '—'}</td>
                <td class="px-5 py-3 text-slate-600">${eq.model || '—'}</td>
                <td class="px-5 py-3">${typeBadge}</td>
                <td class="px-5 py-3 text-slate-500 text-xs">${eq.partner_name || '—'}</td>
                <td class="px-5 py-3">${statusBadge}</td>
                <td class="px-5 py-3 flex gap-3">
                    <a href="<?= base_url('admin/equipment/edit/') ?>${eq.id}"
                       class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                    <button onclick="confirmDelete(${eq.id}, '${eq.name.replace(/'/g, "\\'")}')"
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
        const type = document.getElementById('filter-type').value;
        const partner = document.getElementById('filter-partner').value;
        const status = document.getElementById('filter-status').value;

        filtered = ALL_EQUIPMENT.filter(eq => {
            if (search) {
                const hay = `${eq.name} ${eq.brand || ''} ${eq.model || ''}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }
            if (type && eq.device_type !== type) return false;
            if (partner && String(eq.partner_id) !== partner) return false;
            if (status !== '' && String(eq.is_active) !== status) return false;
            return true;
        });

        // sort
        filtered.sort((a, b) => {
            const va = (a[sortKey] || '').toLowerCase();
            const vb = (b[sortKey] || '').toLowerCase();
            return sortDir === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });

        currentPage = 1;
        render();
    }

    function sortBy(key) {
        if (sortKey === key) {
            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey = key;
            sortDir = 'asc';
        }
        document.getElementById('sort-name').textContent = sortKey === 'name' ? (sortDir === 'asc' ? '▲' : '▼') : '';
        document.getElementById('sort-brand').textContent = sortKey === 'brand' ? (sortDir === 'asc' ? '▲' : '▼') : '';
        applyFilter();
    }

    function resetFilter() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-partner').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('per-page').value = '10';
        sortKey = 'name';
        sortDir = 'asc';
        filtered = [...ALL_EQUIPMENT];
        currentPage = 1;
        render();
    }

    document.getElementById('search-input').addEventListener('input', applyFilter);
    document.getElementById('filter-type').addEventListener('change', applyFilter);
    document.getElementById('filter-partner').addEventListener('change', applyFilter);
    document.getElementById('filter-status').addEventListener('change', applyFilter);
    document.getElementById('per-page').addEventListener('change', () => {
        currentPage = 1;
        render();
    });

    render();

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
                window.location.href = '<?= base_url("admin/equipment/delete/") ?>' + id;
            }
        });
    }
</script>