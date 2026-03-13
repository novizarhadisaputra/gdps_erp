<?php

namespace Modules\CRM\app\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Proposal;

class ProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal,
        public string $customMessage = ''
    ) {}

    public function build(): self
    {
        $mail = $this->subject("Proposal - {$this->proposal->proposal_number}")
            ->view('crm::emails.proposal');

        // Attach PDF if available
        if ($this->proposal->is_manual && $media = $this->proposal->getFirstMedia('final_proposal')) {
            $mail->attach($media->getPath(), [
                'as' => $media->file_name,
                'mime' => $media->mime_type,
            ]);
        } else {
            // Generate PDF on the fly for dynamic proposals
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $this->proposal]);
            $filename = str_replace(['/', '\\'], '-', $this->proposal->proposal_number);

            $mail->attachData($pdf->output(), "proposal-{$filename}.pdf", [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
