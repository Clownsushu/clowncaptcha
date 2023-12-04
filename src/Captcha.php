<?php
namespace clown\captcha;


use clown\redis\Redis;

class Captcha
{
    /**
     * @var string 验证码类型 默认点击验证码click=点击, sliding = 滑块
     */
    public $type = 'click';

    public function __construct($type = 'click')
    {
        $this->type = $type;
    }

    public function create($key, $width = 120, $height = 40)
    {
        switch ($this->type) {
            case 'click':
                return new ClickCaptcha($key, $width, $height);
                break;
        }
    }

    public function check($key, $code)
    {
        switch ($this->type) {
            case 'click':
                return new ClickCaptcha($key);
        }
    }

}