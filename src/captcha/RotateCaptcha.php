<?php
namespace clown\captcha\captcha;

use clown\redis\Redis;

/**
 * 旋转验证码
 */
class RotateCaptcha implements Captcha
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
    private $cache_prefix = 'clown:captcha:rotate:';

    /**
     * @var int 最大背景图
     */
    private $max_bj_number = 5;

    private $config = [
        //验证不成功是否删除
        'unset' => true,
        //宽
        'width' => 120,
        //高
        'height' => 120,
        //误差范围 ±6
        'error_range' => 6,
    ];

    private $image = null;

    public function __construct($config = [])
    {
        $this->redis = (new Redis())->getRedis();
        //合并配置内容
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 生成验证码
     * @param $key string 唯一值
     * @return array
     * @throws \RedisException
     */
    public function captcha($key = '')
    {
        //为空自己生成
        $key = empty($key) ? md5(microtime() . mt_rand(1,99999999)) : $key;
        //创建图片
        $this->image = $this->createImage([$this->config['width'], $this->config['height']],[255, 255, 255, 127], true);
        //背景图
        $this->getBgImage();
        //旋转角度
        $rotate = mt_rand(45, 315);

        $this->image = imagerotate($this->image, $rotate, 0);
        //获取圆形图片
        $this->getRotateImage();

        // 输出图像
        imagepng($this->image);

        $content = ob_get_clean();

        imagedestroy($this->image);
        $base64 = 'data:image/png;base64,' . base64_encode($content);

        //写入缓存
        $value = 360 - $rotate; // 因为旋转是从左往右滑动, 需要用圆的角度减去旋转的角度, 剩下的就是需要用户旋转的角度
        $this->redis->set($this->cache_prefix . $key, $value, $this->expire);

        return [
            'key' => $key,
            'base64' => $base64
        ];
    }

    /**
     * 验证码验证
     * @param $key string 唯一值
     * @param $code string 验证值
     * @return bool
     * @throws \RedisException"
     */
    public function verify($key, $code)
    {
        $key = $this->cache_prefix . $key;
        //验证码不存在
        if(!$this->redis->exists($key)) return false;
        //获取值
        $value = $this->redis->get($key);
        //最小值
        $min = $value - $this->config['error_range'];
        //最大值
        $max = $value + $this->config['error_range'];

        if($code > $min && $code < $max){
            $this->redis->del($key);
            return true;
        }

        if($this->config['unset']) $this->redis->del($key);

        return false;
    }

    /**
     * 获取当前文件路径
     * @param $splice string 要拼接的路径
     * @return string
     */
    public function getFilePath($splice = '')
    {
        $path = __FILE__;

        $filepath = rtrim($path, basename($path)) . $splice;

        return $filepath;
    }

    /**
     * 获取圆形图片
     * @return void
     */
    public function getRotateImage()
    {
        $width = imagesx($this->image);

        $rotate_image = $this->image; //旋转后的图片

        $bg_width = $this->config['width'];
        $bg_height = $this->config['height'];

        $circle = $this->createImage([$bg_width, $bg_height], [255, 255, 255, 127], $alpha = false);

        $this->image = $this->createImage([$bg_width, $bg_height], [255, 255, 255, 127], $alpha = true);

        $s_r = ($width - $bg_width) / 2;

        $r = (int) ($bg_width / 2) - 2; //半径

        for ($x = 0; $x < $bg_width; $x++) {
            for ($y = 0; $y < $bg_height; $y++) {
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    $rgb_color = imagecolorat($rotate_image, ceil($x + 2 + $s_r), ceil($y + 2 + $s_r));
                    imagesetpixel($circle, $x + 2, $y + 2, $rgb_color);
                }
            }
        }

        imagecopyresampled($this->image, $circle, 0, 0, 2, 2, $bg_width, $bg_height, $bg_width - 3, $bg_height - 3);

        imagedestroy($rotate_image);
        imagedestroy($circle);

    }

    /**
     * 获取背景图片
     * @return void
     */
    public function getBgImage()
    {
        //获取随机背景图片
        $bg_image_path = $this->getFilePath('rotate/rotate0'. mt_rand(1, $this->max_bj_number). '.jpg');

        list($width, $height, $type) = @getimagesize($bg_image_path);
        //获取背景图片类型
        $type = image_type_to_extension($type, false);
        //
        $pic = ('imagecreatefrom' . $type)($bg_image_path);

        imagecopyresized($this->image, $pic, 0, 0, 0, 0, $this->config['width'], $this->config['height'], $width, $height);

        imagedestroy($pic);
    }

    /**
     * 创建画布
     * @param $rgba
     * @param $alpha
     * @return false|\GdImage|resource
     */
    public function createImage($wh,$rgba = [], $alpha = false)
    {
        $cut = imagecreatetruecolor($wh[0], $wh[1]);

        $color = $this->createColorAlpha($cut, $rgba);

        imagesavealpha($cut, $alpha);

        imagefill($cut, 0, 0, $color);

        return $cut;
    }

    /**
     * 获取颜色值，可设置透明度
     */
    public function createColorAlpha($cut, $rgba = [255, 255, 255, 127])
    {

        if (empty($rgba)) $rgba = [255, 255, 255, 127];

        return imagecolorallocatealpha($cut, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
    }

}