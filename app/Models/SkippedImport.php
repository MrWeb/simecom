<?php

namespace App\Models;

use App\Jobs\GenerateVideoJob;
use App\Jobs\SendCampaignEmailJob;
use App\Jobs\SendCampaignSmsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkippedImport extends Model
{
    protected $fillable = [
        'source_file',
        'row_number',
        'row_data',
        'error_type',
        'offer_code',
        'email',
        'phone',
        'customer_name',
        'status',
        'video_campaign_id',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function videoCampaign(): BelongsTo
    {
        return $this->belongsTo(VideoCampaign::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeMissingEmail($query)
    {
        return $query->where('error_type', 'missing_email');
    }

    public function scopeMissingOfferCode($query)
    {
        return $query->where('error_type', 'missing_offer_code');
    }

    /**
     * Riprova l'elaborazione del record scartato.
     * Crea una VideoCampaign e dispatcha i job necessari.
     * Priorità: email > SMS > skip
     *
     * @param bool $skipSend Se true, non invia email/SMS
     * @return VideoCampaign|null
     */
    public function retry(bool $skipSend = false): ?VideoCampaign
    {
        // Deve avere almeno email o telefono
        if (empty($this->email) && empty($this->phone)) {
            return null;
        }

        $offerCode = OfferCode::findByCode($this->offer_code);

        if (!$offerCode) {
            return null;
        }

        // Estrai i dati originali per costruire la combinazione video
        $rowData = $this->row_data;
        $sesso = $rowData['SEX'] ?? $rowData['sex'] ?? 'M';
        $fatturaWeb = strtoupper($rowData['fatturaWEB'] ?? $rowData['fatturaweb'] ?? $rowData['FATTURAWEB'] ?? 'NO');

        $tipoFinale = ($sesso === 'F') ? 2 : 1;
        $combination = $this->buildVideoCombination($offerCode->video_segment, $tipoFinale, $fatturaWeb === 'SI');

        $campaign = VideoCampaign::create([
            'email' => $this->email,
            'phone' => $this->phone,
            'customer_name' => $this->customer_name,
            'video_combination' => $combination,
            'video_type' => $offerCode->type,
            'offer_code' => $offerCode->code,
            'offer_name' => $offerCode->offer_name,
        ]);

        // Aggiorna lo stato
        $this->update([
            'status' => 'processed',
            'video_campaign_id' => $campaign->id,
        ]);

        // Check if video already exists
        $existing = $campaign->findExistingVideo();

        if ($existing) {
            $campaign->reuseVideoFrom($existing);
            if (!$skipSend) {
                $this->dispatchNotificationJob($campaign);
            }
        } else {
            GenerateVideoJob::dispatch($campaign, $skipSend);
        }

        return $campaign;
    }

    /**
     * Dispatcha il job di notifica appropriato (email o SMS).
     */
    protected function dispatchNotificationJob(VideoCampaign $campaign): void
    {
        $channel = $campaign->getPreferredChannel();

        if ($channel === 'email') {
            SendCampaignEmailJob::dispatch($campaign);
        } elseif ($channel === 'sms') {
            SendCampaignSmsJob::dispatch($campaign);
        }
    }

    protected function buildVideoCombination(string $videoSegment, int $tipoFinale, bool $hasFatturaWeb = false): array
    {
        $combination = [
            'benvenuto',
            $videoSegment,
        ];

        if (!$hasFatturaWeb) {
            $combination[] = 'bolletta-digitale';
        }

        $combination[] = 'porta-un-amico';
        $combination[] = 'prodotti';

        if (!$hasFatturaWeb) {
            $combination[] = 'bolletta-digitale-2';
        } else {
            $combination[] = 'fine-1';
        }

        return $combination;
    }
}
