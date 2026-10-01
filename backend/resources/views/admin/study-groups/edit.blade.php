@extends('admin.layouts.app')

@section('title', 'Edit Study Group')

@section('content')
<div class="card">
    <div class="card-body">
        <h2>Edit Study Group</h2>
        <form method="POST" action="{{ route('admin.study-groups.update', $study_group) }}">
            @csrf
            @method('PATCH')
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $study_group->name) }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control">{{ old('description', $study_group->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label for="visibility" class="form-label">Visibility</label>
                <select name="visibility" id="visibility" class="form-select" required>
                    <option value="public" {{ $study_group->visibility === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="private" {{ $study_group->visibility === 'private' ? 'selected' : '' }}>Private</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    @foreach(['draft','preview','active','suspended','archived'] as $s)
                    <option value="{{ $s }}" {{ $study_group->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.study-groups.show', $study_group) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
