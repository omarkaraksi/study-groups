@extends('admin.layouts.app')

@section('title', 'Study Groups')

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">@lang('admin.study_groups')</h2>
        <table class="table table-striped">
            <thead>
                <tr><th>@lang('admin.name')</th><th>@lang('admin.slug')</th><th>@lang('admin.visibility')</th><th>@lang('admin.owner')</th><th>@lang('admin.actions')</th></tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ $group->slug }}</td>
                    <td>{{ $group->visibility }}</td>
                    <td>{{ $group->owner->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.study-groups.show', $group) }}" class="btn btn-sm btn-primary">@lang('admin.view')</a>
                        <a href="{{ route('admin.study-groups.edit', $group) }}" class="btn btn-sm btn-secondary">@lang('admin.edit')</a>
                        <form method="POST" action="{{ route('admin.study-groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('@lang('admin.delete_confirmation')')">
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
            {{ $groups->links('pagination::custom') }}
        </div>
    </div>
</div>
@endsection
