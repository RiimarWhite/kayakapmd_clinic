<div class="modal fade" data-bs-backdrop="static" id="reschedule_modal" tabindex="-1" aria-labelledby="reschedule_modalModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<form class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title"><i class="fa-solid fa-clock"></i> Reschedule Patient</h3>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body ">
                <div class="input-group">
                    <input class="form-control" type="date" name="resched_date" id="resched_date">
                    <select class="form-select" name="resched_time" id="resched_time"></select>
                </div>
                <div class="form-text">Enter new schedule.</div>
			</div>

			<div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="reschedule_btn">Reschedule</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</div>
        </form>
	</div>
</div>