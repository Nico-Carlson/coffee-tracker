<?php

function post(string $key): string
{
    return trim($_POST[$key] ?? '');
}

// short alias for escaping on output
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// "3:05" -> 185, or null if invalid
function parseBrewTime(string $s): ?int
{
    return preg_match('/^(\d{1,2}):([0-5]\d)$/', $s, $m) ? $m[1] * 60 + $m[2] : null;
}

function formatBrewTime(?int $sec): string
{
    return $sec === null ? '—' : sprintf('%d:%02d', intdiv($sec, 60), $sec % 60);
}

// validates the brew form; returns [params ready for execute(), errors]
function readBrewInput(array $allowedCoffeeIds): array
{
    $errors = [];
    $grinds = ['Fine', 'Medium-fine', 'Medium', 'Medium-coarse', 'Coarse'];

    $int = function (string $key, int $min, int $max, bool $required, string $label) use (&$errors) {
        $v = post($key);
        if ($v === '') {
            if ($required) {
                $errors[] = "$label is required.";
            }
            return null;
        }
        $n = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($n === false) {
            $errors[] = "$label must be $min to $max.";
            return null;
        }
        return $n;
    };

    $coffeeId = filter_var(post('coffeeId'), FILTER_VALIDATE_INT);
    if ($coffeeId === false || !in_array($coffeeId, $allowedCoffeeIds, true)) {
        $errors[] = 'Pick a coffee from the list.';
    }

    $grindSize = post('grindSize');
    if (!in_array($grindSize, $grinds, true)) {
        $errors[] = 'Pick a grind size.';
    }

    $brewDate = post('brewDate');
    $d = DateTime::createFromFormat('Y-m-d', $brewDate);
    if (!$d || $d->format('Y-m-d') !== $brewDate) {
        $errors[] = 'Enter a valid date.';
    }

    $totalSeconds = parseBrewTime(post('totalTime'));
    if ($totalSeconds === null) {
        $errors[] = 'Brew time must look like 3:00.';
    }

    return [[
        ':coffeeId'     => $coffeeId,
        ':brewDate'     => $brewDate,
        ':grindSize'    => $grindSize,
        ':coffeeGrams'  => $int('coffeeGrams', 1, 200, true, 'Coffee grams'),
        ':waterGrams'   => $int('waterGrams', 1, 5000, false, 'Water grams'),
        ':waterTemp'    => $int('waterTemp', 100, 220, false, 'Water temperature'),
        ':bloomTime'    => $int('bloomTime', 0, 300, false, 'Bloom time'),
        ':rating'       => $int('rating', 1, 5, true, 'Rating'),
        ':totalSeconds' => $totalSeconds,
        ':brewNotes'    => post('brewNotes'),
    ], $errors];
}
