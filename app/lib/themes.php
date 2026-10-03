<?php
declare(strict_types=1);

/**
 * World themes. The author shell is "nightfall"; each book or series picks a world.
 * Add a new world by adding an entry here and (optionally) an atmosphere block in site.css
 * under .world-<key>.
 */
function themes(): array
{
    return [
        'nightfall' => [
            'label' => 'Nightfall (author default)',
            'mode'  => 'dark',
            'bg' => '#241526', 'surface' => '#311E36', 'ink' => '#F5E8EE', 'muted' => '#BFA3B6',
            'accent' => '#E9B872', 'accent_ink' => '#241526', 'line' => 'rgba(245,232,238,.16)',
            'c1' => '#3A2142', 'c2' => '#7A3B5E',
        ],
        'siren' => [
            'label' => 'Deep sea (Siren Unleashed)',
            'mode'  => 'dark',
            'bg' => '#0A2730', 'surface' => '#0F3540', 'ink' => '#E4F5F1', 'muted' => '#93BDB8',
            'accent' => '#72E0C6', 'accent_ink' => '#082028', 'line' => 'rgba(228,245,241,.16)',
            'c1' => '#0F4A57', 'c2' => '#06161C',
        ],
        'glacier' => [
            'label' => 'Glacier (light, icy)',
            'mode'  => 'light',
            'bg' => '#EAF2F7', 'surface' => '#FFFFFF', 'ink' => '#132F47', 'muted' => '#4C687F',
            'accent' => '#1F6FA8', 'accent_ink' => '#FFFFFF', 'line' => 'rgba(19,47,71,.14)',
            'c1' => '#CFE6F3', 'c2' => '#7FB4D6',
        ],
        'aelithra' => [
            'label' => 'Eclipse sky (Aelithra)',
            'mode'  => 'dark',
            'bg' => '#1A1946', 'surface' => '#232259', 'ink' => '#F7EEDC', 'muted' => '#B9B1D9',
            'accent' => '#F2B544', 'accent_ink' => '#1A1946', 'line' => 'rgba(247,238,220,.16)',
            'c1' => '#2B2A73', 'c2' => '#9C2F4A',
        ],
        'ember' => [
            'label' => 'Ember (spare, warm dark)',
            'mode'  => 'dark',
            'bg' => '#2A1612', 'surface' => '#371E18', 'ink' => '#F8E9DF', 'muted' => '#C9A796',
            'accent' => '#F08A5D', 'accent_ink' => '#2A1612', 'line' => 'rgba(248,233,223,.16)',
            'c1' => '#5A2318', 'c2' => '#1C0D0A',
        ],
    ];
}

function theme(string $key): array
{
    $all = themes();
    return $all[$key] ?? $all['nightfall'];
}

function theme_key(?string $key): string
{
    return isset(themes()[$key ?? '']) ? (string) $key : 'nightfall';
}

function valid_hex(?string $hex): bool
{
    return (bool) preg_match('~^#[0-9a-fA-F]{6}$~', (string) $hex);
}

/** Inline CSS custom properties for a world, with an optional accent override. */
function theme_vars(string $key, string $accent = ''): string
{
    $t = theme($key);
    if (valid_hex($accent)) {
        $t['accent'] = $accent;
    }
    $map = [
        '--bg' => $t['bg'], '--surface' => $t['surface'], '--ink' => $t['ink'], '--muted' => $t['muted'],
        '--accent' => $t['accent'], '--accent-ink' => $t['accent_ink'], '--line' => $t['line'],
        '--c1' => $t['c1'], '--c2' => $t['c2'],
    ];
    $out = '';
    foreach ($map as $k => $v) {
        $out .= $k . ':' . $v . ';';
    }
    return $out;
}
