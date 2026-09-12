<div class="modal fade" data-bs-backdrop="static" id="takePhotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-camera"></span> Capture Photo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-4 p-4">
                <video id="camera_preview" autoplay playsinline style="width: 100%, max-width: 500px; height: 100%; max-height: 500px;"></video>
                <canvas id="camera_canvas" class="d-none"></canvas>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_photo">Take Photo</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeTakePhoto">Close</button>
            </div>
        </div>
    </div>
</div>