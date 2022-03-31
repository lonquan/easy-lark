<h1 align="center"> EasyLark - 飞书开放平台 PHP SDK </h1>

<p align="center"> PHP SDK for Lark Open.</p>

## 文档链接

- [飞书开放平台](https://open.feishu.cn/app)
- [飞书开放 API 列表](https://open.feishu.cn/document/ukTMukTMukTM/uYTM5UjL2ETO14iNxkTN/server-api-list)

## 安装

```shell
$ composer require antcool/easy-lark -vvv
```

## 使用

### 创建实例

```php
<?php

require 'vendor/autoload.php';

// 配置项
$config = [ 
    // 开启 debug 将在 logger_file 写入请求日志
    'debug' => true,
    // 指定日志文件
    'runtime_path' => '/tmp/easy-lark',

    // 应用信息
    'app_id' => '',
    'app_secret' => '',

    // curl 配置
    'http' => [
        'timeout' => 10,
        'base_uri' => 'https://open.feishu.cn',
    ],

    // 事件订阅信息
    'event' => [
        // Encrypt Key, 配置后将会自动解密消息
        'encrypt_key' => '',
        
        // Verification Token, 配置后将会自动校验消息 token 字段
        'verify_token' => '',
        
        // 是否开启请求来源签名验证(依赖 encrypt_key)
        'verify_request' => true,
    ],

    'access_token' => \AntCool\EasyLark\Support\AccessToken::class,
];

$app = new \AntCool\EasyLark\Application($config);

$client = $app->getClient();
$server = $app->getServer();
$config = $app->getConfig();
```

### 在 Laravel 中注册

```php
$config = [
    'debug' => env('APP_DEBUG', false),
    'runtime_path' => storage_path('lark'),

    'app_id' => '',
    'app_secret' => '',

    'http' => [
        'timeout' => 10,
        'base_uri' => 'https://open.feishu.cn',
    ],
    
    'event' => [
        'encrypt_key' => '',
        'verify_token' => '',
        'verify_request' => true,
    ],
    
    'access_token' => \AntCool\EasyLark\Support\AccessToken::class,
]; 

// AppServiceProvider
public function boot()
{
    $this->app->singleton('lark', fn () => new Application($config));
}

$app = app('lark');
```

### 基本用法

```php
try {
    // 发起 API 请求
    $response = $app->getClient()->getJson('uri', $query = []);
    $response = $app->getClient()->postJson('uri', $data = [], $query = []);
    
    // 免登授权
    $response = $app->getClient()->postJson('/open-apis/authen/v1/access_token', [
        'grant_type' => 'authorization_code',
        'code'       => 'dDieky8JXDywpnOlhR8ydf',
    ]);

   
    // 飞书审批 (飞书部分接口使用的 www.feishu.cn 域名, 但是 AccessToken 相同)
    $app->getClient()->postJson('https://www.feishu.cn/approval/openapi/v2/approval/get', [
        'approval_code' => '376DA07B-XXXX-XXXX-XXXX-98B7B907C6B3',
    ]);
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

### 事件订阅

> 需在应用后台订阅, 审批事件订阅后需对 Approval Code 进行订阅

```php
// 订阅指定审批单
$response = app('lark')->getClient()->postJson(
    'https://www.feishu.cn/approval/openapi/v2/subscription/subscribe',
    ['approval_code' => 'approval_code']
);

// 订阅事件处理
$server = $this->app->getServer();

// 方式一: 获取来自飞书服务器的推送事件内容, 你可以自行处理后 return $server->serve()
$event = $server->getRequestEvent();

// 方式二: 事件处理中间件处理, 可注册多个事件处理中间件
$server->with(function (Event $event, \Closure $next) {
    $body = $event->getBody(); // 推送的消息内容
    return $next($event);
});

// 方式三: 指定事件名称处理中间件
$server->addEventListener('approval_instance', function (Event $event, \Closure $next) {
    $body = $event->getBody(); // 推送的消息内容
    return $next($event);
});

// 别忘了调用 $server->serve();
return $server->serve();
```

## TODO

- [x] 事件订阅
- [ ] 消息模板
- [ ] 机器人

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/lonquan/easy-lark/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/lonquan/easy-lark/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any
new code contributions must be accompanied by unit tests where applicable._

## License

MIT
