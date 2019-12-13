<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserAddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // TODO 授权策略控制
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('user_addresses.index', [
            'addresses' => $request->user()->addresses()->orderBy("last_used_at", "desc")->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user_addresses.edit_or_create', ['address' => new UserAddress()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserAddressRequest $request)
    {
        $userAddress = new UserAddress();
        $userAddress->user_id = Auth::id();
        $userAddress->fill($request->validated());
        $userAddress->save();

        return redirect()->route('user_addresses.index')->with('notice.success', '创建地址成功');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserAddress  $userAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAddress $userAddress)
    {
        $this->authorize('own', $userAddress);

        return view('user_addresses.edit_or_create', ['address' => $userAddress]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserAddress  $userAddress
     * @return \Illuminate\Http\Response
     */
    public function update(UserAddressRequest $request, UserAddress $userAddress)
    {
        $this->authorize('own', $userAddress);

        $userAddress->fill($request->validated());
        $userAddress->save();
        return redirect()->route('user_addresses.index')->with("notice.success", "更新地址成功");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserAddress  $userAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAddress $userAddress)
    {
        $this->authorize('own', $userAddress);

        $userAddress->delete();
        return session()->flash('notice.success', '删除地址成功');
    }
}
