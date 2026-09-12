<div class="container-fluid p-0">
    <div class="card mb-4">
        <form id="familyHistoryForm">
            <div class="card-body">
                <div class="alert alert-danger fw-bold" role="alert">
                    FAMILY HISTORY
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Family Medical Conditions</h6>
                        <div class="mb-3">
                            @foreach ($mDiseaseLibrary as $disease)
                                @if ($disease->mdisease_code != '999')
                                    <div class="form-check">
                                        <input class="form-check-input fam-disease-checkbox" type="checkbox"
                                            id="famDiseaseCode{{ $disease->mdisease_code }}"
                                            name="chkFamHistDiseases[{{ $disease->mdisease_code }}][selected]"
                                            value="{{ $disease->mdisease_code }}">
                                        <label class="form-check-label"
                                            for="famDiseaseCode{{ $disease->mdisease_code }}">
                                            {{ $disease->mdisease_desc }}
                                        </label>
                                    </div>
                                    <div class="mb-3" id="divfamDiseaseCode{{ $disease->mdisease_code }}"
                                        style="display:none; margin-left: 20px;">
                                        <label for="specificFamCode{{ $disease->mdisease_code }}"
                                            class="form-label">Specify
                                            {{ $disease->mdisease_desc }}</label>
                                        <input type="text" class="form-control"
                                            id="specificFamCode{{ $disease->mdisease_code }}"
                                            name="chkFamHistDiseases[{{ $disease->mdisease_code }}][specify]"
                                            maxlength="2000">
                                    </div>
                                @endif
                            @endforeach
                            <div class="form-check">
                                <input class="form-check-input fam-disease-checkbox" type="checkbox" id="chkFamHistNone"
                                    name="chkFamHistDiseases[999][selected]" value="999">
                                <label class="form-check-label" for="chkFamHistNone">None</label>
                            </div>
                            <!-- Add more checkboxes as needed -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <form id="personalSocialHistoryForm">
            <div class="card-body">
                <div class="alert alert-danger fw-bold" role="alert">
                    PERSONAL/SOCIAL HISTORY
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3"><span class="text-danger">*</span> Smoking</h6>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsSmoker" id="dIsSmokerY"
                                    value="Y">
                                <label class="form-check-label" for="dIsSmokerY">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsSmoker" id="dIsSmokerN"
                                    value="N">
                                <label class="form-check-label" for="dIsSmokerN">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsSmoker" id="dIsSmokerX"
                                    value="X">
                                <label class="form-check-label" for="dIsSmokerX">Quit</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dNoCigpk" class="form-label">No. of packs/year</label>
                            <input type="text" class="form-control" id="dNoCigpk" name="dNoCigpk" maxlength="5">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3"><span class="text-danger">*</span> Alcohol</h6>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsADrinker" id="dIsADrinkerY"
                                    value="Y">
                                <label class="form-check-label" for="dIsADrinkerY">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsADrinker" id="dIsADrinkerN"
                                    value="N">
                                <label class="form-check-label" for="dIsADrinkerN">No</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsADrinker" id="dIsADrinkerX"
                                    value="X">
                                <label class="form-check-label" for="dIsADrinkerX">Quit</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dNoBottles" class="form-label">No. of bottles/day</label>
                            <input type="text" class="form-control" id="dNoBottles" name="dNoBottles"
                                maxlength="5">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3"><span class="text-danger">*</span> Illicit Drugs</h6>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIllDrugUser"
                                    id="dIllDrugUserY" value="Y">
                                <label class="form-check-label" for="dIllDrugUserY">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIllDrugUser"
                                    id="dIllDrugUserN" value="N">
                                <label class="form-check-label" for="dIllDrugUserN">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3"><span class="text-danger">*</span> Sexual History Screening</h6>
                        <div class="mb-3">
                            <label class="form-label">Sexually Active</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="dIsSexuallyActive"
                                        id="dIsSexuallyActiveY" value="Y">
                                    <label class="form-check-label" for="dIsSexuallyActiveY">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="dIsSexuallyActive"
                                        id="dIsSexuallyActiveN" value="N">
                                    <label class="form-check-label" for="dIsSexuallyActiveN">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input.fam-disease-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const id = this.id.replace('chk', 'div');
                const div = document.getElementById('div' + id);
                if (div) {
                    div.style.display = this.checked ? 'block' : 'none';
                }
            });
        });
    });
</script>
