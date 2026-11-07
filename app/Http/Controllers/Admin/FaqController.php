<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', ['faqs' => Faq::orderBy('sort_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_active' => true, 'sort_order' => Faq::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', __('FAQ saved.'));
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', __('FAQ saved.'));
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', __('FAQ deleted.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'question_bn' => ['nullable', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'answer_bn' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
