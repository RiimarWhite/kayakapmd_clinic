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
			<div class="modal-header">
				<h2 class="modal-title"><i class="fa-solid fa-people-group"></i> Patient Consultation History</h2>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<input type="hidden" name="mpincode" id="mpincode">
				<div class="table-responsive">
					<table class="table table-bordered" id="medhistorytable">
						<thead class="table-danger">
							<tr>
								<th scope="col">Photo</th>
								<th scope="col">Consultation Date</th>
								<th scope="col">Reason for Consultation</th>
								<th scope="col">Status</th>
								<th scope="col">Recorded By</th>
								<th scope="col">Recorded On</th>
							</tr>
						</thead>

						<tbody>
							<tr>
								<td></td>
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