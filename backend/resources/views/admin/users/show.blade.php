@extends('admin.layouts.app')

@section('title', $user->name)

@section('content')
<div class="card">
    <div class="card-body">
        <h2>{{ $user->name }}</h2>
        <p><strong>@lang('admin.email'):</strong> {{ $user->email }}</p>
        <p><strong>@lang('admin.id'):</strong> {{ $user->id }}</p>
        <p><strong>@lang('admin.created_at'):</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}</p>
        <div class="mb-3">
            <strong>@lang('admin.roles'):</strong>
            @foreach($user->roles as $role)
                <span class="badge bg-primary text-white">{{ $role->name }}</span>
            @endforeach
        </div>
        <div class="mb-3">
            <strong>@lang('admin.permissions'):</strong>
            @foreach($user->permissions as $permission)
                <span class="badge bg-secondary">{{ $permission->name }}</span>
            @endforeach
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">@lang('admin.back')</a>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">@lang('admin.edit')</a>
    </div>
</div>
@endsection