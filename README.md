#### 验证码类库

##### 	1. 安装

```
composer require clown/captcha
```

##### 	2. 创建验证码

```php
<?php
    //实例化验证码类, 可以传参验证码类型 不传默认点选验证码
    $captcha = \clown\captcha\Captcha();
	//使用创建验证码, 可以传参字符串, 不传会自动返回生成
	$key = md5(microtime());
	$result = $captcha->create($key);
	//$result为点选验证码时返回内容
	//[
	//	"key" => "11d4a596-4e0e-4798-9425-026700292d0f" // 生成的唯一值
    //	"text" => array:2 [
    //    	0 => "<梨>"
    //    	1 => "站"
    //	]// 点选内容带<>是点击图标 不带是点击文字 
    //	"base64" => "data:image/png;base64," // 返回的图片内容
    //	"width" => 350 // 图片宽
    //	"height" => 200 // 图片高
	//]
            
```

##### 	3. 校验验证码

```php
<?php
    //实例化验证码类, 可以传参验证码类型 不传默认点选验证码
    $captcha = \clown\captcha\Captcha();
	//使用check方法进行校验, 需要传入创建时候的key 和验证码内容, 校验成功返回true, 否则返回false
	//点选验证码为: 200,117-246,106;350;200 200,117第一个点击点的x和y坐标, 246,106第二个点击点的x和y坐标, 350是宽, 200是高
	$result = $captcha->check($key, $info)
	
            
```

