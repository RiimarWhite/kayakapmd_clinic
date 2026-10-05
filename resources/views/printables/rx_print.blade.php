<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ strtoupper($type) }} - {{ $patient->patientname ?? 'Consultation' }}</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #2d3748;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Outer Frame matching reference image */
        .rx-outer-border {
            border: 3.5px solid #286aa0;
            border-radius: 8px;
            padding: 16px 20px;
            min-height: 980px;
            position: relative;
            box-sizing: border-box;
        }

        /* Typography & Colors */
        .primary-blue {
            color: #286aa0;
        }

        .dark-navy {
            color: #1a365d;
        }

        .muted-text {
            color: #718096;
        }

        /* Patient Information Box */
        .patient-info-box {
            background-color: #edf5fc;
            border-radius: 6px;
            padding: 8px 14px;
            margin-top: 10px;
            margin-bottom: 16px;
        }

        .info-row-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .info-row-table td {
            padding: 3px 0;
            vertical-align: bottom;
        }

        .info-label {
            color: #4a5568;
            font-weight: 600;
            white-space: nowrap;
        }

        .info-underline {
            border-bottom: 1px solid #94a3b8;
            color: #1a202c;
            font-weight: 500;
            padding-left: 6px;
            padding-right: 6px;
        }

        /* Rx Section */
        .rx-symbol {
            font-size: 42px;
            font-family: "Times New Roman", Times, serif;
            font-weight: 900;
            color: #286aa0;
            line-height: 1;
            margin-right: 12px;
        }

        .med-item-name {
            font-size: 13px;
            font-weight: bold;
            color: #1a202c;
        }

        .med-item-sig {
            font-size: 12px;
            color: #2d3748;
            margin-top: 2px;
            padding-left: 8px;
        }

        /* Footer & decorative layout */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .contact-item {
            font-size: 9px;
            color: #4a5568;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="rx-outer-border">
        {{-- Header Section: Left Stethoscope/Heart Logo + Doctor Details, Right Hospital / Clinic Title --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 58px; vertical-align: middle;">
                    {{-- Detailed Comment: Scalable Stethoscope forming Heart vector icon matching reference image --}}
                    <svg width="52" height="58" viewBox="0 0 64 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 6 V22 C14 32 26 38 32 44 C38 38 50 32 50 22 V6" stroke="#286aa0" stroke-width="4" stroke-linecap="round" fill="none"/>
                        <circle cx="14" cy="6" r="3.5" fill="#286aa0"/>
                        <circle cx="50" cy="6" r="3.5" fill="#286aa0"/>
                        <path d="M32 44 V52 C32 58 24 62 24 62 C18 58 14 53 14 48 C14 42 20 40 24 44 C28 40 34 42 34 48" stroke="#286aa0" stroke-width="3" stroke-linecap="round" fill="none"/>
                        <circle cx="48" cy="50" r="7" stroke="#286aa0" stroke-width="3.5" fill="none"/>
                        <path d="M32 52 H41" stroke="#286aa0" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </td>
                <td style="vertical-align: middle; padding-left: 12px;">
                    <div style="font-size: 19px; font-weight: bold; color: #286aa0; letter-spacing: 0.3px;">
                        @php
                            $docDisplayName = $doctor->docname ?? 'Attending Physician';
                            if (!empty($docDisplayName) && !preg_match('/^dr\.?\s+/i', $docDisplayName)) {
                                $docDisplayName = 'Dr. ' . $docDisplayName;
                            }
                        @endphp
                        {{ $docDisplayName }}
                    </div>
                    <div style="font-size: 10px; font-weight: bold; color: #4a5568; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px;">
                        {{ $doctor->specialization ?? 'QUALIFICATION / GENERAL PRACTITIONER' }}
                    </div>
                    <div style="width: 100%; border-bottom: 2px solid #7ea6cb; margin-top: 4px; margin-bottom: 4px;"></div>
                    <div style="font-size: 9px; color: #718096;">
                        @if(!empty($doctor->Licno)) Lic. No.: <strong>{{ $doctor->Licno }}</strong> &nbsp;|&nbsp; @endif
                        @if(!empty($doctor->PTR)) PTR No.: <strong>{{ $doctor->PTR }}</strong> &nbsp;|&nbsp; @endif
                        @if(!empty($doctor->S2no)) S2 No.: <strong>{{ $doctor->S2no }}</strong> @endif
                    </div>
                </td>
                <td style="width: 180px; vertical-align: middle; text-align: right;">
                    <div style="font-size: 15px; font-weight: bold; color: #286aa0; text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ $profile->HOSP_NAME ?? config('app.name', 'KayakapMD Clinic') }}
                    </div>
                    <div style="font-size: 8.5px; color: #718096; margin-top: 2px; text-transform: uppercase; letter-spacing: 1px;">
                        {{ $profile->businessgroup_name ?? 'CLINICAL CARE & SERVICES' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Patient Information Box (Soft light blue background with underlined fields including Diagnosis) --}}
        <div class="patient-info-box">
            <table class="info-row-table">
                <tr>
                    <td class="info-label" style="width: 85px;">Patient Name:</td>
                    <td class="info-underline" colspan="3">{{ $patient->patientname ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Address:</td>
                    <td class="info-underline" colspan="3">{{ $patient->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                            <tr>
                                <td class="info-label" style="width: 40px;">Age:</td>
                                <td class="info-underline" style="width: 25%;">{{ $patient->age ?? 'N/A' }}</td>
                                <td class="info-label" style="width: 40px; padding-left: 12px;">Sex:</td>
                                <td class="info-underline" style="width: 25%;">{{ $patient->gender ?? 'N/A' }}</td>
                                <td class="info-label" style="width: 40px; padding-left: 12px;">Date:</td>
                                <td class="info-underline" style="width: 25%;">{{ now()->format('m/d/Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Diagnosis:</td>
                    <td class="info-underline" colspan="3">
                        {{ $patient->finadiagnosis ?: (!empty($patient->impression) ? $patient->impression : (!empty($patient->reasonforconsultation) ? $patient->reasonforconsultation : '')) }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Main Document Content Area --}}
        <div style="min-height: 480px; padding: 4px 6px;">
            @if ($type === "rx")
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        {{-- Detailed Comment: Prominent theme-matching blue Rx Symbol image with ASCII Latin 'Rx' fallback to prevent Dompdf '?' missing glyphs --}}
                        <td style="vertical-align: top; width: 48px;">
                            @php
                                $rxImgPath = file_exists(public_path('images/rx_icon_blue.png'))
                                    ? public_path('images/rx_icon_blue.png')
                                    : (file_exists(public_path('images/rx_icon.png')) ? public_path('images/rx_icon.png') : null);
                            @endphp
                            @if ($rxImgPath)
                                <img src="{{ $rxImgPath }}" style="width: 44px; height: auto;" alt="Rx">
                            @else
                                <div style="font-size: 32px; font-weight: bold; font-family: 'Times New Roman', serif; color: #286aa0; line-height: 1;">Rx</div>
                            @endif
                        </td>

                        {{-- Itemized Medicine List and Instructions --}}
                        <td style="vertical-align: top; padding-left: 8px;">
                            @if (!empty($medicines) && count($medicines) > 0)
                                @foreach ($medicines as $medicine)
                                    @php
                                        $medName = is_array($medicine) ? ($medicine['medicinename'] ?? '') : ($medicine->medicinename ?? $medicine->item_dscr ?? '');
                                        $qty = is_array($medicine) ? ($medicine['medicinequantity'] ?? $medicine['qty'] ?? 1) : ($medicine->medicinequantity ?? $medicine->qty ?? 1);
                                        $instruction = is_array($medicine) ? ($medicine['instructions'] ?? '') : ($medicine->instructions ?? '');
                                        $dosage = is_array($medicine) ? ($medicine['medicinedosage'] ?? '') : ($medicine->medicinedosage ?? '');
                                        $duration = is_array($medicine) ? ($medicine['medicineduration'] ?? '') : ($medicine->medicineduration ?? '');
                                    @endphp
                                    <div style="margin-bottom: 16px;">
                                        {{-- Line 1: Name / Description and Quantity --}}
                                        <div class="med-item-name">
                                            {{ $loop->iteration }}. {{ $medName }}
                                            @if(!empty($dosage))
                                                <span style="font-weight: normal; color: #4a5568; font-size: 11.5px;">({{ $dosage }})</span>
                                            @endif
                                            @if (!empty($qty) && (float)$qty > 0)
                                                <span style="font-weight: 600; font-size: 12px; color: #286aa0; margin-left: 8px;">
                                                    # {{ (int)$qty == $qty ? (int)$qty : $qty }}
                                                </span>
                                            @endif
                                        </div>
                                        {{-- Line 2: Sig: Instructions --}}
                                        <div class="med-item-sig">
                                            <strong style="color: #286aa0;">Sig:</strong> {{ !empty($instruction) ? $instruction : 'As directed by physician' }}
                                            @if(!empty($duration))
                                                <span style="color: #718096; font-size: 10.5px; margin-left: 8px;">(Duration: {{ $duration }})</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p style="color: #a0aec0; font-style: italic; margin-top: 10px;">No prescription medicines recorded.</p>
                            @endif

                            {{-- Detailed Comment: General Rx instructions placed strictly at the bottommost part of the prescription --}}
                            @if (!empty($patient->instructions))
                                <div style="margin-top: 35px; border-top: 1px dashed #cbd5e0; padding-top: 8px;">
                                    <div style="font-size: 11px; font-weight: bold; color: #286aa0; text-transform: uppercase; margin-bottom: 3px;">
                                        General Advice / Special Instructions:
                                    </div>
                                    <div style="font-size: 11px; color: #4a5568; white-space: pre-wrap; line-height: 1.5;">{{ $patient->instructions }}</div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>

            @elseif ($type === "instructions")
                <h4 style="border-bottom: 2px solid #286aa0; padding-bottom: 4px; color: #286aa0; margin-top: 0;">CONSULTATION INSTRUCTIONS &amp; ORDERS</h4>
                <p style="font-size: 13px; white-space: pre-wrap; line-height: 1.6; color: #2d3748;">{{ !empty($patient->instructions) ? $patient->instructions : 'No special instructions recorded.' }}</p>

            @elseif ($type === "diagnostics")
                <h4 style="border-bottom: 2px solid #286aa0; padding-bottom: 4px; color: #286aa0; margin-top: 0;">DIAGNOSTICS &amp; LABORATORY REQUEST</h4>
                @if (!empty($requests) && count($requests) > 0)
                    <ul style="font-size: 13px; line-height: 1.8; color: #2d3748;">
                        @foreach ($requests as $request)
                            @php
                                $diagName = is_object($request) ? ($request->diagnostic_name ?? $request->item_dscr ?? '') : (is_array($request) ? ($request['diagnostic_name'] ?? $request['item_dscr'] ?? '') : (string)$request);
                            @endphp
                            <li><strong>{{ $diagName }}</strong></li>
                        @endforeach
                    </ul>
                @else
                    <p style="color: #a0aec0; font-style: italic;">No diagnostic requests recorded.</p>
                @endif

            @elseif ($type === "admission")
                <div style="background-color: #f0f4f8; border-left: 4px solid #286aa0; padding: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #286aa0; font-size: 15px;">ADMISSION ORDERS &amp; INSTRUCTIONS TO KIN</h3>
                    <p style="margin: 2px 0 0 0; font-size: 10px; color: #718096;">
                        Consultation Ref: {{ $patient->consultationrefno ?? 'N/A' }} | Case No: {{ $patient->caseno ?? 'N/A' }}
                    </p>
                </div>

                <table style="width: 100%; margin-bottom: 15px; font-size: 12px;">
                    <tr>
                        <td style="width: 160px; font-weight: bold; vertical-align: top; color: #4a5568;">Admitting Impression:</td>
                        <td style="color: #1a202c;">{{ !empty($patient->impression) ? $patient->impression : (!empty($patient->reasonforconsultation) ? $patient->reasonforconsultation : 'For clinical evaluation and management') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; vertical-align: top; padding-top: 8px; color: #4a5568;">Admission Status:</td>
                        <td style="padding-top: 8px;">
                            <span style="display: inline-block; background-color: #e8f5e9; color: #2e7d32; font-weight: bold; padding: 2px 8px; border-radius: 3px; font-size: 11px;">
                                DIRECT ADMISSION RECOMMENDED
                            </span>
                        </td>
                    </tr>
                </table>

                <h4 style="border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; margin-top: 15px; font-size: 13px; color: #286aa0;">PHYSICIAN'S ORDERS &amp; INSTRUCTIONS FOR ADMITTING</h4>
                <div style="font-size: 12px; line-height: 1.6; white-space: pre-wrap; background-color: #fafafa; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #2d3748;">{{ !empty($patient->foradmit_instructions) ? $patient->foradmit_instructions : (!empty($patient->instructions) ? $patient->instructions : 'Please escort patient to Admitting Section / Emergency Room for direct admission and room assignment.') }}</div>

                <div style="border: 1px dashed #cbd5e0; padding: 10px; font-size: 10px; color: #4a5568; background-color: #fffde7; border-radius: 4px;">
                    <strong>Instructions for Patient Watcher / Immediate Kin:</strong>
                    <ol style="margin: 4px 0 0 16px; padding: 0;">
                        <li>Present this order slip immediately to the Admitting Department or Emergency Room Triage.</li>
                        <li>Bring patient's valid PhilHealth Member Data Record (MDR), HMO card/authorization, and government-issued ID.</li>
                        <li>Maintain NPO (nothing by mouth) if fasting laboratory or immediate surgical procedure was instructed above.</li>
                    </ol>
                </div>

            @elseif ($type === "soa")
                <div style="background-color: #f7fafc; border-left: 4px solid #286aa0; padding: 8px 12px; margin-bottom: 12px;">
                    <h3 style="margin: 0; color: #286aa0; font-size: 14px;">STATEMENT OF ACCOUNT (OUTPATIENT BILLING SLIP)</h3>
                    <p style="margin: 2px 0 0 0; font-size: 10px; color: #718096;">
                        Transaction Ref: <strong>{{ $settlement->transactionrefno ?? 'TRX-PENDING' }}</strong> |
                        Consultation Ref: {{ $patient->consultationrefno ?? 'N/A' }} |
                        Date: {{ !empty($settlement->created) ? date('m/d/Y h:i A', strtotime($settlement->created)) : now()->format('m/d/Y h:i A') }}
                    </p>
                </div>

                <h4 style="border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; margin-bottom: 6px; font-size: 12px; color: #286aa0;">ITEMIZED CHARGES</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 12px;">
                    <thead>
                        <tr style="background-color: #edf2f7; border-bottom: 1px solid #cbd5e0;">
                            <th style="padding: 5px; text-align: left;">Particulars / Service</th>
                            <th style="padding: 5px; text-align: left;">Category</th>
                            <th style="padding: 5px; text-align: center; width: 40px;">Qty</th>
                            <th style="padding: 5px; text-align: right; width: 80px;">Unit Price</th>
                            <th style="padding: 5px; text-align: right; width: 80px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($charges) && count($charges) > 0)
                            @foreach ($charges as $charge)
                                <tr style="border-bottom: 1px solid #edf2f7;">
                                    <td style="padding: 4px 5px;">{{ $charge->item_dscr }}</td>
                                    <td style="padding: 4px 5px; color: #718096;">{{ $charge->item_grouping ?? 'OTHER' }}</td>
                                    <td style="padding: 4px 5px; text-align: center;">{{ (float)($charge->qty ?: 1) }}</td>
                                    <td style="padding: 4px 5px; text-align: right;">PHP {{ number_format((float)($charge->cost_ave ?? $charge->retails ?? 0), 2) }}</td>
                                    <td style="padding: 4px 5px; text-align: right;">PHP {{ number_format((float)($charge->totalamt ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="padding: 10px; text-align: center; color: #a0aec0; font-style: italic;">No charges recorded for this consultation.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <table style="width: 100%; font-size: 11px; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 55%; vertical-align: top; padding-right: 15px;">
                            <div style="border: 1px solid #e2e8f0; padding: 8px; border-radius: 4px; background-color: #fafafa;">
                                <strong style="font-size: 10px; color: #4a5568;">PAYMENT SETTLEMENT SUMMARY</strong>
                                <table style="width: 100%; font-size: 10px; margin-top: 4px;">
                                    <tr>
                                        <td>Cash Payment:</td>
                                        <td style="text-align: right; font-weight: bold;">PHP {{ number_format((float)($settlement->payment_cash ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Card / CTA ({{ strtoupper($settlement->cta_type ?? 'None') }}):</td>
                                        <td style="text-align: right; font-weight: bold;">PHP {{ number_format((float)($settlement->payment_card ?? 0), 2) }}</td>
                                    </tr>
                                    @if (!empty($settlement->hmo_type))
                                    <tr>
                                        <td>HMO Provider:</td>
                                        <td style="text-align: right; font-weight: bold;">{{ $settlement->hmo_type }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                        <td style="width: 45%; vertical-align: top;">
                            <table style="width: 100%; font-size: 11px;">
                                <tr>
                                    <td style="padding: 2px 0;">Total Gross Charges:</td>
                                    <td style="padding: 2px 0; text-align: right; font-weight: bold;">PHP {{ number_format((float)($settlement->total_gross ?? (!empty($charges) ? $charges->sum('totalamt') : 0)), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #2e7d32;">Less PhilHealth (PHIC):</td>
                                    <td style="padding: 2px 0; text-align: right; color: #2e7d32;">- PHP {{ number_format((float)($settlement->less_phic ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #1565c0;">Less HMO Deduction:</td>
                                    <td style="padding: 2px 0; text-align: right; color: #1565c0;">- PHP {{ number_format((float)($settlement->less_hmo ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 2px 0; color: #b7791f;">Less Senior/PWD Discount:</td>
                                    <td style="padding: 2px 0; text-align: right; color: #b7791f;">- PHP {{ number_format((float)($settlement->discount_senior ?? 0), 2) }}</td>
                                </tr>
                                <tr style="border-top: 1.5px solid #2d3748;">
                                    <td style="padding: 5px 0; font-weight: bold; font-size: 12px;">Net Payable:</td>
                                    <td style="padding: 5px 0; text-align: right; font-weight: bold; font-size: 12px;">PHP {{ number_format((float)($settlement->net_payable ?? 0), 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        {{-- Physician Signature Block (Aligned to the Right) --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    <div style="height: 30px;"></div>
                    <div style="border-top: 1.5px solid #4a5568; padding-top: 4px; display: inline-block; width: 220px;">
                        <div style="font-weight: bold; font-size: 11.5px; color: #1a202c; text-transform: uppercase;">
                            {{ $docDisplayName }}
                        </div>
                        <div style="font-size: 9px; color: #718096; margin-top: 1px;">Physician's Signature</div>
                        <div style="font-size: 8.5px; color: #718096; margin-top: 1px;">
                            @if(!empty($doctor->Licno)) Lic. No.: {{ $doctor->Licno }} @endif
                            @if(!empty($doctor->PTR)) &nbsp;|&nbsp; PTR: {{ $doctor->PTR }} @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Bottom 2x2 Contact Info Footer + Translucent Watermark Curve matching reference image --}}
        <div style="position: absolute; bottom: 16px; left: 20px; right: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    {{-- Contact Strip (Left Column: Phone & Address) --}}
                    <td style="width: 42%; vertical-align: top;">
                        <table style="border-collapse: collapse;">
                            <tr>
                                <td style="width: 16px; vertical-align: middle;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#286aa0"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.02l-2.2 2.19z"/></svg>
                                </td>
                                <td class="contact-item" style="padding-left: 4px;">
                                    {{ !empty($profile->TEL_NO) ? $profile->TEL_NO : (!empty($doctor->mobileno) ? $doctor->mobileno : '(02) 8800-0000') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 16px; vertical-align: middle; padding-top: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#286aa0"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
                                </td>
                                <td class="contact-item" style="padding-left: 4px; padding-top: 4px;">
                                    @php
                                        $hospAddr = array_filter([$profile->HOSP_ADDBRGY ?? '', $profile->HOSP_ADDMUN ?? '', $profile->HOSP_ADDPROV ?? '']);
                                    @endphp
                                    {{ !empty($hospAddr) ? implode(', ', $hospAddr) : ($profile->reports_address ?? 'Clinic Location, Philippines') }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    {{-- Contact Strip (Middle Column: Email & Website) --}}
                    <td style="width: 40%; vertical-align: top;">
                        <table style="border-collapse: collapse;">
                            <tr>
                                <td style="width: 16px; vertical-align: middle;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#286aa0"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                </td>
                                <td class="contact-item" style="padding-left: 4px;">
                                    {{ !empty($profile->EMAIL_ADD) ? $profile->EMAIL_ADD : (!empty($doctor->email) ? $doctor->email : 'info@kayakapmd.com') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 16px; vertical-align: middle; padding-top: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="#286aa0"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                </td>
                                <td class="contact-item" style="padding-left: 4px; padding-top: 4px;">
                                    {{ config('app.url') ? str_replace(['http://', 'https://'], '', config('app.url')) : 'www.kayakapmd.com' }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    {{-- Decorative Stethoscope Watermark Loop matching bottom right of reference image --}}
                    <td style="width: 18%; text-align: right; vertical-align: bottom;">
                        <svg width="80" height="55" viewBox="0 0 100 70" fill="none" style="opacity: 0.35;">
                            <path d="M95 65 C95 40 65 30 65 50 C65 70 100 65 95 30 C90 -5 50 10 40 35" stroke="#7ea6cb" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                        </svg>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>