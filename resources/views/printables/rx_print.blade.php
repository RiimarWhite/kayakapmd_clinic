<body style="height: 100vh;">
    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="7.5rem" align="center">
                <img src="{{ public_path('images/company_logo.png') }}" width="80">
            </td>
            <td style="align-items: top;" align="left">
                <h3 style="margin: 0;">{{ $profile->HOSP_NAME }}</h3>
                <p style="margin: 0;">{{ $profile->HOSP_ADDBRGY }}</p>
            </td>
        </tr>
    </table>

    <section>
        <hr style="margin: 5px;">

        <table style="width: 100%; padding: 1rem;">
            <tr>
                <td style="font-weight: bold;">Name:</td>
                <td colspan="4">{{ $patient->patientname }} {{-- implode(' ', array_filter([$patient->patientname, $patient->pxmidname, $patient->pxlastname, $patient->pxsuffix])) --}}</td>
            </tr>

            <tr>
                <td style="font-weight: bold; width: 100px; text-align: end;">Age:</td>
                <td style="padding-left: 10px;">{{ $patient->age }}</td>

                <td style="font-weight: bold; width: 100px; text-align: end;">Sex:</td>
                <td style="padding-left: 10px;">{{ $patient->gender }}</td>

                <td style="font-weight: bold; width: 100px; text-align: end;">Date:</td>
                <td style="padding-left: 10px;">{{ now()->format('m/d/Y') }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold; width: 100px;">Address:</td>
                <td colspan="3">{{ $patient->address }}</td>
            </tr>
        </table>

        <article style="padding: 2rem;">
            @if ($type === "rx")
                <table>
                    <tr>
                        <td style="vertical-align: top;">
                            <img style="max-width: 8rem;" src="{{ public_path('images/rx_icon.png')}}" alt="rx_logo">
                        </td>

                        <td style="vertical-align: top;">
                            @foreach ($medicines as $medicine)
                                <div style="margin-bottom: 2px; margin-left: 10px;">
                                    <p style="margin-bottom: 0; font-weight: bold; font-size: 14px;">{{ $medicine["medicinename"] }}</p>
                                    <p style="font-size: 14px; margin: 0;">
                                        Dosage: {{ $medicine['medicinedosage'] }}
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        Duration: {{ $medicine['medicineduration'] }}
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        Quantity: {{ $medicine['medicinequantity'] }}
                                    </p>
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </table>
            @elseif ($type === "instructions")
                <h4>INSTRUCTIONS</h4>
                <p>{{ $patient->instructions }}</p>
            @elseif ($type === "diagnostics")
                <h4>DIAGNOSTICS REQUEST</h4>
                <ul>
                    @foreach ($requests as $request)
                        <li>{{ $request->diagnostic_name }}</li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>

    <footer style="position: absolute; bottom: 0; right: 0; text-align: right; display: flex;">
        <h5>Physician's Signature</h5>
        <img style="height: 50px;" src="{{ public_path('images/company_logo.png') }}">
        <hr style="width: 10rem;">
        <table style="font-size: 10px;">
            <tr>
                <td style="text-align: end; font-weight: bold;">License No.</td>
                <td>{{ $doctor->Licno }}</td>
            </tr>

            <tr>
                <td style="text-align: end; font-weight: bold;">PTR No.</td>
                <td>{{ $doctor->PTR }}</td>
            </tr>

            <tr>
                <td style="text-align: end; font-weight: bold;">S2 No.</td>
                <td>{{ $doctor->S2no }}</td>
            </tr>
        </table>
    </footer>
</body>