<div class="container-fluid p-0">
    <div class="card mb-4">
        <form id="immunizationsForm">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    IMMUNIZATIONS
                </div>
                <h6 class="mb-3">For Children</h6>
                <div class="row mb-4">
                    <div class="col-12">
                        @foreach ($immChildLibrary as $key => $immChild)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="immchild{{ $immChild->imm_code }}"
                                    name="chkImmChild[{{ $key }}]" value="{{ $immChild->imm_code }}">
                                <label class="form-check-label" for="immchild{{ $immChild->imm_code }}">
                                    {{ $immChild->imm_desc }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <h6 class="mb-3">For Adults</h6>
                <div class="row mb-4">
                    <div class="col-12">
                        @foreach ($immYoungwLibrary as $key => $immYoungw)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="immyoungw{{ $immYoungw->imm_code }}"
                                    name="chkImmAdult[{{ $key }}]" value="{{ $immYoungw->imm_code }}">
                                <label class="form-check-label" for="immyoungw{{ $immYoungw->imm_code }}">
                                    {{ $immYoungw->imm_desc }}
                                </label>
                            </div>
                        @endforeach
                        <!-- Add more adult immunizations as needed -->
                    </div>
                </div>

                <h6 class="mb-3">For Pregnant Women</h6>
                <div class="row mb-4">
                    <div class="col-12">
                        @foreach ($immPregwLibrary as $key => $immPregw)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="immpregw{{ $immPregw->imm_code }}"
                                    name="chkImmPregnant[{{ $key }}]" value="{{ $immPregw->imm_code }}">
                                <label class="form-check-label" for="immpregw{{ $immPregw->imm_code }}">
                                    {{ $immPregw->imm_desc }}
                                </label>
                            </div>
                        @endforeach
                        <!-- Add more pregnant women immunizations as needed -->
                    </div>
                </div>

                <h6 class="mb-3">For Elderly and Immunocompromised</h6>
                <div class="row mb-4">
                    <div class="col-12">
                        @foreach ($immElderlyLibrary as $key => $immElderly)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    id="immelderly{{ $immElderly->immcode }}"
                                    name="chkImmElderly[{{ $key }}]" value="{{ $immElderly->immcode }}">
                                <label class="form-check-label" for="immelderly{{ $immElderly->immcode }}">
                                    {{ $immElderly->imm_desc }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <h6 class="mb-3">Others, please specify</h6>
                <div class="row">
                    <div class="col-12">
                        <textarea class="form-control" id="dOtherImm" name="dOtherImm" rows="3" maxlength="2000"
                            placeholder="Specify other immunizations..."></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
