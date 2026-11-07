<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Real customers (with a disbursed loan) can submit ONE review.
 * It stays hidden until an admin verifies and publishes it.
 */
class ReviewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $loan = $user->loans()->first();

        abort_unless($loan, 403, __('Only customers who received a loan can write a review.'));
        if (Review::where('user_id', $user->id)->exists()) {
            return back()->with('info', __('You have already submitted a review. Thank you!'));
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:600'],
            'location' => ['nullable', 'string', 'max:60'],
        ]);

        Review::create([
            'user_id' => $user->id,
            'loan_id' => $loan->id,
            'display_name' => $user->name,
            'location' => $data['location'] ? strip_tags($data['location']) : null,
            'rating' => $data['rating'],
            'comment' => strip_tags($data['comment']),
        ]);

        return back()->with('success', __('Thank you! Your review will appear after verification.'));
    }
}
