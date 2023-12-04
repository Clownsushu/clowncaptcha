<?php
namespace clown\captcha;


interface Captcha
{
    /**
     * 验证码
     * @param string $key
     * @param int $width
     * @param int $height
     * @return string
     */
    public function captcha($key, $width = 120, $height = 40);

    /**
     * 验证
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify($key, $code);
}