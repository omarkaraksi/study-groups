@extends('admin.layouts.app')

@section('title', 'Study Groups')

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">Study Groups</h2>
        <table class="table table-striped">
            <thead>
                <tr><th>Name</th><th>Slug</th><th>Visibility</th><th>Owner</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ $group->slug }}</td>
                    <td>{{ $group->visibility }}</td>
                    <td>{{ $group->owner->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.study-groups.show', $group) }}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ route('admin.study-groups.edit', $group) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('admin.study-groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $groups->links() }}
    </div>
</div>
@endsection
