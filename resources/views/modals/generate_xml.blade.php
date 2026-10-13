<div class="modal fade" data-bs-backdrop="static" id="generate_xml">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">XML Report Preview</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <iframe src="" frameborder="0" class="w-100" style="min-height: 40rem;" id="xml_preview_frame"></iframe>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="btn_save_xml">Save</button>
                <button type="button" class="btn btn-success" id="btn_encrypt_modal_xml"><i class="fa-solid fa-lock"></i> Encrypt</button>
                <button type="button" class="btn btn-warning" id="btn_transmit_xml"><i class="fa-solid fa-paper-plane"></i> Transmit to PhilHealth</button>
                <button type="button" class="btn btn-primary" id="btn_download_xml"><i class="fa-solid fa-download"></i> Download</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
