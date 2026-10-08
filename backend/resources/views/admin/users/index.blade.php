@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">@lang('admin.users')</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('admin.id')</th>
                    <th>@lang('admin.name')</th>
                    <th>@lang('admin.email')</th>
                    <th>@lang('admin.roles')</th>
                    <th>@lang('admin.permissions')</th>
                    <th>@lang('admin.created_at')</th>
                    <th>@lang('admin.actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary text-white">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @foreach($user->permissions as $permission)
                            <span class="badge bg-secondary">{{ $permission->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary">@lang('admin.view')</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-secondary">@lang('admin.edit')</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('@lang('admin.delete_confirmation')')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">@lang('admin.delete')</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3 text-center">
            {{ $users->links('pagination::custom') }}
        </div>
    </div>
</div>
@endsection