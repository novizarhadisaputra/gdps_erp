<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\GeneralInformation;

class GeneralInformationApiController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * List General Information records.
     * GET /api/v1/crm/general-informations
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 15);

        $query = GeneralInformation::query()
            ->with(['customer:id,name', 'lead:id,title', 'projectArea:id,name'])
            ->select([
                'id', 'document_number', 'customer_id', 'lead_id', 'project_area_id',
                'status', 'rr_status', 'rr_document_number', 'created_at', 'updated_at'
            ]);

        // Optional Filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('rr_status')) {
            $query->where('rr_status', $request->input('rr_status'));
        }

        if ($request->has('rr_document_number')) {
            $query->where('rr_document_number', 'like', '%'.$request->input('rr_document_number').'%');
        }

        $paginator = $query->paginate($limit);
        $data = \Modules\CRM\Http\Resources\GeneralInformationResource::collection($paginator);

        return $this->paginated(
            $data,
            'General Information list retrieved successfully'
        );
    }

    /**
     * Show details of a General Information record.
     * GET /api/v1/crm/general-informations/{id}
     */
    public function show($id)
    {
        $gi = GeneralInformation::with([
            'customer',
            'lead',
            'projectArea',
            'pics.contactRole',
            'profitabilityAnalyses',
        ])->findOrFail($id);

        return $this->success(
            new \Modules\CRM\Http\Resources\GeneralInformationResource($gi),
            'General Information detail retrieved successfully'
        );
    }
}
