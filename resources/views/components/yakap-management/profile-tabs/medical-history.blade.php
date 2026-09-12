<div class="container-fluid p-0">
    <div class="card mb-4">
        <form id="medicalHistoryForm">
            <div class="card-body">
                <div class="alert alert-danger fw-bold" role="alert">
                    PAST MEDICAL HISTORY
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-3">Medical Conditions</h6>
                        <div class="mb-3">
                            @foreach ($mDiseaseLibrary as $disease)
                                @if ($disease->mdisease_code != '999')
                                    <div class="form-check">
                                        <input class="form-check-input disease-checkbox" type="checkbox"
                                            id="diseaseCode{{ $disease->mdisease_code }}"
                                            name="chkMedHistDiseases[{{ $disease->mdisease_code }}][selected]"
                                            value="{{ $disease->mdisease_code }}">
                                        <label class="form-check-label" for="diseaseCode{{ $disease->mdisease_code }}">
                                            {{ $disease->mdisease_desc }}
                                        </label>
                                    </div>
                                    <div class="mb-3" id="divdiseaseCode{{ $disease->mdisease_code }}"
                                        style="display:none; margin-left: 20px;">
                                        <label for="specificCode{{ $disease->mdisease_code }}"
                                            class="form-label">Specify
                                            {{ $disease->mdisease_desc }}</label>
                                        <input type="text" class="form-control"
                                            id="specificCode{{ $disease->mdisease_code }}"
                                            name="chkMedHistDiseases[{{ $disease->mdisease_code }}][specify]"
                                            maxlength="2000">
                                    </div>
                                @endif
                            @endforeach
                            <div class="form-check">
                                <input class="form-check-input disease-checkbox" type="checkbox" id="chkMedHistNone"
                                    name="chkMedHistDiseases[999][selected]" value="999">
                                <label class="form-check-label" for="chkMedHistNone">None</label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <form id="surgicalHistoryForm">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    PAST SURGICAL HISTORY
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tblMedHistOpHist">
                        <thead>
                            <tr>
                                <th>Operation</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-empty="1">
                                <td>
                                    <input type="text" class="form-control" name="surgicalHistory[operation][]"
                                        maxlength="2000">
                                </td>
                                <td>
                                    <input type="date" class="form-control" name="surgicalHistory[date][]">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="addOperationHist(this)">Add</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input.disease-checkbox');
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
