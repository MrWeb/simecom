<?php

namespace App\Exports;

use App\Models\VideoCampaign;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VideoCampaignsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $dates,
    ) {}

    public function query()
    {
        return VideoCampaign::query()
            ->where(function ($q) {
                $q->whereIn(\DB::raw('DATE(email_sent_at)'), $this->dates)
                  ->orWhereIn(\DB::raw('DATE(sms_sent_at)'), $this->dates);
            })
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Email',
            'Telefono',
            'Tipo',
            'Codice Offerta',
            'Nome Offerta',
            'Stato Video',
            'Stato Email',
            'Stato SMS',
            'Email Inviata il',
            'SMS Inviato il',
            'Aperta il',
            'Secondi Video Visti',
            'Durata Video',
            'Video Completato',
            'Allegato',
            'Creata il',
        ];
    }

    public function map($row): array
    {
        return [
            $row->customer_name,
            $row->email,
            $row->phone,
            ucfirst($row->video_type),
            $row->offer_code,
            $row->offer_name,
            $this->formatVideoStatus($row->video_status),
            $this->formatEmailStatus($row->email_status),
            $this->formatSmsStatus($row->sms_status),
            $row->email_sent_at?->format('d/m/Y H:i'),
            $row->sms_sent_at?->format('d/m/Y H:i'),
            $row->opened_at?->format('d/m/Y H:i'),
            $row->video_watched_seconds,
            $row->video_duration,
            $row->video_completed ? 'Sì' : 'No',
            $row->hasAttachment() ? 'Sì' : 'No',
            $row->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatVideoStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'In attesa',
            'processing' => 'In elaborazione',
            'ready' => 'Pronto',
            'failed' => 'Fallito',
            default => $status,
        };
    }

    private function formatEmailStatus(?string $status): string
    {
        return match ($status) {
            'pending' => 'Non inviata',
            'sent' => 'Inviata',
            'failed' => 'Fallita',
            default => $status ?? '',
        };
    }

    private function formatSmsStatus(?string $status): string
    {
        return match ($status) {
            'pending' => 'Non inviato',
            'sent' => 'Inviato',
            'failed' => 'Fallito',
            default => $status ?? '',
        };
    }
}
