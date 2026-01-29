<?php

namespace App\Mail;

use App\Models\VideoCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VideoCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public VideoCampaign $campaign
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Benvenuto in Simecom - Apri la mail e guarda il video',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.video-campaign',
            with: [
                'customerName' => $this->campaign->customer_name,
                'videoUrl' => $this->campaign->getLandingUrl(),
            ],
        );
    }

    public function attachments(): array
    {
        if (!$this->campaign->hasAttachment()) {
            return [];
        }

        $fullPath = $this->campaign->getAttachmentFullPath();

        if (!$fullPath || !file_exists($fullPath)) {
            return [];
        }

        return [
            Attachment::fromPath($fullPath)
                ->as('Lettera_Benvenuto.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
