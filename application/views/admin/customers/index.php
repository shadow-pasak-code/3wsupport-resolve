<?php $page_title = 'จัดการลูกค้าและอุปกรณ์'; ?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-slate-800">จัดการลูกค้าและอุปกรณ์</h1>
        <a href="<?= base_url('admin/customers/add') ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">
            + เพิ่มลูกค้า
        </a>
    </div>

    <!-- Search + Filter -->
    <div class="flex gap-3 mb-5 flex-wrap">
        <input type="text" id="search-input" placeholder="🔍 ค้นหาชื่อ เบอร์ อีเมล S/N..."
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select id="filter-partner"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุก Partner</option>
            <?php foreach ($partners as $p): ?>
                <option value="<?= $p->id ?>"><?= $p->company_name ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filter-line"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกสถานะ Line</option>
            <option value="linked">ผูก Line แล้ว</option>
            <option value="unlinked">ยังไม่ผูก</option>
        </select>

        <select id="filter-warranty"
            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">ทุกสถานะประกัน</option>
            <option value="valid">มีประกันอยู่</option>
            <option value="expired">หมดประกัน</option>
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

    <!-- List -->
    <div id="customer-list" class="space-y-5"></div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-5">
        <p class="text-xs text-slate-400" id="page-info"></p>
        <div class="flex gap-2" id="pagination-btns"></div>
    </div>
</div>

<!-- Modal อุปกรณ์ -->
<div id="device-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl w-full max-w-2xl shadow-xl max-h-[80vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="font-semibold text-slate-800" id="modal-customer-name"></h3>
                <p class="text-xs text-slate-400 mt-0.5" id="modal-customer-info"></p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">✕</button>
        </div>
        <div class="overflow-y-auto flex-1 p-6">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left">อุปกรณ์</th>
                        <th class="px-4 py-2.5 text-left">Serial Number</th>
                        <th class="px-4 py-2.5 text-left">ประเภท</th>
                        <th class="px-4 py-2.5 text-left">วันที่ซื้อ</th>
                        <th class="px-4 py-2.5 text-left">ประกันหมด</th>
                        <th class="px-4 py-2.5 text-left">Partner ประจำ</th>
                    </tr>
                </thead>
                <tbody id="modal-devices" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center">
            <a id="modal-edit-link" href="#" class="text-sm text-blue-600 hover:underline">✏️ แก้ไขข้อมูลลูกค้า</a>
            <button onclick="closeModal()"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm px-4 py-2 rounded-lg">ปิด</button>
        </div>
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

    const ALL_CUSTOMERS = <?= json_encode(array_values($customers), JSON_UNESCAPED_UNICODE) ?>;
    const TODAY = new Date().toISOString().slice(0, 10);

    // สร้าง flat list ของ device rows สำหรับกรอง
    // แต่ละแถวมีข้อมูลทั้ง customer และ device
    let allRows = [];
    ALL_CUSTOMERS.forEach(c => {
        if ((c.devices || []).length === 0) {
            allRows.push({
                customer: c,
                device: null,
                partner_id: null,
                partner_name: 'ไม่มีอุปกรณ์'
            });
        } else {
            c.devices.forEach(d => {
                allRows.push({
                    customer: c,
                    device: d,
                    partner_id: d.partner_id,
                    partner_name: d.partner_name || 'ไม่ระบุ Partner'
                });
            });
        }
    });

    let filtered = [...allRows];
    let currentPage = 1;

    function getPerPage() {
        return parseInt(document.getElementById('per-page').value);
    }

    function groupByPartner(rows) {
        const groups = {};
        const order = [];
        rows.forEach(r => {
            const key = r.partner_name;
            if (!groups[key]) {
                groups[key] = [];
                order.push(key);
            }
            groups[key].push(r);
        });
        return {
            groups,
            order
        };
    }

    function render() {
        const perPage = getPerPage();
        const total = filtered.length;
        const totalPages = Math.ceil(total / perPage) || 1;
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const items = filtered.slice(start, start + perPage);

        document.getElementById('summary-text').textContent =
            `แสดง ${total === 0 ? 0 : start + 1}–${Math.min(start + perPage, total)} จาก ${total} รายการ`;

        const {
            groups,
            order
        } = groupByPartner(items);
        const list = document.getElementById('customer-list');

        if (items.length === 0) {
            list.innerHTML = `<div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-slate-400">ไม่พบข้อมูล</div>`;
        } else {
            list.innerHTML = order.map(partnerName => {
                const rows = groups[partnerName];

                // group customer ใน partner นี้
                const customerMap = {};
                const customerOrder = [];
                rows.forEach(r => {
                    const cid = r.customer.id;
                    if (!customerMap[cid]) {
                        customerMap[cid] = {
                            customer: r.customer,
                            devices: []
                        };
                        customerOrder.push(cid);
                    }
                    if (r.device) customerMap[cid].devices.push(r.device);
                });

                const customerRows = customerOrder.map(cid => {
                    const {
                        customer: c,
                        devices
                    } = customerMap[cid];
                    const lineStatus = c.line_uid ?
                        `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">✅ ผูก Line</span>` :
                        `<span class="px-2 py-0.5 bg-slate-100 text-slate-400 rounded-full text-xs">ยังไม่ผูก</span>`;

                    const deviceRows = devices.map(d => {
                        const inW = d.warranty_end && d.warranty_end >= TODAY;
                        const wBadge = inW ?
                            `<span class="text-green-600 text-xs">ในประกัน</span>` :
                            `<span class="text-red-400 text-xs">หมดประกัน</span>`;
                        const typeIcon = d.device_type === 'hardware' ? '🖨️' : '💿';
                        const typeColor = d.device_type === 'hardware' ?
                            'bg-orange-50 text-orange-700' :
                            'bg-sky-50 text-sky-700';

                        return `<div class="flex items-center gap-2 py-1.5 border-b border-slate-50 last:border-0">
                        <span class="text-base">${typeIcon}</span>
                        <span class="font-medium text-slate-700 text-xs">${d.name}</span>
                        <span class="font-mono text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">${d.serial_number}</span>
                        ${wBadge}
                    </div>`;
                    }).join('');

                    const deviceBtn = devices.length > 0 ?
                        `<button onclick='showDevices(${JSON.stringify(c).replace(/'/g, "&#39;")})'
                          class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-600 px-2.5 py-1.5 rounded-lg whitespace-nowrap">
                          📋 รายละเอียด
                       </button>` :
                        '';

                    return `<div class="flex items-start gap-4 py-3 border-b border-slate-100 last:border-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-medium text-slate-800 text-sm">${c.company_name || c.name || ''}</p>

                            ${lineStatus}
                        </div>
                        <div class="flex gap-3 text-xs text-slate-400 mb-2">
                            ${c.phone ? `<span>📞 ${c.phone}</span>` : ''}
                            ${c.email ? `<span>✉️ ${c.email}</span>` : ''}
                        </div>
                        <div class="pl-1">${deviceRows || '<p class="text-xs text-slate-400">ไม่มีอุปกรณ์</p>'}</div>
                    </div>
                    <div class="shrink-0 flex flex-col gap-1.5 items-end">
                        ${deviceBtn}
                        <a href="<?= base_url('admin/customers/edit/') ?>${c.id}"
                           class="text-xs text-blue-600 hover:underline">แก้ไข</a>
                        <button onclick="confirmDelete(${c.id}, '${(c.company_name || c.name || '').replace(/'/g, "\\'")}')"
                           class="text-xs text-red-500 hover:underline">ลบ</button>
                    </div>
                </div>`;
                }).join('');

                // หา partner color
                const partnerColors = [
                    'border-blue-400 bg-blue-50',
                    'border-purple-400 bg-purple-50',
                    'border-emerald-400 bg-emerald-50',
                    'border-orange-400 bg-orange-50',
                ];
                const colorIdx = order.indexOf(partnerName) % partnerColors.length;

                return `<div class="bg-white border-2 ${partnerColors[colorIdx]} rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-current/20 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏢</span>
                        <span class="font-semibold text-slate-700">${partnerName}</span>
                        <span class="text-xs text-slate-400">(${Object.keys(customerMap).length} ลูกค้า · ${rows.filter(r=>r.device).length} อุปกรณ์)</span>
                    </div>
                </div>
                <div class="px-5">${customerRows}</div>
            </div>`;
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
        const partner = document.getElementById('filter-partner').value;
        const line = document.getElementById('filter-line').value;
        const warranty = document.getElementById('filter-warranty').value;

        filtered = allRows.filter(r => {
            const c = r.customer;
            const d = r.device;

            // search ชื่อ เบอร์ อีเมล S/N
            if (search) {
                const hay = `${c.company_name || c.name || ''} ${c.phone||''} ${c.email||''} ${d ? d.serial_number : ''} ${d ? d.name : ''}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }

            // filter partner
            if (partner && String(r.partner_id) !== partner) return false;

            // filter line
            if (line === 'linked' && !c.line_uid) return false;
            if (line === 'unlinked' && c.line_uid) return false;

            // filter warranty
            if (warranty && d) {
                const inW = d.warranty_end && d.warranty_end >= TODAY;
                if (warranty === 'valid' && !inW) return false;
                if (warranty === 'expired' && inW) return false;
            }

            return true;
        });

        currentPage = 1;
        render();
    }

    function resetFilter() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-partner').value = '';
        document.getElementById('filter-line').value = '';
        document.getElementById('filter-warranty').value = '';
        document.getElementById('per-page').value = '10';
        filtered = [...allRows];
        currentPage = 1;
        render();
    }

    document.getElementById('search-input').addEventListener('input', applyFilter);
    document.getElementById('filter-partner').addEventListener('change', applyFilter);
    document.getElementById('filter-line').addEventListener('change', applyFilter);
    document.getElementById('filter-warranty').addEventListener('change', applyFilter);
    document.getElementById('per-page').addEventListener('change', () => {
        currentPage = 1;
        render();
    });

    render();

    function showDevices(customer) {
        document.getElementById('modal-customer-name').textContent = customer.company_name || customer.name || '';
    document.getElementById('modal-customer-info').textContent =
        (customer.phone || '') + (customer.email ? ' · ' + customer.email : '');
        document.getElementById('modal-customer-info').textContent =
            (customer.phone || '') + (customer.email ? ' · ' + customer.email : '');
        document.getElementById('modal-edit-link').href =
            '<?= base_url("admin/customers/edit/") ?>' + customer.id;

        const tbody = document.getElementById('modal-devices');
        tbody.innerHTML = '';

        (customer.devices || []).forEach(d => {
            const inW = d.warranty_end && d.warranty_end >= TODAY;
            const wClass = inW ? 'text-green-600' : 'text-red-500';
            const wLabel = inW ? 'ในประกัน' : 'หมดประกัน';
            const wDate = d.warranty_end ?
                ` (${new Date(d.warranty_end).toLocaleDateString('th-TH')})` :
                '';
            const bDate = d.purchase_date ?
                new Date(d.purchase_date).toLocaleDateString('th-TH') : '—';
            const typeBadge = d.device_type === 'hardware' ?
                `<span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs">Hardware</span>` :
                `<span class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs">Software</span>`;

            tbody.innerHTML += `
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium">${d.name}</td>
                <td class="px-4 py-3 font-mono text-xs text-slate-500">${d.serial_number}</td>
                <td class="px-4 py-3">${typeBadge}</td>
                <td class="px-4 py-3 text-xs text-slate-500">${bDate}</td>
                <td class="px-4 py-3 text-xs ${wClass}">${wLabel}${wDate}</td>
                <td class="px-4 py-3 text-xs text-slate-500">${d.partner_name || '—'}</td>
            </tr>`;
        });

        document.getElementById('device-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('device-modal').classList.add('hidden');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'ลบลูกค้า?',
            text: name + ' และอุปกรณ์ทั้งหมดจะถูกลบออกจากระบบ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url("admin/customers/delete/") ?>' + id;
            }
        });
    }
</script>