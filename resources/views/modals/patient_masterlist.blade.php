<div class="modal fade" data-bs-backdrop="static" id="patientMasterlistModal" tabindex="-1" aria-labelledby="patientMasterlistModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title"><i class="fa-solid fa-people-group"></i> Patient Masterlist</h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="table-responsive">
					<table class="table table-sm table-bordered align-middle" id="patientMasterlistTable">
						<thead class="table-light">
							<tr>
								<th>Actions</th>
								<th>Photo</th>
								<th>Full Name <span class="text-secondary">(First, Middle, Last, Suffix)</span></th>
								<th>Mobile</th>
								<th>Email Adress</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="modal-footer">

			</div>
		</div>
	</div>
</div>

<div class="modal fade m-0 p-0" data-bs-backdrop="static" id="patientMedhistoryModal" tabindex="-1" aria-labelledby="patientMedhistoryModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-light">
				<h2 class="modal-title fs-5"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Patient Consultation History <span id="medhistory_patient_name" class="fs-6 text-muted fw-normal ms-2"></span></h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body p-3">
				<input type="hidden" name="mpincode" id="mpincode">
				<div class="table-responsive">
					<table class="table table-sm table-bordered table-hover align-middle w-100" id="medhistorytable">
						<thead class="table-info">
							<tr>
								<th scope="col" class="text-center" style="width: 50px;">Photo</th>
								<th scope="col" style="width: 120px;">Consultation Date</th>
								<th scope="col">Reason for Consultation</th>
								<th scope="col" style="width: 110px;" class="text-center">Status</th>
								<th scope="col" style="width: 150px;">Recorded By</th>
								<th scope="col" style="width: 120px;">Recorded On</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>

			<div class="modal-footer bg-light py-2">
				<button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>