<div class="modal fade" data-bs-backdrop="static" id="management_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fa-solid fa-user-gear"></i> Management</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body d-flex gap-4" style="height: 700px; max-height: 700px;">
                <nav class="nav nav-pills justify-content-start flex-column d-flex gap-2 w-25" id="management_sidebar">
                    <li class="nav-item">
                        <button class="nav-link active w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#profile" role="tab" aria-controls="general" aria-selected="true"
                            id="account_tab">
                            <i class="fa-solid fa-user-pen"></i>
                            Account
                        </button>
                    </li>

                    <hr class="m-0">

                    <select class="form-select border-1 border-secondary border-opacity-25 shadow-none"
                        name="choose_doctor" id="choose_doctor"></select>

                    <li class="nav-item">
                        <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#dprofile" role="tab" aria-controls="dprofile" aria-selected="false"
                            id="info_tab">
                            <i class="fa-solid fa-user-doctor"></i>
                            Doctor Info
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#questions" role="tab" aria-controls="questions" aria-selected="false"
                            id="questions_tab">
                            <i class="fa-solid fa-file-circle-question"></i>
                            Questions
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#schedules" role="tab" aria-controls="schedules" aria-selected="false"
                            id="schedules_tab">
                            <i class="fa-solid fa-calendar-days"></i>
                            Schedules
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#services_group_management" role="tab"
                            aria-controls="services_group_management" aria-selected="false"
                            id="services_group_management_tab">
                            <i class="fa-solid fa-book-medical"></i>
                            Service Groups
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                            data-bs-target="#services_management" role="tab" aria-controls="services_management"
                            aria-selected="false" id="services_management_tab">
                            <i class="fa-solid fa-hand-holding-medical"></i>
                            Services
                        </button>
                    </li>
                </nav>

                <div class="tab-content d-flex overflow-y-auto w-100 px-2">
                    <!-- Account -->
                    <div class="tab-pane active w-100" id="profile" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="fw-bold m-0"><i class="fa-solid fa-user-pen me-2"></i> My Account Details</h4>
                        </div>
                        <hr class="m-0 mb-4">

                        <form class="d-flex flex-column gap-3" id="account_form">
                            @csrf

                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="sec_fname">First Name <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="secfname" id="sec_fname" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="sec_mname">Middle Name</label>
                                    <input class="form-control" type="text" name="secmname" id="sec_mname">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="sec_lname">Last Name <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="seclname" id="sec_lname" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold" for="sec_suffix">Suffix</label>
                                    <input class="form-control" type="text" name="secsuffix" id="sec_suffix"
                                        maxlength="10">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_username_input">Username <span
                                            class="text-danger">*</span></label>
                                    <input class="form-control font-monospace" type="text" name="username"
                                        id="sec_username_input" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_password_input">New Password</label>
                                    <input class="form-control" type="password" name="secpassword"
                                        id="sec_password_input" placeholder="Leave blank to keep current password">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_gender_input">Sex</label>
                                    <select class="form-select" name="secgender" id="sec_gender_input">
                                        <option value="MALE">MALE</option>
                                        <option value="FEMALE">FEMALE</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_bday_input">Birthdate</label>
                                    <input class="form-control" type="date" name="secbday" id="sec_bday_input">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_contact">Contact No.</label>
                                    <input class="form-control" type="tel" name="seccontactno" id="sec_contact"
                                        placeholder="(+63)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="sec_email">Email Address</label>
                                    <input class="form-control" type="email" name="secemail" id="sec_email">
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-bold" for="sec_adrs_input">Home Address</label>
                                <input class="form-control" type="text" name="secadrs" id="sec_adrs_input">
                            </div>

                            <div>
                                <button type="button" class="btn btn-primary fw-bold" id="save_profile_btn">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Updates
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Info -->
                    <div class="tab-pane w-100" id="dprofile" role="tabpanel" aria-labelledby="dprofile-tab"
                        tabindex="0">
                        <h4 class="fw-bold">Doctor Info</h4>
                        <hr class="m-0 mb-4">

                        <div class="d-flex flex-column">
                            <div class="d-flex">
                                <div class="d-flex flex-column w-100">
                                    <p class="fw-bold">Fullname: <span class="fw-normal" id="doc_fullname"></span></p>
                                    <p class="fw-bold">Contact #: <span class="fw-normal" id="doc_contact"></span></p>
                                    <p class="fw-bold">Email address: <span class="fw-normal" id="doc_email"></span></p>
                                </div>

                                <div class="d-flex flex-column w-100">
                                    <p class="fw-bold">Title: <span class="fw-normal" id="doc_title"></span></p>
                                </div>
                            </div>

                            <hr>

                            <p class="fw-bold">License No.: <span class="fw-normal" id="doc_lic"></span></p>
                            <p class="fw-bold">PHIC No.: <span class="fw-normal" id="doc_phic"></span></p>
                            <p class="fw-bold">S2 No.: <span class="fw-normal" id="doc_s2"></span></p>
                        </div>

                    </div>

                    <!-- Questions -->
                    <div class="tab-pane w-100" id="questions" role="tabpanel" aria-labelledby="questions-tab"
                        tabindex="0">
                        <h4 class="fw-bold">Add Questions</h4>
                        <hr class="m-0 mb-4">

                        <form class="mb-5" id="create_question_form">
                            @csrf

                            <div class="input-group">
                                <input class="form-control rounded-start-2" type="text" name="dquestion" id="dquestion"
                                    required>
                                <button type="button" class="btn btn-warning" id="create_question_btn"><i
                                        class="fa-solid fa-plus"></i> Add Question</button>
                            </div>
                            <div class="form-text">Type question here.</div>
                        </form>

                        <h4 class="fw-bold">Questions</h4>
                        <hr class="mb-4">

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered" id="doctor_questions_tab">
                                <thead class="table-warning">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Question</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Schedules -->
                    <div class="tab-pane w-100" id="schedules" role="tabpanel" aria-labelledby="schedules-tab"
                        tabindex="0">
                        <h4 class="fw-bold">Add Schedule</h4>
                        <hr class="m-0 mb-4">

                        <form class="d-flex flex-column mb-5" id="create_schedule_form">
                            @csrf

                            <div class="input-group">
                                <select class="form-select" name="schedule_day" id="sched_day">
                                    <option value="" selected disabled>-- Select Day --</option>
                                    <option value="Sunday">Sunday</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>

                                <input class="form-control" type="time" name="sched_from" id="sched_from">
                                <span class="input-group-text">To</span>
                                <input class="form-control" type="time" name="sched_to" id="sched_to">

                                <button type="button" class="btn btn-success" id="add_schedule_btn"><i
                                        class="fa-solid fa-plus"></i> Add Schedule</button>
                            </div>
                            <div class="form-text">Create a schedule by choosing a weekday, the starting time, and an
                                ending time.</div>
                        </form>

                        <h4 class="fw-bold">Schedules</h4>
                        <hr class="m-0">

                        <div class="mt-4 table-responsive">
                            <table class="table table-bordered" id="schedules_calendar">
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Group Management -->
                    <div class="tab-pane w-100" id="services_group_management" role="tabpanel"
                        aria-labelledby="services_group_management-tab" tabindex="0">
                        <h4 class="fw-bold">Add Services Group</h4>
                        <hr class="m-0 mb-4">

                        <form class="mb-5" id="create_group_form">
                            @csrf

                            <!-- Group Name -->
                            <div class="mb-3">
                                <label for="servicegroup_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control rounded-2" name="servicegroup_name"
                                    id="servicegroup_name" placeholder="Enter group name" required>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="servicegroup_dscr" class="form-label">Description</label>
                                <textarea class="form-control rounded-2" name="servicegroup_dscr" id="servicegroup_dscr"
                                    rows="3" placeholder="Enter group description" required></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button type="button" class="btn btn-primary" id="create_group_btn">
                                <i class="fa-solid fa-plus"></i> Add Group
                            </button>
                        </form>

                        <h4 class="fw-bold">Service Group</h4>
                        <hr class="mb-4">

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered" id="group_management_tab">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Reference No.</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Service Management -->
                    <div class="tab-pane w-100" id="services_management" role="tabpanel"
                        aria-labelledby="services_management-tab" tabindex="0">
                        <h4 class="fw-bold">Add Services</h4>
                        <hr class="m-0 mb-4">

                        <form class="mb-5" id="create_services_form">
                            @csrf

                            <div class="d-flex gap-2">
                                <!-- Service Name -->
                                <div class="mb-3 w-100">
                                    <label class="form-label" for="service_name">Service</label>
                                    <input type="text" class="form-control rounded-2" name="service_name"
                                        id="service_name" required>
                                    <div class="form-text">Enter service name.</div>
                                </div>

                                <!-- Category -->
                                <div class="mb-3 w-100">
                                    <label class="form-label" for="service_name">&#8203;</label>
                                    <select class="form-select rounded-2" name="service_category" id="service_category"
                                        required>
                                        <option value="" disabled selected>Select category</option>
                                    </select>
                                    <div class="form-text">Enter service category.</div>
                                </div>

                                <!-- Service Charge -->
                                <div class="mb-3 w-100">
                                    <label class="form-label" for="service_name">&#8203;</label>
                                    <input type="number" class="form-control rounded-2" name="service_charge"
                                        id="service_charge" step="0.01" required>
                                    <div class="form-text">Enter service charge.</div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="service_dscr" class="form-label">Description</label>
                                <textarea class="form-control rounded-2" name="service_dscr" id="service_dscr" rows="3"
                                    required></textarea>
                                <div class="form-text">Enter description.</div>
                            </div>

                            <!-- Submit Button -->
                            <button type="button" class="btn btn-info" id="create_services_btn">
                                <i class="fa-solid fa-plus"></i> Add Service
                            </button>
                        </form>



                        <h4 class="fw-bold">Service Management</h4>
                        <hr class="mb-4">

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered" id="services_management_table_tab">
                                <thead class="table-info">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Service</th>
                                        <th scope="col">Doctor</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Fee</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td><!-- Actions buttons go here --></td>
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
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>