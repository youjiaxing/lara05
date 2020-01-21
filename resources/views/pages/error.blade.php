@extends('layouts.app')
@section('title', '错误')

@section('content')
    <div class="card">
        <div class="card-header">错误 {{ $code }}</div>
        <div class="card-body text-center">
            <h1 class="card-title">{{ $message }}</h1>
{{--            <a href="{{ url()->previous('/') }}" class="btn btn-primary">返回上一页</a>--}}
            <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>

        </div>
    </div>
@endsection
