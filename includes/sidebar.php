<?php
/**
 * Renders the admin-shell sidebar. $active must match one of the 'key' values below.
 * Reused across every module page so navigation + styling stay in exactly one place.
 */
function render_sidebar(string $active): void {
    $role = current_user()['role'] ?? null;
    $groups = [
        [
            'label' => 'USER PORTALS',
            'owner' => null,
            'items' => [
                ['key' => 'public',          'label' => 'Public Gym Website',     'href' => base_url('public/index.php'), 'icon' => 'globe'],
                ['key' => 'member-portal',   'label' => 'Member Dashboard Portal', 'href' => base_url('modules/member-portal/index.php'), 'icon' => 'user'],
                ['key' => 'trainer-portal',  'label' => 'Trainer Dashboard Portal','href' => base_url('modules/trainer-portal/index.php'), 'icon' => 'badge'],
            ],
        ],
        [
            'label' => 'ADMIN MANAGEMENT',
            'owner' => null,
            'items' => [
                ['key' => 'dashboard', 'label' => '12. Dashboard & Reports', 'href' => base_url('modules/dashboard/index.php'), 'icon' => 'grid', 'owner' => 'Janith'],
            ],
        ],
        [
            'label' => 'MEMBERS & AUTH',
            'owner' => 'Sajatha',
            'items' => [
                ['key' => 'login',   'label' => '01. Login Screen',      'href' => base_url('auth/login.php'), 'icon' => 'login'],
                ['key' => 'members', 'label' => '02. Member Management', 'href' => base_url('modules/members/index.php'), 'icon' => 'users'],
            ],
        ],
        [
            'label' => 'STAFF & ATTENDANCE',
            'owner' => 'Hasith',
            'items' => [
                ['key' => 'employees',  'label' => '03. Employee Directory',      'href' => base_url('modules/employees/index.php'), 'icon' => 'id'],
                ['key' => 'attendance', 'label' => '04. Staff Attendance & Leave','href' => base_url('modules/attendance/index.php'), 'icon' => 'calendar'],
            ],
        ],
        [
            'label' => 'TRAINERS & CLASSES',
            'owner' => 'Senuka',
            'items' => [
                ['key' => 'trainers', 'label' => '05. Trainers & Schedule', 'href' => base_url('modules/trainers/index.php'), 'icon' => 'award'],
                ['key' => 'classes',  'label' => '06. Class / PT Booking',  'href' => base_url('modules/classes/index.php'), 'icon' => 'clock'],
            ],
        ],
        [
            'label' => 'PLANS & RENEWALS',
            'owner' => 'Methul',
            'items' => [
                ['key' => 'plans',         'label' => '07. Membership Plans',      'href' => base_url('modules/plans/index.php'), 'icon' => 'layers'],
                ['key' => 'subscriptions', 'label' => '08. Subscriptions & Renewal','href' => base_url('modules/subscriptions/index.php'), 'icon' => 'refresh'],
            ],
        ],
        [
            'label' => 'PAYMENTS',
            'owner' => 'Asiri',
            'items' => [
                ['key' => 'payment-submission',  'label' => '09. Payment Submission',   'href' => base_url('modules/payments/submit.php'), 'icon' => 'card'],
                ['key' => 'payment-verification', 'label' => '10. Payment Verification', 'href' => base_url('modules/payments/index.php'), 'icon' => 'doc'],
            ],
        ],
        [
            'label' => 'EQUIPMENT',
            'owner' => 'Janith',
            'items' => [
                ['key' => 'equipment', 'label' => '11. Equipment & Maintenance', 'href' => base_url('modules/equipment/index.php'), 'icon' => 'tool'],
            ],
        ],
    ];

    $user = current_user();

    // Non-admins (member/trainer) only see the User Portals group + their own portal already covers their needs;
    // the rest is Admin Management territory (NFR-S02: server-side role gating, this is just matching UI to it).
    if ($role !== 'admin') {
        $groups = array_slice($groups, 0, 1);
    }
    ?>
    <aside class="w-[260px] shrink-0 bg-white border-r border-slate-200 min-h-screen flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100">
            <a href="<?= e(base_url('public/index.php')) ?>" class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-600 to-teal-400 flex items-center justify-center text-white font-extrabold">FC</span>
                <span>
                    <span class="block font-extrabold text-slate-900 leading-none">Fit<span class="text-teal-600">Core</span></span>
                    <span class="block text-[10px] font-bold tracking-widest text-slate-400 mt-1">GYM MANAGER V1.0</span>
                </span>
            </a>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
            <?php foreach ($groups as $group): ?>
                <div>
                    <div class="px-3 mb-1.5 flex items-center justify-between">
                        <span class="text-[10px] font-bold tracking-widest text-slate-400"><?= e($group['label']) ?></span>
                        <?php if ($group['owner']): ?>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-500"><?= e(strtoupper($group['owner'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php foreach ($group['items'] as $item):
                        $isActive = $item['key'] === $active; ?>
                        <a href="<?= e($item['href']) ?>"
                           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-[13px] font-medium mb-0.5 transition <?= $isActive ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50' ?>">
                            <span class="w-4 h-4 shrink-0"><?= sidebar_icon($item['icon']) ?></span>
                            <span class="flex-1"><?= e($item['label']) ?></span>
                            <?php if (!empty($item['owner'])): ?>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400"><?= e(strtoupper($item['owner'])) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <?php if ($user): ?>
        <div class="border-t border-slate-100 p-4 flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-bold"><?= e(strtoupper(substr($user['name'] ?? $user['email'], 0, 2))) ?></span>
            <span class="flex-1 min-w-0">
                <span class="block text-sm font-semibold text-slate-900 truncate"><?= e($user['name'] ?? $user['email']) ?></span>
                <span class="block text-xs text-slate-400 capitalize"><?= e($user['role']) ?></span>
            </span>
            <a href="<?= e(base_url('auth/logout.php')) ?>" title="Log out" class="text-slate-400 hover:text-rose-500">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </aside>
    <?php
}

function sidebar_icon(string $name): string {
    $paths = [
        'globe'  => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 0 20 15.3 15.3 0 0 1 0-20"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/>',
        'badge'  => '<circle cx="12" cy="9" r="5"/><path d="M9 14 7 22l5-3 5 3-2-8"/>',
        'grid'   => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>',
        'login'  => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
        'users'  => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'id'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 8h4M15 12h4M6 16h12"/>',
        'calendar'=> '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'award'  => '<circle cx="12" cy="8" r="6"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
        'clock'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'refresh'=> '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'card'   => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'doc'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'tool'   => '<path d="M14.7 6.3a4 4 0 1 1-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 1 1 5.4-5.4z"/>',
    ];
    $d = $paths[$name] ?? $paths['grid'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">' . $d . '</svg>';
}
