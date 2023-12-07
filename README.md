#### 1. 验证码类库

##### 	1. 安装

```
composer require clown/captcha dev-main
```

##### 	2. 示例代码

```php
<?php

// 创建验证码 支持如下类型
//number = 数字验证码 
//compute = 计算验证码 
//click = 点选验证码 
//rotate = 选择验证码    
//sliding = 滑块验证码    
$captcha = new Captcha('compute');

$result = $captcha->create(md5(microtime()),['length' => 4]);

echo json_encode(['code' => 0, 'msg' => '获取成功', 'data' => $result], JSON_UNESCAPED_UNICODE);

            
```

##### 	3. 校验验证码

```php
<?php
// 校验验证码
$key = $this->request->get('key', '');

$code = $this->request->get('code', '');
//number = 数字验证码 
//compute = 计算验证码 
//click = 点选验证码 
//rotate = 选择验证码    
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

