<?php

namespace Modules\Project\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkCompletionReport $report,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?? 'Work Completion Report (BAPP) - '.$this->report->report_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'project::mail.work_completion_report',
            with: [
                'customMessage' => $this->customMessage,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // Check if there is an uploaded Draft BAPP (Unsigned)
        if ($this->report->hasMedia('draft_report')) {
            $media = $this->report->getFirstMedia('draft_report');
            $attachments[] = Attachment::fromPath($media->getPath())
                ->as('BAPP-Draft-Unsigned.pdf')
                ->withMime('application/pdf');
        } else {
            // Fallback to generated PDF if no media uploaded
            $pdf = Pdf::loadView('project::pdf.work_completion_report', ['record' => $this->report]);
            $filename = str_replace(['/', '\\'], '-', $this->report->report_number);
            $attachments[] = Attachment::fromData(fn () => $pdf->output(), "BAPP-{$filename}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
