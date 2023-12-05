<?php
namespace clown\captcha;


use clown\captcha\captcha\ClickCaptcha;
use clown\captcha\captcha\ComputeCaptcha;
use clown\captcha\captcha\SlidingCaptcha;


class Captcha
{
    /**
     * @var string 验证码类型 默认点击验证码click=点击, sliding = 滑块
     */
    public $type = 'click';

    /**
     * 构造函数
     * @param $type string 验证码类型 click=点击, sliding = 滑块
     */
    public function __construct($type = 'click')
    {
        $this->type = $type;
    }

    /**
     * 创建验证码
     * @param $key string 唯一值
     * @param $config array 配置值
     * @return array
     */
    public function create($key = '', $config = [])
    {
        switch ($this->type) {
            case 'click': // 点击验证码
                $captcha = new ClickCaptcha($config);
                break;
            case 'sliding': // 滑块验证码
                $captcha = new SlidingCaptcha($config);
                break;
            case 'compute': // 计算验证码
                $captcha = new ComputeCaptcha($config);
                break;
            default:
                $captcha = new ClickCaptcha($config);
        }

        return $captcha->captcha($key);
    }

    /**
     * 验证码校验
     * @param $key string 唯一值
     * @param $code string 验证码内容
     * @param $config array 配置值
     * @return bool
     */
    public function check($key, $code, $config = [])
    {
        switch ($this->type) {
            case 'click': // 点击验证码
                $captcha = new ClickCaptcha($config);
                break;
            case 'sliding': // 滑块验证码
                $captcha = new SlidingCaptcha($config);
                break;
            case 'compute': // 计算验证码
                $captcha = new ComputeCaptcha($config);
                break;
            default:
                $captcha = new ClickCaptcha($config);
        }

        return $captcha->verify($key, $code);
    }

}