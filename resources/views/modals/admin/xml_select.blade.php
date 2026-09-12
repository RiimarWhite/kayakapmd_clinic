<div class="modal fade" data-bs-backdrop="static" id="xml_select_modal" data-bs-keyboard="false"  tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-plus"></span> XML Generation</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-4 p-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="soap">
                    <label class="form-check-label" for="soap">SOAP</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="soap">
                    <label class="form-check-label" for="soap">DIAGNOSTIC EXAM RESULT</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="soap">
                    <label class="form-check-label" for="soap">MEDICINES</label>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="save_item">Generate XML</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
