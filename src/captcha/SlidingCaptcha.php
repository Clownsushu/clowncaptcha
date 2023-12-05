<?php
namespace clown\captcha\captcha;

use clown\redis\Redis;

/**
 * 滑块验证码
 */
class SlidingCaptcha implements Captcha
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var int 验证码过期时间单位秒
     */
    private $expire = 600;

    /**
     * @var string 缓存前缀
     */
    private $cache_prefix = 'clown:captcha:sliding:';

    private $config = [];

    public function __construct($config = [])
    {
        $this->redis = (new Redis())->getRedis();
        //合并配置内容
        $this->config = array_merge($this->config, $config);
    }

    public function captcha($key = '')
    {
        // TODO: Implement captcha() method.
    }


    public function verify($key, $code)
    {
        // TODO: Implement verify() method.
    }

}