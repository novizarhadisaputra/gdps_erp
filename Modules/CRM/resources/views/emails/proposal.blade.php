<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proposal</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Proposal - {{ $proposal->proposal_number }}</h2>
        
        <p>Dear {{ $proposal->customer?->name }},</p>
        
        @if($customMessage)
            <div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
                {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($customMessage) }}
            </div>
        @endif
        
        <p>Best regards,<br>
        <strong>{{ auth()->user()?->name ?? 'GDPS ERP System' }}</strong></p>
    </div>
</body>
</html>
