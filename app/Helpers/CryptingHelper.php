<?php

function encryptPayload(string $value): string
{
    $cipher = 'AES-256-CBC';
    $secret_key = 'secret_key';
    $secret_iv = 'secret_iv';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $output = openssl_encrypt($value, $cipher, $key, 0, $iv);
    $output = base64_encode($output);

    return $output;
}

function decryptPayload(string $encryptedValue): string
{
    $cipher = 'AES-256-CBC';
    $secret_key = 'secret_key';
    $secret_iv = 'secret_iv';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    return openssl_decrypt(base64_decode($encryptedValue), $cipher, $key, 0, $iv);
}
