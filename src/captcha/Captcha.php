<?php
namespace clown\captcha\captcha;


interface Captcha
{
    /**
     * 创建验证码
     * @param string $key
     * @return string
     */
    public function captcha($key = '');

    /**
     * 验证验证码
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify($key, $code);
}