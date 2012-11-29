<?php //

function hex2binlocal($hexdata) {
    $bindata = "";

    for ($i = 0; $i < strlen($hexdata); $i += 2) {
        $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
    }

    return $bindata;
}

function pkcs5_pad($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

/**
 * @package helper
 * @author vpriem
 * @since 25.10.2010
 */
class Default_Helpers_Crypt {

    /**
     * Get hash for an id for downloading
     * @author vpriem
     * @since 13.10.2010
     * @param string $value
     * @return string
     */
    public static function hash($value) {
        return md5(SALT . $value . SALT); // 2x more secure :D
    }

    /**
     * Aes Encryption, works with defaut JAVA  Implementation, used by dummy Programmers
     * @param type $str
     * @param type $key
     * @return type 
     */
    public static function encryptAES($str, $key) {

        $key = md5($key);
        $key = hex2binlocal($key);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, pkcs5_pad($str, 16), MCRYPT_MODE_ECB);

        return base64_encode($encrypted);
    }

    
    public static function encryptHMAC_SHA1($str, $key) {
        return base64_encode(hash_hmac('sha1', $str, $key, true));
    }
    
}
