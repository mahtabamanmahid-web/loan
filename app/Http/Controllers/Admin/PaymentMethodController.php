<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\ActivityLogger;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Manual payment methods (bKash / Nagad / Rocket / Upay / bank / QR). */
class PaymentMethodController extends Controller
{
    public function index(UddoktaPayService $gateway): View
    {
        return view('admin.payment-methods.index', [
            'methods' => PaymentMethod::orderBy('sort_order')->get(),
            'gatewayActive' => $gateway->isConfigured(),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.form', ['method' => new PaymentMethod(['is_active' => true, 'type' => 'bkash'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['qr_path'] = $this->storeQr($request);
        PaymentMethod::create($data);
        ActivityLogger::log('payment_method.created', null, "Payment method {$data['name']} created");

        return redirect()->route('admin.payment-methods.index')->with('success', __('Payment method saved.'));
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_qr') && $paymentMethod->qr_path) {
            Storage::disk('public')->delete($paymentMethod->qr_path);
            $data['qr_path'] = null;
        }
        if ($path = $this->storeQr($request)) {
            if ($paymentMethod->qr_path) {
                Storage::disk('public')->delete($paymentMethod->qr_path);
            }
            $data['qr_path'] = $path;
        }

        $paymentMethod->update($data);
        ActivityLogger::log('payment_method.updated', null, "Payment method {$paymentMethod->name} updated");

        return redirect()->route('admin.payment-methods.index')->with('success', __('Payment method saved.'));
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->qr_path) {
            Storage::disk('public')->delete($paymentMethod->qr_path);
        }
        $paymentMethod->delete();
        ActivityLogger::log('payment_method.deleted', null, "Payment method {$paymentMethod->name} deleted");

        return back()->with('success', __('Payment method deleted.'));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', Rule::in(['bkash', 'nagad', 'rocket', 'upay', 'bank', 'other'])],
            'account_number' => ['nullable', 'string', 'max:60'],
            'account_name' => ['nullable', 'string', 'max:100'],
            'account_kind' => ['nullable', Rule::in(['personal', 'agent', 'merchant'])],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'qr' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);
        unset($data['qr']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function storeQr(Request $request): ?string
    {
        return $request->hasFile('qr') ? $request->file('qr')->store('payment-qr', 'public') : null;
    }
}
