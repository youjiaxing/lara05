<?php

namespace Illuminate\Http {

    class Request
    {
        /**
         * 使用 validator 验证数据
         *
         * @param array $rules
         * @param array $messages
         * @param array $customAttributes
         *
         * @return array
         *
         * @link \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         */
        public function validate(array $rules = [], array $messages = [], array $customAttributes = []);
    }
}

namespace PHPSTORM_META {

    // 使用容器生成对象
    override(\app(), map([
        "alipay" => \Yansongda\Pay\Gateways\Alipay::class,
        "wechat" => \Yansongda\Pay\Gateways\Wechat::class,
    ]));
}
