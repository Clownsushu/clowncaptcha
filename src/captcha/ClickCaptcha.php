<?php
namespace clown\captcha\captcha;

use clown\redis\Redis;

class ClickCaptcha implements Captcha
{
    /**
     * @var Redis
     */
    protected $redis;

    public function __construct()
    {
        $this->redis = (new Redis())->getRedis();
    }

    public function captcha($key, $width = 120, $height = 40)
    {

    }

    public function verify($key, $code)
    {
        // TODO: Implement verify() method.
    }
}