<?php
//$private_key = openssl_pkey_new();
//var_dump($private_key);
//
//while($message = openssl_error_string()){
//    echo $message.'<br />'.PHP_EOL;
//}
////var_dump(openssl_error_string());
//
//$public_key_pem = openssl_pkey_get_details($private_key)['key'];
//var_dump($public_key_pem);
//
//$public_key = openssl_pkey_get_public($public_key_pem);
//var_dump($public_key);


/* Create the private and public key */
$res = openssl_pkey_new();
openssl_error_string(); // May throw error even though its working fine!

/* Extract the private key from $res to $privKey */
openssl_pkey_export($res, $privKey);
openssl_error_string(); // May throw error even though its working fine!

/* Extract the public key from $res to $pubKey */
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];

$data = 'i.amniels.com is a great website!';

/* Encrypt the data using the public key
 * The encrypted data is stored in $encrypted */
openssl_public_encrypt($data, $encrypted, $pubKey);

/* Decrypt the data using the private key and store the
 * result in $decrypted. */
openssl_private_decrypt($encrypted, $decrypted, $privKey);

echo $decrypted;