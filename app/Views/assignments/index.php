<main class="app-main">
    <div class="assignments-page">
        <div class="assignments-header">
            <div class="assignments-title">
                <h1>Assignments</h1>
                <p>Manage school assignments and officers</p>
            </div>
            <div class="assignments-header-action">
                <button type="button" class="btn btn-primary" id="btnAddAssignment"><i class="fas fa-plus"></i><span>Add Assignment</span></button>
            </div>
        </div>
        <div class="assignments-toolbar">
            <div class="assignment-search">
                <i class="fas fa-search"></i>
                <input type="text" id="assignmentSearch" class="form-control" placeholder="Search school, NPSN or officer...">
            </div>
            <div class="assignment-filter">
                <select id="assignmentStatus" class="form-select">
                    <option value="">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="CANCELLED">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="assignments-card">
            <div class="table-responsive">
                <table class="table assignments-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>School</th>
                            <th>Level</th>
                            <th>City</th>
                            <th>District</th>
                            <th>Region</th>
                            <th>Officer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentTableBody">
                        <tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="assignmentEmpty" class="assignment-empty" style="display:none;">
                <div class="assignment-empty-icon"><i class="fas fa-tasks"></i></div>
                <h5>No assignments found</h5>
                <p>There are currently no assignments to display.</p>
            </div>
        </div>
    </div>
    <div class="modal fade" id="assignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="assignmentModalTitle">Add Assignment</h5>
                        <small class="text-muted">Create a new school assignment</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="assignmentForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="assignmentId">
                    <div class="modal-body">
                        <div class="assignment-form-section">
                            <div class="assignment-section-title"><i class="fas fa-school"></i><span>School Information</span></div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="assignmentSchool" class="form-label">School <span class="text-danger">*</span></label>
                                    <select name="school_id" id="assignmentSchool" class="form-select" required><option value="">Select School</option></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" id="assignmentCity" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">District</label>
                                    <input type="text" id="assignmentDistrict" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Region</label>
                                    <input type="text" id="assignmentRegion" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Level</label>
                                    <input type="text" id="assignmentLevel" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="assignment-form-section">
                            <div class="assignment-section-title"><i class="fas fa-user-check"></i><span>Assignment Information</span></div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="assignmentUser" class="form-label">Officer <span class="text-danger">*</span></label>
                                    <select name="user_id" id="assignmentUser" class="form-select" required><option value="">Select Officer</option></select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assignmentDate" class="form-label">Assignment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="assignment_date" id="assignmentDate" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="assignmentStatusForm" class="form-label">Status</label>
                                    <select name="status" id="assignmentStatusForm" class="form-select">
                                        <option value="ACTIVE">Active</option>
                                        <option value="COMPLETED">Completed</option>
                                        <option value="CANCELLED">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assignmentNotes" class="form-label">Notes</label>
                                    <input type="text" name="notes" id="assignmentNotes" class="form-control" placeholder="Optional notes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveAssignment"><i class="fas fa-save"></i><span>Save Assignment</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="delete-assignment-icon"><i class="fas fa-trash"></i></div>
                    <h6>Delete this assignment?</h6>
                    <p class="text-muted mb-2">This action cannot be undone.</p>
                    <strong id="deleteAssignmentSchool"></strong>
                    <input type="hidden" id="deleteAssignmentId">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDeleteAssignment"><i class="fas fa-trash"></i><span>Delete</span></button>
                </div>
            </div>
        </div>
    </div>
