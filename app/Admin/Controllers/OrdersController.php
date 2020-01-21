<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        // 添加数据查询
        $grid->model()->whereNotNull('paid_at');

        $grid->column('id', 'Id')->sortable();
        $grid->column('no', '订单流水号');
        $grid->column('status', '订单状态')->display(function ($value) {
            return $this->status_map;
        });

        // 这样写没有 n+1 的问题
        $grid->column('user.name', '买家');

        // 这样写会有 n+1 的问题
        // $grid->column('user_id', '买家')->display(function ($value) {
        //     return $this->user->name;
        // });
        $grid->column('paid_amount', '总金额');
        $grid->column('paid_at', '付款时间');
        $grid->column('payment_method', '付款方式');
        $grid->column('refund_status', '退款状态')->display(function ($value) {
            return $this->refund_status_map;
        });
        $grid->column('express_status', '物流状态')->display(function ($value) {
            return $this->express_status_map;
        });

        // 禁止创建按钮
        $grid->disableCreateButton();

        // 禁止批量删除
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        // 禁止单个删除
        // 禁止单个编辑
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $order = Order::query()->with('orderItems.productSku.product')->findOrFail($id);
        return view('admin.orders.show', ['order' => $order]);
    }

    public function express(Order $order, Request $request)
    {
        if (!$order->isPaid()) {
            throw new InvalidRequestException("订单未支付");
        }

        $validatedData = $request->validate([
            'express_company' => 'required|string',
            'express_no' => 'required|min:5',
        ],[],[
            'express_company' => '物流公司',
            'express_no' => '物流单号',
        ]);

        $expressData = [];
        // Log::debug(var_export($expressData,true));
        $expressData['company'] = $validatedData['express_company'];
        $expressData['no'] = $validatedData['express_no'];
        $order->express_data = $expressData;
        // 标记物流发货状态
        if ($order->express_status == Order::EXPRESS_STATUS_PENDING) {
            $order->express_status = Order::EXPRESS_STATUS_DELIVERED;
        }
        // 标记订单发货状态
        if ($order->status == Order::ORDER_STATUS_PAID) {
            $order->status = Order::ORDER_STATUS_DELIVERED;
        }
        $order->save();

        // return redirect()->route('admin.orders.show', [$order->id]);
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->number('no', __('No'));
        $form->number('user_id', __('User id'));
        $form->text('status', __('Status'))->default('created');
        $form->decimal('amount', __('Amount'));
        $form->decimal('paid_amount', __('Paid amount'));
        $form->text('address', __('Address'));
        $form->text('remark', __('Remark'));
        $form->datetime('paid_at', __('Paid at'))->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', __('Payment method'));
        $form->text('payment_no', __('Payment no'));
        $form->text('express_status', __('Express status'))->default('pending');
        $form->text('express_data', __('Express data'));
        $form->text('refund_status', __('Refund status'))->default('pending');
        $form->text('refund_no', __('Refund no'));
        $form->decimal('refund_amount', __('Refund amount'))->default(0.00);
        $form->text('extra', __('Extra'));

        return $form;
    }
}
