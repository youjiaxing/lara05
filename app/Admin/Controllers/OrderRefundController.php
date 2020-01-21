<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderRefund;
use App\Services\OrderRefundService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class OrderRefundController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\OrderRefund';

    public function reject(OrderRefund $orderRefund, Request $request, OrderRefundService $service)
    {
        $rejectReason = $request->input('reject_reason', "");

        $service->rejectRefund($orderRefund, $rejectReason);

        return $orderRefund;
    }

    public function accept(OrderRefund $orderRefund, OrderRefundService $service)
    {
        if (!in_array($orderRefund->status, [OrderRefund::STATUS_CREATED, OrderRefund::STATUS_RETURN])) {
            throw new InvalidRequestException("退款单状态出错");
        }

        $service->acceptRefund($orderRefund);

        return $orderRefund;
    }

    // /**
    //  * Make a grid builder.
    //  *
    //  * @return Grid
    //  */
    // protected function grid()
    // {
    //     $grid = new Grid(new OrderRefund());
    //
    //     $grid->column('id', __('Id'));
    //     $grid->column('no', __('No'));
    //     $grid->column('order_id', __('Order id'));
    //     $grid->column('user_id', __('User id'));
    //     $grid->column('amount', __('Amount'));
    //     $grid->column('status', __('Status'));
    //     $grid->column('type', __('Type'));
    //     $grid->column('refunded_at', __('Refunded at'));
    //     $grid->column('refund_no', __('Refund no'));
    //     $grid->column('user_remark', __('User remark'));
    //     $grid->column('reject_reason', __('Reject reason'));
    //     $grid->column('extra', __('Extra'));
    //     $grid->column('created_at', __('Created at'));
    //     $grid->column('updated_at', __('Updated at'));
    //
    //     return $grid;
    // }
    //
    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(OrderRefund::findOrFail($id));
    //
    //     $show->field('id', __('Id'));
    //     $show->field('no', __('No'));
    //     $show->field('order_id', __('Order id'));
    //     $show->field('user_id', __('User id'));
    //     $show->field('amount', __('Amount'));
    //     $show->field('status', __('Status'));
    //     $show->field('type', __('Type'));
    //     $show->field('refunded_at', __('Refunded at'));
    //     $show->field('refund_no', __('Refund no'));
    //     $show->field('user_remark', __('User remark'));
    //     $show->field('reject_reason', __('Reject reason'));
    //     $show->field('extra', __('Extra'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));
    //
    //     return $show;
    // }
    //
    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     $form = new Form(new OrderRefund);
    //
    //     $form->text('no', __('No'));
    //     $form->number('order_id', __('Order id'));
    //     $form->number('user_id', __('User id'));
    //     $form->decimal('amount', __('Amount'));
    //     $form->text('status', __('Status'))->default('created');
    //     $form->text('type', __('Type'));
    //     $form->datetime('refunded_at', __('Refunded at'))->default(date('Y-m-d H:i:s'));
    //     $form->text('refund_no', __('Refund no'));
    //     $form->text('user_remark', __('User remark'));
    //     $form->text('reject_reason', __('Reject reason'));
    //     $form->text('extra', __('Extra'));
    //
    //     return $form;
    // }
}
