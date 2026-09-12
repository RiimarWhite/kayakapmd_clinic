<?php

namespace App\Services\YakapManagement;

use App\Models\Libraries\MedicineLibModel;
use App\Models\Stocks\StocksListingModel;
use Illuminate\Support\Facades\DB;

class MedicineService
{
    public function getMedicineDetails($medicineCode, $source)
    {
        $medicine = MedicineLibModel::where('DRUG_CODE', $medicineCode)->first();

        if ($medicine) {
            $medicine->FORM_DESC = $this->getCodeDescriptions($medicine->FORM_CODE, 'dw_lib_meds_form', 'form_code', 'form_desc');
            $medicine->GEN_DESC = $this->getCodeDescriptions($medicine->GEN_CODE, 'dw_lib_meds_generic', 'gen_code', 'gen_desc');
            $medicine->PACKAGE_DESC = $this->getCodeDescriptions($medicine->PACKAGE_CODE, 'dw_lib_meds_package', 'package_code', 'package_desc');
            $medicine->SALT_DESC = $this->getCodeDescriptions($medicine->SALT_CODE, 'dw_lib_meds_salt', 'salt_code', 'salt_desc');
            $medicine->STRENGTH_DESC = $this->getCodeDescriptions($medicine->STRENGTH_CODE, 'dw_lib_meds_strength', 'strength_code', 'strength_desc');
            $medicine->UNIT_DESC = $this->getCodeDescriptions($medicine->UNIT_CODE, 'dw_lib_meds_unit', 'unit_code', 'unit_desc');
        }
        if ($source === 'inhouse') {
            $inhouseRecord = StocksListingModel::where('phic_reference_code', $medicineCode)->first();

            if($inhouseRecord){
                $medicine->price = $inhouseRecord->price_regular;
            }
        }

        return $medicine;
    }

    private function getCodeDescriptions($code, $table, $codeColumn, $descField)
    {
        return DB::table($table)->where($codeColumn, $code)->value($descField);
    }
}
