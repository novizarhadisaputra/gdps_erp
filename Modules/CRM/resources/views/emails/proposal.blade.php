@extends('layouts.mail', ['subject' => "Proposal - {$proposal->number}"])

@section('content')
    <h2 style="color: #2563eb; margin-top: 0;">Proposal - {{ $proposal->number }}</h2>
    
    <p>Dear {{ $proposal->customer?->name }},</p>
    
    <p>Thank you for your interest in our services. We have prepared a proposal tailored to your requirements.</p>

    @if($customMessage)
        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid #e2e8f0;">
            {!! \Filament\Forms\Components\RichEditor\RichContentRenderer::make($customMessage) !!}
        </div>
    @endif
    
    <p>Please find the detailed proposal attached to this email.</p>
    
    <p>If you have any questions or require further clarification, please do not hesitate to contact us.</p>
    
    <p style="margin-top: 30px;">Best regards,<br>
    <strong>{{ auth()->user()?->name ?? 'GDPS CRM Team' }}</strong></p>
@endsection

