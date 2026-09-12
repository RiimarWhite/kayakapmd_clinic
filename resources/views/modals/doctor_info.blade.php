<div class="modal fade" data-bs-backdrop="static" id="doctor_profile_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-user-doctor"></span> Profile</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-2 p-4">
                <div class="d-flex">
                    <p class="m-0 fw-bold w-50">Fullname: <span class="fw-normal" id="doc_fullname"></span></p>
                    <p class="m-0 fw-bold w-50">Title: <span class="fw-normal" id="doc_title"></span></p>
                </div>

                <p class="m-0 fw-bold">Mobile number: <span class="fw-normal" id="doc_contact"></span></p>
                <p class="m-0 fw-bold">Email address: <span class="fw-normal" id="doc_email"></span></p>

                <hr>

                <div class="d-flex">
                    <p class="m-0 fw-bold w-50">License no.: <span class="fw-normal" id="doc_lic"></span></p>
                    <p class="m-0 fw-bold w-50">Expiry: <span class="fw-normal" id="doc_lic_expiry"></span></p>
                </div>

                <div class="d-flex">
                    <p class="m-0 fw-bold w-50">PHIC no.: <span class="fw-normal" id="doc_phic"></span></p>
                    <p class="m-0 fw-bold w-50">Expiry: <span class="fw-normal" id="doc_phic_expiry"></span></p>
                </div>

                <div class="d-flex">
                    <p class="m-0 fw-bold w-50">S2 no.: <span class="fw-normal" id="doc_s2"></span></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
</div>