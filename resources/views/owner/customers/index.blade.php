@extends('layouts.owner')

@section('header', 'Customer Database')

@section('content')
    <div id="customer-manager" data-customers="{{ json_encode($customers) }}">
        <customer-manager :initial-customers="{{ json_encode($customers) }}"></customer-manager>
    </div>
@endsection
