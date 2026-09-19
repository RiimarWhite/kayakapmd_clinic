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
            @endif
        </article>
    </section>

    <footer style="position: absolute; bottom: 0; right: 0; text-align: right; display: flex;">
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
</body>