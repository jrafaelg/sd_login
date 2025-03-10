<?php

include '..\vendor\autoload.php';

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
//
//echo $google2fa->generateSecretKey();

// NNQXZMEVASLTH26P


$secret = 378766;

$window = 4; // 8 keys (respectively 4 minutes) past and future

$valid = $google2fa->verifyKey('NNQXZMEVASLTH26P', $secret, $window);

echo (int)$valid . "\n";

echo time() . "\n";

echo $google2fa->getWindow() . "\n";

$timestamp = $google2fa->verifyKeyNewer('NNQXZMEVASLTH26P', $secret, 58052284);

echo (int)$timestamp . "\n";

if ($timestamp !== false) {
    //$user->update(['google2fa_ts' => $timestamp]);
    // successful
    echo 'sucesso';
} else {
    // failed
    echo 'erro';
}