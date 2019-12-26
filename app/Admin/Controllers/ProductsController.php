<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Log;

class ProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product);

        $grid->column('id', __('Id'))->sortable();
        $grid->column('title', '商品名称');
        $grid->column('skus', 'SKUs')->display(function ($skus) {
            return sprintf('<span class="label label-info">%s</span>', count($skus));
        });
        $grid->column('price_min', '最低价格');
        $grid->column('price_max', '最高价格');
        $grid->column('rating', '评分')->display(function ($rating) {
            $starHtml = '<i class="fa fa-star" style="color: #ff8913;"></i>';
            $halfStarHtml = '<i class="fa fa-star-half-empty" style="color: #ff8913;"></i>';
            return str_repeat($starHtml, floor($rating)) . str_repeat($halfStarHtml, $rating - floor($rating) >= 0.5 ? 1 : 0);
        });
        $grid->column('is_sale', '已上架')->display(function ($isSale) {
            return $isSale ? "是" : "否";
        });
        $grid->column('review_count', '评论数');
        $grid->column('sold_count', '销量');
        $grid->column('created_at', '创建日期');




        $grid->batchActions(function (Grid\Tools\BatchActions $actions) {
            $actions->disableDelete();
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
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('price_min', __('Price min'));
        $show->field('price_max', __('Price max'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'))->image();
        $show->field('rating', __('Rating'));
        $show->field('is_sale', __('Is sale'));
        $show->field('review_count', __('Review count'));
        $show->field('sold_count', __('Sold count'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        $show->skus('商品SKU', function (Grid $skus) {
            $skus->resource('/admin/product_skus');

            $skus->column('id', 'SkuId');
            $skus->column('product_id', 'ProductId');
            $skus->column('title', 'SKU 标题');
            $skus->column('description', 'SKU 描述');
            $skus->column('price', '价格');
            $skus->column('stock', '库存');
            $skus->column('sold_count', '销量');
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product);

        $form->text('title', '商品标题')->rules(['required', 'min:2', 'max:255']);
        $form->UEditor('description')->rules(['required']);
        // 这里用到了
        $form->image('image', '主图')->uniqueName()->rules(['required', 'image'])->resize(800, 600, function ($contraint) {
//            $contraint->aspectRatio();
        });
//        $form->switch('is_sale', '是否销售')->states([
//            'on' => ['value' => 1, 'text' => '销售', 'color' => 'success'],
//            'off' => ['value' => 0, 'text' => '下架', 'color' => 'warning'],
//        ])->default(1)->rules(['required', 'integer', 'between:0,1']);
        $form->radio('is_sale', '是否销售')->options([1 => '销售', 0 => '下架'])->default(0)->rules(['required', 'integer', 'between:0,1']);
        $form->text('rating', '评分')->default(0)->readonly();
        $form->text('review_count', '评价人数')->default(0)->readonly();
        $form->text('sold_count', '销量')->default(0)->readonly();
        $form->decimal('price_min', '最低售价')->default(0)->readonly();
        $form->decimal('price_max', '最高售价')->default(0)->readonly();

        $form->hasMany('skus', "商品SKUs", function (Form\NestedForm $form) {
            $form->decimal('price', '价格')->rules(['required', 'numeric', 'min:0.01']);
            $form->number('stock', '库存')->default(0)->rules(['required', 'integer', 'min:0']);
            $form->text('description', 'SKU 描述')->rules(['required', 'max:255', 'min:1']);
            $form->text('title', 'SKU 名称')->rules(['required', 'max:255', 'min:1']);
            $form->text('sold_count')->readonly();  // 注意该字段保存的时候要忽略掉, 暂时不知道怎么通过配置来忽略

        });

        $form->ignore(['rating', 'review_count', 'sold_count', 'price_min', 'price_max', 'skus.sold_count']);

        $form->saving(function (Form $form) {
            $skus = $form->input('skus');
            foreach ($skus as $k => &$sku) {
                unset($sku['sold_count']);
            }
            $form->input('skus', $skus);

            $skuCollection = collect($skus)->where(Form::REMOVE_FLAG_NAME, 0);
            $form->model()->price_min = max(0, $skuCollection->pluck('price')->min());
            $form->model()->price_max = max(0, $skuCollection->pluck('price')->max());
        });

        return $form;
    }
}
