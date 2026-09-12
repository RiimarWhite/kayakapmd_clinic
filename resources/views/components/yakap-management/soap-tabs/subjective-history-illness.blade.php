<div class="container-fluid p-0">
    <form id="subjectiveHistoryForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    SUBJECTIVE/HISTORY OF ILLNESS
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold"><u>A. Chief of Complaint</u></h6>
                        <div class="mb-3">
                            @foreach ($signsSymptomsLibrary as $signsSymptom)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="signsSymptoms[{{ $signsSymptom->symptoms_id }}]"
                                        value="{{ $signsSymptom->symptoms_id }}"
                                        id="signsSymptom{{ $signsSymptom->symptoms_id }}">
                                    <label class="form-check-label" for="signsSymptom{{ $signsSymptom->symptoms_id }}">
                                        {{ $signsSymptom->symptoms_desc }}
                                    </label>
                                </div>
                                @if ($signsSymptom->symptoms_id == 38)
                                    <input type="text" class="form-control" name="dPainSite" id="dPainSite"
                                        placeholder="Site of pain">
                                @endif
                            @endforeach
                            <input type="text" class="form-control" name="dOtherComplaint" id="dOtherComplaint">
                        </div>
                        <h6 class="fw-bold"><u>B. History of Illness</u></h6>
                        <div class="mb-3">
                            <textarea class="form-control" name="dIllnessHistory" id="dIllnessHistory" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
