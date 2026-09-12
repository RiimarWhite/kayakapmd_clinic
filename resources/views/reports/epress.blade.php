<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Electronic Prescription Slip (ePresS)</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #000;
                padding: 15px;
                background: #fff;
            }

            .page-wrapper {
                border: 1px solid #999;
                padding: 10px;
                width: 100%;
            }

            /* ── HEADER ── */
            .header {
                display: table;
                width: 100%;
                padding-bottom: 6px;
                margin-bottom: 6px;
            }

            .header-logo {
                display: table-cell;
                width: 50px;
                vertical-align: middle;
            }

            .logo-placeholder {
                width: 40px;
                height: 40px;
                background: #4a7c4e;
                border-radius: 50%;
                display: inline-block;
                text-align: center;
                line-height: 40px;
                color: #fff;
                font-weight: bold;
                font-size: 14px;
            }

            .header-title {
                display: table-cell;
                vertical-align: middle;
                padding-left: 6px;
            }

            .header-title h1 {
                font-size: 13px;
                font-weight: bold;
                text-decoration: underline;
                text-transform: uppercase;
            }

            /* ── FIELD ROWS ── */
            .field-row {
                display: table;
                width: 100%;
                margin-bottom: 4px;
            }

            .field-cell {
                display: table-cell;
                white-space: nowrap;
                padding-right: 10px;
                vertical-align: bottom;
            }

            .field-label {
                font-size: 9px;
            }

            .field-line {
                display: inline-block;
                border-bottom: 1px solid #000;
                vertical-align: bottom;
                height: 12px;
            }

            .field-line-xs {
                min-width: 8px;
            }

            .field-line-sm {
                min-width: 70px;
            }

            .field-line-md {
                min-width: 120px;
            }

            .field-line-lg {
                min-width: 180px;
            }

            .field-line-xl {
                min-width: 220px;
            }

            /* spacer between HCI rows */
            .spacer {
                height: 6px;
            }

            /* ── SECTION HEADER ── */
            .section-header {
                background: #c6d9f0;
                padding: 4px 6px;
                font-size: 9px;
                font-style: italic;
                margin-top: 8px;
                margin-bottom: 0;
                border: 1px solid #999;
                border-bottom: none;
            }

            /* ── PRESCRIPTION TABLE ── */
            .rx-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9px;
            }

            .rx-table th,
            .rx-table td {
                border: 1px solid #999;
                padding: 4px 5px;
                vertical-align: middle;
            }

            .rx-table thead th {
                background: #f0f0f0;
                text-align: center;
                font-size: 9px;
            }

            .rx-table .col-cat {
                width: 12%;
                text-align: center;
            }

            .rx-table .col-med {
                width: 20%;
                text-align: center;
            }

            .rx-table .col-qty {
                width: 8%;
                text-align: center;
            }

            .rx-table .col-phys {
                width: 20%;
                text-align: center;
            }

            .rx-table .col-disp {
                width: 15%;
                text-align: center;
            }

            .rx-table .col-date {
                width: 12%;
                text-align: center;
            }

            .rx-table .col-dispname {
                width: 13%;
                text-align: center;
            }

            .rx-table tbody td {
                height: 36px;
                vertical-align: top;
                padding-top: 5px;
            }

            /* physician / dispensing sig lines inside table */
            .td-sig-line {
                border-bottom: 1px solid #000;
                height: 11px;
                margin-top: 6px;
                width: 90%;
            }

            /* ── PATIENT SECTION ── */
            .patient-section {
                border: 1px solid #999;
                padding: 8px 10px;
                font-size: 10px;
            }

            .question-block {
                margin-bottom: 8px;
            }

            .question-text {
                font-size: 10px;
            }

            .question-sub {
                font-size: 9px;
                font-weight: bold;
            }

            /* Yes / No blanks */
            .yn-blank {
                display: inline-block;
                border-bottom: 1px solid #000;
                width: 18px;
                height: 10px;
                margin-right: 2px;
                vertical-align: bottom;
            }

            /* Smiley row */
            .smiley-group {
                display: inline-block;
                margin-right: 8px;
                vertical-align: middle;
            }

            .checkbox-sq {
                display: inline-block;
                width: 10px;
                height: 10px;
                border: 1px solid #000;
                vertical-align: middle;
                margin-right: 3px;
            }

            .smiley-icon {
                display: inline-block;
                width: 22px;
                height: 22px;
                border-radius: 50%;
                text-align: center;
                line-height: 22px;
                font-size: 14px;
                vertical-align: middle;
                font-weight: bold;
            }

            .smiley-happy {
                background: #4caf50;
                color: #fff;
            }

            .smiley-neutral {
                background: #ffc107;
                color: #fff;
            }

            .smiley-sad {
                background: #f44336;
                color: #fff;
            }

            /* Comment lines */
            .comment-line {
                border-bottom: 1px solid #000;
                height: 14px;
                margin-bottom: 6px;
                width: 100%;
            }

            /* Attestation */
            .attestation {
                font-size: 9.5px;
                margin: 8px 0;
                line-height: 1.4;
            }

            /* Signature row */
            .sig-row {
                display: table;
                width: 100%;
                margin-top: 12px;
            }

            .sig-left,
            .sig-right {
                display: table-cell;
                vertical-align: bottom;
                width: 50%;
            }

            .sig-line {
                border-bottom: 1px solid #000;
                height: 14px;
                width: 80%;
                margin-bottom: 2px;
            }

            .sig-label {
                font-size: 9px;
            }

            .sig-label-bold {
                font-size: 9px;
                font-weight: bold;
            }

            /* Note */
            .note-section {
                margin-top: 10px;
                font-size: 9.5px;
                line-height: 1.5;
            }

            .bold-italic {
                font-weight: bold;
                font-style: italic;
            }
        </style>
    </head>

    <body>
        <!-- ══ HEADER ══ -->
        <div class="header">
            <div class="header-logo">
                <img src="{{ public_path('images/phic.png') }}" width="40" height="40" alt="PhilHealth">
                {{-- <span class="logo-placeholder">P</span> --}}
            </div>
            <div class="header-title">
                <h1>Electronic Prescription Slip (ePresS)</h1>
            </div>
        </div>

        <!-- ══ HCI ROW 1 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">HCI Name:</span>
                <span class="field-line field-line-xl">{{ $hciName ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Case No.:</span>
                <span class="field-line field-line-xl">{{ $caseNo ?? '' }}</span>
            </div>
        </div>

        <!-- ══ HCI ROW 2 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">HCI Accreditation No.</span>
                <span class="field-line field-line-lg">{{ $accreno ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Transaction No:</span>
                <span class="field-line field-line-xl">{{ $transNo ?? '' }}</span>
            </div>
        </div>

        <div class="spacer"></div>

        <!-- ══ PATIENT ROW 1 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">Patient Name <strong>(pangalan ng pasyente)</strong>:</span>
                <span class="field-line field-line-xl">{{ $patientName ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Age <strong>(edad):</strong></span>
                <span class="field-line field-line-sm">{{ $age ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Contact No.</span>
                <span class="field-line field-line-lg">{{ $pxContactNo ?? '' }}</span>
            </div>
        </div>

        <!-- ══ PATIENT ROW 2 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">PIN (PhilHealth Identification Number):</span>
                <span class="field-line field-line-md">{{ $pxPin ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Membership Category:</span>
                <span class="field-line field-line-md">{{ $membershipCategory ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Membership type:</span>
                <span class="field-line field-line-xs"
                    style="padding-left: 8px">{{ $membershipType == 'MM' ? 'X' : '' }}</span>
                <span class="field-label" style="margin-left:3px;">Member:</span>
                <span class="field-line field-line-xs"
                    style="padding-left: 8px">{{ $membershipType == 'DD' ? 'X' : '' }}</span>
                <span class="field-label" style="margin-left:3px;">Dependent</span>
            </div>
        </div>

        <!-- ══ FACILITY SECTION ══ -->
        <div class="section-header">To be filled out by the facility <em>(pupunuan ng pasilidad)</em></div>

        <table class="rx-table">
            <thead>
                <tr>
                    <th class="col-cat">
                        Category<br><em>(Kategorya)</em>
                    </th>
                    <th class="col-med">
                        Medicine Strength/Form/Volume<br>
                        <em>(Gamot/Anyo/Dami)</em>
                    </th>
                    <th class="col-qty">
                        Quantity<br><em>(bilang)</em>
                    </th>
                    <th class="col-phys">
                        Name of the Prescribing Physician<br>
                        <em>(Pangalan ng nagresetsang doktor)</em>
                    </th>
                    <th class="col-disp">
                        &#10003; Dispensed <em>(naibigay)</em><br>
                        X Not dispensed <em>(hindi naibigay)</em>
                    </th>
                    <th class="col-date">
                        Date dispensed<br>
                        <em>(Petsa kung kelan naibigay)</em>
                    </th>
                    <th class="col-dispname">
                        Name of the Dispensing Personnel<br>
                        <em>(Pangalan ng nagbibigay)</em>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($medicines as $medicine)
                    <tr>
                        <td class="col-cat">Antibacterial (dummy)</td>
                        <td class="col-med">{{ $medicine->medicineDetails->DRUG_DESC ?? '' }}</td>
                        <td class="col-qty">{{ $medicine->qty ?? '' }}</td>
                        <td class="col-phys">{{ $medicine->doc_name ?? '' }}</td>
                        <td class="col-disp">{{ $medicine->is_dispensed ?? '' }}</td>
                        <td class="col-date">{{ $medicine->dispensed_date ?? '' }}</td>
                        <td class="col-dispname">{{ $medicine->dispensedby ?? '' }}</td>
                    </tr>
                @endforeach
                <!-- Row 1: Antibacterial -->
                {{-- <tr>
                    <td class="col-cat">Antibacterial</td>
                    <td class="col-med">
                        {{ $med1_name ?? 'Amoxicillin' }}<br>
                        {{ $med1_strength ?? '500 mg Capsule' }}
                    </td>
                    <td class="col-qty">{{ $med1_qty ?? '' }}</td>
                    <td class="col-phys">{{ $med1_physician ?? '' }}</td>
                    <td class="col-disp">{{ $med1_dispensed ?? '' }}</td>
                    <td class="col-date">{{ $med1_date ?? '' }}</td>
                    <td class="col-dispname">{{ $med1_dispenser ?? '' }}</td>
                </tr>
                <!-- Row 2: Antipyretic — with sig lines -->
                <tr>
                    <td class="col-cat">Antipyretic</td>
                    <td class="col-med">
                        {{ $med2_name ?? 'Paracetamol' }}<br>
                        {{ $med2_strength ?? '500 mg Tablet' }}
                    </td>
                    <td class="col-qty">{{ $med2_qty ?? '' }}</td>
                    <td class="col-phys">
                        Signature over printed name<br>
                        License #:
                        <div class="td-sig-line">{{ $physician_license ?? '' }}</div>
                    </td>
                    <td class="col-disp">{{ $med2_dispensed ?? '' }}</td>
                    <td class="col-date">{{ $med2_date ?? '' }}</td>
                    <td class="col-dispname">
                        Signature over printed name<br>
                        Name of Dispensing Facility:
                        <div class="td-sig-line">{{ $dispensing_facility ?? '' }}</div>
                    </td>
                </tr> --}}
            </tbody>
        </table>

        <!-- ══ PATIENT SECTION ══ -->
        <div class="section-header">To be filled out by the patient <em>(pupunuan ng pasyente)</em></div>

        <div class="patient-section">

            <!-- Q1: Received medicines -->
            <div class="question-block">
                <span class="question-text">Did you receive the above mentioned medicines?</span>
                &nbsp;
                <span class="yn-blank">{{ $received_yes ?? '' }}</span> Yes
                &nbsp;&nbsp;
                <span class="yn-blank">{{ $received_no ?? '' }}</span> No
                <br>
                <span class="question-sub">(Natanggap mo ba ang mga gamot na nabanggit?)</span>
            </div>

            <!-- Q2: Satisfied + smileys on same line -->
            <div class="question-block">
                <span class="question-text">Are you satisfied with the medicines you received?</span>
                &nbsp;&nbsp;
                <img src="{{ public_path('images/emoticons.png') }}" class="header-logo-img" alt="Emoticons">
                <br>
                <span class="question-sub">(Nasiyahan ka ba sa mga gamot na natanggap mo?)</span>
            </div>

            <!-- Comment -->
            <div class="question-block">
                <span class="question-text">For your comment, suggestion or complaint:</span><br>
                <span class="question-sub">(Para sa iyong komento, mungkahi o reklamo)</span>
                <div style="margin-top:6px;">
                    <div class="comment-line">{{ $comment_1 ?? '' }}</div>
                    <div class="comment-line">{{ $comment_2 ?? '' }}</div>
                </div>
            </div>

            <!-- Attestation -->
            <div class="attestation">
                Under the penalty of law, I attest that the information I provided in this slip are true and
                accurate.<br>
                <strong>(Sa ilalim ng batas, pinatutunayan ko na ang impormasyong ibinigay ko ay totoo at
                    tama)</strong>
            </div>

            <!-- Signature row -->
            <div class="sig-row">
                <div class="sig-left">
                    <div class="sig-line">{{ $patient_signature ?? '' }}</div>
                    <div class="sig-label">Signature over printed name of patient</div>
                    <div class="sig-label-bold">(Lagda sa nakalimbag na pangalan ng pasyente)</div>
                </div>
                <div class="sig-right" style="padding-left: 20px;">
                    <div class="sig-label">
                        Next Dispensing Date:
                        <span class="field-line field-line-lg">{{ $next_dispensing_date ?? '' }}</span>
                    </div>
                    <div class="sig-label-bold">(Petsa ng susunod na bigay ng gamot)</div>
                </div>
            </div>

        </div><!-- end patient-section -->

        <!-- ══ NOTE ══ -->
        <div class="note-section">
            <strong>Note:</strong><br>
            Accomplished form shall be submitted to PhilHealth.<br>
            <span class="bold-italic">(Ang kumpletong form ay dapat isumite sa PhilHealth)</span>
        </div>
    </body>

</html>
