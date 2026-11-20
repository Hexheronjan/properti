<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Sistem - APRO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            max-width: 540px;
            width: 100%;
            padding: 32px;
            text-align: center;
        }
        .icon-wrapper {
            width: 56px;
            height: 56px;
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
        }
        .icon-svg {
            width: 28px;
            height: 28px;
            color: #d97706;
            display: block;
        }
        .error-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
        }
        .error-message {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .technical-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
            font-size: 12px;
        }
        .technical-label {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 10px;
            color: #dc2626;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .technical-label-dot {
            width: 6px;
            height: 6px;
            background-color: #dc2626;
            border-radius: 50%;
            display: inline-block;
        }
        .technical-message {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #334155;
            word-break: break-all;
            line-height: 1.4;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            margin-top: 4px;
        }
        .technical-location {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 6px;
        }
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #ffffff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-wrapper">
            <svg class="icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="error-title">Terjadi Kendala Sistem</h1>
        <p class="error-message">{{ $message ?? 'Sistem mendeteksi kendala saat memproses permintaan ini.' }}</p>

        @if(!empty($technical_message) || !empty($debug['message']))
            <div class="technical-box">
                <div class="technical-label">
                    <span class="technical-label-dot"></span>
                    Detail Penyebab / Pesan Teknis:
                </div>
                <div class="technical-message">
                    {{ $technical_message ?? $debug['message'] }}
                </div>
                @if(!empty($file))
                    <div class="technical-location">
                        Lokasi: {{ $file }} @if(!empty($line)) (baris {{ $line }}) @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="button-group">
            <a href="javascript:history.back()" class="btn-secondary">Kembali</a>
            <a href="{{ url('/admin/dashboard') }}" class="btn-primary">Ke Dashboard</a>
        </div>
    </div>
</body>
</html>
