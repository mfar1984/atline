<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Loan Form - {{ $movement->asset->asset_tag ?? 'Unknown' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 11px; line-height: 1.5; color: #000; background: #fff; }
        .page { width: 210mm; min-height: 297mm; padding: 15mm 20mm; margin: 0 auto; background: white; }
        .header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 15px; border-bottom: 2px solid #000; margin-bottom: 20px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .company-info h1 { font-size: 18px; font-weight: 700; color: #000; margin-bottom: 2px; }
        .company-info p { font-size: 10px; color: #000; }
        .header-right { text-align: right; }
        .doc-title { font-size: 14px; font-weight: 700; color: #000; text-transform: uppercase; letter-spacing: 1px; }
        .doc-subtitle { font-size: 10px; color: #000; margin-top: 2px; }
        .ref-number { margin-top: 8px; padding: 4px 12px; border: 2px solid #000; font-size: 11px; font-weight: 600; color: #000; display: inline-block; }
        .section { margin-bottom: 20px; border: 2px solid #000; }
        .section-header { padding: 8px 12px; border-bottom: 2px solid #000; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-content { padding: 15px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-row { display: flex; align-items: flex-start; }
        .info-label { width: 120px; font-weight: 600; color: #000; flex-shrink: 0; }
        .info-colon { width: 15px; text-align: center; flex-shrink: 0; }
        .info-value { flex: 1; color: #000; }
        .info-full { grid-column: 1 / -1; }
        .status-badge { display: inline-block; padding: 3px 10px; font-size: 10px; font-weight: 600; text-transform: uppercase; border: 2px solid #000; }
        .signature-line { border-bottom: 2px solid #000; height: 50px; margin-bottom: 5px; }
        @media print {
            .no-print { display: none !important; }
            .page { padding: 10mm 15mm; width: 100%; min-height: auto; }
            @page { size: A4; margin: 10mm; }
        }
        @media screen and (max-width: 800px) {
            .page { width: 100%; padding: 15px; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @php
        $companyName = \App\Models\SystemSetting::companyName();
        $logoPath = \App\Models\SystemSetting::logoPath();
    @endphp

    <button class="no-print" onclick="window.print()" style="position:fixed;top:20px;right:20px;padding:12px 24px;background:#000;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">🖨️ Print / Save PDF</button>
    
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="{{ $logoPath }}" alt="{{ $companyName }}" style="width: 80px; height: 80px; object-fit: contain;">
                <div class="company-info">
                    <h1>{{ $companyName }}</h1>
                    <p>Asset Management System</p>
                </div>
            </div>
            <div class="header-right">
                <div class="doc-title">Asset Loan Form</div>
                <div class="doc-subtitle">Internal Asset Movement Record</div>
                <div class="ref-number">REF: MOV-{{ str_pad($movement->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <!-- Requestor Information -->
        <div class="section">
            <div class="section-header">👤 REQUESTOR INFORMATION / MAKLUMAT PEMOHON</div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->employee->full_name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Position</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->employee->position ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->employee->telephone ?? $movement->employee->whatsapp ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->employee->email ?? '-' }}</span>
                    </div>
                    <div class="info-row info-full">
                        <span class="info-label">Purpose</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->purpose ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Checkout Date</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->checkout_date ? $movement->checkout_date->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Expected Return</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->expected_return_date ? $movement->expected_return_date->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Actual Return</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->actual_return_date ? $movement->actual_return_date->format('d/m/Y H:i') : '________________________' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">
                            <span class="status-badge">
                                {{ $movement->status === 'returned' ? 'RETURNED' : 'CHECKED OUT' }}
                            </span>
                        </span>
                    </div>
                    @if($movement->notes)
                    <div class="info-row info-full">
                        <span class="info-label">Notes</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Asset Information -->
        <div class="section">
            <div class="section-header">📦 ASSET INFORMATION / MAKLUMAT ASET</div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Asset Tag</span>
                        <span class="info-colon">:</span>
                        <span class="info-value" style="font-weight:700;">{{ $movement->asset->asset_tag ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Serial Number</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->serial_number ?? '-' }}</span>
                    </div>
                    <div class="info-row info-full">
                        <span class="info-label">Asset Name</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->category->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Brand</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->brand->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Model</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->model ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Location</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->asset->location->name ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Condition (Out)</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ ucfirst($movement->checkout_condition ?? '-') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Condition (Return)</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $movement->return_condition ? ucfirst($movement->return_condition) : '________________________' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div style="margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:0;">
            <!-- Approval (Left) -->
            <div style="border:2px solid #000;border-right:1px solid #000;">
                <div style="padding:8px 12px;border-bottom:2px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;text-align:center;">
                    ✅ APPROVAL / KELULUSAN
                </div>
                <div style="padding:15px;">
                    <div style="margin-bottom:12px;">
                        <div class="signature-line"></div>
                        <div style="font-size:9px;color:#000;text-transform:uppercase;font-weight:600;">Borrower Signature / Tandatangan Peminjam</div>
                        <div style="font-size:10px;color:#000;margin-top:3px;">Name: {{ $movement->employee->full_name ?? '________________________' }}</div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <div class="signature-line"></div>
                        <div style="font-size:9px;color:#000;text-transform:uppercase;font-weight:600;">Approver Signature / Tandatangan Pelulus</div>
                        <div style="font-size:10px;color:#000;margin-top:3px;">Name: {{ $movement->approver->name ?? '________________________' }}</div>
                    </div>
                    <div style="font-size:9px;color:#000;margin-top:8px;font-weight:600;">
                        Date / Tarikh: {{ $movement->checkout_date ? $movement->checkout_date->format('d/m/Y') : '________________________' }}
                    </div>
                </div>
            </div>
            <!-- Return (Right) -->
            <div style="border:2px solid #000;border-left:1px solid #000;">
                <div style="padding:8px 12px;border-bottom:2px solid #000;font-size:11px;font-weight:700;text-transform:uppercase;text-align:center;">
                    🔄 RETURN / PEMULANGAN
                </div>
                <div style="padding:15px;">
                    <div style="margin-bottom:12px;">
                        <div class="signature-line"></div>
                        <div style="font-size:9px;color:#000;text-transform:uppercase;font-weight:600;">Borrower Signature / Tandatangan Peminjam</div>
                        <div style="font-size:10px;color:#000;margin-top:3px;">Name: {{ $movement->employee->full_name ?? '________________________' }}</div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <div class="signature-line"></div>
                        <div style="font-size:9px;color:#000;text-transform:uppercase;font-weight:600;">Receiver Signature / Tandatangan Penerima</div>
                        <div style="font-size:10px;color:#000;margin-top:3px;">Name: ________________________</div>
                    </div>
                    <div style="font-size:9px;color:#000;margin-top:8px;font-weight:600;">
                        Date / Tarikh: {{ $movement->actual_return_date ? $movement->actual_return_date->format('d/m/Y') : '________________________' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top:25px;padding-top:15px;border-top:1px solid #000;text-align:center;">
            <p style="font-size:9px;color:#000;margin-bottom:3px;">Generated on {{ now()->format('d/m/Y H:i') }} by {{ auth()->user()->name ?? 'System' }}</p>
            <p style="font-size:9px;color:#000;margin-bottom:3px;">{{ $companyName }} - Asset Management System</p>
            <p style="font-size:9px;color:#000;font-style:italic;">This document requires physical signatures to be valid for official records.</p>
        </div>
    </div>
</body>
</html>
