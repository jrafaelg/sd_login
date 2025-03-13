<?php

include '..\vendor\autoload.php';

//phpinfo();
//exit();

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

$password = 123;

//$private = RSA::createKey(512);
//echo $private;
//echo "\n";
//echo $private->withPassword($password . 'a');
//exit();
//$public = $private->getPublicKey();

$pkey = '
-----BEGIN PRIVATE KEY-----
MIIBVQIBADANBgkqhkiG9w0BAQEFAASCAT8wggE7AgEAAkEAtwrX9FX5lHw5nz9j
b1qLaE7TmSWwxub7Hx3VwLaMry6EX4pPECKfZkqyRiTqaYlMGsk/1TCDTQPqC91a
ccmiQQIDAQABAkEAjpEWlQul4j5Dj0KLCz9F18+VxfHyV2mu7JSLWZeVyhcqj2h7
uRQHuBG4reSoznAI5qrNfuQx7JCp5j73hgR3JQIhAOMqeOJMpX8cpIYG+A4I2nGs
03J3ycXYcUMfxueTIk27AiEAzkae+QPx9LCxoPeRFz2JlCX0IufESgg6F3QOExMn
EjMCIC3jq43Te+8koxusFroHEdG63vAVwL3lzmOH7M9lCp33AiBROg/5wOrzTuzz
lOlGfI4Dj5p+cEOkX274s1OjgUQ+bwIhAIv31AVCsEccV5gcyrluI5QPSYV5nGfb
N6F9IejANcNm
-----END PRIVATE KEY-----
';

$pekey = '
-----BEGIN ENCRYPTED PRIVATE KEY-----
MIIBvTBXBgkqhkiG9w0BBQ0wSjApBgkqhkiG9w0BBQwwHAQIOPfpzgjZjlsCAggA
MAwGCCqGSIb3DQIJBQAwHQYJYIZIAWUDBAECBBDwxsRMyeR+UOw9vh6zSD/jBIIB
YMLMDlRglrWsJlEKJRYZTTmMRCQ1eGrk/oTbdVpJlN0LcpukRnLDcf897KqxJsw7
i/5xrJw50BWBfPzTt2wM85FeNKQGGVq1eISbaSYGEewSf9E9nQTXCf4DUVW5xO25
h1voiL5W/tGiQBhqO0hGTOVtb5LEtOVGqk+u5vfU+1C5sWGXuIlGT/Qa/DFB+Ed/
ltgUJyB8tkoZgO5z09wfrh1aNqWi0GQ0eL2iGb7MjZZLdR0b2Jv8EHpOUs7jSmBa
JxMa9pJtv/Vm8Dhs+ZwdAQae3zbWJcRWLLghIWBKJUgJGAFUV4twrwUZq2OB1a7U
7LdgtYHvAEPScv1RALQg0b9RoMGROke1Bb9tHS0lJuM1gZLsCa1qiu1sW2L9qkng
YFe5IohHyX5BZ53O/vlbgvERAMClP5aNZ54dW6p5Ph+pa+WLj44GvMZL3tNGMPjF
42apHusY1cIuv+wJcDEMHIQ=
-----END ENCRYPTED PRIVATE KEY-----
';

$key = PublicKeyLoader::load($pekey, $password . 'a');

//$private = RSA::load($pekey, '123');

echo $key;
exit();


$private = RSA::createKey(512);
echo $private;
//echo "\n";
//echo $private->withPassword('123');
//exit();
$public = $private->getPublicKey();

//$public->encrypt();

//var_dump($private->getLabel());

$search = [
    "-----BEGIN PRIVATE KEY-----",
    "-----END PRIVATE KEY-----",
    "-----BEGIN PUBLIC KEY-----",
    "-----END PUBLIC KEY-----",
];

echo trim(str_replace($search, '', $private));
echo "\n";
echo $private;
echo "\n";
echo trim(str_replace($search, '', $public));
echo "\n";
echo $public;


////use phpseclib3\Crypt\PublicKeyLoader;
////use phpseclib3\Math\BigInteger;
//
//$/[key = PublicKeyLoader::load([
//    'e' => new BigInteger(1024),
//    'n' => new BigInteger(256)
//]);
//echo "\n";
//echo $key;

//$config = array();

//$config['config'] = 'C:\Users\jrafa\PhpstormProjects\sd_login\vendor\phpseclib\phpseclib\phpseclib\openssl.cnf';

