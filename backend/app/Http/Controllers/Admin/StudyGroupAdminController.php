<?php

namespace App\Http\Controllers\Admin;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudyGroupAdminController extends Controller
{
    public function index()
    {
        $groups = StudyGroup::with(['owner', 'category', 'academicLevel'])
            ->latest()
            ->paginate(5);

        return view('admin.study-groups.index', compact('groups'));
    }

    public function show(StudyGroup $study_group)
    {
        $study_group->load(['owner', 'category', 'academicLevel', 'subjects', 'members']);

        return view('admin.study-groups.show', compact('study_group'));
    }

    public function edit(StudyGroup $study_group)
    {
        return view('admin.study-groups.edit', compact('study_group'));
    }

    public function update(Request $request, StudyGroup $study_group)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'visibility' => ['required', 'in:public,private'],
            'status' => ['required', 'in:draft,preview,active,suspended,archived'],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'rules' => ['nullable', 'string', 'max:65535'],
        ]);

        $study_group->update($validated);

        return redirect()->route('admin.study-groups.show', $study_group)
            ->with('success', 'Study group updated.');
    }

    public function destroy(StudyGroup $study_group)
    {
        $study_group->delete();

        return redirect()->route('admin.study-groups.index')
            ->with('success', 'Study group deleted.');
    }
}
