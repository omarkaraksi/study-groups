<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\StudyGroups\CreateStudyGroup;
use App\Application\StudyGroups\UpdateStudyGroup;
use App\Domain\StudyGroups\Models\StudyGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateStudyGroupRequest;
use App\Http\Requests\Api\V1\ShowStudyGroupRequest;
use App\Http\Requests\Api\V1\UpdateStudyGroupRequest;
use App\Http\Resources\StudyGroupResource;
use Illuminate\Http\JsonResponse;

class StudyGroupController extends Controller
{
    public function store(
        CreateStudyGroupRequest $request,
        CreateStudyGroup $createStudyGroup,
    ): JsonResponse {
        $studyGroup = $createStudyGroup->handle($request->user(), $request->validated());

        return (new StudyGroupResource($studyGroup))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateStudyGroupRequest $request,
        UpdateStudyGroup $updateStudyGroup,
        StudyGroup $study_group
    ): JsonResponse {
        $studyGroup = $updateStudyGroup->handle(
            $request->user(),
            $study_group,
            $request->validated()
        );

        return (new StudyGroupResource($studyGroup))
            ->response()
            ->setStatusCode(200);
    }

    public function show(
        ShowStudyGroupRequest $request,
        StudyGroup $study_group
    ): JsonResponse {
        return (new StudyGroupResource($study_group->load(['owner', 'category', 'academicLevel', 'subjects', 'members', 'translations'])))
            ->response()
            ->setStatusCode(200);
    }
}
