<?php
declare(strict_types=1);

/* Small form-field helpers for the Studio views. */

function f_id(string $name): string
{
    return 'f-' . preg_replace('~[^a-z0-9]+~i', '-', $name);
}

function f_hint(?string $hint, string $id): string
{
    return $hint ? '<p class="hint" id="' . e($id) . '-hint">' . $hint . '</p>' : '';
}

function f_text(string $name, string $label, mixed $value, ?string $hint = null, string $type = 'text', array $attrs = []): string
{
    $id = f_id($name);
    if ($type === 'url') {
        // Plain text so links without https:// aren't blocked; the server adds it.
        $type = 'text';
        $attrs += ['inputmode' => 'url', 'autocapitalize' => 'none', 'spellcheck' => 'false'];
    }
    $extra = '';
    foreach ($attrs as $k => $v) {
        $extra .= ' ' . e($k) . ($v === true ? '' : '="' . e($v) . '"');
    }
    return '<div class="field"><label for="' . $id . '">' . e($label) . '</label>'
        . f_hint($hint, $id)
        . '<input type="' . e($type) . '" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"'
        . ($hint ? ' aria-describedby="' . $id . '-hint"' : '') . $extra . '></div>';
}

function f_area(string $name, string $label, mixed $value, ?string $hint = null, int $rows = 5): string
{
    $id = f_id($name);
    return '<div class="field"><label for="' . $id . '">' . e($label) . '</label>'
        . f_hint($hint, $id)
        . '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . $rows . '"'
        . ($hint ? ' aria-describedby="' . $id . '-hint"' : '') . '>' . e($value) . '</textarea></div>';
}

function f_select(string $name, string $label, mixed $value, array $options, ?string $hint = null): string
{
    $id = f_id($name);
    $html = '<div class="field"><label for="' . $id . '">' . e($label) . '</label>' . f_hint($hint, $id)
        . '<select id="' . $id . '" name="' . e($name) . '"' . ($hint ? ' aria-describedby="' . $id . '-hint"' : '') . '>';
    foreach ($options as $k => $v) {
        $html .= '<option value="' . e($k) . '"' . ((string) $k === (string) $value ? ' selected' : '') . '>' . e($v) . '</option>';
    }
    return $html . '</select></div>';
}

function f_check(string $name, string $label, bool $checked, ?string $hint = null): string
{
    $id = f_id($name);
    return '<div class="field field--check"><input type="checkbox" id="' . $id . '" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '>'
        . '<label for="' . $id . '">' . e($label) . '</label>' . f_hint($hint, $id) . '</div>';
}

function f_errors(array $errors): string
{
    if (!$errors) {
        return '';
    }
    $html = '<div class="notice notice--warn" role="alert"><p><strong>Not saved yet.</strong></p><ul>';
    foreach ($errors as $err) {
        $html .= '<li>' . e($err) . '</li>';
    }
    return $html . '</ul></div>';
}

/** Checklist of "still needs review" notes. Unticking a note clears it on save. */
function f_review(array $notes): string
{
    $html = '<fieldset class="review"><legend>Still to review</legend>';
    if ($notes) {
        $html .= '<p class="hint">Untick anything you’ve dealt with. It disappears when you save.</p>';
        foreach (array_values($notes) as $i => $note) {
            $html .= '<div class="field field--check"><input type="checkbox" id="rv-' . $i . '" name="review_keep[]" value="' . $i . '" checked>'
                . '<label for="rv-' . $i . '">' . e($note) . '</label></div>';
        }
    } else {
        $html .= '<p class="hint">Nothing flagged.</p>';
    }
    $html .= f_text('review_add', 'Add a note for later', '', null);
    return $html . '</fieldset>';
}

function theme_options(): array
{
    $out = [];
    foreach (themes() as $k => $t) {
        $out[$k] = $t['label'];
    }
    return $out;
}
