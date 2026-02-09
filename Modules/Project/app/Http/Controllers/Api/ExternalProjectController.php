<?php

namespace Modules\Project\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Project\Models\Project;
use Modules\Project\Transformers\ProjectResource;

class ExternalProjectController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $client = $request->user();
        if ($client instanceof ApiClient) {
            $client->update(['last_used_at' => now()]);
        }

        // Example filtering or simple pagination
        $paginator = Project::query()
            ->with(['customer'])
            ->latest()
            ->paginate(20);
        $data = ProjectResource::collection($paginator);

        return $this->paginated($data, 'Projects retrieved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $project = Project::with(['customer'])->findOrFail($id);

        return $this->success(
            new ProjectResource($project),
            'Project details retrieved successfully'
        );
    }
}
