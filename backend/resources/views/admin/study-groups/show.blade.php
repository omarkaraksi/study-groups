@extends('admin.layouts.app')

@section('title', $study_group->name)

@section('content')
<div class="card">
    <div class="card-body">
        <h2>{{ $study_group->name }}</h2>
        <p><strong>Slug:</strong> {{ $study_group->slug }}</p>
        <p><strong>Description:</strong> {{ $study_group->description }}</p>
        <p><strong>Visibility:</strong> {{ $study_group->visibility }}</p>
        <p><strong>Status:</strong> {{ $study_group->status }}</p>
        <p><strong>Owner:</strong> {{ $study_group->owner->name ?? '-' }}</p>
        <a href="{{ route('admin.study-groups.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
