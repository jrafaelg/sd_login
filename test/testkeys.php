<?php
$private_key = openssl_pkey_new();
var_dump($private_key);

$public_key_pem = openssl_pkey_get_details($private_key)['key'];
var_dump($public_key_pem);

$public_key = openssl_pkey_get_public($public_key_pem);
var_dump($public_key);
