@extends('layouts.admin')

@section('content')

<h2>Báo cáo hệ thống</h2>

<div class="row">

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Tổng sản phẩm</h5>
            <h2>{{ $totalProducts }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Users</h5>
            <h2>{{ $totalUsers }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Link affiliate</h5>
            <h2>{{ $totalLinks }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h5>Chưa có link</h5>
            <h2 style="color:red">{{ $missingLinks }}</h2>
        </div>
    </div>

</div>

@endsection