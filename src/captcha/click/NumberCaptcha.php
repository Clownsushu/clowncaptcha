<?php
namespace clown\captcha\captcha;

use clown\redis\Redis;

/**
 * 数字验证码
 */
class NumberCaptcha implements Captcha
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
    private $cache_prefix = 'clown:captcha:number:';

    /**
     * @var int[] 背景颜色
     */
    protected $bg = [190, 251, 254];

    /**
     * @var array|string[] 字体文件路径
     */
    private $font_path = [
        'click/fonts/SourceHanSansCN-Normal.ttf'
    ];

    /**
     * @var array 配置内容
     */
    private $config = [
        // 透明度
        'alpha' => 60,
        //宽
        'width' => 200,
        //高
        'height' => 80,
        //字体大小
        'size' => 12,
        //验证不成功是否删除
        'unset' => true,
        //长度
        'length' => 4,
    ];

    /**
     * @var null 当前图片实例
     */
    private $image = null;

    /**
     * @var null 当前图片颜色
     */
    private $color = null;

    public function __construct($config = [])
    {
        $this->redis = (new Redis())->getRedis();
        //合并配置内容
        $this->config = array_merge($this->config, $config);

        if($this->config['length'] <= 0) {
            throw new \Exception('验证码长度必须大于0');
        }
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
        //获取字体路径
        $fontPath = $this->getFilePath($this->font_path[mt_rand(0, count($this->font_path) - 1)]);
        //获取随机验证码
        $rands = $this->getRandNumber();
        //将结果写入缓存
        $this->redis->set($this->cache_prefix . $key, implode('', $rands), $this->expire);

        // 创建图片的实例
        $this->image = imagecreate($this->config['width'], $this->config['height']);
        // 设置背景
        imagecolorallocate($this->image, $this->bg[0], $this->bg[1], $this->bg[2]);
        // 验证码字体随机颜色
        $this->color = imagecolorallocate($this->image, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        // 画干扰线
        $this->writeCurve();
        //绘制验证码
        $i = 0;
        $count = count($rands);
        $width = (int) $this->config['width'] / $count;

        foreach ($rands as $k => $v){
            $size = mt_rand(20, 22);
            $x     = $k == 0 ? 10 : $width * $k;
            $y     = $size + mt_rand(20, 30);
            $angle = $i == 1 || $i == 3 ? 1 : mt_rand(1, 20);
            imagettftext($this->image, $size, $angle, (int)$x, (int)$y, $this->color, $fontPath, $v);
            $i++;
        }
        ob_start();
        // 输出图像
        imagepng($this->image);
        $content = ob_get_clean();
        imagedestroy($this->image);
        $base64 = 'data:image/png;base64,' . base64_encode($content);

        return [
            'key' => $key,
            'base64' => $base64
        ];
    }

    /**
     * 验证码校验
     * @param $key string 唯一值
     * @param $code string 验证码
     * @return bool
     * @throws \RedisException
     */
    public function verify($key, $code)
    {
        // TODO: Implement verify() method.
        $key = $this->cache_prefix . $key;
        //验证码不存在
        if(!$this->redis->exists($key)) return false;
        //获取值
        $value = $this->redis->get($key);

        if($value == $code) {
            $this->redis->del($key);
            return true;
        }

        if($this->config['unset']) $this->redis->del($key);

        return false;
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *
     *      高中的数学公式咋都忘了涅，写出来
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    protected function writeCurve(): void
    {
        $px = $py = 0;

        // 曲线前部分
        $A = mt_rand(1, (int)($this->config['width'] / 2)); // 振幅
        $b = mt_rand((int)(-$this->config['height'] / 4), (int)($this->config['height'] / 4)); // Y轴方向偏移量
        $f = mt_rand((int)(-$this->config['height'] / 4), (int)($this->config['height'] / 4)); // X轴方向偏移量
        $T = mt_rand((int)$this->config['height'], (int)$this->config['width'] * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand((int)($this->config['width'] / 2), (int)($this->config['width'] * 0.8)); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->config['height'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->config['size'] / 5);
                while ($i > 0) {
                    imagesetpixel($this->image, (int)($px + $i), (int)($py + $i), $this->color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, (int)($this->config['height'] / 2)); // 振幅
        $f   = mt_rand((int)(-$this->config['height'] / 4), (int)($this->config['height'] / 4)); // X轴方向偏移量
        $T   = mt_rand((int)$this->config['height'], (int)$this->config['width'] * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->config['height'] / 2;
        $px1 = $px2;
        $px2 = $this->config['width'];

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->config['height'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->config['size'] / 5);
                while ($i > 0) {
                    imagesetpixel($this->image, (int)($px + $i), (int)($py + $i), $this->color);
                    $i--;
                }
            }
        }
    }

    /**
     * 生成固定长度的验证码
     * @return array
     */
    public function getRandNumber()
    {
        $rands = [];

        for ($i = 1; $i <= $this->config['length']; $i++){
            $rands[] = mt_rand(0, 9);
        }

        return $rands;
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

}