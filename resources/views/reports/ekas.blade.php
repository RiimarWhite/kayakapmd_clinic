<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Electronic Konsulta Availment Slip (eKAS)</title>
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

            .header-logo-img {
                width: 40px;
                height: 40px;
            }

            /* Fallback icon when no image is supplied */
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

            /* ── ROW FIELDS ── */
            .field-row {
                display: table;
                width: 100%;
                margin-bottom: 4px;
            }

            .field-cell {
                display: table-cell;
                white-space: nowrap;
                padding-right: 10px;
            }

            .field-label {
                font-size: 9px;
            }

            .field-line {
                display: inline-block;
                border-bottom: 1px solid #000;
                min-width: 100px;
                vertical-align: bottom;
                height: 12px;
            }

            .field-line-sm {
                min-width: 60px;
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

            .field-line-full {
                min-width: 300px;
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

            /* ── TABLE ── */
            .konsulta-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9px;
            }

            .konsulta-table th,
            .konsulta-table td {
                border: 1px solid #999;
                padding: 4px 6px;
                vertical-align: middle;
            }

            .konsulta-table thead th {
                background: #f0f0f0;
                text-align: center;
                font-size: 9px;
            }

            .konsulta-table .col-service {
                width: 40%;
            }

            .konsulta-table .col-performed {
                width: 20%;
                text-align: center;
            }

            .konsulta-table .col-date {
                width: 20%;
                text-align: center;
            }

            .konsulta-table .col-by {
                width: 20%;
                text-align: center;
            }

            .konsulta-table tbody td {
                height: 20px;
            }

            /* ── PATIENT SECTION ── */
            .patient-section {
                border: 1px solid #999;
                padding: 8px 10px;
                font-size: 10px;
            }

            .question-block {
                margin-bottom: 10px;
            }

            .question-text {
                font-size: 10px;
            }

            .question-sub {
                font-size: 9px;
                font-weight: bold;
            }

            /* Yes / No */
            .yn-options {
                display: inline;
                margin-left: 6px;
            }

            .yn-blank {
                display: inline-block;
                border-bottom: 1px solid #000;
                width: 18px;
                height: 10px;
                margin-right: 2px;
                vertical-align: bottom;
            }

            /* Smiley faces */
            .smiley-row {
                margin-top: 4px;
                display: table;
                width: 100%;
            }

            .smiley-cell {
                display: table-cell;
                vertical-align: middle;
            }

            .smiley-group {
                display: inline-block;
                margin-right: 8px;
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
            .comment-lines {
                margin-top: 6px;
                margin-bottom: 6px;
            }

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

            .attestation-bold {
                font-weight: bold;
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

            .note-section .bold-italic {
                font-weight: bold;
                font-style: italic;
            }
        </style>
    </head>

    <body>
        <!-- ══ HEADER ══ -->
        <div class="header">
            <div class="header-logo">
                {{-- Replace src with your actual logo path, e.g. public_path('images/philhealth_logo.png') --}}
                <img src="{{ public_path('images/phic.png') }}" class="header-logo-img" alt="PhilHealth Logo">
                {{-- <span class="logo-placeholder">P</span> --}}
            </div>
            <div class="header-title">
                <h1>Electronic Konsulta Availment Slip (eKAS)</h1>
            </div>
        </div>

        <!-- ══ HCI ROW ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">HCI Name:</span>
                <span class="field-line field-line-md">{{ $hciName ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Case No.:</span>
                <span class="field-line field-line-md">{{ $caseNo ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">HCI Accreditation No.</span>
                <span class="field-line field-line-sm">{{ $accreno ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Transaction No:</span>
                <span class="field-line field-line-md">{{ $transNo ?? '' }}</span>
            </div>
        </div>

        <!-- ══ PATIENT ROW 1 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">Patient Name <em>(Pangalan ng pasyente)</em>:</span>
                <span class="field-line field-line-xl">{{ $patientName ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Age <em>(Edad)</em>:</span>
                <span class="field-line field-line-sm">{{ $age ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Contact No.</span>
                <span class="field-line field-line-md">{{ $pxContactNo ?? '' }}</span>
            </div>
        </div>

        <!-- ══ PATIENT ROW 2 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">PIN (PhilHealth Identification Number):</span>
                <span class="field-line field-line-xl">{{ $pxPin ?? '' }}</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Membership Category:</span>
                <span class="field-line field-line-lg">{{ $membershipCategory ?? '' }}</span>
            </div>
        </div>

        <!-- ══ PATIENT ROW 3 ══ -->
        <div class="field-row">
            <div class="field-cell">
                <span class="field-label">Membership type:</span>
                <span class="field-line field-line-sm"
                    style="padding-left: 30px">{{ $membershipType == 'MM' ? 'X' : '' }}</span>
                <span class="field-label" style="margin-left:4px;">Member</span>
                <span class="field-line field-line-sm"
                    style="padding-left: 30px">{{ $membershipType == 'DD' ? 'X' : '' }}</span>
                <span class="field-label" style="margin-left:4px;">Dependent</span>
            </div>
            <div class="field-cell">
                <span class="field-label">Authorization Transaction Code (ATC):</span>
                <span class="field-line field-line-xl">{{ $pxATC ?? '' }}</span>
            </div>
        </div>

        <!-- ══ FACILITY SECTION ══ -->
        <div class="section-header">To be filled out by the facility <em>(pupunuan ng pasilidad)</em></div>

        <table class="konsulta-table">
            <thead>
                <tr>
                    <th class="col-service">Konsulta Services</th>
                    <th class="col-performed">
                        &#10003; Performed <em>(nagawa)</em><br>
                        X Not performed <em>(hindi nagawa)</em>
                    </th>
                    <th class="col-date">
                        Date performed<br>
                        <em>(Petsa kung kelan ginawa)</em>
                    </th>
                    <th class="col-by">
                        Performed by <em>(Ginawa ni)</em><br>
                        (Initial/Signature of Health care Provider/technician)<br>
                        (Initial o Lagda ng Health care Provider/technician)
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($diagnostics as $diagnostic)
                    <tr>
                        <td>{{ $diagnostic->diagnosticDescription->diagnostic_desc ?? '' }}</td>
                        <td>{{ $diagnostic->dIsPhysicianRecommend ?? '' }}</td>
                        <td>{{ $diagnostic->created ?? '' }}</td>
                        <td>{{ $diagnostic->createdby ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ══ PATIENT SECTION ══ -->
        <div class="section-header">To be filled out by the patient <em>(pupunuan ng pasyente)</em></div>

        <div class="patient-section">

            <!-- Question 1 -->
            <div class="question-block">
                <span class="question-text">Have you received the above-mentioned essential services?</span>
                <span class="yn-options">
                    <span class="yn-blank">{{ $received_yes ?? '' }}</span> Yes
                    &nbsp;&nbsp;
                    <span class="yn-blank">{{ $received_no ?? '' }}</span> No
                </span>
                <br>
                <span class="question-sub">(Natanggap mo ba ang mga essential services na nabanggit?)</span>
            </div>

            <!-- Question 2 -->
            <div class="question-block">
                <span class="question-text">How satisfied are you with the services provided?</span>
                &nbsp;&nbsp;
                <img src="{{ public_path('images/emoticons.png') }}" alt="Emoticons">
                <br>
                <span class="question-sub">(Gaano ka nasiyahan sa natanggap mong serbisyo?)</span>
            </div>

            <!-- Comment -->
            <div class="question-block">
                <span class="question-text">For your comment, suggestion or complaint:</span><br>
                <span class="question-sub">(Para sa iyong komento, mungkahi o reklamo)</span>
                <div class="comment-lines" style="margin-top:6px;">
                    <div class="comment-line">{{ $comment_1 ?? '' }}</div>
                    <div class="comment-line">{{ $comment_2 ?? '' }}</div>
                </div>
            </div>

            <!-- Attestation -->
            <div class="attestation">
                Under the penalty of law, I attest that the information I provided in this slip are true and
                accurate.<br>
                <span class="attestation-bold">(Sa ilalim ng batas, pinatutunayan ko na ang impormasyong ibinigay ko
                    ay totoo at tama)</span>
            </div>

            <!-- Signature -->
            <div class="sig-row">
                <div class="sig-left">
                    <div class="sig-line">{{ $patient_signature ?? '' }}</div>
                    <div class="sig-label">Signature over printed name of patient</div>
                    <div class="sig-label-bold">(Lagda sa nakalimbag na pangalan ng pasyente)</div>
                </div>
                <div class="sig-right" style="padding-left:20px;">
                    <div class="sig-label">Next Consultation Date: <span
                            class="field-line field-line-md">{{ $next_consult_date ?? '' }}</span></div>
                    <div class="sig-label-bold">(Petsa ng susunod na konsultasyon)</div>
                </div>
            </div>

        </div><!-- end patient-section -->

        <!-- ══ NOTE ══ -->
        <div class="note-section">
            <strong>Note:</strong><br>
            Accomplished form shall be submitted to PhilHealth.<br>
            <span class="bold-italic">(Ang kumpletong form ay dapat isumite sa PhilHealth)</span>
        </div>
        {{-- <div class="page-wrapper">


        </div><!-- end page-wrapper --> --}}
    </body>

</html>
