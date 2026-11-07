<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Review moderation. Every review must belong to a REAL customer account that
 * has received a loan – the admin cannot invent testimonials.
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $q = Review::with('user')->latest();
        if ($request->query('filter') === 'pending') {
            $q->where('is_published', false);
        }

        return view('admin.reviews.index', ['reviews' => $q->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.reviews.form', ['review' => new Review(['rating' => 5]), 'customers' => $this->eligibleCustomers()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $review = new Review($data);
        $review->loan_id = User::find($data['user_id'])->loans()->value('id');
        $this->applyFlags($review, $request);
        $review->save();
        ActivityLogger::log('review.created', $review, "Review added for user #{$review->user_id}");

        return redirect()->route('admin.reviews.index')->with('success', __('Review saved.'));
    }

    public function edit(Review $review): View
    {
        return view('admin.reviews.form', ['review' => $review, 'customers' => $this->eligibleCustomers()]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $review->fill($this->validated($request));
        $this->applyFlags($review, $request);
        $review->save();
        ActivityLogger::log('review.updated', $review, "Review #{$review->id} updated");

        return redirect()->route('admin.reviews.index')->with('success', __('Review saved.'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();
        ActivityLogger::log('review.deleted', null, "Review #{$review->id} deleted");

        return back()->with('success', __('Review deleted.'));
    }

    public function toggle(Review $review, string $field): RedirectResponse
    {
        $review->{$field} = ! $review->{$field};
        // A review cannot be public unless it is verified
        if ($field === 'is_published' && $review->is_published && ! $review->is_verified) {
            return back()->with('error', __('Verify the review first (confirm it is from a real customer).'));
        }
        if ($field === 'is_verified' && ! $review->is_verified) {
            $review->is_published = false;
        }
        $review->save();

        return back()->with('success', __('Review updated.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->whereNull('admin_role_id'), function ($attr, $value, $fail) {
                if (! User::find($value)?->loans()->exists()) {
                    $fail(__('Only customers who received a loan can have a review.'));
                }
            }],
            'display_name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:60'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:600'],
        ]);
    }

    private function applyFlags(Review $review, Request $request): void
    {
        $review->is_verified = $request->boolean('is_verified');
        $review->is_published = $review->is_verified && $request->boolean('is_published');
        $review->is_featured = $request->boolean('is_featured');
    }

    private function eligibleCustomers()
    {
        return User::whereNull('admin_role_id')->whereHas('loans')->orderBy('name')->get(['id', 'name', 'phone']);
    }
}
