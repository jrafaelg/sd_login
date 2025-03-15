<?php

/**
 * @author Juan Domenick Fernandes <juandomenick12@gmail.com>
 * Funções de encriptação e desincriptação
 */
class CipherHelper
{
    /**
     * encrypt - Função que faz a encriptação da string passada e retorna uma string
     *
     * @param String $string
     * @param string|null $key
     * @return string|null
     */
    public function encrypt(string $string, string $key = null): string|null
    {
        return strlen($string) ?
            openssl_encrypt($string, "AES-128-ECB", $key, 0, '')
            : null;
    }

    /**
     * decrypt - Função que faz a descriptação da string passada e retorna uma string
     *
     * @param String $string
     * @param string|null $key
     * @return string|null
     */
    public function decrypt(string $string, string $key = null): string|null
    {

        return strlen($string) ?
            openssl_decrypt($string, "AES-128-ECB", $key, 0, '')
            : null;
    }
}