<?php
/**
 * Created by PhpStorm.
 * User: joselee
 * Date: 2018/3/9
 * Time: 上午1:26
 */


class WXOpenSSLAES
{
    /**
     * var string $method 加解密方法，可通过openssl_get_cipher_methods()获得
     */
    protected $method;

    /**
     * var string $secret_key 加解密的密钥
     */
    protected $secret_key;

    /**
     * var string $iv 加解密的向量，有些方法需要设置比如CBC
     */
    protected $iv;

    /**
     * var string $options （不知道怎么解释，目前设置为0没什么问题）
     */
    protected $options;

    /**
     * 构造函数
     *
     * @param string $key 密钥
     * @param string $method 加密方式
     * @param string $iv iv向量
     * @param mixed $options 还不是很清楚
     *
     */
    public function __construct($key, $method = 'AES-128-ECB', $iv = '', $options = OPENSSL_RAW_DATA )
    {
        // key是必须要设置的
        $this->secret_key = $key;

        $this->method = $method;

        $this->iv = $iv;

        $this->options = $options;

//        $iv_length = openssl_cipher_iv_length($method);
        //$iv = openssl_random_pseudo_bytes($iv_length);
    }

    /**
     * 加密方法，对数据进行加密，返回加密后的数据
     *
     * @param string $data 要加密的数据
     *
     * @return string
     *
     */
    public function encrypt($data)
    {
        return openssl_encrypt($data, $this->method, $this->secret_key, $this->options, $this->iv);
    }

    /**
     * 解密方法，对数据进行解密，返回解密后的数据
     *
     * @param string $data 要解密的数据
     *
     * @return string
     *
     */
    public function decrypt($data)
    {
        $iv_length = openssl_cipher_iv_length($this->method);
        $iv = substr($this->iv,0,$iv_length);
        return openssl_decrypt($data, $this->method, $this->secret_key, $this->options, $iv);
    }
}