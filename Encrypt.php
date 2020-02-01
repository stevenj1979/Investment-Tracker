<?php

$key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
$ciphertext = sodium_crypto_secretbox('This is a secret!', $nonce, $key);

echo "<BR> This is a test: ".$ciphertext;

//edit Encrypt

?>
