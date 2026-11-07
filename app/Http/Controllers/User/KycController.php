<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\KycRequest;
use App\Services\KycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** NID verification page for customers. */
class KycController extends Controller
{
    public function show(Request $request): View
    {
        return view('user.kyc', [
            'document' => $request->user()->latestDocument,
            'requireSelfie' => sys_setting('require_selfie') == '1',
        ]);
    }

    public function store(KycRequest $request, KycService $kyc): RedirectResponse
    {
        $kyc->submit(
            $request->user(),
            $request->file('nid_front'),
            $request->file('nid_back'),
            $request->file('selfie'),
        );

        // Return to the loan application if the customer came from there
        $return = $request->session()->pull('kyc_return');

        return redirect($return ?: route('user.kyc.show'))
            ->with('success', __('NID submitted! We will verify it soon.'));
    }
}
