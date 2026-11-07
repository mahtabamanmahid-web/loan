{{-- Shared PDF layout (DomPDF). Uses DejaVu Sans (bundled with DomPDF). Labels are in English for reliable PDF rendering. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>@yield('title')</title>
<style>
    @page { margin: 28px 32px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    body { font-size: 11px; color: #1e293b; line-height: 1.5; }
    .header { border-bottom: 3px solid {{ setting('primary_color') }}; padding-bottom: 10px; margin-bottom: 18px; }
    .brand { font-size: 20px; font-weight: bold; color: {{ setting('primary_color') }}; }
    .muted { color: #64748b; }
    .title { font-size: 16px; font-weight: bold; margin: 0 0 4px; }
    table { width: 100%; border-collapse: collapse; }
    .kv td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .kv td:first-child { color: #64748b; width: 40%; }
    .grid th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
    .grid td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    .box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; margin: 12px 0; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; background: #dcfce7; color: #166534; }
    .right { text-align: right; }
    .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; }
    h2 { font-size: 14px; margin: 14px 0 6px; } h3 { font-size: 12px; margin: 12px 0 4px; }
    ul { margin: 4px 0 8px 16px; padding: 0; }
</style>
</head>
<body>
    <div class="header">
        <table><tr>
            <td><span class="brand">{{ site_name() }}</span><br><span class="muted">{{ setting('office_address') }}</span></td>
            <td class="right muted">{{ setting('contact_phone') }}<br>{{ setting('support_email') }}<br>{{ sys_setting('lender_license_number') ? 'License: '.sys_setting('lender_license_number') : '' }}</td>
        </tr></table>
    </div>
    @yield('content')
    <div class="footer">{{ site_name() }} · Generated {{ now()->format('d M Y, h:i A') }} · This is a computer-generated document.</div>
</body>
</html>
