
<div class="schools-page">
    <div class="schools-page-header">
        <div>
            <h1 class="schools-page-title">Schools</h1>
            <p class="schools-page-subtitle">Manage school data for monitoring and evaluation.</p>
        </div>
        <button type="button" class="btn btn-primary" id="btnAddSchool"><i class="fas fa-plus"></i> Add School</button>
    </div>
    <div class="schools-card">
        <div class="schools-card-header">
            <div class="schools-card-title"><i class="fas fa-school"></i> School Data</div>
            <div class="schools-card-total"><span id="schoolTotal">0</span> schools</div>
        </div>
        <div class="schools-card-body">
            <div class="schools-toolbar">
                <div class="schools-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="schoolSearch" class="form-control" placeholder="Search NPSN or school name..." autocomplete="off">
                </div>
                <div class="schools-filter">
                    <select id="schoolLevelFilter" class="form-select">
                        <option value="">All Levels</option>
                        <option value="SMA">SMA</option>
                        <option value="SMK">SMK</option>
                    </select>
                </div>
            </div>
            <div class="schools-table-responsive">
                <table class="table schools-table" id="schoolTable">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>NPSN</th>
                            <th>School Name</th>
                            <th>Level</th>
                            <th>City</th>
                            <th>District</th>
                            <th>Region</th>
                            <th>Status</th>
                            <th width="145">Action</th>
                        </tr>
                    </thead>
                    <tbody id="schoolTableBody">
                        <tr>
                            <td colspan="9" class="school-loading">
                                <div class="school-spinner"></div>
                                Loading school data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="schoolEmpty" class="school-empty" style="display:none">
                <div class="school-empty-icon"><i class="fas fa-school"></i></div>
                <div class="school-empty-title">No school data found</div>
                <div class="school-empty-text">No data matches your search.</div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="schoolAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content school-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-school"></i> Add School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="school-form-alert" id="schoolAddAlert"></div>
                <form id="schoolAddForm">
                    <div class="mb-3">
                        <label class="form-label">NPSN</label>
                        <input type="text" name="npsn" class="form-control" maxlength="20" inputmode="numeric" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school_name" class="form-control" maxlength="150" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <select name="city_id" id="schoolCity" class="form-select">
                            <option value="">Select City</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">District</label>
                        <select name="district_id" id="schoolDistrict" class="form-select" disabled>
                            <option value="">Select city first</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level" id="schoolLevel" class="form-select">
                            <option value="">Select Level</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSchool"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="schoolEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content school-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="school-form-alert" id="schoolEditAlert"></div>
                <form id="schoolEditForm">
                    <input type="hidden" name="id" id="editSchoolId">
                    <div class="mb-3">
                        <label class="form-label">NPSN</label>
                        <input type="text" name="npsn" id="editNpsn" class="form-control" maxlength="20" inputmode="numeric">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school_name" id="editSchoolName" class="form-control" maxlength="150">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <select name="city_id" id="editSchoolCity" class="form-select">
                            <option value="">Select City</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">District</label>
                        <select name="district_id" id="editSchoolDistrict" class="form-select" disabled>
                            <option value="">Select city first</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level" id="editLevel" class="form-select">
                            <option value="">Select Level</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="editIsActive" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnUpdateSchool"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content user-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i> Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div id="deleteUserAlert" class="modal-alert px-3 pt-3 mb-0"></div>

            <div class="modal-body">
                <div class="user-delete-content text-center py-2">
                    <div class="user-delete-icon text-danger fs-1 mb-3">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <p class="mb-2">Are you sure you want to delete this user?</p>
                    <strong id="deleteUserName" class="fs-5 text-dark d-block mb-3">-</strong>
                    <input type="hidden" id="deleteUserId">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>