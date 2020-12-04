<?php
/**
 * AES encrypt
 * @date 2016-3-15 11:19:53
 **/

class aesCryptUtil {
	private static $key = 'ZNvF6dL4GiYqNpe6';
	private static $isCompress = true;
	/**
     * [encrypt description]
     * AES加密
     * @param  [type] $input
     * @return [type]
     */
    public static function encrypt($input, $key) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5Pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);//MCRYPT_DEV_URANDOM
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
    /**
     * [pkcs5Pad description]
     * @param  [type] $text
     * @param  [type] $blocksize
     * @return [type]
     */
    private static function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    /**
     * [decrypt description]
     * AES解密
     * @param  [type] $sStr
     * @param  [type] $sKey
     * @return [type]
     */
    public static function decrypt($sStr, $sKey) {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);//MCRYPT_DEV_URANDOM
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $sKey, base64_decode($sStr), MCRYPT_MODE_ECB, $iv);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
    /**
     * [opensslDecrypt description]
     * @param  [type] $sStr
     * @param  [type] $sKey
     * @return [type] 使用openssl库进行解密
     */
    public static function opensslEncrypt($sStr, $sKey, $method = 'AES-128-ECB'){
        $str = openssl_encrypt($sStr, $method, $sKey);
        return $str;
    }
    /**
     * [opensslDecrypt description]
     * @param  [type] $sStr
     * @param  [type] $sKey
     * @return [type] 使用openssl库进行解密
     */
    public static function opensslDecrypt($sStr, $sKey, $method = 'AES-128-ECB'){
        $str = openssl_decrypt($sStr,$method,$sKey, 0);
        return $str;
    }
    /**
     * [getDecryptData description]
     * @param  [type]  $str
     * @param  boolean $isCompress
     * @return [type]
     */
    public static function getDecryptData($str, $key = '') {
        if (strlen($key) < 1) {
            $key = self::$key;
        }
        $decryStr = self::opensslDecrypt($str, $key);
        $decryArr = explode('|',$decryStr);
        $time = $decryArr[0];
        $rand = $decryArr[1];
        $encryptStr = $decryArr[2];
        $md5 = md5(($time.$rand.$key));
        $key2 = substr($md5,strlen($md5)-16);
        $resStr = self::opensslDecrypt($encryptStr, $key2);
        if (self::$isCompress) {
            $resStr = gzuncompress($resStr);
        }
        $index = strrpos($resStr,'|');
        $rawData = substr($resStr,0,$index);
        $rawMask = substr($resStr,$index+1);
        $randMaskByServ = substr(md5($time.$rand.$rawData),0,10);
        if ($rawMask == $randMaskByServ) {
            $data['DECRYPT'] = $rawData;
            $data['MD5MASK'] = $rawMask;
            return $data;
        }
        return array();
    }
    /**
     * [getDecryptStr description]
     * @param  [type] $str
     * @return [type]
     */
    public static function getDecryptStr($str, $key = ''){
        $data = '';
        $res = self::getDecryptData($str, $key);
        if (!empty($res)) {
            $data = $res['DECRYPT'];
        }
        return $data;
    }

    /**
     * 生成加密字符串
     * @param $str String 原始字符串
     * @return String 加密后的字符串
     */
    public static function getEncryptStr($str, $key = '') {
        if (strlen($key) < 1) {
            $key = self::$key;
        }
        $time = date('YmdHis');
        $rand = self::random(5, false);
        $mask_raw = md5($time.$rand.$str);
        $mask_raw_substr = substr($mask_raw, 0, 10);

        $key2_raw = md5($time.$rand.$key);
        $key2 = substr($key2_raw, strlen($key2_raw) - 16);

        $str_mask = $str . "|" . $mask_raw_substr;
        // compress
        if (self::$isCompress) {
            $str_mask = gzcompress($str_mask);
        }
        $str_mask_encrypt = self::opensslEncrypt($str_mask, $key2);
        $str_mask_encrypt_base64 = $str_mask_encrypt;
        return self::opensslEncrypt(($time . "|" . $rand . "|" . $str_mask_encrypt_base64), $key);
    }

    /** 功能 生成随机字符串
     *  @param $length Integer 生成的随机串的字符长度
     *  @param $numeric Boolean 是否纯数字串 缺省为false [true:是, false:否]
     *  @return String
     */
    public static function random($length, $numeric = false)
    {
        $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * [getParamArrbyStr description]
     * 将post字符串参数处理成数组
     * @param  [type] $paramStr
     * @return [type]
     */
    public static function getParamArrbyStr($paramStr){
        $paramArr = array();
        parse_str($paramStr, $paramArr);
        return $paramArr;
    }

    /**
     * [encryptId description]
     * @param  [type] $unCryptStr
     * @return [type]
     */
    public static function unBaseEncrypt($unCryptStr){
        $encryptStr = self::opensslEncrypt($unCryptStr,self::$key);
        $decodeStr = base64_decode($encryptStr);
        $str = bin2hex($decodeStr);
        return $str;
    }

    /**
     * [unBaseDecrypt description]
     * @param  [type] $cryptStr
     * @return [type]
     */
    public static function unBaseDecrypt($cryptStr){
        $binCryptStr = hex2bin($cryptStr);
        $encodeStr = base64_encode($binCryptStr);
        $str = self::opensslDecrypt($encodeStr,self::$key);
        return $str;
    }
}
