@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">@lang('admin.administration')</div>
                <h2 class="page-title">@lang('admin.dashboard')</h2>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">@lang('admin.welcome')</h3>
                    <p class="text-secondary mb-0">
                        The Laravel Blade admin foundation is ready. Administration features will be added in later steps.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
