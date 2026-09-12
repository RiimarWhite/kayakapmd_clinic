<!DOCTYPE html>
<html lang="{{ str_replace('_', '_', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">

        <style>
            @font-face {
                font-family: 'AnonymousPro';
                src: url("{{ public_path('fonts/AnonymiceProNerdFont-Regular.ttf') }}");
                font-weight: normal;
                font-style: normal;
            }

            body {
                font-family: "AnonymousPro", sans-serif;
            }
        </style>
    </head>
    
    <body style="height: 100vh;">
        <!-- Header -->
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="20%" align="center">
                    <img src="{{ public_path('images/logo.png') }}" alt="company_logo" width="80">
                </td>

                <td width="80%" align="left">
                    <h1 style="margin: 0;">{{ $profile->COMPANYNAME }}</h1>
                    <p style="margin: 0;">{{ $profile->COMPANYADDRESS }}</p>
                </td>
            </tr>
        </table>

        <hr style="margin: 5px;">

        <section>
            <table width="100%" style="padding: 1rem;">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: left;">Patient Name</th>
                        <th scope="col" style="text-align: left;">Transaction Date</th>
                        <th scope="col" style="text-align: left;">Cash</th>
                        <th scope="col" style="text-align: left;">CTA</th>
                        <th scope="col" style="text-align: left;">--/--</th>
                        <th scope="col" style="text-align: left;">HMO</th>
                        <th scope="col" style="text-align: left;">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($records as $record)
                        <tr style="font-size: 12px;">
                            <td>{{ $record->consultation->patientname }}</td>
                            <td>{{ Carbon\Carbon::parse($record->created_at) }}</td>
                            <td>{{ $record->cash }}</td>
                            <td>{{ $record->cta }}</td>
                            <td>{{ $record->something }}</td>
                            <td>{{ $record->hmo }}</td>
                            <td>{{ $record->net_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </body>
</html>