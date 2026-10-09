<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Financial Summary Report - {{ $queueDate }}</title>
    <style>
        @page {
            margin: 8mm 10mm 8mm 10mm;
            size: A4 landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #2d3748;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .report-outer {
            border: 2px solid #286aa0;
            border-radius: 6px;
            padding: 12px 16px;
            box-sizing: border-box;
            position: relative;
        }

        .primary-blue {
            color: #286aa0;
        }

        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .kpi-box {
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            padding: 6px 8px;
            background-color: #f7fafc;
            text-align: center;
        }

        .kpi-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 12px;
            font-weight: 800;
            margin-top: 2px;
            color: #1a202c;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 8px;
        }

        .items-table th {
            background-color: #edf2f7;
            border: 1px solid #cbd5e0;
            padding: 4px 5px;
            font-weight: bold;
            color: #2d3748;
            text-transform: uppercase;
        }

        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 5px;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-paid {
            background-color: #e8f5e9;
            color: #2e7d32;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
        }

        .badge-partial {
            background-color: #fffde7;
            color: #b7791f;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
        }

        .badge-unpaid {
            background-color: #ffebee;
            color: #c62828;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="report-outer">
        {{-- Header Section: Clinic Details & Report Title --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 48px; vertical-align: middle;">
                    {{-- Scalable Stethoscope Heart vector icon --}}
                    <svg width="40" height="44" viewBox="0 0 64 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6 V22 C14 32 26 38 32 44 C38 38 50 32 50 22 V6" stroke="#286aa0" stroke-width="4" stroke-linecap="round" fill="none"/>
                        <circle cx="14" cy="6" r="3.5" fill="#286aa0"/>
                        <circle cx="50" cy="6" r="3.5" fill="#286aa0"/>
                        <path d="M32 44 V52 C32 58 24 62 24 62 C18 58 14 53 14 48 C14 42 20 40 24 44 C28 40 34 42 34 48" stroke="#286aa0" stroke-width="3" stroke-linecap="round" fill="none"/>
                        <circle cx="48" cy="50" r="7" stroke="#286aa0" stroke-width="3.5" fill="none"/>
                        <path d="M32 52 H41" stroke="#286aa0" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </td>
                <td style="vertical-align: middle; padding-left: 10px;">
                    <div style="font-size: 16px; font-weight: bold; color: #286aa0; text-transform: uppercase;">
                        {{ $profile->HOSP_NAME ?? config('app.name', 'KayakapMD Clinic') }}
                    </div>
                    <div style="font-size: 8.5px; color: #718096; margin-top: 1px;">
                        @php
                            $hospAddr = array_filter([$profile->HOSP_ADDBRGY ?? '', $profile->HOSP_ADDMUN ?? '', $profile->HOSP_ADDPROV ?? '']);
                        @endphp
                        {{ !empty($hospAddr) ? implode(', ', $hospAddr) : ($profile->reports_address ?? 'Clinic Location, Philippines') }}
                        | Contact: {{ !empty($profile->TEL_NO) ? $profile->TEL_NO : '(02) 8800-0000' }}
                    </div>
                </td>
                <td style="vertical-align: middle; text-align: right;">
                    <div style="font-size: 15px; font-weight: 800; color: #1a365d; text-transform: uppercase; letter-spacing: 0.5px;">
                        DAILY FINANCIAL / INCOME REPORT
                    </div>
                    <div style="font-size: 9px; color: #4a5568; margin-top: 2px;">
                        Queue Date: <strong>{{ date('F d, Y', strtotime($queueDate)) }}</strong> | Doctor: <strong>{{ $doctorName }}</strong>
                    </div>
                    <div style="font-size: 8px; color: #718096; margin-top: 1px;">
                        Generated: {{ now()->format('m/d/Y h:i A') }} | By: {{ $generatedBy }}
                    </div>
                </td>
            </tr>
        </table>

        <div style="border-bottom: 2px solid #286aa0; margin-top: 8px; margin-bottom: 8px;"></div>

        {{-- Executive KPI Metrics Table --}}
        <table class="kpi-table">
            <tr>
                <td style="width: 14%; padding: 2px;">
                    <div class="kpi-box">
                        <div class="kpi-title">Queue Patients</div>
                        <div class="kpi-value primary-blue">{{ $summary->total_patients }}</div>
                    </div>
                </td>
                <td style="width: 14%; padding: 2px;">
                    <div class="kpi-box">
                        <div class="kpi-title">Gross Charges</div>
                        <div class="kpi-value">PHP {{ number_format($summary->total_gross, 2) }}</div>
                    </div>
                </td>
                <td style="width: 14%; padding: 2px;">
                    <div class="kpi-box">
                        <div class="kpi-title" style="color: #2e7d32;">PhilHealth (PHIC)</div>
                        <div class="kpi-value" style="color: #2e7d32;">PHP {{ number_format($summary->total_phic, 2) }}</div>
                    </div>
                </td>
                <td style="width: 14%; padding: 2px;">
                    <div class="kpi-box">
                        <div class="kpi-title" style="color: #1565c0;">HMO Coverage</div>
                        <div class="kpi-value" style="color: #1565c0;">PHP {{ number_format($summary->total_hmo, 2) }}</div>
                    </div>
                </td>
                <td style="width: 14%; padding: 2px;">
                    <div class="kpi-box">
                        <div class="kpi-title" style="color: #b7791f;">Senior/PWD Disc.</div>
                        <div class="kpi-value" style="color: #b7791f;">PHP {{ number_format($summary->total_senior, 2) }}</div>
                    </div>
                </td>
                <td style="width: 15%; padding: 2px;">
                    <div class="kpi-box" style="background-color: #ebf8ff; border-color: #bee3f8;">
                        <div class="kpi-title" style="color: #2b6cb0;">Net Billing</div>
                        <div class="kpi-value" style="color: #2b6cb0;">PHP {{ number_format($summary->total_net, 2) }}</div>
                    </div>
                </td>
                <td style="width: 15%; padding: 2px;">
                    <div class="kpi-box" style="background-color: #f0fff4; border-color: #c6f6d5;">
                        <div class="kpi-title" style="color: #22543d;">Total Collected</div>
                        <div class="kpi-value" style="color: #22543d;">PHP {{ number_format($summary->total_paid, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Payment Channels Breakdown Sub-strip --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 8.5px; background-color: #faf5ff; border: 1px solid #e9d8fd; border-radius: 4px; padding: 4px 8px; margin-bottom: 8px;">
            <tr>
                <td style="padding: 2px 6px;">
                    <strong>Collections Breakdown:</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Cash: <strong>PHP {{ number_format($summary->total_cash, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Card / CTA: <strong>PHP {{ number_format($summary->total_card, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    PhilHealth Yakap Co-Pay: <strong>PHP {{ number_format($summary->total_copay, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px; text-align: right; color: #c53030;">
                    Unsettled Balance: <strong>PHP {{ number_format($summary->total_balance, 2) }}</strong>
                </td>
            </tr>
        </table>

        {{-- Itemized Patients Billing Table --}}
        <div style="font-weight: bold; font-size: 10px; color: #286aa0; margin-bottom: 3px; text-transform: uppercase;">
            <i class="fa-solid fa-list-check me-1"></i> Itemized Patient Settlements (Queue Date: {{ date('m/d/Y', strtotime($queueDate)) }})
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">Q#</th>
                    <th>Patient Name</th>
                    <th>Attending Doctor</th>
                    <th style="width: 60px;" class="text-right">Gross</th>
                    <th style="width: 55px;" class="text-right">PHIC</th>
                    <th style="width: 55px;" class="text-right">HMO</th>
                    <th style="width: 50px;" class="text-right">Discount</th>
                    <th style="width: 65px;" class="text-right">Net Payable</th>
                    <th style="width: 55px;" class="text-right">Cash</th>
                    <th style="width: 55px;" class="text-right">Card/CTA</th>
                    <th style="width: 55px;" class="text-right">Co-Pay</th>
                    <th style="width: 60px;" class="text-right">Total Paid</th>
                    <th style="width: 55px;" class="text-right">Balance</th>
                    <th style="width: 45px;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($items) && count($items) > 0)
                    @foreach ($items as $item)
                        <tr>
                            <td class="text-center font-monospace"><strong>{{ $item->queueno }}</strong></td>
                            <td>
                                <strong>{{ $item->patientname }}</strong>
                                @if(!empty($item->hmo_loa_no))
                                    <div style="font-size: 7.5px; color: #4a5568;">LOA: {{ $item->hmo_loa_no }}</div>
                                @endif
                                @if($item->is_philhealth_yakap)
                                    <span style="font-size: 7.5px; color: #2b6cb0; font-weight: bold;">[PH Yakap]</span>
                                @endif
                            </td>
                            <td>{{ $item->docname ?: '--' }}</td>
                            <td class="text-right">{{ number_format($item->gross, 2) }}</td>
                            <td class="text-right" style="color: #2e7d32;">{{ $item->phic > 0 ? '-' . number_format($item->phic, 2) : '-' }}</td>
                            <td class="text-right" style="color: #1565c0;">{{ $item->hmo > 0 ? '-' . number_format($item->hmo, 2) : '-' }}</td>
                            <td class="text-right" style="color: #b7791f;">{{ $item->senior > 0 ? '-' . number_format($item->senior, 2) : '-' }}</td>
                            <td class="text-right"><strong>{{ number_format($item->net, 2) }}</strong></td>
                            <td class="text-right">{{ $item->cash > 0 ? number_format($item->cash, 2) : '-' }}</td>
                            <td class="text-right">{{ $item->card > 0 ? number_format($item->card, 2) : '-' }}</td>
                            <td class="text-right">{{ $item->copay > 0 ? number_format($item->copay, 2) : '-' }}</td>
                            <td class="text-right" style="color: #2e7d32;"><strong>{{ number_format($item->paid, 2) }}</strong></td>
                            <td class="text-right" style="color: {{ $item->balance > 0 ? '#c62828' : '#718096' }};">
                                {{ $item->balance > 0 ? number_format($item->balance, 2) : '0.00' }}
                            </td>
                            <td class="text-center">
                                @if($item->status === 'PAID')
                                    <span class="badge-paid">PAID</span>
                                @elseif($item->status === 'PARTIAL')
                                    <span class="badge-partial">PARTIAL</span>
                                @else
                                    <span class="badge-unpaid">UNPAID</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #edf2f7; font-weight: bold;">
                        <td colspan="3" class="text-center">TOTALS</td>
                        <td class="text-right">{{ number_format($summary->total_gross, 2) }}</td>
                        <td class="text-right" style="color: #2e7d32;">-{{ number_format($summary->total_phic, 2) }}</td>
                        <td class="text-right" style="color: #1565c0;">-{{ number_format($summary->total_hmo, 2) }}</td>
                        <td class="text-right" style="color: #b7791f;">-{{ number_format($summary->total_senior, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_net, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_cash, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_card, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_copay, 2) }}</td>
                        <td class="text-right" style="color: #2e7d32;">{{ number_format($summary->total_paid, 2) }}</td>
                        <td class="text-right" style="color: #c62828;">{{ number_format($summary->total_balance, 2) }}</td>
                        <td></td>
                    </tr>
                @else
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 12px; color: #a0aec0; font-style: italic;">
                            No patient consultations recorded for this date.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- Verification Signatures --}}
        <table class="signature-table">
            <tr>
                <td style="width: 45%; vertical-align: bottom;">
                    <div style="width: 200px;">
                        <div style="font-size: 8.5px; color: #718096; margin-bottom: 24px;">Prepared By (Cashier / Secretary):</div>
                        <div style="border-top: 1px solid #4a5568; padding-top: 3px; font-weight: bold; font-size: 10px;">
                            {{ $generatedBy }}
                        </div>
                    </div>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; vertical-align: bottom; text-align: right;">
                    <div style="width: 200px; display: inline-block; text-align: center;">
                        <div style="font-size: 8.5px; color: #718096; margin-bottom: 24px;">Certified True and Correct:</div>
                        <div style="border-top: 1px solid #4a5568; padding-top: 3px; font-weight: bold; font-size: 10px;">
                            Clinic Administrator / Billing Officer
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
