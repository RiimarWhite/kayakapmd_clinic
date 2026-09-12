<?php

namespace App\Services;

use App\Models\KayakapProfileModel;
use App\Models\PCBModel;
use Illuminate\Support\Collection;

class ClientService
{

    public $clientCode;

    public function __construct()
    {
        $this->clientCode = config('app.clientcode');
    }

    public function getClientCode(): string
    {
        return $this->clientCode;
    }

    public function getPcbDetails()
    {
        return PCBModel::where('clientcode', $this->clientCode)->first();
    }

    public function getHciAccreditationNumber(): string
    {
        $pcbModel = PCBModel::where('clientcode', $this->clientCode)->first();
        return $pcbModel->hciaccreno;
    }

    public function getKayakapmdProfileDetails(): Collection | null
    {
        return KayakapProfileModel::where('clientcode', $this->clientCode)->first();
    }
}
