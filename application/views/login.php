<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ — After-Sales Service</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center">

<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">

        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-slate-800">After-Sales Service</h1>
            <p class="text-sm text-slate-500 mt-1">เข้าสู่ระบบเพื่อดำเนินการต่อ</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-5">
            <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('login') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อผู้ใช้</label>
                <input type="text" name="username" required autofocus
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           placeholder:text-slate-400"
                    placeholder="กรอกชื่อผู้ใช้">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">รหัสผ่าน</label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           placeholder:text-slate-400"
                    placeholder="กรอกรหัสผ่าน">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                       py-2.5 rounded-lg transition-colors">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>
    <p class="text-center text-xs text-slate-400 mt-4">After-Sales Service System</p>
</div>

</body>
</html>
