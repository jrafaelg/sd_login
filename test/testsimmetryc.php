<?php
include '..\vendor\autoload.php';

$key = random_bytes(16); // generate a random key
$iv = random_bytes(16); // generate a random IV
echo $iv;
echo "\n";

$message = "Hello world!"; // the data to encrypt
$cipher = "AES-128-ECB"; // the algorithm and mode

$encrypted = openssl_encrypt($message, $cipher, $key, 0, $iv); // encrypt the data
echo $encrypted;
echo "\n";
$decrypted = openssl_decrypt($encrypted, $cipher, $key, 0, $iv); // decrypt the data
echo $decrypted; // output: Hello world!


//use phpseclib3\Crypt\AES;
//use phpseclib3\Crypt\Random;
//
//$cipher = new AES('ctr');
//$cipher->setIV(Random::string(16));
//$cipher->setKey(Random::string(16));
//
//
//$ciphertext = $cipher->encrypt('rafael');
//echo $ciphertext;
//echo "\n";
//echo $cipher->decrypt($ciphertext);