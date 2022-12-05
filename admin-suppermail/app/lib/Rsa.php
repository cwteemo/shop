<?php
/**
 * Created by PhpStorm.
 * User: sMac
 * Date: 9/24/21
 * Time: 4:07 PM
 */

namespace app\lib;


use think\Exception;


class Rsa
{
    //rsa 私钥
    private $private_key = "-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBANJTMbNdaZT0YJ00
H81S4SqMND5tvjroESC0IIhafUrFU89pmFquJ0AKYyBv4BGfCzxT+Kh9Q80fdghG
IPIWZU+fFzmzBrzWWFfMgGTibbPK7FryXm1R0Urqs68xhHgoSGnC7+13SYCnsdEj
h13TyD98XO3buMsC3ILifPtTuiN7AgMBAAECgYAeKEmnE0zRS90SjWfF9A1PIX1Y
OjodjiruioVjp73xW6vxwI1U60W7fAHMo934CNr+knfECPoQzmMJOqz8qVNrPXVX
dYZ6CzeYiJOydpfFCgBx4N5VWqh/jLqg7x7xLwflqRl6EUTDOt7qvQzRZqw9o1gV
MaopuKKM53lKbNmMYQJBAPziwtY1k2zF4KY5DnHTQ8ZV1/yoNxYbWWUImZd6rQLz
atwMSUNWN6XbMVBGY2hFkrmcNFisGVh9rRZm4i1Lv7ECQQDU6kHhCpnn7wsEtZaB
woWLwNKi41jU7YpLEHa6mMFP2xDM7FPKD/xGParAHUAs3KYt5/d++w60eFjOj2K6
muzrAkBE440KB4xCnGEHRxG9Rjz3QZlV9YkUF50xnbchgcSxwhfBHAO1OT5tixmS
8anQ1OsUbw3/fdpltc66BIrmpfTxAkEAowbf/LWGVQ43DJsSLDdK1FCHuEuGKDve
SU3I62Wtlzyw54gJPE3zJ8FuLf33tqIY6EuWuXb4snz32un1edbIkwJAEyddgoaW
cEYOGcp4EJG71VH7+TwJDX2too8GozZ0DIvuuGtTToxSD/ZcN50TtLPUET3emw4t
xyujQee1GImIvA==
-----END PRIVATE KEY-----";

    /**
     * 解密数据
     *
     * @param $es
     * @return mixed
     */
    public function decode($es)
    {
        //私钥解密
        openssl_private_decrypt(base64_decode($es),$decrypted, $this->private_key);
        $encrypt_exist = false;
        if(!empty($decrypted)) {
            try {
                $arr = json_decode($decrypted, true);
                if (array_key_exists('_t', $arr)) {
                    // 60s内的数据有效
                    if (abs(time() - $arr['_t']) < 60) {
                        $encrypt_exist = true;
                        unset($arr['_t']);
                    } else {
                        throw new Exception("数据包已过期");
                    }
                }
                // if (array_key_exists("encrypt", $arr)) {
                //     if ($arr['encrypt']=="yes") {
                //         $encrypt_exist = true;
                //         unset($arr['encrypt']);
                //     }
                // }
            } catch (Exception $e) {
                Res::returnErr($e->getMessage());
            }
        }
        if(!$encrypt_exist) Res::returnErr('抱歉！数据提交错误！');

        //继续后续处理
        return $arr;
    }
}