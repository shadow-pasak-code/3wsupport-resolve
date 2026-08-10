<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' — ' : '' ?>Partner Portal</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
<script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Sarabun', 'sans-serif'] } } } }</script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

<div class="flex h-screen overflow-hidden">
    <aside class="w-56 bg-slate-900 flex flex-col shrink-0">
        <div class="px-5 py-5 border-b border-slate-700">
            <span class="text-white font-semibold text-base">After-Sales</span>
            <span class="ml-2 text-xs bg-purple-600 text-white px-2 py-0.5 rounded-full">Partner</span>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <?php
            $uri = uri_string();
            $active = strpos($uri, 'partner/tickets') !== false;
            $cls = $active ? 'bg-purple-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white';
            echo "<a href='" . base_url('partner/tickets') . "' class='flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm $cls'>
                    <svg class='w-4 h-4 shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'/>
                    </svg>งานที่ได้รับ</a>";
            ?>
        </nav>
        <div class="px-3 py-4 border-t border-slate-700">
            <div class="px-3 mb-2">
                <p class="text-sm text-white"><?= $current_user['name'] ?? '' ?></p>
                <p class="text-xs text-slate-400">Partner</p>
            </div>
            <a href="<?= base_url('logout') ?>"
               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                ออกจากระบบ
            </a>
        </div>
    </aside>
    <main class="flex-1 overflow-y-auto">
        <?php $this->load->view($content_view, $this->load->get_vars()) ?>
    </main>
</div>
</body>
</html>
