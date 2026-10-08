@extends('admin.layouts.app')

@section('title', __('admin.edit') . ' ' . __('admin.user'))

@section('content')
<div class="card">
    <div class="card-body">
        <h2>@lang('admin.edit') @lang('admin.user')</h2>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PATCH')
            <div class="mb-3">
                <label for="name" class="form-label">@lang('admin.name')</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">@lang('admin.email')</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('admin.roles')</label>
                <div class="d-flex gap-3">
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                   {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role-{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('admin.permissions')</label>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($user->permissions as $permission)
                        <span class="badge bg-secondary text-white">{{ $permission->name }}</span>
                    @endforeach
                    @if($user->permissions->isEmpty())
                        <span class="text-muted">@lang('admin.no_permissions')</span>
                    @endif
                </div>
            </div>
            <button type="submit" class="btn btn-primary">@lang('admin.update')</button>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">@lang('admin.cancel')</a>
        </form>
    </div>
</div>
@endsection