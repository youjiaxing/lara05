<?php
return [
    // 支付宝支付
    'alipay' => [
        'app_id' => '2016093000630075',
        // 异步回调url
        'notify_url' => '',
        // 前端回调url
        'return_url' => '',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAn55NyypZRVYW3jYRtiO93tl7XK+Z7iXc7iCmMmchIuGkHOs/1Afa6vCGhTxocgqeWNOmpjmxi8O3uKuDNjOtl4Idz+1Fvb4SY7wHivcZMyJVDOqp0JxhqOxwwEJzpOXPWEQuXPo47FigIwziPzi8HRJ7F0GphVTT7bpHZIzwXfUoF3mkfmHvwO+bOYf6uskyHEfdYzILQzDX0eP0cTCofvYJDJjSG+UhNJY4JukONwHLmeeZe+fiolZ2PKeKNHDbf5hXP588EhMNYBPH6PFScIJWnU3u4R5l292EYFDSLtAFZ6IROB5g10KNCCkN7h9oujdNF7C7nyBws9i739PMywIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIEowIBAAKCAQEAiY/RFjkNMQ99HeMgmfkdj6MHmkHlbBcgsHoX0kNvzQ+49bNAC/F3nPU0EzClP9p7APXkaWjX1+PTNQm8FoLQNaEecZGWQ3r5lIl9dJNn/4pbXjjX/vYBOrB/SFx1OO6QlShFsp0sax4DdFFnkeZeY7/M2Jz/7ntxvRuxfKTWzHH31NXs8lzLUVBZdW1ibYSkgL/Q5RjYPPkiykdtAz+lJgsN86XzxbVGJ+IX5/DbcrbYUNUY1qCP2jarxiIeXBipm5ggnen7rYyd/a/Gu2IXodaNwlzvIX17iwgvgTSzXuq5OS0RNutYWh8vSNUZZctMQz3P/V+E/rWu8yBC6ItDXQIDAQABAoIBACNxkNf+/y0i5oPq352MmFdIFE9kpy5Aj0WpT7X3djkJ0ghMlTX+k2k3rKE9KXbYXlUTBjU6tFKF+wdxaKsMLYWD7AorDuJ4LxJckpyU1nj7NyuBzfV9gTeT/lewqutuXIZyB/NLmARJiW5RAzSOfMeKxccSPMc/u0CUgBiziDzIxBzegWl5vv4iLWtx21HJpGIGOiF6CEqu9CUgKmL8nkbBLnAY081TD9K37KwaqfyF7Onfb2mgzLiR3rZ5wZqTtLzNdM4SC7XA96/jLgqi3JuwxyygAWr5CizTdtvZJHQTftMl7A1Q1a/WlCgyASkC55IBuzkYJy1Vq5qTF7H4HIECgYEA+IZbrLFBCO92iMyBOdF85pcX6mptLnlHbPBf/fBfB9IK7y7QQvggDhgPURX3QroOSTUuRRg3FK/M9lnPK/fQABrkYNZ9Njg6y1mR6GE/lteKMF/Xr1wbJdwE7ItnFWyzOpMRy56VaZQyrs61GDiaG05GOYaCPzjBsgdZcnZKcA0CgYEAjbMK9//uXJi2zRZz7KPWpkw4WNq5lX/JYOP/B+9D7UtGuDv6G4+X/bpC6bBb7RYrbtG5Ta27drfSLOHulU0hCwHd1zGoHOBu8H6JLPJknUDVEabv4yfrnFVjr+IvfQ9LnTujZRRtDWrdrMtpwUZbinNbBZvGQTudriS1d3Ap/JECgYA6RRZdiTncZHkAXiK2cb29OxPqbIWo7dqnWjzHh4JgjQjvR6Sg7xhk9ZMxydqtlH4hAA0XOjQ/73A5GpmOj43/WE7Pvbqh6dEvOJMTTynfri3CZdZmUUw0NkAZTh4fUds8EpuLiGUz2gGAJwsi4LKRUGr6teT3+dDAcySvx7hwSQKBgCy9rKfzqnqYSZfmEAJXFQ57IIuFvHVk3Nv4AW2q8aK6UwEXBviu1UnrBveslMn+ZwZduAm74mYw0m8Tg3am3NkR5M7uweskPkM3YO7NNlGkx+ID+NiboTxChfBiFaaCLtjdnNDsqyZCaO4HaEP7iCnmFIZn0iiEv1veAWJOCyPRAoGBAMnKXetG7yTABRxIsA8Nio/IXXKWIKaKDeeAjrnbJ94LEMnlLKfFwxyfw1Cgatjq//G+Cifvjrqxl8QRSOmJj4LI4h/K+gUrj7FLqQWBPZqYj61DlvvkVhF6gD16bXAH2/4pyR+TCwmTQh0vrDFjYPIhVR81yU1d4bYJA52j6hC2',
        // 使用公钥证书模式，请配置下面两个参数，同时修改ali_public_key为以.crt结尾的支付宝公钥证书路径，如（./cert/alipayCertPublicKey_RSA2.crt）
        // 'app_cert_public_key' => './cert/appCertPublicKey.crt', //应用公钥证书路径
        // 'alipay_root_cert' => './cert/alipayRootCert.crt', //支付宝根证书路径
        'log' => [ // optional
            'file' => storage_path('logs/alipay/log'),
            // 注册到容器时配置根据当前环境被重写
            'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => env('PAY_ALIPAY_MODE'), // optional,设置此参数，将进入沙箱模式
    ],

    // 微信支付
    'wechat' => [
        'appid' => '...', // APP APPID
//        'app_id' => '...', // 公众号 APPID
//        'miniapp_id' => '...', // 小程序 APPID
        'mch_id' => '...',
        'key' => '...',
        // 异步回调url, 微信没有前端回调
        'notify_url' => 'http://yanda.net.cn/notify.php',
        'cert_client' => '...', // optional，退款等情况时用到
        'cert_key' => '...',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => storage_path('logs/wechat.log'),
            // 注册到容器时配置根据当前环境被重写
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => env('PAY_WECHAT_MODE'), // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ],
];
