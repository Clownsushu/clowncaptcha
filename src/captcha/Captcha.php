<?php
namespace clown\captcha\captcha;


interface Captcha
{
    /**
     * 验证码
     * @param string $key
     * @return string
     */
    public function captcha($key = '');

    /**
     * 验证
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify($key, $code);
}