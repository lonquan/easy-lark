<?php

namespace AntCool\EasyLark\Support;

use AntCool\EasyLark\Exceptions\InvalidArgumentException;

class Utils
{
    /**
     * AES 解密
     *
     * @see https://open.feishu.cn/document/ukTMukTMukTM/uYDNxYjL2QTM24iN0EjN/event-subscription-configure-/encrypt-key-encryption-configuration-case
     */
    public static function aesDecrypt(string $ciphertext, string $encryptKey): string
    {
        $encrypt = base64_decode($ciphertext);
        $iv = substr($encrypt, 0, 16);
        $encrypt = substr($encrypt, 16);

        $decrypt = openssl_decrypt($encrypt, 'AES-256-CBC', hash('sha256', $encryptKey, true), OPENSSL_RAW_DATA, $iv);

        if ($decrypt === false) {
            throw new InvalidArgumentException('Decrypt failed: ' . openssl_error_string() . '.');
        }

        return $decrypt;
    }

    /**
     * 校验请求来源
     * @see https://open.feishu.cn/document/ukTMukTMukTM/uYDNxYjL2QTM24iN0EjN/event-security-verification
     */
    public static function signature(string $time, string $nonce, string $encryptKey, string $body): string
    {
        return hash('sha256', $time . $nonce . $encryptKey . $body);
    }
}
