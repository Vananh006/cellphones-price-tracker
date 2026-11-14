@extends('layouts.admin')

@section('content')

<h2>Quản lý link Affiliate</h2>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Sản phẩm</th>
            <th>Store</th>
            <th>Link Affiliate</th>
        </tr>
    </thead>

    <tbody>
        @foreach($links as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->store_name }}</td>

            <td>
                @if($item->affiliate_url)
                <a href="{{ $item->affiliate_url }}" target="_blank">
                    {{ $item->affiliate_url }}
                </a>
                @else
                <span style="color:red">Chưa có link</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection