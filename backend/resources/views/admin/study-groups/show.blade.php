@extends('admin.layouts.app')

@section('title', $study_group->name)

@section('content')
<div class="card">
    <div class="card-body">
        <h2>{{ $study_group->name }}</h2>
        <p><strong>@lang('admin.slug'):</strong> {{ $study_group->slug }}</p>
        <p><strong>@lang('admin.description'):</strong> {{ $study_group->description }}</p>
        <p><strong>@lang('admin.visibility'):</strong> @lang('admin.' . $study_group->visibility)</p>
        <p><strong>@lang('admin.status'):</strong> {{ $study_group->status }}</p>
        <p><strong>@lang('admin.owner'):</strong> {{ $study_group->owner->name ?? '-' }}</p>
        <a href="{{ route('admin.study-groups.index') }}" class="btn btn-secondary">@lang('admin.back')</a>
    </div>
</div>
@endsection
