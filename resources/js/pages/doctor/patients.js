$(function () {
    loadMasterlist();

    function loadMasterlist() {
        if ($.fn.DataTable.isDataTable("#masterlist_table")) { $("#masterlist_table").DataTable().clear().destroy(); }
        $("#masterlist_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_consultation_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
            },
            columns: [
                { data: 'consultationrefno', render: function (data) { return `<button class="btn btn-sm btn-primary masterlist-view" value="${data}"><i class="fa-solid fa-eye"></i> View</button>`; } },
                {
                    data: null,
                    render: function (data) {
                        return `<img src="${data.photo_path ?? '/images/blank_photo.png'}" class="rounded rounded-circle" style="max-width: 50px;" alt="patient_photo"> <span>${data.patientname}</span>`
                    }
                },
                { data: 'consultation_date', render: function (data) { return new Date(data).toLocaleDateString("en-US", { month: "long", day: "2-digit", year: "numeric" }); } },
            ],
            columnDefs: [
                { target: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { targets: [1, 2], orderable: false, className: 'text-nowrap align-middle' }
            ],
            order: [[1, 'asc']],
            language: { emptyTable: "No patients yet." },
            pageLength: 10, lengthChange: false, paging: true, searching: true, ordering: true, responsive: true
        });
    }

    $(document).on("click", ".masterlist-view", function () {
        const infoModal = new bootstrap.Modal("#viewPatientModal");

        $.ajax({
            url: "/api/fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $(this).val() },
            success: function (response) {
                infoModal.show();
                const p = response.patient;
                $("#view_memPin").val(p.pincode);
                $("#view_pPatientFname").val(p.pxfirstname);
                $("#view_pPatientMname").val(p.pxmidname);
                $("#view_pPatientLname").val(p.pxlastname);
                $("#view_pPatientExtname").val(p.suffix);
                $("#view_pPatientDob").val(p.birthday);
                $("#view_pPatientSex").val(p.gender);
                $("#view_pPatientMobileNo").val(p.mobilenumber);
                $("#view_email").val(p.emailaddress);
                $("#view_address").val(p.address);
            }
        });
    });

    // Detailed Comment: Self-service Doctor Profile modal lifecycle (populate 5 tabs and save updates)
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("show.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    if (response.success && response.user) {
                        const u = response.user;
                        // Tab 1: Personal & Account
                        $("#prof_docfname").val(u.docfname || '');
                        $("#prof_docmname").val(u.docmname || '');
                        $("#prof_doclname").val(u.doclname || '');
                        $("#prof_suffix").val(u.suffix || '');
                        $("#prof_titlename").val(u.titlename || 'MD');
                        $("#prof_username").val(u.username || '');
                        $("#prof_new_password").val('');
                        $("#prof_emailadd").val(u.emailadd || '');
                        $("#prof_cellno").val(u.cellno || '');
                        $("#prof_adrs").val(u.adrs || '');

                        // Tab 2: Licenses & Accreditations
                        $("#prof_licno").val(u.Licno || '');
                        $("#prof_licnoexpiry").val(u.licnoexpiry || '');
                        $("#prof_phicno").val(u.phicno || '');
                        $("#prof_phicexpiry").val(u.phicexpiry || '');
                        $("#prof_phicname").val(u.phicname || '');
                        $("#prof_phicrate").val(u.phicrate || 0);
                        $("#prof_phicenable").prop('checked', !!(u.phicenable == 1 || u.phicenable === true));
                        $("#prof_s2no").val(u.S2no || '');
                        $("#prof_ptr").val(u.PTR || '');

                        // Tab 3: Practice & Clinic
                        $("#prof_expertise").val(u.expertise || '');
                        $("#prof_proftype").val(u.proftype || 'ATTENDING');
                        $("#prof_department").val(u.department || '');
                        $("#prof_profgroup").val(u.profgroup || '');
                        $("#prof_catg").val(u.catg || '');
                        $("#prof_station").val(u.station || '');
                        $("#prof_groupname").val(u.groupname || '');
                        $("#prof_clinicroom").val(u.clinicroom || '');
                        $("#prof_clinichours").val(u.clinichours || '');
                        $("#prof_hospadrs").val(u.hospadrs || '');

                        // Tab 4: Rates, Tax & Billing
                        $("#prof_consultationfee").val(u.consultationfee || 0);
                        $("#prof_emergencyfee").val(u.emergencyfee || 0);
                        $("#prof_admissionfee").val(u.admissionfee || 0);
                        $("#prof_tin").val(u.tin || '');
                        $("#prof_taxpercent").val(u.taxpercent || 0);
                        $("#prof_withholdingtax").val(u.withholdingtax || 0);
                        $("#prof_bankacct").val(u.bankacct || '');
                        $("#prof_slcode").val(u.slcode || '');
                        $("#prof_autoAddVAT").prop('checked', !!(u.autoAddVAT == 1 || u.autoAddVAT === true));
                        $("#prof_issuehospOR").prop('checked', !!(u.issuehospOR == 1 || u.issuehospOR === true));

                        // Tab 5: System Settings & Notes
                        $("#prof_quevisible").prop('checked', !!(u.quevisible == 1 || u.quevisible === true || u.quevisible === undefined));
                        $("#prof_allowtextresult").prop('checked', !!(u.allowtextresult == 1 || u.allowtextresult === true));
                        $("#prof_allowdocsystem").prop('checked', !!(u.allowdocsystem == 1 || u.allowdocsystem === true));
                        $("#prof_disabletext").prop('checked', !!(u.disabletext == 1 || u.disabletext === true));
                        $("#prof_otherinfo").val(u.otherinfo || '');
                        $("#prof_biodata").val(u.biodata || '');

                        // Legacy view elements
                        const map = {
                            "#doc_fullname": [u.docfname, u.docmname, u.doclname, u.suffix].filter(Boolean).join(" "),
                            "#doc_username": u.username,
                            "#doc_title": u.titlename,
                            "#doc_expertise": u.expertise,
                            "#doc_contact": u.cellno,
                            "#doc_email": u.emailadd,
                            "#doc_clinicroom": u.clinicroom,
                            "#doc_clinichours": u.clinichours,
                            "#doc_lic": u.Licno,
                            "#doc_lic_expiry": u.licnoexpiry,
                            "#doc_phic": u.phicno,
                            "#doc_phic_expiry": u.phicexpiry,
                            "#doc_s2": u.S2no,
                            "#doc_ptr": u.PTR
                        };
                        $.each(map, (sel, val) => $(sel).text(val ?? ""));
                    }
                }
            });
        });

        $("#save_doctor_profile_btn").off("click").on("click", function () {
            const form = document.getElementById("doctor_profile_form");
            if (form && !form.checkValidity()) return form.reportValidity();

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...');

            $.ajax({
                url: "/api/doctor/update_profile",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: $("#doctor_profile_form").serialize(),
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: "Success",
                            text: "Doctor profile updated successfully.",
                            icon: "success"
                        });
                    } else {
                        Swal.fire({ title: "Error", text: response.message || "Failed to update profile.", icon: "error" });
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update profile.";
                    Swal.fire({ title: "Error", text: msg, icon: "error" });
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }

    $("#add_patient_modal_btn").on("click", function () {
        const modal = new bootstrap.Modal(("#add_patient_modal"));
        modal.show();
    });

    $("#add_patient_btn").on("click", function () {
        const form = document.getElementById("add_patient_form");

        if (!form.checkValidity()) return form.reportValidity;

        $.ajax({
            url: "/api/add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token').attr('content') },
            data: $("#add_patient_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient rescheduled!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance("#add_patient_modal").hide();
                    $("#masterlist_table").DataTable().ajax.reload();
                }
            }
        });
    });
});
