<?php

namespace Modules\Finance\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Finance\Models\Invoice;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?? 'Invoice '.$this->invoice->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'finance::mail.invoice',
            with: [
                'customMessage' => $this->customMessage,
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('finance::pdf.invoice', ['record' => $this->invoice]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Invoice-'.$this->invoice->number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
