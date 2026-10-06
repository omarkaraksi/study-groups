@extends('admin.layouts.app')

@section('title', $user->name)

@section('content')
<div class="card">
    <div class="card-body">
        <h2>{{ $user->name }}</h2>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>ID:</strong> {{ $user->id }}</p>
        <p><strong>Created At:</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}</p>
        <div class="mb-3">
            <strong>Roles:</strong>
            @foreach($user->roles as $role)
                <span class="badge bg-primary text-white">{{ $role->name }}</span>
            @endforeach
        </div>
        <div class="mb-3">
            <strong>Permissions:</strong>
            @foreach($user->permissions as $permission)
                <span class="badge bg-secondary">{{ $permission->name }}</span>
            @endforeach
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit</a>
    </div>
</div>
@endsection