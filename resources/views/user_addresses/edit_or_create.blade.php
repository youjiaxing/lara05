@extends("layouts.app")

@section("title", $address->exists ? "编辑收货地址" : "创建收货地址")

@section("content")
    <div class="card border-info">
        <div class="card-header">
            <h2 class="text-center">{{ $address->exists ? "编辑" : "创建" }}收货地址</h2>
        </div>
        <div class="card-body">
            @include("common._validation_errors")

            <form method="POST" id="address_form" action="{{ $address->exists ? route('user_addresses.update', [$address]) : route('user_addresses.store') }}">
                @if ($address->exists)
                    {{ method_field("PATCH") }}
                @endif

                {{ csrf_field() }}

                <div class="form-group row">
                    <label for="" class="col-sm-2 col-form-label">省市区</label>
                    <div class="col-sm-3">
                        <select class="form-control" name="province" id="province_select" required>
                            <option style="display: none;">选择省</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select class="form-control" name="city" id="city_select" required>
                            <option style="display: none;">选择市</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select class="form-control" name="district" id="district_select" required>
                            <option style="display: none;">选择区</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="" class="col-sm-2 col-form-label">联系人</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="contact_name" id="" placeholder="" required value="{{ old('contact_name', $address->contact_name) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="" class="col-sm-2 col-form-label">联系电话</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="contact_phone" id="" placeholder="" required value="{{ old('contact_phone', $address->contact_phone) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="" class="col-sm-2 col-form-label">地址</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="address" id="" placeholder="" required value="{{ old('address', $address->address) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="" class="col-sm-2">邮编</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="zip" id="" aria-describedby="helpId" placeholder="" required value="{{ old('zip', $address->zip) }}">
                    </div>


                </div>

                <button type="submit" class="btn btn-primary">{{ $address->exists ? "保存" : "创建" }}</button>
            </form>
        </div>
    </div>
@stop


@section("script")
    <script>
        $('#address_form').on('submit', function (event) {
            if (address.district == "" || address.city == "" || address.district == "") {
                event.preventDefault();
                swal({
                    text: "无效的省市区",
                    icon: "warning"
                })
            }
        });

        var provinceSelect = $('#province_select');
        var citySelect = $('#city_select');
        var districtSelect = $('#district_select');

        var address = {
            province: "{{ old('province', $address->province) }}",
            city: "{{ old('city', $address->city) }}",
            district: "{{ old('district', $address->district) }}",
        };

        {{--console.log("{{ $address->fullAddress }}");--}}

        $(function () {
            // 初始化"省"
            var matchedCode = init_options(provinceSelect, 86, address.province);
            if (matchedCode !== false) {
                // 初始化"市"
                matchedCode = init_options(citySelect, matchedCode, address.city);
                if (matchedCode !== false) {
                    // 初始化"区"
                    matchedCode = init_options(districtSelect, matchedCode, address.district);
                    if (matchedCode === false) {
                        address.district = "";
                    }
                } else {
                    address.city = "";
                    address.district = "";
                }

            } else {
                address.province = "";
                address.city = "";
                address.district = "";
            }
        });

        /**
         * 初始化 select 地址选择框
         * @param select jquery 对象
         * @param code 对应编码
         * @param match 默认选择项
         * @returns {boolean} 若有匹配的默认选择项, 返回true, 否则false
         */
        function init_options(select, code, match = null) {
            var matched = false;
            select[0].length = 1;

            if (!addressData.hasOwnProperty(code)) {
                if (code !== 0) {
                    console.warn("没有指定代码的地址数据: " + code);
                }
                return false;
            }

            var data = addressData[code];
            for (var k in data) {
                // console.log(k, data[k]);
                var option = $('<option value="' + data[k] + '" data-code="' + k + '">' + data[k] + '</option>');
                if (match == data[k]) {
                    option.prop('selected', 'selected');
                    matched = k;
                }
                select.append(option);
            }

            return matched;
        }

        // 省 select的触发器
        provinceSelect.on('change', function (event) {
            var selectedOption = this.options[this.selectedIndex];
            if (address.province == selectedOption.value) {
                return;
            }

            address.province = selectedOption.value;
            address.district = "";
            address.city = "";
            console.log(selectedOption.dataset.code);
            init_options(citySelect, selectedOption.dataset.code);
            init_options(districtSelect, 0);
            // console.log(this.options[this.selectedIndex].value);
        });

        // 市 select的触发器
        citySelect.on('change', function (event) {
            var selectedOption = this.options[this.selectedIndex];
            if (address.city == selectedOption.value) {
                return;
            }

            address.city = selectedOption.value;
            address.district = "";
            console.log(selectedOption.dataset.code);
            init_options(districtSelect, selectedOption.dataset.code);
        });

        // 区 select的触发器
        districtSelect.on('change', function (event) {
            address.district = this.options[this.selectedIndex].value;
        });
    </script>
@stop
