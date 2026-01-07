<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectApiResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Get Master Project List
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $projects = Project::where('type', 'layanan')->orderBy('name', 'asc')->get();

        return $this->successResponse(ProjectApiResource::collection($projects), 'List Data Master Project');
    }
}
