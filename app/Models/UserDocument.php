<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NID / KYC document. Images are stored on the private "nid" disk and are only
 * served through an authorised admin route (never through a public URL).
 */
class UserDocument extends Model
{
    protected $fillable = [
        'nid_number', 'nid_hash', 'front_image_path', 'back_image_path', 'selfie_path', 'status', 'admin_note',
    ];

    protected $hidden = ['nid_number', 'nid_hash', 'front_image_path', 'back_image_path', 'selfie_path'];

    protected function casts(): array
    {
        return [
            'nid_number' => 'encrypted',
            'status' => DocumentStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    /** Show only the last 4 digits, e.g. "******1234". */
    public function maskedNid(): string
    {
        $nid = (string) $this->nid_number;

        return str_repeat('•', max(strlen($nid) - 4, 0)).substr($nid, -4);
    }

    public static function hashNid(string $nid): string
    {
        return hash_hmac('sha256', preg_replace('/\D/', '', $nid), (string) config('app.key'));
    }
}
