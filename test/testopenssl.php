<?php

include '..\vendor\autoload.php';

//phpinfo();
//exit();

//use phpseclib3\Crypt\RSA;
//
//$private = RSA::createKey(256);
//$public = $private->getPublicKey();
//
////var_dump($private->getLabel());
//
////var_dump($private);
//
//var_dump($public);

//use phpseclib3\Crypt\PublicKeyLoader;
//use phpseclib3\Math\BigInteger;
//
//$key = PublicKeyLoader::load([
//    'e' => new BigInteger(2),
//    'n' => new BigInteger(2)
//]);
//
//echo $key;

$config = array();

$config['config'] = 'C:\Users\jrafa\PhpstormProjects\sd_login\vendor\phpseclib\phpseclib\phpseclib\openssl.cnf';

$privateKey = openssl_pkey_new(array(
    'private_key_bits' => 512,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config' => 'C:\Users\jrafa\PhpstormProjects\sd_login\vendor\phpseclib\phpseclib\phpseclib\openssl.cnf'
));

openssl_pkey_export($privateKey, $privkey, "123", $config);

var_dump($privkey);

// Extract the private key into $private_key
//openssl_pkey_export($privateKey, $private_key);

// Extract the public key into $public_key
$public_key = openssl_pkey_get_details($privateKey);

$public_key = $public_key["key"];

var_dump($public_key);

/*
exit();
var_dump($privkey);

print_r($public_key);
*/

//$public = openssl_pkey_get_details(openssl_pkey_get_private($privateKey))['key'];
//print_r($public);