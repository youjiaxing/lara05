<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Gateways\Alipay;

class PaymentController extends Controller
{
    /**
     * 跳转到支付宝 - 支付页面
     *
     * @param Order  $order
     * @param Alipay $alipay
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws InvalidRequestException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function alipay(Order $order, Alipay $alipay)
    {
        // 权限验证
        $this->authorize('own', $order);

        // 判断当前订单状态
        if ($order->isClose() || $order->isPaid()) {
            throw new InvalidRequestException("订单状态错误");
        }

        // 相关官方文档: https://docs.open.alipay.com/api_1/alipay.trade.page.pay
        $resp = $alipay->web([
            'out_trade_no' => $order->no,
            'total_amount' => $order->paid_amount,  // 单位"元", 精确到小数后2位
            'subject' => config('app.name') . " 订单 " . $order->no,
            // 'timeout_express' => max(1,$order->created_at->copy()->addSeconds(config('shop.order_ttl'))->subMinutes(1)->diffInMinutes($order->created_at)) . "m",  // 过期时间, 分钟
            'timeout_express' => min(60, max(1,$order->created_at->copy()->addSeconds(config('shop.order_ttl'))->subMinutes(1)->diffInMinutes($order->created_at))) . "m",  // 过期时间, 分钟
        ]);

        return $resp;
    }

    // 支付宝 - 前端回调地址
    // 相关文档: https://docs.open.alipay.com/59/103665/
    public function alipayReturn(Alipay $alipay)
    {
        /**
         * @var Collection $data = [▼
         *      "charset" => "utf-8",
         *      "out_trade_no" => "200115102863354806",  // 我方订单号
         *      "method" => "alipay.trade.page.pay.return"
         *      "total_amount" => "95756.00",
         *      "sign" => "OX4o95OOd9Q/5onMSuQzPYNBdK+TXekP6GqbbAmp5+g/MQzM9p2UjxUS0BIFK6DmMQ9Yc+IjawCs4C7fCYbTLSjll/qNQvSA+EDSo2MGGY2XhIM1RN5ty8TwiyNr2BRtF+Dhdwk8COaim1vBymEK+iNSuaHYCAYy ▶",
         *      "trade_no" => "2020011522001402511000109615",    //第三方交易订单号
         *      "auth_app_id" => "2016093000630075",
         *      "version" => "1.0",
         *      "app_id" => "2016093000630075",
         *      "sign_type" => "RSA2",
         *      "seller_id" => "2088102177924410",  // 商户UID
         *      "timestamp" => "2020-01-15 10:57:32",
         * ]
         */
        try {
            $data = $alipay->verify();
        } catch (\Throwable $e) {
            view('payments.finish', ['success'=>false, 'msg' => '支付验证失败']);
        }

        $order = Order::query()->where(['no' => $data['out_trade_no']])->firstOrFail();

        // 显示支付结果页 - 延迟3s跳转
        return view('payments.finish', ['delay' => 3, 'order' => $order, 'success'=>true]);
    }

    // 支付宝 - 后端异步回调地址
    // 相关文档: https://docs.open.alipay.com/59/103666/
    public function alipayNotify(Alipay $alipay, OrderService $orderService)
    {
        /**
         * @var Collection $data = [
         *     "gmt_create" => "2020-01-15 12:19:17",
         *     "charset" => "utf-8",
         *     "gmt_payment" => "2020-01-15 12:19:25",
         *     "notify_time" => "2020-01-15 12:19:26",
         *     "subject" => "Laravel Shop 订单 200115121906815826",
         *     "sign" => "e9SUSkBE+AgO9LFIjMScTnUxKFhWHDm0A1UCJKC8L3WpeO/pchU2cFUPQuvV0WKU8+tRpw8Ga1gn1BQ6awH6Ls/nABdg8CBdHN4SGZX0dGjaLtcKzOmCfDsttzkz7PgqDXO67UeCgQwaPblP0rdXcpWIGSoQdRJcFNT5RAJeMaJrLX8XyqeHHMChHP9ftm7SBAeyNVdR5pFuhukMpvcJE47VgQmLLw3kQvqAMImH+syx3i+W2YjWxsYrANLohs0vtaYcTAf0i0PMPEOK2xQ9y+w/RSvheGNo8JIGwNJldMa5kN5RZAmNM8gFYJFGBXCByirXMBycE0iuRy6lewHCXg==",
         *      "buyer_id" => "2088102178102518",
         *      "invoice_amount" => "34.89",
         *      "version" => "1.0",
         *      "notify_id" => "2020011500222121925002511000631770",
         *      "fund_bill_list" => "[{\"amount\":\"34.89\",\"fundChannel\":\"ALIPAYACCOUNT\"}]",
         *      "notify_type" => "trade_status_sync",
         *      "out_trade_no" => "200115121906815826",         // 我方订单号
         *      "total_amount" => "34.89",
         *      "trade_status" => "TRADE_SUCCESS",              // 交易状态, 所有通知交易状态: https://docs.open.alipay.com/59/103672
         *      "trade_no" => "2020011522001402511000110907",   // 支付宝订单号
         *      "auth_app_id" => "2016093000630075",
         *      "receipt_amount" => "34.89",
         *      "point_amount" => "0.00",
         *      "app_id" => "2016093000630075",
         *      "buyer_pay_amount" => "34.89",
         *      "sign_type" => "RSA2",
         *      "seller_id" => "2088102177924410"
         * ]
         */
        $data = $alipay->verify();

        // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
        // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
        // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
        // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
        // 4、验证app_id是否为该商户本身。
        // 5、其它业务逻辑情况

        try {
            if ($data['app_id'] != config('pay.alipay.app_id')) {
                throw new \Exception("APPID 错误");
            }

            if (!in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                throw new \Exception("支付状态错误");
            }

            /* @var Order $order*/
            $order = Order::query()->where('no', $data['out_trade_no'])->first();
            if (!$order) {
                throw new \Exception("订单不存在");
            }

            if ($order->paid_amount != (float)$data['total_amount']) {
                throw new \Exception('订单金额不一致, 实际需支付金额为:' . $order->paid_amount);
            }

            // TODO 可能需要处理重复支付退款的问题
            // 包括:
            // 1. 在其他渠道支付
            // 2. 订单因为其他原因关闭

            $orderService->paid($order, Order::PAYMENT_ALIPAY, $data['trade_no'], Carbon::parse($data['gmt_payment']));
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e, 'alipay_data' => $data]);
        }

        return $alipay->success();
    }
}
