<?php
/**
 * Small inline-SVG icon set for the public site (line-icon style, currentColor stroke).
 * Keeps every page from hand-rolling <svg> markup.
 */
function gym_icon(string $name, string $class = 'w-6 h-6'): string {
    $paths = [
        'dumbbell'   => '<rect x="1" y="10" width="3" height="4" rx="1"/><rect x="4.5" y="8" width="2" height="8" rx="1"/><line x1="7" y1="12" x2="17" y2="12"/><rect x="17.5" y="8" width="2" height="8" rx="1"/><rect x="20" y="10" width="3" height="4" rx="1"/>',
        'flame'      => '<path d="M12 2c1 3-2 4-2 7a3 3 0 0 0 6 0c1.5 1.5 2 3.5 2 5a6 6 0 1 1-12 0c0-4 3-5 3-8 0-1.5-.5-3-1-4 1.5.5 3 1.5 4 6Z"/>',
        'heartbeat'  => '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
        'shield'     => '<path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/>',
        'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'users'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17.5" cy="9.5" r="2.8"/><path d="M15.2 13a5 5 0 0 1 6.3 4.8"/>',
        'trophy'     => '<path d="M8 4h8v5a4 4 0 0 1-8 0Z"/><path d="M8 5H4v1a4 4 0 0 0 4 4"/><path d="M16 5h4v1a4 4 0 0 1-4 4"/><path d="M12 13v4"/><path d="M9 21h6"/><path d="M9 21c0-2 1.3-3 3-3s3 1 3 3"/>',
        'arrow'      => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'star'       => '<path d="m12 2 2.9 6.3 6.9.7-5.2 4.7 1.5 6.8L12 17l-6.1 3.5 1.5-6.8L2.2 9l6.9-.7Z"/>',
        'quote'      => '<path d="M9.5 6.5c-2.8 1-4.5 3.3-4.5 6.3a3 3 0 1 0 3-3c.3-1.3 1.2-2.5 2.5-3.3Z"/><path d="M19 6.5c-2.8 1-4.5 3.3-4.5 6.3a3 3 0 1 0 3-3c.3-1.3 1.2-2.5 2.5-3.3Z"/>',
        'pin'        => '<path d="M12 22s7-6.3 7-12a7 7 0 1 0-14 0c0 5.7 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone'      => '<path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2.2 2A17 17 0 0 1 2 5.2 2 2 0 0 1 4 4Z"/>',
        'mail'       => '<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 7 9-7"/>',
        'check'      => '<path d="m4 12 5 5L20 6"/>',
        'bolt'       => '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>',
        'calendar'   => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M8 2.5v4M16 2.5v4M3 9.5h18"/>',
        'target'     => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
    ];
    $body = $paths[$name] ?? $paths['bolt'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="' . e($class) . '">' . $body . '</svg>';
}
