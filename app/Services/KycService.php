<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Models\UserDocument;
use App\Notifications\KycStatusChanged;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * NID / KYC handling (photo only – no NID number is collected).
 *
 * Images are downsized and re-compressed before being saved on the private "local"
 * disk (storage/app/private) under kyc/{user_id}/ with random names, so they stay
 * small and never fill up hosting storage. They are NEVER reachable through a public
 * URL – admins view them through an authorised streaming route.
 */
class KycService
{
    public const DISK = 'local';

    /** Photos are scaled down to at most this width/height (px) to save storage. */
    private const MAX_DIMENSION = 1000;

    public function submit(User $user, UploadedFile $front, UploadedFile $back, ?UploadedFile $selfie): UserDocument
    {
        $latest = $user->latestDocument;
        if ($latest && in_array($latest->status, [DocumentStatus::Pending, DocumentStatus::Approved], true)) {
            throw ValidationException::withMessages(['nid_front' => __('Your NID is already submitted. Please wait for the review result.')]);
        }

        $dir = 'kyc/'.$user->id;

        return DB::transaction(function () use ($user, $front, $back, $selfie, $dir) {
            return $user->documents()->create([
                'front_image_path' => $this->store($front, $dir),
                'back_image_path' => $this->store($back, $dir),
                'selfie_path' => $selfie ? $this->store($selfie, $dir) : null,
                'status' => DocumentStatus::Pending,
            ]);
        });
    }

    public function review(UserDocument $doc, DocumentStatus $status, ?string $note, User $admin): void
    {
        DB::transaction(function () use ($doc, $status, $note, $admin) {
            $doc->forceFill([
                'status' => $status,
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ])->save();

            // Applications that were waiting for KYC move forward automatically.
            if ($status === DocumentStatus::Approved) {
                $doc->user->applications()->where('status', ApplicationStatus::NidPending->value)
                    ->update(['status' => ApplicationStatus::Submitted->value]);
            }
        });

        ActivityLogger::log('kyc.'.$status->value, $doc, "NID document #{$doc->id} marked {$status->value}");
        $doc->user->notify(new KycStatusChanged($doc));
    }

    /** Store a photo with a random name, downsized and compressed when GD is available. */
    private function store(UploadedFile $file, string $dir): string
    {
        $compressed = $this->compress($file);

        if ($compressed !== null) {
            $path = $dir.'/'.Str::random(40).'.jpg';
            Storage::disk(self::DISK)->put($path, $compressed);

            return $path;
        }

        // Fallback: store the original (server-detected extension, never the client name)
        return Storage::disk(self::DISK)->putFileAs($dir, $file, Str::random(40).'.'.$file->guessExtension());
    }

    /** Return downsized JPEG bytes, or null if GD is unavailable / the file is not an image. */
    private function compress(UploadedFile $file): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
            if ($image === false) {
                return null;
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $scale = min(1, self::MAX_DIMENSION / max($width, $height));

            if ($scale < 1) {
                $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
                if ($resized !== false) {
                    imagedestroy($image);
                    $image = $resized;
                }
            }

            ob_start();
            imagejpeg($image, null, 70);
            $bytes = ob_get_clean();
            imagedestroy($image);

            return $bytes ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
