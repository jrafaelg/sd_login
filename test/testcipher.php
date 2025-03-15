<?php

include '..\vendor\autoload.php';

use \phpseclib3\Crypt\RSA;

include '..\helper\CipherHelper.php';

$private = RSA::createKey(512);
$public = $private->getPublicKey();
echo 'chave privada';
echo "\n";
echo $private;
echo "\n";
echo 'chave publica';
echo "\n";
echo $public;
echo "\n";

$cipher = new CipherHelper();
$ciphedKey = $cipher->encrypt($private, 'rafael123');
echo 'chave privada cifrada';
echo "\n";
echo $ciphedKey;
echo "\n";
$deciphedKey = $cipher->decrypt($ciphedKey, 'rafael123');
echo 'chave privada decifrada';
echo "\n";
echo $deciphedKey;