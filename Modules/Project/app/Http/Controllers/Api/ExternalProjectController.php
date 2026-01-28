<?php

namespace Modules\Project\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Project\Models\Project;
use Modules\Project\Transformers\ProjectResource;

class ExternalProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $client = $request->user();
        if ($client instanceof \App\Models\ApiClient) {
            $client->update(['last_used_at' => now()]);
        }

        // Example filtering or simple pagination
        $projects = Project::query()
            ->with(['customer']) // Eager load relationships
            ->latest()
            ->paginate(20);

        return ProjectResource::collection($projects)->additional([
            'status' => 'success',
            'message' => 'Projects retrieved successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $client = $request->user();
        if ($client instanceof \App\Models\ApiClient) {
            $client->update(['last_used_at' => now()]);
        }

        $project = Project::with(['customer'])->findOrFail($id);

        return (new ProjectResource($project))->additional([
            'status' => 'success',
            'message' => 'Project details retrieved successfully',
        ]);
    }
}
