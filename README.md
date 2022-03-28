<h1 align="center"> EasyLark </h1>

<p align="center"> PHP SDK for Lark.</p>

## Installing

```shell
$ composer require antcool/easy-lark -vvv
```

## Usage

```php
<?php

require 'vendor/autoload.php';

$config = [
    'debug'      => false,
    'app_id'     => '',
    'app_secret' => '',

    'timeout'      => 8,
    'base_url'     => 'https://open.feishu.cn',
    'access_token' => \AntCool\EasyLark\Kernel\Support\AccessToken::class,
    'storage_path' => __DIR__.'/storage',
];

$app = new \AntCool\EasyLark\Application($config);

try {
   // $response = $app->getClient()->getJson('uri', $query = []);
   
    $response = $app->getClient()->postJson('/open-apis/authen/v1/access_token', [
        'grant_type' => 'authorization_code',
        'code'       => 'dDieky8JXDywpnOlhR8ydf',
    ]);

    var_dump($response);
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

## TODO
- [ ] 事件订阅
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
