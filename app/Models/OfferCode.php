<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferCode extends Model
{
    protected $fillable = [
        'code',
        'offer_name',
        'video_segment',
        'type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)
            ->where('active', true)
            ->first();
    }

    public static function getVideoSegment(string $code, string $default = 'offerta-1'): string
    {
        $offer = self::findByCode($code);
        return $offer?->video_segment ?? $default;
    }

    public static function getType(string $code, string $default = 'luce'): string
    {
        $offer = self::findByCode($code);
        return $offer?->type ?? $default;
    }
}
