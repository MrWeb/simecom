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
        'customer_name',
        'video_combination',
        'video_type',
        'video_hash',
        'video_path',
        'video_status',
        'email_status',
        'email_sent_at',
        'email_service_id',
        'opened_at',
    ];

    protected $casts = [
        'video_combination' => 'array',
        'email_sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

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
                $campaign->video_hash = self::generateHash($campaign->video_combination, $campaign->video_type ?? 'luce');
            }
        });
    }

    public static function generateHash(array $combination, string $type = 'luce'): string
    {
        return md5($type . '_' . implode('_', $combination));
    }

    public function findExistingVideo(): ?self
    {
        return self::where('video_hash', $this->video_hash)
            ->where('video_status', 'ready')
            ->whereNotNull('video_path')
            ->first();
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
}
