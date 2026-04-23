<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
</head>
<body>
    <h1>Hello, {{ $invoice->customer->name }}</h1>
    @if($customMessage)
        {!! $customMessage !!}
    @else
        <p>Please find your invoice attached.</p>
        <p>
            Invoice Number: {{ $invoice->invoice_number }}<br>
            Total Amount: IDR {{ number_format($invoice->total_amount, 2) }}
        </p>
    @endif
    <p>Thank you for your business!</p>
</body>
</html>
