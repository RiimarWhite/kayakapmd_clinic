<body style="height: 100vh;">
    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="7.5rem" align="center">
                {{-- Detailed Comment: Guard PNG rendering with function_exists('imagecreatefrompng') to prevent Dompdf crashes if GD extension is unavailable --}}
                @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/company_logo.png')))
                    <img src="{{ public_path('images/company_logo.png') }}" width="80">
                @else
                    <div style="font-weight: bold; font-size: 22px; color: #1e88e5;">KAYAKAP</div>
                @endif
            </td>
            <td style="align-items: top;" align="left">
                <h3 style="margin: 0;">{{ $profile->HOSP_NAME ?? config('app.name', 'KayakapMD Clinic') }}</h3>
                <p style="margin: 0;">{{ $profile->HOSP_ADDBRGY ?? '' }}</p>
            </td>
        </tr>
    </table>

    <section>
        <hr style="margin: 5px;">

        <table style="width: 100%; padding: 1rem;">
            <tr>
                <td style="font-weight: bold;">Name:</td>
                <td colspan="4">{{ $patient->patientname ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold; width: 100px; text-align: end;">Age:</td>
                <td style="padding-left: 10px;">{{ $patient->age ?? '' }}</td>

                <td style="font-weight: bold; width: 100px; text-align: end;">Sex:</td>
                <td style="padding-left: 10px;">{{ $patient->gender ?? '' }}</td>

                <td style="font-weight: bold; width: 100px; text-align: end;">Date:</td>
                <td style="padding-left: 10px;">{{ now()->format('m/d/Y') }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold; width: 100px;">Address:</td>
                <td colspan="3">{{ $patient->address ?? '' }}</td>
            </tr>
        </table>

        <article style="padding: 2rem;">
            @if ($type === "rx")
                <table>
                    <tr>
                        <td style="vertical-align: top;">
                            @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/rx_icon.png')))
                                <img style="max-width: 8rem;" src="{{ public_path('images/rx_icon.png')}}" alt="rx_logo">
                            @else
                                <div style="font-size: 38px; font-weight: bold; font-family: serif; color: #1e88e5; margin-right: 15px; line-height: 1;">&#8478;</div>
                            @endif
                        </td>

                        <td style="vertical-align: top;">
                            @if (!empty($medicines) && count($medicines) > 0)
                                @foreach ($medicines as $medicine)
                                    @php
                                        $medName = is_array($medicine) ? ($medicine['medicinename'] ?? '') : ($medicine->medicinename ?? $medicine->item_dscr ?? '');
                                        $dosage = is_array($medicine) ? ($medicine['medicinedosage'] ?? '') : ($medicine->medicinedosage ?? '');
                                        $duration = is_array($medicine) ? ($medicine['medicineduration'] ?? '') : ($medicine->medicineduration ?? '');
                                        $qty = is_array($medicine) ? ($medicine['medicinequantity'] ?? $medicine['qty'] ?? 1) : ($medicine->medicinequantity ?? $medicine->qty ?? 1);
                                    @endphp
                                    <div style="margin-bottom: 8px; margin-left: 10px;">
                                        <p style="margin-bottom: 0; font-weight: bold; font-size: 14px;">{{ $medName }}</p>
                                        <p style="font-size: 13px; margin: 0; color: #333;">
                                            @if ($dosage) Dosage: {{ $dosage }} &nbsp;&nbsp;&nbsp;&nbsp; @endif
                                            @if ($duration) Duration: {{ $duration }} &nbsp;&nbsp;&nbsp;&nbsp; @endif
                                            Quantity: {{ $qty }}
                                        </p>
                                    </div>
                                @endforeach
                            @else
                                <p style="margin-left: 10px; color: #666; font-style: italic;">No prescription medicines recorded.</p>
                            @endif
                        </td>
                    </tr>
                </table>
            @elseif ($type === "instructions")
                <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 4px;">INSTRUCTIONS</h4>
                <p style="font-size: 14px; white-space: pre-wrap; line-height: 1.6;">{{ !empty($patient->instructions) ? $patient->instructions : 'No special instructions recorded.' }}</p>
            @elseif ($type === "diagnostics")
                <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 4px;">DIAGNOSTICS REQUEST</h4>
                @if (!empty($requests) && count($requests) > 0)
                    <ul style="font-size: 14px; line-height: 1.8;">
                        @foreach ($requests as $request)
                            @php
                                $diagName = is_object($request) ? ($request->diagnostic_name ?? $request->item_dscr ?? '') : (is_array($request) ? ($request['diagnostic_name'] ?? $request['item_dscr'] ?? '') : (string)$request);
                            @endphp
                            <li><strong>{{ $diagName }}</strong></li>
                        @endforeach
                    </ul>
                @else
                    <p style="color: #666; font-style: italic;">No diagnostic requests recorded.</p>
                @endif
            @elseif ($type === "admission")
                {{-- Detailed Comment: Admission orders and instructions to kin / watcher --}}
                <div style="background-color: #f0f4f8; border-left: 4px solid #1e88e5; padding: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #1e88e5; font-size: 16px;">ADMISSION ORDERS &amp; INSTRUCTIONS TO KIN</h3>
                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">
                        Consultation Ref: {{ $patient->consultationrefno ?? 'N/A' }} | Case No: {{ $patient->caseno ?? 'N/A' }}
                    </p>
                </div>

                <table style="width: 100%; margin-bottom: 15px; font-size: 13px;">
                    <tr>
                        <td style="width: 160px; font-weight: bold; vertical-align: top;">Admitting Impression:</td>
                        <td>{{ !empty($patient->impression) ? $patient->impression : (!empty($patient->reasonforconsultation) ? $patient->reasonforconsultation : 'For clinical evaluation and management') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; vertical-align: top; padding-top: 8px;">Admission Status:</td>
                        <td style="padding-top: 8px;">
                            <span style="display: inline-block; background-color: #e8f5e9; color: #2e7d32; font-weight: bold; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                                DIRECT ADMISSION RECOMMENDED
                            </span>
                        </td>
                    </tr>
                </table>

                <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-top: 15px; font-size: 14px;">PHYSICIAN'S ORDERS &amp; INSTRUCTIONS FOR ADMITTING</h4>
                <div style="font-size: 13px; line-height: 1.6; white-space: pre-wrap; background-color: #fafafa; border: 1px solid #e0e0e0; padding: 12px; border-radius: 4px; margin-bottom: 15px;">{{ !empty($patient->foradmit_instructions) ? $patient->foradmit_instructions : (!empty($patient->instructions) ? $patient->instructions : 'Please escort patient to Admitting Section / Emergency Room for direct admission and room assignment.') }}</div>

                <div style="border: 1px dashed #bbb; padding: 10px; font-size: 11px; color: #555; background-color: #fffde7;">
                    <strong>Instructions for Patient Watcher / Immediate Kin:</strong>
                    <ol style="margin: 5px 0 0 18px; padding: 0;">
                        <li>Present this order slip immediately to the Admitting Department or Emergency Room Triage.</li>
                        <li>Bring patient's valid PhilHealth Member Data Record (MDR), HMO card/authorization, and government-issued ID.</li>
                        <li>Maintain NPO (nothing by mouth) if fasting laboratory or immediate surgical procedure was instructed above.</li>
                    </ol>
                </div>
            @elseif ($type === "soa")
                {{-- Detailed Comment: Statement of Account (Outpatient Billing Slip) --}}
                <div style="background-color: #f5f5f5; border-left: 4px solid #43a047; padding: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #2e7d32; font-size: 16px;">STATEMENT OF ACCOUNT (OUTPATIENT BILLING SLIP)</h3>
                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">
                        Transaction Ref: <strong>{{ $settlement->transactionrefno ?? 'TRX-PENDING' }}</strong> |
                        Consultation Ref: {{ $patient->consultationrefno ?? 'N/A' }} |
                        Date: {{ !empty($settlement->created) ? date('m/d/Y h:i A', strtotime($settlement->created)) : now()->format('m/d/Y h:i A') }}
                    </p>
                </div>

                <h4 style="border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 8px; font-size: 13px;">ITEMIZED CHARGES</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                    <thead>
                        <tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <th style="padding: 6px; text-align: left;">Particulars / Service</th>
                            <th style="padding: 6px; text-align: left;">Category</th>
                            <th style="padding: 6px; text-align: center; width: 40px;">Qty</th>
                            <th style="padding: 6px; text-align: right; width: 80px;">Unit Price</th>
                            <th style="padding: 6px; text-align: right; width: 80px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($charges) && count($charges) > 0)
                            @foreach ($charges as $charge)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 5px 6px;">{{ $charge->item_dscr }}</td>
                                    <td style="padding: 5px 6px; color: #666;">{{ $charge->item_grouping ?? 'OTHER' }}</td>
                                    <td style="padding: 5px 6px; text-align: center;">{{ (float)($charge->qty ?: 1) }}</td>
                                    <td style="padding: 5px 6px; text-align: right;">₱{{ number_format((float)($charge->cost_ave ?? $charge->retails ?? 0), 2) }}</td>
                                    <td style="padding: 5px 6px; text-align: right;">₱{{ number_format((float)($charge->totalamt ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="padding: 12px; text-align: center; color: #888; font-style: italic;">No charges recorded for this consultation.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <table style="width: 100%; font-size: 12px; margin-bottom: 15px;">
                    <tr>
                        <td style="width: 55%; vertical-align: top; padding-right: 20px;">
                            <div style="border: 1px solid #e0e0e0; padding: 10px; border-radius: 4px; background-color: #fafafa;">
                                <strong style="font-size: 11px; color: #444;">PAYMENT SETTLEMENT CHANNELS</strong>
                                <table style="width: 100%; font-size: 11px; margin-top: 5px;">
                                    <tr>
                                        <td>Cash Payment:</td>
                                        <td style="text-align: right; font-weight: bold;">₱{{ number_format((float)($settlement->payment_cash ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Card / Electronic ({{ strtoupper($settlement->cta_type ?? 'None') }}):</td>
                                        <td style="text-align: right; font-weight: bold;">₱{{ number_format((float)($settlement->payment_card ?? 0), 2) }}</td>
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
                            <table style="width: 100%; font-size: 12px;">
                                <tr>
                                    <td style="padding: 3px 0;">Total Gross Charges:</td>
                                    <td style="padding: 3px 0; text-align: right; font-weight: bold;">₱{{ number_format((float)($settlement->total_gross ?? (!empty($charges) ? $charges->sum('totalamt') : 0)), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #2e7d32;">Less PhilHealth (PHIC):</td>
                                    <td style="padding: 3px 0; text-align: right; color: #2e7d32;">- ₱{{ number_format((float)($settlement->less_phic ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #1565c0;">Less HMO Deduction:</td>
                                    <td style="padding: 3px 0; text-align: right; color: #1565c0;">- ₱{{ number_format((float)($settlement->less_hmo ?? 0), 2) }}</td>
                                </tr>
                                <tr style="border-top: 2px solid #333;">
                                    <td style="padding: 6px 0; font-weight: bold; font-size: 14px;">Net Payable / Settled:</td>
                                    <td style="padding: 6px 0; text-align: right; font-weight: bold; font-size: 14px;">₱{{ number_format((float)($settlement->net_payable ?? 0), 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @endif
        </article>
    </section>

    @if ($type === "soa")
        <footer style="position: absolute; bottom: 0; right: 0; text-align: right;">
            <h5 style="margin-bottom: 2px;">{{ $settlement->createdby ?? 'Secretary / Cashier' }}</h5>
            <div style="font-size: 11px; color: #555; margin-bottom: 4px;">Billing Officer / Cashier</div>
            <hr style="width: 12rem; margin-top: 2px; margin-bottom: 4px; border: 0; border-top: 1px solid #333;">
            <div style="font-size: 10px; color: #777;">Official Outpatient Consultation Statement</div>
        </footer>
    @else
        <footer style="position: absolute; bottom: 0; right: 0; text-align: right;">
            <h5 style="margin-bottom: 2px;">{{ $doctor->docname ?? 'Attending Physician' }}</h5>
            <div style="font-size: 11px; color: #555; margin-bottom: 4px;">Physician's Signature</div>
            @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/company_logo.png')))
                <img style="height: 45px;" src="{{ public_path('images/company_logo.png') }}">
            @else
                <div style="height: 45px;"></div>
            @endif
            <hr style="width: 12rem; margin-top: 2px; margin-bottom: 4px;">
            <table style="font-size: 10px; float: right;">
                <tr>
                    <td style="text-align: end; font-weight: bold;">License No.</td>
                    <td style="padding-left: 6px;">{{ $doctor->Licno ?? $doctor->licno ?? '' }}</td>
                </tr>

                <tr>
                    <td style="text-align: end; font-weight: bold;">PTR No.</td>
                    <td style="padding-left: 6px;">{{ $doctor->PTR ?? $doctor->ptr ?? '' }}</td>
                </tr>

                <tr>
                    <td style="text-align: end; font-weight: bold;">S2 No.</td>
                    <td style="padding-left: 6px;">{{ $doctor->S2no ?? $doctor->s2no ?? '' }}</td>
                </tr>
            </table>
        </footer>
    @endif
</body>