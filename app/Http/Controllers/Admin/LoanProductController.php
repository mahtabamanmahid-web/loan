<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Loan packages + their durations & interest rates. */
class LoanProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', ['products' => LoanProduct::with('terms')->withCount('terms')->orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new LoanProduct(['is_active' => true, 'processing_fee_type' => 'percent', 'fee_collection' => 'deduct', 'grace_days' => 3, 'icon' => 'wallet'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = LoanProduct::create($this->validated($request) + ['slug' => $this->uniqueSlug($request->name)]);
            $this->syncTerms($product, $request);

            return $product;
        });
        ActivityLogger::log('product.created', $product, "Loan package {$product->name} created");

        return redirect()->route('admin.products.index')->with('success', __('Loan package saved.'));
    }

    public function edit(LoanProduct $product): View
    {
        return view('admin.products.form', ['product' => $product->load('terms')]);
    }

    public function update(Request $request, LoanProduct $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->validated($request));
            $this->syncTerms($product, $request);
        });
        ActivityLogger::log('product.updated', $product, "Loan package {$product->name} updated");

        return redirect()->route('admin.products.index')->with('success', __('Loan package saved.'));
    }

    public function destroy(LoanProduct $product): RedirectResponse
    {
        if (\App\Models\LoanApplication::where('loan_product_id', $product->id)->exists()) {
            $product->update(['is_active' => false]);

            return back()->with('warning', __('This package has applications, so it was deactivated instead of deleted.'));
        }
        $product->delete();
        ActivityLogger::log('product.deleted', null, "Loan package {$product->name} deleted");

        return back()->with('success', __('Loan package deleted.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'name_bn' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_bn' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/'],
            'min_amount' => ['required', 'numeric', 'min:1'],
            'max_amount' => ['required', 'numeric', 'gte:min_amount'],
            'repayment_frequency' => ['required', Rule::in(['monthly', 'weekly'])],
            'processing_fee_type' => ['required', Rule::in(['percent', 'fixed'])],
            'processing_fee_value' => ['required', 'numeric', 'min:0', $request->processing_fee_type === 'percent' ? 'max:20' : 'max:1000000'],
            'fee_collection' => ['required', Rule::in(['deduct', 'upfront'])],
            'fee_refundable' => ['nullable', 'boolean'],
            'fee_purpose' => ['nullable', 'string', 'max:500'],
            'fee_refund_policy' => ['nullable', 'string', 'max:1000'],
            'late_fee_per_installment' => ['required', 'numeric', 'min:0'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'terms' => ['required', 'array', 'min:1'],
            'terms.*.months' => ['required', 'integer', 'min:1', 'max:120', 'distinct'],
            'terms.*.interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'terms.*.is_active' => ['nullable', 'boolean'],
        ], ['terms.*.months.distinct' => __('Each duration can be added only once.')]);

        $data['fee_refundable'] = $request->boolean('fee_refundable');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return collect($data)->except('terms')->all();
    }

    /** Update / create / remove duration rows. Durations used by applications are deactivated, not deleted. */
    private function syncTerms(LoanProduct $product, Request $request): void
    {
        $keep = [];
        foreach ($request->input('terms', []) as $row) {
            $term = $product->terms()->updateOrCreate(
                ['months' => (int) $row['months']],
                ['interest_rate' => $row['interest_rate'], 'is_active' => ! empty($row['is_active'])]
            );
            $keep[] = $term->id;
        }

        foreach ($product->terms()->whereNotIn('id', $keep)->get() as $old) {
            \App\Models\LoanApplication::where('loan_term_id', $old->id)->exists()
                ? $old->update(['is_active' => false])
                : $old->delete();
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'loan';
        $slug = $base;
        $i = 2;
        while (LoanProduct::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
