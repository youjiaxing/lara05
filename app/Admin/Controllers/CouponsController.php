<?php

namespace App\Admin\Controllers;

use App\Models\Coupon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CouponsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Coupon';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Coupon);

        $grid->column('code', '优惠码');
        $grid->column('title', '说明');
        $grid->column('total', '总量')->editable();
        $grid->column('used', '已使用');
        $grid->column('desc', '说明');
        // $grid->column('type_str', '类型');
        // $grid->column('value', '折扣')->display(function ($value) {
        //     if ($this->type == Coupon::TYPE_PERCENT) {
        //         return $value . '%';
        //     } else {
        //         return '￥' . $value;
        //     }
        // });
        // $grid->column('cond_min_amount', '使用条件')->display(function ($amount) {
        //     return '￥' . $amount;
        // });
        // $grid->column('max_discount_amount', '最多优惠');
        $grid->column('not_before', '生效日期')->editable('datetime');
        $grid->column('not_after', '失效日期')->editable('datetime');
        $grid->column('enabled', '启用')->switch();
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(Coupon::findOrFail($id));

    // $show->field('id', __('Id'));
    // $show->field('code', __('Code'));
    // $show->field('title', __('Title'));
    // $show->field('total', __('Total'));
    // $show->field('used', __('Used'));
    // $show->field('type', __('Type'));
    // $show->field('value', __('Value'));
    // $show->field('cond_min_amount', __('Cond min amount'));
    // $show->field('max_discount_amount', __('Max discount amount'));
    // $show->field('not_before', __('Not before'));
    // $show->field('not_after', __('Not after'));
    // $show->field('enabled', __('Enabled'));
    // $show->field('created_at', __('Created at'));
    // $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Coupon);

        $form->text('code', '优惠码(可选)')->rules(function (Form $form) {
            $except = '';
            if ($form->model()->exists) {
                $except = ',' . $form->model()->id;
            }

            return ['nullable', 'between:1,255', 'unique:coupons,code' . $except];
        });
        $form->text('title', '说明')->rules(['required', 'min:1', 'max:255']);
        $form->number('total', '总量')->rules(['required', 'int', 'min:0']);
        // $form->number('used', '已使用')->readonly()->disable()->placeholder(' ');
        $form->radio('type', '类型')->options(Coupon::$TYPE_MAP)->default(Coupon::TYPE_FIXED)->rules(['required']);
        $form->decimal('value', '折扣(固定优惠金额或百分比)')->rules(function (Form $form) {
            if (request()->input('type') == Coupon::TYPE_FIXED) {
                return ['required', 'numeric', 'min:0.01'];
            } else {
                return ['required', 'numeric', 'between:1,99'];
            }
        });
        $form->decimal('cond_min_amount', '订单最低金额')->default(0.00)->rules(['required', 'numeric', 'min:0']);
        $form->decimal('max_discount_amount', '百分比最大优惠金额(可选)')->rules(['nullable', 'numeric', 'min:0.01']);
        $form->datetime('not_before', '生效日期(可选)');
        $form->datetime('not_after', '失效日期(可选)');
        $form->switch('enabled', '是否启用')->default(1)->states([
            'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
        ]);

        $form->saving(function (Form $form) {
            if (empty($form->input('code'))) {
                $form->code = Coupon::genCode();
            }
        });
        return $form;
    }
}
