<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' : '' ?>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 font-sans text-slate-800">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="w-60 bg-slate-900 flex flex-col shrink-0">
            <div class="px-5 py-5 border-b border-slate-700">
                <span class="text-white font-semibold text-base">After-Sales</span>
                <span class="ml-2 text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full">Admin</span>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <?php
                $uri  = uri_string();
                $role = $current_user['role'];

                function nav_item($href, $label, $icon, $uri)
                {
                    $active = (strpos($uri, $href) !== false);
                    $cls = $active
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white';
                    echo "<a href='" . base_url($href) . "' class='flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors $cls'>
                <span class='w-4 h-4 shrink-0'>$icon</span>$label</a>";
                }

                $ic_dash     = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
                $ic_ticket   = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>';
                $ic_device   = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>';
                $ic_tech     = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
                $ic_partner  = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';
                $ic_faq      = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                $ic_customer = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
                $ic_history  = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                if ($role === 'admin'):
                    nav_item('admin/dashboard',   'แดชบอร์ด',       $ic_dash,     $uri);
                    nav_item('admin/tickets',     'จัดการ Ticket',   $ic_ticket,   $uri);
                    $ic_history = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    nav_item('admin/history', 'ประวัติการซ่อม', $ic_history, $uri);
                    nav_item('admin/customers', 'จัดการลูกค้าและอุปกรณ์', $ic_customer, $uri);
                    $ic_equipment = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
                    nav_item('admin/equipment',  'จัดการอุปกรณ์ในร้าน', $ic_equipment, $uri);
                    // nav_item('admin/devices',    'อุปกรณ์ลูกค้า',        $ic_device,    $uri);
                    nav_item('admin/technicians', 'จัดการช่าง',      $ic_tech,     $uri);
                    $ic_category = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    nav_item('admin/repair_categories', 'หมวดหมู่การซ่อม', $ic_category, $uri);
                    nav_item('admin/partners',    'จัดการ Partner',  $ic_partner,  $uri);
                    nav_item('admin/faq',         'จัดการ FAQ',      $ic_faq,      $uri);

                elseif ($role === 'technician'):
                    nav_item('tech/tickets', 'งานของฉัน', $ic_ticket, $uri);
                    nav_item('tech/history', 'ประวัติงาน', $ic_history, $uri);

                elseif ($role === 'partner'):
                    nav_item('partner/tickets', 'งานที่ได้รับ', $ic_ticket, $uri);

                endif;
                ?>
            </nav>

            <div class="px-3 py-4 border-t border-slate-700">
                <div class="flex items-center gap-3 px-3 mb-2">
                    <?php
                    $avatar_url = null;
                    if ($role === 'technician') {
                        $tech_row = $this->db->get_where('technicians', ['id' => $current_user['ref_id']])->row();
                        if ($tech_row && $tech_row->avatar) {
                            $avatar_url = base_url('uploads/avatars/technicians/' . $tech_row->avatar);
                        }
                    } elseif ($role === 'partner') {
                        $partner_row = $this->db->get_where('partners', ['id' => $current_user['ref_id']])->row();
                        if ($partner_row && $partner_row->logo) {
                            $avatar_url = base_url('uploads/avatars/partners/' . $partner_row->logo);
                        }
                    }
                    ?>
                    <?php if ($avatar_url): ?>
                        <img src="<?= $avatar_url ?>" class="w-8 h-8 rounded-full object-cover border border-slate-600 shrink-0">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold shrink-0
            <?= $role === 'admin' ? 'bg-blue-600' : ($role === 'technician' ? 'bg-sky-600' : 'bg-purple-600') ?>">
                            <?= mb_substr($current_user['name'] ?? 'A', 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <p class="text-sm text-white truncate"><?= $current_user['name'] ?? '' ?></p>
                        <p class="text-xs text-slate-400">
                            <?= $role === 'admin' ? 'Administrator' : ($role === 'technician' ? 'ช่างเทคนิค' : 'Partner') ?>
                        </p>
                    </div>
                </div>
                <a href="<?= base_url($current_user['role'] === 'admin' ? 'admin/profile' : ($current_user['role'] === 'technician' ? 'tech/profile' : 'partner/profile')) ?>"
                    class="flex items-center gap-2 px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    โปรไฟล์
                </a>
                <a href="<?= base_url('logout') ?>"
                    class="flex items-center gap-2 px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    ออกจากระบบ
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto">
            <?php $this->load->view($content_view, $this->load->get_vars()) ?>
        </main>

    </div>
</body>

</html>