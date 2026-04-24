<!DOCTYPE html>
<html>
<head>
    <title>Work Completion Report (BAPP)</title>
</head>
<body>
    <h1>Hello, {{ $report->customer->name ?? 'Customer' }}</h1>
    @if($customMessage)
        {!! $customMessage !!}
    @else
        <p>Please find the Work Completion Report (BAPP) for your project attached.</p>
        <p>
            Report Number: {{ $report->report_number }}<br>
            Project: {{ $report->project?->name ?? 'N/A' }}
        </p>
        <p>Please review the document. If you have any questions, feel free to contact us.</p>
    @endif
    <p>Thank you!</p>
</body>
</html>
