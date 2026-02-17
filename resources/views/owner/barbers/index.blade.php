@extends('layouts.owner')

@section('header', 'Team Management')

@section('content')
    <div id="barber-manager" data-barbers="{{ json_encode($barbers) }}">
        <!-- Vue component will mount here -->
        <barber-manager :initial-barbers="{{ json_encode($barbers) }}"></barber-manager>
    </div>
@endsection

@push('vue-components')
    <!-- This stack push is optional if we mount efficiently in app.js -->
@endpush
