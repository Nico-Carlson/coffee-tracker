<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Run this from the command line.\n");
}

require __DIR__ . '/../../includes/db_connect.php';
/** @var PDO $pdo */

const DEMO_USERNAME = 'demo';
const DEMO_PASSWORD = 'demo-password-123';

$pdo->beginTransaction();

try {
    // Re-runnable: deleting the old demo user cascades to their coffees and brews.
    $pdo->prepare('DELETE FROM tblUser WHERE username = :username')
        ->execute([':username' => DEMO_USERNAME]);

    $pdo->prepare(
        'INSERT INTO tblUser (firstName, lastName, username, password)
         VALUES (:firstName, :lastName, :username, :password)'
    )->execute([
        ':firstName' => 'Demo',
        ':lastName'  => 'User',
        ':username'  => DEMO_USERNAME,
        ':password'  => password_hash(DEMO_PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]),
    ]);
    $userId = (int) $pdo->lastInsertId();

    $coffees = [
      ['Yirgacheffe Kochere', 'Ridgeline Roasters', 'Light', 'Washed', 'Jasmine, bergamot, lemon tea.'],
      ['Guji Natural', 'Foxglove Coffee', 'Light', 'Natural', 'Blueberry jam, floral, sweet finish.'],
      ['Huila Pink Bourbon', 'Alder & Ash', 'Light', 'Washed', 'Red apple, honey, clean acidity.'],
      ['Kiambu AA', 'Tin Roof Roasting', 'Medium', 'Washed', 'Blackcurrant, brown sugar.'],
      ['Cerrado Dulce', 'Mesa Verde Coffee', 'Medium', 'Natural', 'Milk chocolate, hazelnut, low acid.'],
      ['Antigua Reserve', 'Copper Kettle', 'Medium', 'Washed', 'Cocoa, orange zest, round body.'],
      ['Sumatra Mandheling', 'Northgate Roasters', 'Dark', 'Wet-hulled', 'Earthy, dark chocolate, heavy body.'],
      ['Honey Process Tarrazu', 'Bluebird Coffee Co.', 'Medium', 'Honey', 'Caramel, stone fruit, silky.'],
      ['Rwanda Nyamasheke', 'Harbor Lane Roasters', 'Light', 'Washed', 'Red berry, black tea.'],
      ['Colombia Decaf', 'Quiet Hours Coffee', 'Medium', 'Washed', 'Toffee, almond, soft.'],
      ['Panama Geisha Lot 7', 'Foxglove Coffee', 'Light', 'Washed', 'Peach, jasmine, very delicate.'],
      ['House Blend', 'Copper Kettle', 'Dark', 'Washed', 'Smoky, bittersweet, good for milk.'],
    ];

    $coffeeStmt = $pdo->prepare(
        'INSERT INTO tblCoffee (userID, coffeeName, roaster, roast, process, notes)
         VALUES (:userID, :coffeeName, :roaster, :roast, :process, :notes)'
    );

    $coffeeIds = [];
    $roastOf = [];
    foreach ($coffees as [$name, $roaster, $roast, $process, $notes]) {
        $coffeeStmt->execute([
            ':userID'     => $userId,
            ':coffeeName' => $name,
            ':roaster'    => $roaster,
            ':roast'      => $roast,
            ':process'    => $process,
            ':notes'      => $notes,
        ]);
      $coffeeIds[$name] = (int) $pdo->lastInsertId();
      $roastOf[$name] = $roast;
    }

      mt_srand(2026); // fixed seed: every run produces the same demo data

    $baseRating = ['Light' => 4.2, 'Medium' => 3.8, 'Dark' => 3.3];
    $grinds = [
        'Light'  => ['Medium-fine', 'Medium'],
        'Medium' => ['Medium', 'Medium-coarse'],
        'Dark'   => ['Medium-coarse', 'Coarse'],
    ];
    $temps = ['Light' => 205, 'Medium' => 200, 'Dark' => 195];
    $notesByRating = [
        5 => ['Dialed in. Sweet and clear.', 'Best cup this week.', 'Would brew exactly this again.'],
        4 => ['Really good, slightly bright.', 'Clean and balanced.'],
        3 => ['Fine, a bit flat.', 'Okay, probably under-extracted.'],
        2 => ['Bitter and harsh, ground too fine.', 'Thin and sour.'],
        1 => ['Drain pour, channeled badly.'],
    ];

    $brewStmt = $pdo->prepare(
        'INSERT INTO tblBrew (userID, coffeeId, coffeeGrams, waterGrams, brewDate, waterTemp,
                              grindSize, bloomTime, totalSeconds, rating, brewNotes)
         VALUES (:userID, :coffeeId, :coffeeGrams, :waterGrams, :brewDate, :waterTemp,
                 :grindSize, :bloomTime, :totalSeconds, :rating, :brewNotes)'
    );

    $names = array_keys($coffeeIds);
    $brewCount = 60;
    for ($i = 0; $i < $brewCount; $i++) {
        $name = $names[mt_rand(0, count($names) - 1)];
        $roast = $roastOf[$name];
        $grams = mt_rand(15, 20);
        $rating = max(1, min(5, (int) round($baseRating[$roast] + mt_rand(-10, 10) / 10)));
        $notePool = $notesByRating[$rating];

        $brewStmt->execute([
            ':userID'       => $userId,
            ':coffeeId'     => $coffeeIds[$name],
            ':coffeeGrams'  => $grams,
            ':waterGrams'   => (int) round($grams * mt_rand(150, 170) / 10),
            ':brewDate'     => date('Y-m-d', strtotime('-' . mt_rand(0, 89) . ' days')),
            ':waterTemp'    => $temps[$roast] + mt_rand(-2, 2),
            ':grindSize'    => $grinds[$roast][mt_rand(0, 1)],
            ':bloomTime'    => mt_rand(30, 45),
            ':totalSeconds' => mt_rand(150, 240),
            ':rating'       => $rating,
            ':brewNotes'    => $notePool[mt_rand(0, count($notePool) - 1)],
        ]);
    }

    $pdo->commit();
    echo "Seeded demo user (userID $userId) with " . count($coffeeIds) . " coffees and $brewCount brews.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
