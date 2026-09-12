<?php

namespace App\Services\Enlistment;

use App\Models\EnlistmentModel;
use App\Models\XmlEnlistUploading;
use App\Services\ClientService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class EnlistmentService
{
    private $clientCode;
    public function __construct(
        private ClientService $clientService
    ) {
        $this->clientCode = $this->clientService->getClientCode();
    }

    public function parseXmlFromUpload(string $uploadId): array
    {
        $upload = XmlEnlistUploading::find($uploadId);

        if (! $upload) {
            throw new \InvalidArgumentException('Upload record not found');
        }

        if (empty($upload->UPLOAD_XML)) {
            throw new \InvalidArgumentException('No XML data found for this upload');
        }

        $dom = new \DOMDocument;
        $dom->loadXML($upload->UPLOAD_XML);

        $assignments = [];
        $assignmentNodes = $dom->getElementsByTagName('ASSIGNMENT');

        foreach ($assignmentNodes as $node) {
            $assignment = [];
            foreach ($node->attributes as $attr) {
                $assignment[$attr->name] = $attr->value;
            }
            $assignments[] = $assignment;
        }

        return [
            'success' => true,
            'count' => count($assignments),
            'data' => $assignments,
        ];
    }

    public function saveEnlistmentFromXml(string $uploadId): array
    {
        try {
            DB::beginTransaction();
            $upload = XmlEnlistUploading::find($uploadId);

            if (! $upload) {
                throw new \InvalidArgumentException('Upload record not found');
            }

            if (empty($upload->UPLOAD_XML)) {
                throw new \InvalidArgumentException('No XML data found for this upload');
            }

            $dom = new \DOMDocument;
            $dom->loadXML($upload->UPLOAD_XML);

            $assignmentNodes = $dom->getElementsByTagName('ASSIGNMENT');
            $enlistmentRecords = [];

            // Parse all records first
            foreach ($assignmentNodes as $node) {
                $data = [];
                foreach ($node->attributes as $attr) {
                    $data[$attr->name] = $attr->value;
                }

                // Map XML attributes to EnlistmentModel fields
                $enlistmentRecords[] = [
                    'dEffyear' => $data['pEffYear'] ?? null,
                    'dPatientPin' => $data['pAssignedPin'] ?? null,
                    'dPatientLname' => $data['pAssignedLastName'] ?? null,
                    'dPatientFname' => $data['pAssignedFirstName'] ?? null,
                    'dPatientMname' => $data['pAssignedMiddleName'] ?? null,
                    'patientname' => trim(($data['pAssignedFirstName'] ?? '') . ' ' . ($data['pAssignedMiddleName'] ?? '') . ' ' . ($data['pAssignedLastName'] ?? '')),
                    'dPatientExtname' => $data['pAssignedExtName'] ?? null,
                    'dPatientDob' => $data['pAssignedDateOfBirth'] ?? null,
                    'dPatientSex' => $data['pAssignedSex'] ?? null,
                    'dPatientLandlineNo' => $data['pLandlineNumber'] ?? null,
                    'dPatientMobileNo' => $data['pMobileNumber'] ?? null,
                    'dPackageType' => $data['pPackageType'] ?? null,
                    'dEnlistDate' => $data['pAssignedDate'] ?? null,
                    'dEnlistStat' => $data['pAssignedStatus'] ?? null,
                    'dMemDob' => $data['pPrimaryDateOfBirth'] ?? null,
                    'dMemExtname' => $data['pPrimaryExtName'] ?? null,
                    'dMemFname' => $data['pPrimaryFirstName'] ?? null,
                    'dMemLname' => $data['pPrimaryLastName'] ?? null,
                    'dMemMname' => $data['pPrimaryMiddleName'] ?? null,
                    'dMemPin' => $data['pPrimaryPIN'] ?? null,
                    'dw_clientcode' => $this->clientCode,
                    'dCaseNo' => $this->generateCaseNo(),
                    'dTransNo' => $this->generateTransNo(),
                ];
            }

            $savedCount = 0;

            // Update or create records one by one
            foreach ($enlistmentRecords as $record) {
                EnlistmentModel::updateOrCreate(
                    [
                        'dEffyear' => $record['dEffyear'],
                        'dPatientPin' => $record['dPatientPin'],
                    ],
                    $record
                );
                $savedCount++;
            }

            $upload->status = 'DONE';
            $upload->imported = CarbonImmutable::now();
            $upload->save();

            DB::commit();

            return [
                'success' => true,
                'saved_count' => $savedCount,
                'total_count' => $assignmentNodes->length,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error saving enlistment: ' . $e->getMessage());
        }
    }

    private function generateCaseNo(): string
    {
        $acreNo = $this->clientService->getHciAccreditationNumber();
        $yearMont = CarbonImmutable::now()->format('Ym');
        $fiveSeries = fake()->numerify('#####'); // TODO: Implement actual 5 series random number generation logic

        return "T{$acreNo}{$yearMont}{$fiveSeries}";
    }

    private function generateTransNo(): string
    {
        $acreNo = $this->clientService->getHciAccreditationNumber();
        $yearMont = CarbonImmutable::now()->format('Ym');
        $fiveSeries = fake()->numerify('#####'); // TODO: Implement actual 5 series random number generation logic

        return "E{$acreNo}{$yearMont}{$fiveSeries}";
    }
}
