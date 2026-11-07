<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use App\Services\ActivityLogger;
use App\Services\KycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** NID verification queue. Images are streamed through this controller only. */
class KycController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        $q = UserDocument::with('user', 'reviewer')->latest();
        if ($status !== 'all') {
            $q->where('status', $status);
        }

        return view('admin.kyc.index', ['documents' => $q->paginate(20)->withQueryString(), 'status' => $status]);
    }

    public function show(UserDocument $document): View
    {
        return view('admin.kyc.show', [
            'document' => $document->load('user.profile', 'reviewer'),
            // Other documents with the same NID (possible duplicate accounts)
            'duplicates' => UserDocument::where('nid_hash', $document->nid_hash)->whereKeyNot($document->id)->with('user')->get(),
        ]);
    }

    /** Stream a private NID image to an authorised admin. Never cached by browsers or proxies. */
    public function file(UserDocument $document, string $side): StreamedResponse
    {
        $path = match ($side) {
            'front' => $document->front_image_path,
            'back' => $document->back_image_path,
            'selfie' => $document->selfie_path,
        };
        abort_unless($path && Storage::disk(KycService::DISK)->exists($path), 404);

        // Every access to an identity document is audit-logged
        ActivityLogger::log('kyc.viewed', $document, "Viewed NID {$side} image of document #{$document->id}");

        return Storage::disk(KycService::DISK)->response($path, null, [
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function review(Request $request, UserDocument $document, KycService $kyc): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([DocumentStatus::Approved->value, DocumentStatus::Rejected->value, DocumentStatus::Resubmit->value])],
            'note' => [Rule::requiredIf($request->status !== 'approved'), 'nullable', 'string', 'max:500'],
        ]);

        $kyc->review($document, DocumentStatus::from($data['status']), $data['note'] ?? null, $request->user());

        return redirect()->route('admin.kyc.index')->with('success', __('NID marked as :status.', ['status' => DocumentStatus::from($data['status'])->label()]));
    }
}
