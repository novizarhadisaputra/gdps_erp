<?php

namespace Modules\MasterData\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\MasterData\Services\SignatureService;

class SignatureVerificationController extends Controller
{
    public function verify(Request $request, string $token, SignatureService $service)
    {
        $result = $service->decodeToken($token);

        if (! $result) {
            abort(404, 'Invalid or expired signature token.');
        }

        $model = $result['model'];
        $signer = $result['user'];
        $signatures = $model->signatures()->with('user')->orderBy('signed_at')->get();

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('masterdata::signature.verification_sheet_pdf', [
                'document' => $model,
                'signer' => $signer,
                'signatures' => $signatures,
                'signed_at' => $result['time'],
                'type' => $result['type'],
            ]);

            $filename = 'Verification_'.($model->number ? Str::slug($model->number, '_') : $model->id).'.pdf';

            return $pdf->download($filename);
        }

        return view('masterdata::signature.verification_sheet', [
            'document' => $model,
            'signer' => $signer,
            'signatures' => $signatures,
            'signed_at' => $result['time'],
            'type' => $result['type'],
        ]);
    }
}
