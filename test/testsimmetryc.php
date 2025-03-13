<?php
include '..\vendor\autoload.php';

//$key = random_bytes(32); // generate a random key
//$key = 'teste2';
//$iv = random_bytes(16); // generate a random IV
/**
 * ���״7�d~A&��|
 */
//$iv = '* ���״7�d~A&��|';
//echo $iv;
//echo "\n";
//
//$message = "Hello world!"; // the data to encrypt
//$cipher = "AES-256-CBC"; // the algorithm and mode
//
//$encrypted = openssl_encrypt($message, $cipher, $key, 0, $iv); // encrypt the data
//echo $encrypted;
//echo "\n";
//$decrypted = openssl_decrypt($encrypted, $cipher, $key, 0, $iv); // decrypt the data
//echo $decrypted; // output: Hello world!

// On Alice's computer:
$msg = 'This comes from Alice.';
$signed_msg = sodium_crypto_sign($msg, 'rafael123');


// On Bob's computer:
$original_msg = sodium_crypto_sign_open($signed_msg, 'rafael123');
if ($original_msg === false) {
    throw new Exception('Invalid signature');
} else {
    echo $original_msg; // Displays "This comes from Alice."
}

exit();

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;

$cipher = new AES('ctr');
$cipher->setIV(Random::string(16));
$cipher->setKey('rafael123');

sodium_add();
$ciphertext = $cipher->encrypt('rafael');
echo $ciphertext;
echo "\n";
echo $cipher->decrypt($ciphertext);