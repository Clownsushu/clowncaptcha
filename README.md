#### 1. 验证码类库

##### 			1. 安装

```shell
composer require clown/captcha dev-main
```

##### 		2. 需要准备的

```
1. 需要使用redis拓展
2. 并在.env目录下至少配置如下参数
REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379
REDIS_PASS =
REDIS_SELECT = 0
```

##### 			3. 示例代码

```php
<?php

// 创建验证码 支持如下类型
//number = 数字验证码 
//compute = 计算验证码 
//click = 点选验证码 
//rotate = 选择验证码    
//sliding = 滑块验证码    暂时未完成
$captcha = new Captcha('compute');

$result = $captcha->create(md5(microtime()),['length' => 4]);

//$result返回的固定参数有: key => 唯一值, base64 => 图片base64后的数据, 额外返回的有点选验证码, 增加了图片宽高参数

echo json_encode(['code' => 0, 'msg' => '获取成功', 'data' => $result], JSON_UNESCAPED_UNICODE);

            
```

##### 			4. 校验验证码

```php
<?php
// 校验验证码
$key = $this->request->get('key', '');

$code = $this->request->get('code', '');
//number = 数字验证码  需要提交的参数格式:  9662 验证码数字 
//compute = 计算验证码 需要提交的参数格式:  15 计算的结果 
//click = 点选验证码 需要提交的参数格式: 200,117-246,106;350;200 200,117第一个点击点的x和y坐标, 246,106第二个点击点的x和y坐标, 350是宽, 200是高
//rotate = 旋转验证码 需要提交的参数格式:  32 旋转的角度, 思路: 一个固定长度的div, 等比分成360份, 每往右滑动一份, 图片逆时针旋转角度+1
//sliding = 滑块验证码  
$captcha = new Captcha('compute');
//$result = true 验证成功
//$result = false 验证失败
$result = $captcha->check($key, $code,['unset' => false]);
	
            
```



#### 2. 验证码使用

1. 数字验证码

    ![image-20231206152756951](/Users/sushu/Library/Application Support/typora-user-images/image-20231206152756951.png)

2. 计算验证码

    ![image-20231206153300361](/Users/sushu/Library/Application Support/typora-user-images/image-20231206153300361.png)

3. 点选验证码

    ![image-20231206162032176](/Users/sushu/Library/Application Support/typora-user-images/image-20231206162032176.png)

4. 旋转验证码

    ​                                                     ![image-20231207102336137](/Users/sushu/Library/Application Support/typora-user-images/image-20231207102336137.png)  

5. 滑块验证码



#### 3. 部分前端js

1. 旋转验证码

```javascript
<image ref="image" :src="base64" :style="transformStyle"  class="rotate-image" alt="" @touchmove="handleTouchMove" />
handleTouchMove(event) {
      const containerWidth = this.$refs.container.$el.offsetWidth; // 获取容器宽度
      const touchX = event.touches[0].clientX; // 获取手指触摸点的X坐标

      const touchPercentage = (touchX / containerWidth) * 100; // 计算触摸点的百分比
      const rotationAngle = (touchPercentage / 360) * 360; // 根据触摸点的百分比计算旋转角度
      this.rotationAngle = Math.round(rotationAngle); // 更新旋转角度
      this.code = this.rotationAngle
      this.transformStyle = `transform: rotate(-${rotationAngle}deg);`; // 设置图片旋转样式
},
```

