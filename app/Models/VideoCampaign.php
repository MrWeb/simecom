<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VideoCampaign extends Model
{
    protected $fillable = [
        'uuid',
        'email',
        'phone',
        'customer_name',
        'video_combination',
        'video_type',
        'offer_code',
        'offer_name',
        'video_hash',
        'video_path',
        'attachment_path',
        'video_status',
        'email_status',
        'email_sent_at',
        'email_service_id',
        'sms_status',
        'sms_sent_at',
        'opened_at',
    ];

    protected $casts = [
        'video_combination' => 'array',
        'email_sent_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    /**
     * Determina il canale di contatto preferito.
     * Priorità: email > sms > null
     */
    public function getPreferredChannel(): ?string
    {
        if (!empty($this->email)) {
            return 'email';
        }

        if (!empty($this->phone)) {
            return 'sms';
        }

        return null;
    }

    /**
     * Verifica se la campagna può essere inviata (ha almeno un canale di contatto).
     */
    public function canSend(): bool
    {
        return $this->getPreferredChannel() !== null;
    }

    protected function customerName(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    protected static function booted(): void
    {
        static::creating(function (VideoCampaign $campaign) {
            if (empty($campaign->uuid)) {
                $campaign->uuid = Str::uuid()->toString();
            }
            if (empty($campaign->video_hash) && ! empty($campaign->video_combination)) {
                $campaign->video_hash = self::generateHash(
                    $campaign->video_combination,
                    $campaign->video_type ?? 'luce',
                    $campaign->offer_name
                );
            }
        });
    }

    public static function generateHash(array $combination, string $type = 'luce', ?string $offerName = null): string
    {
        $base = $type . '_' . implode('_', $combination);
        if (in_array('__DYNAMIC_OFFER__', $combination) && $offerName) {
            $base .= '_' . $offerName;
        }
        return md5($base);
    }

    public function findExistingVideo(): ?self
    {
        $existing = self::where('video_hash', $this->video_hash)
            ->where('video_status', 'ready')
            ->whereNotNull('video_path')
            ->first();

        // Verifica che il file esista realmente
        if ($existing && !file_exists(storage_path('app/public/' . $existing->video_path))) {
            return null;
        }

        return $existing;
    }

    public function reuseVideoFrom(self $existing): void
    {
        $this->video_path = $existing->video_path;
        $this->video_status = 'ready';
        $this->save();
    }

    public function getLandingUrl(): string
    {
        return url("/v/{$this->uuid}");
    }

    public function getVideoUrl(): ?string
    {
        if (! $this->video_path) {
            return null;
        }

        return asset('storage/' . $this->video_path);
    }

    public function markAsOpened(): void
    {
        if (! $this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function scopePending($query)
    {
        return $query->where('video_status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('video_status', 'ready');
    }

    public function scopeEmailPending($query)
    {
        return $query->where('email_status', 'pending');
    }

    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function getAttachmentFullPath(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return storage_path('app/public/' . $this->attachment_path);
    }

    public function getAttachmentUrl(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }
}
