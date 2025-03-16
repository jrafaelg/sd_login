<?php

include '..\vendor\autoload.php';

use \phpseclib3\Crypt\RSA;

//include '..\helper\CipherHelper.php';

use \helper\CipherHelper;

$private = RSA::createKey(1024);

$public = $private->getPublicKey();
$sing = $private->sign('teste');
echo $private;
echo "\n---\n";
//echo $private->toString('PKCS8');
//echo "\n---\n";
echo $sing;
//echo "\n---\n";
//$public->verify('teste', $private->toString('PKCS8'));
exit();
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