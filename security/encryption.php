<?php

function encryptData($data){
    // Without using ENV since we did not tackle this in our topic yet. So yes, keys are in plain view.
    $key = "icts-secret";

    $iv = openssl_random_pseudo_bytes(16);
    $encryptedData = openssl_encrypt($data, "AES-256-CBC", $key, 0, $iv);

    $encryptedData = $iv . $encryptedData;

    return urlencode(base64_encode($encryptedData));
}

function decryptData($data) {
    $data = base64_decode(urldecode($data));
    $key = "icts-secret";

    $iv = substr($data, 0, 16);
    $actualData = substr($data, 16);
    $decryptedData = openssl_decrypt($actualData, "AES-256-CBC", $key, 0, $iv);

    return $decryptedData;
}

?>