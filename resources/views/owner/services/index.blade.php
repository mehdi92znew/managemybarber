@extends('layouts.owner')

@section('header', 'Services Management')

@section('content')
    <div id="service-manager" data-services="{{ json_encode($services) }}">
        <service-manager :initial-services="{{ json_encode($services) }}"></service-manager>
    </div>
@endsection
