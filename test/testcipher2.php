<?php

include '..\vendor\autoload.php';

use \phpseclib3\Crypt\RSA;


//include '..\helper\CipherHelper.php';

use \helper\CipherHelper;

$privKey = '
-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMYkX5LO72hUr103
tN0MEdOa6GGljgGlwPBpDpMboT08V8sEreqn0cg/yV9Ao4mFxb/1umqCg0OLExA9
ONvdO8yULGfNNl2Pa+ld/Ao480g25t/pXQuPTxb4pDZLZfNdlhdjA8+ENDpVVsC7
rw3kKSCcqNvpFaveaAf1U2wBou/3AgMBAAECgYAQqvSjO+clxqHt3IbJZX+GZmyP
8kZM2y2sy5mfRF6+8PmNqXob2DwsdTkyWxSmt+sXp8sjtJDoOKXE4vGKhUTHXfXg
55loW5pAgTx3f+ZEJ36uN6nPEM3xhK1XntQT19pWX75X+3mrIFfb/APpv6hFB58l
gbnBPCbQxy1U3m5sqQJBAOUIUZzZ09GBlPzM4v7wvNtxOa05pPc6MW3Ct62Xr87j
OWZbnIyO58SZAXoJ+JeG39z7RgKOA+jShwuwXCTCIqsCQQDdeO5TLHqmWBk+i2ME
zjV2Wn9x9L7XAGdNLJ0d46+d1A7hRehOotVfzHO2HD+Hg28knv+v3Ony420J27Yd
FcflAkEA1Ohtzcyk3v6B/JuObQZn2esgfcq3cufS6UD4tNPsp/uM8X06Q2PZSmYd
/E/mmx3TXz7q7xcLxVRoG9KfZcBerwJARDZP6QwlBoDR3aKer8c3TimXYSjJcnzs
VCeuiJss5sZ+gsG+SCH86BGXffp7UqiXncbe53J6F9YWKlgiYhjoRQJAMaEARzhL
Lu2u+xzDCigMPowYdBtRWKBt5t5tyJoIFtjzlm0SzPcOvHCfXqGn2t8fGA0k78vF
7lour/wVxE4V8w==
-----END PRIVATE KEY-----
';

$testSigned = 'xg/oK53GNAw6gshqI+RIj9CJ4vCI4e/v9+nAWgpurgdoZTZlMq+fd0FCvg//g8wAJV9v1rICibs0ux4W6/FfoSQvBcOlNHW9lNjcOkNvuAqNJ2GulylI+kB44U4UHqvtO/6agI4B9D4emLs91YJul8FdHEVvVJT/Sz7hIURka2M=';

$private = RSA::loadPrivateKey($privKey);
$public = $private->getPublicKey();

$sing = $private->sign('teste');
echo base64_encode($sing);
echo "\n---\n";
//echo $private->toString('PKCS8');
//echo "\n---\n";
//echo sha1($sing);
//echo "\n---\n";
$testSigned = base64_decode($testSigned);
echo $public->verify('teste', $testSigned) ? 'valid signature' : 'invalid signature';
echo "\n---\n";
$ciphertext = $private->getPublicKey()->encrypt('teste');
$ciphertext = base64_encode($ciphertext);
echo "\n---\n";
echo $ciphertext;
echo "\n---\n";
$ciphertext = 'ISpSu58ImKpxkT/EHxmECIpTSfB5plQIJYrqq7H6Pdw5vXMC+L84/7OGgmfUIblAYaa6jKKQHU02YgzvgsAFvM1mTcC41EbM0sDDyfsq2BN83OqJ09knHsrnurreVqgdjlDsGjelJ/UC+KIR9lZ9xxmBNcZ9ChPKUm17Un9PifM=';
$ciphertext = base64_decode($ciphertext);
echo $private->decrypt($ciphertext);


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