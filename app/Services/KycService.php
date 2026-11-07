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

/**
 * NID / KYC handling.
 * Files are saved on the "local" disk (storage/app/private) under kyc/{user_id}/
 * with random file names. They are NEVER reachable through a public URL – admins
 * view them through an authorised streaming route.
 */
class KycService
{
    public const DISK = 'local';

    public function submit(User $user, string $nid, UploadedFile $front, UploadedFile $back, ?UploadedFile $selfie): UserDocument
    {
        $nid = preg_replace('/\D/', '', $nid);
        $hash = UserDocument::hashNid($nid);

        // Same NID cannot be used by two different accounts (duplicate account prevention)
        $usedByOther = UserDocument::where('nid_hash', $hash)->where('user_id', '!=', $user->id)
            ->whereIn('status', [DocumentStatus::Pending->value, DocumentStatus::Approved->value])->exists();
        if ($usedByOther) {
            throw ValidationException::withMessages(['nid_number' => __('This NID is already registered with another account.')]);
        }

        $latest = $user->latestDocument;
        if ($latest && in_array($latest->status, [DocumentStatus::Pending, DocumentStatus::Approved], true)) {
            throw ValidationException::withMessages(['nid_number' => __('Your NID is already submitted. Please wait for the review result.')]);
        }

        $dir = 'kyc/'.$user->id;

        return DB::transaction(function () use ($user, $nid, $hash, $front, $back, $selfie, $dir) {
            $doc = $user->documents()->create([
                'nid_number' => $nid,
                'nid_hash' => $hash,
                'front_image_path' => $this->store($front, $dir),
                'back_image_path' => $this->store($back, $dir),
                'selfie_path' => $selfie ? $this->store($selfie, $dir) : null,
                'status' => DocumentStatus::Pending,
            ]);

            return $doc;
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

    private function store(UploadedFile $file, string $dir): string
    {
        // Random name + server-detected extension (never trust the client file name)
        $name = Str::random(40).'.'.$file->guessExtension();

        return Storage::disk(self::DISK)->putFileAs($dir, $file, $name);
    }
}
