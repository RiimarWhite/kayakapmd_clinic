<div class="container-fluid p-0">
    <form id="assessmentDiagnosisForm">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    ASSESSMENT/DIAGNOSIS
                </div>
                <div class="row">
                    <div class="col-2 d-flex align-items-center">
                        <span class="align-middle"><span class="text-danger">*</span>Diagnosis</span>
                    </div>
                    <div class="col-7">
                        <select class="form-select select2-diagnosis" id="dDiagnosis" style="width: 100%;">
                            <option value="">Select a diagnosis</option>
                        </select>
                        <div id="diagnosisError" class="text-danger small mt-1" style="display: none;"></div>
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-success" id="addDiagnosisBtn">Add Diagnosis</button>
                    </div>
                </div>

                <hr>

                <div class="row mt-3">
                    <div class="col-12">
                        <table class="table table-bordered" id="diagnosisTable">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
        color: #212529;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem + 2px);
        right: 0.75rem;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
    }
</style>
