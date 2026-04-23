@php
    $signatureService = app(\Modules\MasterData\Services\SignatureService::class);
    $requiredApprovers = $signatureService->getRequiredApprovers($record);

    // Helper to get image as base64
    if (!function_exists('imageToBase64')) {
        function imageToBase64($media, $defaultPath = null)
        {
            try {
                $content = null;
                $extension = 'png';

                if ($media && $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                    if ($media->disk === 's3') {
                        $content = \Storage::disk('s3')->get($media->getPath());
                    } else {
                        $path = $media->getPath();
                        if (file_exists($path)) {
                            $content = file_get_contents($path);
                        }
                    }
                } elseif ($defaultPath && file_exists($defaultPath)) {
                    $extension = pathinfo($defaultPath, PATHINFO_EXTENSION);
                    $content = file_get_contents($defaultPath);
                }

                if (!$content) {
                    return null;
                }
                return 'data:image/' . $extension . ';base64,' . base64_encode($content);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    // Branding Assets
    $logoLogogram = imageToBase64(null, public_path('images/branding/header_left.png'));
    $logoDetail = imageToBase64(null, public_path('images/branding/header_right.png'));
    $footerKop = imageToBase64(null, public_path('images/branding/footer.png'));

    $latestAmendment = $record->salesOrder?->amendments()
        ?->where('status', \Modules\CRM\Enums\SalesOrderAmendmentStatus::Approved)
        ?->latest('sequence_number')
        ?->first();
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice - {{ $record->invoice_number }}</title>
    <style>
        @page {
            margin: 1.5in 0.7in 1.5in 0.7in;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 9px;
            line-height: 1.2;
        }

        header {
            position: fixed;
            top: -1.5in;
            left: 0;
            right: -0.7in;
            z-index: 1000;
        }

        footer {
            position: fixed;
            bottom: -1.5in;
            left: -0.7in;
            right: -0.7in;
            width: 8.27in;
            z-index: 1000;
        }

        .header-info-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
        }

        .title-box {
            text-align: right;
            padding-bottom: 5px;
        }

        .title-box h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .meta-table td {
            padding: 3px 6px;
            border: 1px solid #000;
        }

        .bg-gray {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 18%;
        }

        .bg-white {
            background-color: #fff;
            width: 32%;
        }

        .section-header {
            background-color: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        table.data-table th {
            background-color: #e5e7eb;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        table.data-table td {
            border: 1px solid #000;
            padding: 8px 6px;
            vertical-align: top;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .payment-box {
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }

        .signature-table {
            margin-top: 20px;
            width: 100%;
            table-layout: fixed;
        }

        .signature-table td {
            text-align: center;
            vertical-align: top;
            border: 1px solid #000;
            padding: 8px;
        }

        .sig-space {
            min-height: 50px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <header>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: left;">
                    @if ($logoLogogram)
                        <img src="{{ $logoLogogram }}" style="height: 160px;">
                    @endif
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    @if ($logoDetail)
                        <img src="{{ $logoDetail }}" style="height: 110px;">
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <footer>
        @if ($footerKop)
            <img src="{{ $footerKop }}" style="width: 100%;">
        @endif
    </footer>

    <table class="header-info-table">
        <tr>
            <td class="title-box">
                <h1>INVOICE / TAGIHAN</h1>
                <p>No: {{ $record->invoice_number }}</p>
                <div style="font-weight: normal; font-size: 9px;">Date: {{ $record->invoice_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Billing Information</div>
    <table class="meta-table">
        <tr>
            <td class="bg-gray">Customer Name</td>
            <td class="bg-white">{{ $record->customer->name ?? '-' }}</td>
            <td class="bg-gray">Invoice Date</td>
            <td class="bg-white">{{ $record->invoice_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="bg-gray">SO Reference</td>
            <td class="bg-white">
                {{ $record->salesOrder->so_number ?? '-' }}
                @if($latestAmendment)
                    <br><small>(Amend: {{ $latestAmendment->amendment_number }})</small>
                @endif
            </td>
            <td class="bg-gray">Due Date</td>
            <td class="bg-white" style="color: red; font-weight: bold;">{{ $record->due_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="bg-gray">BAPP Reference</td>
            <td class="bg-white" colspan="3">
                {{ $record->workCompletionReport->report_number ?? '-' }} 
                ({{ $record->workCompletionReport->service_period_start->format('d/m/Y') }} - {{ $record->workCompletionReport->service_period_end->format('d/m/Y') }})
            </td>
        </tr>
    </table>

    <div class="section-header">Invoice Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="55%">Description</th>
                <th width="40%">Amount (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    Implementation / Service charges for period 
                    {{ $record->workCompletionReport->service_period_start->format('M Y') }}
                    <br><small>As per BAPP {{ $record->workCompletionReport->report_number ?? '-' }}</small>
                </td>
                <td class="text-right">{{ \Illuminate\Support\Number::currency($record->amount, 'IDR', 'id') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="bg-gray text-right">Subtotal</td>
                <td class="text-right">{{ \Illuminate\Support\Number::currency($record->amount, 'IDR', 'id') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="bg-gray text-right">VAT (PPN)</td>
                <td class="text-right">{{ \Illuminate\Support\Number::currency($record->tax_amount, 'IDR', 'id') }}</td>
            </tr>
            <tr class="font-bold">
                <td colspan="2" class="bg-gray text-right" style="background-color: #000; color: #fff;">Total Amount Due</td>
                <td class="text-right" style="background-color: #f3f4f6;">{{ \Illuminate\Support\Number::currency($record->total_amount, 'IDR', 'id') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-box">
        <div class="font-bold" style="text-decoration: underline; margin-bottom: 5px;">Payment Instructions:</div>
        <div>Please settle the payment to the following bank account within the due date:</div>
        <table style="border: none; margin-top: 10px; width: 60%;">
            <tr><td style="border: none; width: 30%;">Account Name</td><td style="border: none;">: PT. GARUDA DAYA PRATAMA SEJAHTERA</td></tr>
            <tr><td style="border: none;">Bank Name</td><td style="border: none;">: BANK MANDIRI</td></tr>
            <tr><td style="border: none;">Account Number</td><td style="border: none; font-weight: bold;">: 123-456-7890 (Placeholder)</td></tr>
        </table>
        <div style="margin-top: 10px; font-style: italic;">*Please include Invoice Number in the payment reference.</div>
    </div>

    <div class="section-header">Signatures</div>
    <table class="signature-table">
        <tr>
            <td>
                <div class="font-bold">Issued By</div>
                <div class="sig-space"></div>
                <div class="font-bold">( ........................................ )</div>
                <div>Finance Dept.</div>
            </td>
            @foreach ($requiredApprovers as $rule)
                <td>
                    <div class="font-bold">{{ $rule->signature_type->getLabel() }}</div>
                    <div class="sig-space"></div>
                    <div class="font-bold">( ........................................ )</div>
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>
