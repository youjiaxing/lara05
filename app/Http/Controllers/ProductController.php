<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_sale', true);

        // 商品标题|内容|sku标题|sku描述 查询
        $search = $request->input('search', '');
        $search = addslashes($search);
        if (!empty($search)) {
            $searchPattern = '%' . $search . '%';
            $query->where(function (Builder $query) use ($searchPattern) {
                $query->where('title', 'like', $searchPattern)
                    ->orWhere('description', 'like', $searchPattern)
                    ->orWhereHas('skus', function (Builder $query) use ($searchPattern) {
                        $query->where('title', 'like', $searchPattern)
                            ->orWhere('description', 'like', $searchPattern);
                    });
            });

        }

        // 排序
        $sort = "";
        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            if (in_array($sort, [
                'id:desc',
                'price_min:asc',
                'price_max:desc',
                'sold_count:asc',
                'sold_count:desc',
                'rating:asc',
                'rating:desc',
            ])) {
                list($column, $direction) = explode(':', $sort);
                $query->orderBy($column, $direction);
            }
        }

        $products = $query->paginate(16, ['id', 'title', 'image', 'rating', 'review_count', 'price_min', 'price_max', 'sold_count']);
        if (!empty($search)) {
            $products->appends('search', $search);
        }
        if (!empty($sort)) {
            $products->appends('sort', $sort);
        }

//        dump(compact('products', 'sort', 'search'));
//        $products->links();
        return view('products.index', [
            'products' => $products,
            'filters' => [
                'sort' => $sort,
                'search' => $search,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        if (!$product->is_sale || count($product->skus) == 0) {
            throw new InvalidRequestException("商品未上架");
        }

        $isFavor = false;

        if ($user = Auth::user()) {
            /* @var User $user */
            if ($user->favorProducts()->wherePivot('product_id', $product->id)->count() != 0) {
                $isFavor = true;
            }
        }

        return view('products.show', ['product' => $product, 'isFavor' => $isFavor]);
    }

    public function favor(Product $product)
    {
        /* @var User $user */
        $user = Auth::user();
        $user->favorProducts()->syncWithoutDetaching($product);
    }

    public function disfavor(Product $product)
    {
        /* @var User $user */
        $user = Auth::user();
        $user->favorProducts()->detach($product);
    }

    public function favorites()
    {
        /* @var User $user */
        $user = Auth::user();
        $products = $user->favorProducts()->paginate(16);
        return view('products.favorites', ['products' => $products]);
    }
}

;
