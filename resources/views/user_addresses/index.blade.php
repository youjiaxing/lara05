@extends("layouts.app")

@section("title", Auth::user()->name . "的收货地址")

@section("content")
    <div class="card">
        <div class="card-header">
            <p class="float-left mb-0">收货地址列表</p>
            <a href="{{ route('user_addresses.create') }}" class="float-right">新增收货地址</a>
        </div>
        <div class="card-body">
            @include("common._message")

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">联系人</th>
                    <th scope="col">电话</th>
                    <th scope="col">邮编</th>
                    <th scope="col">地址</th>
                    <th scope="col">上一次使用</th>
                    <th scope="col">操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach($addresses as $address)
                    <tr>
                        <td>{{ $address->contact_name }}</td>
                        <td>{{ $address->contact_phone }}</td>
                        <td>{{ $address->zip }}</td>
                        <td>{{ $address->fullAddress }}</td>
                        <td>{{ $address->last_used_at }}</td>
                        <td>
                            <a type="button" href="{{ route('user_addresses.edit', [$address->id]) }}" class="btn btn-primary btn-sm edit_btn"
                               data-id="{{ $address->id }}">@lang("edit")</a>
                            <button type="button" class="btn btn-danger btn-sm delete_btn" data-id="{{ $address->id }}">@lang("delete")</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


@stop

@section("script")
    <script>
        $('.delete_btn').on('click', function (event) {
            var id = $(this).data('id');

            swal({
                text: "是否删除该地址?",
                icon: "warning",
                buttons: true,
                dangerMode: true
            }).then(function (confirm) {
                if (confirm) {
                    axios.delete('/user_addresses/' + id)
                        .then(function (response) {
                            console.log(response);
                            swal({
                                text: "删除成功",
                                icon: "success",
                            }).then(function () {
                                location.reload();
                            })
                        })
                        .catch(function (error) {
                            swal({
                                title: error.response.status + " " + error.response.statusText,
                                text: error.response.data.message,
                                icon: "error"
                            })
                        })
                }
            })
        });
    </script>
@stop
