@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('403 Forbidden') }}</div>
                    <div class="card-body">
                        {{ __('You are not authorized to view this page.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script>window.location = "{{ redirect('/login') }}";</script>
