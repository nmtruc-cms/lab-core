<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\Core\Views\admin\partials\page_header') ?>

<?php $departments  = $departments ?? []; ?>
<?php $deptUsersMap = $deptUsersMap ?? []; ?>
<?php $validation   = $validation ?? []; ?>
<?php $modalState   = is_array($modalState ?? null) ? $modalState : []; ?>

<?php if (session('message')) : ?>
    <div class="admin-alert <?= session('message_type') === 'danger' ? 'danger' : 'success' ?>"><?= esc(session('message')) ?></div>
<?php endif ?>

<div class="results-card">
    <div class="ims-master-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3>Departments</h3>
            <span>Organizational structure shared across modules.</span>
        </div>
        <div class="ims-toolbar-actions">
            <button type="button" class="admin-btn primary"
                data-bs-toggle="modal" data-bs-target="#deptAddModal">
                Add Department
            </button>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="results-table admin-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Department Name</th>
                    <th style="width:100px;">Members</th>
                    <th style="width:180px;">Created</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($departments === []) : ?>
                    <tr class="main-row">
                        <td colspan="5" class="admin-muted">No departments yet.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($departments as $dept) : ?>
                        <?php $users = $deptUsersMap[$dept['id']] ?? []; ?>
                        <tr class="main-row">
                            <td class="admin-muted"><?= esc((string) $dept['id']) ?></td>
                            <td><strong><?= esc($dept['name']) ?></strong></td>
                            <td>
                                <?php if ($users === []) : ?>
                                    <span class="admin-muted">—</span>
                                <?php else : ?>
                                    <span class="admin-chip"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?></span>
                                <?php endif ?>
                            </td>
                            <td class="admin-muted"><?= esc($dept['created_at'] ?? '') ?></td>
                            <td data-label="Action">
                                <div class="ims-action-group">
                                    <button type="button" class="ims-action-btn" title="View members"
                                        data-bs-toggle="modal" data-bs-target="#deptUsersModal"
                                        data-id="<?= esc((string) $dept['id']) ?>"
                                        data-name="<?= esc($dept['name']) ?>"
                                        data-users="<?= esc(json_encode($users)) ?>">
                                        <i class="fa-solid fa-users"></i>
                                    </button>
                                    <button type="button" class="ims-action-btn" title="Edit department"
                                        data-bs-toggle="modal" data-bs-target="#deptEditModal"
                                        data-id="<?= esc((string) $dept['id']) ?>"
                                        data-name="<?= esc($dept['name']) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Members Modal -->
<div class="modal fade" id="deptUsersModal" tabindex="-1" aria-labelledby="deptUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptUsersModalLabel">Members — <span id="deptUsersModalName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <div id="deptUsersTableWrap" style="overflow-x:auto;">
                    <table class="results-table admin-table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th style="width:90px;">Primary</th>
                            </tr>
                        </thead>
                        <tbody id="deptUsersTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="deptAddModal" tabindex="-1" aria-labelledby="deptAddModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= site_url('admin/departments') ?>" id="deptAddForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="deptAddModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="add-dept-name">Department Name</label>
                        <input id="add-dept-name" name="name" type="text"
                            value="<?= ($modalState['open'] ?? '') === 'add' ? esc(old('name') ?? '') : '' ?>"
                            placeholder="e.g. Quality Assurance">
                        <?php if (($modalState['open'] ?? '') === 'add' && isset($validation['name'])) : ?>
                            <small class="admin-error"><?= esc($validation['name']) ?></small>
                        <?php endif ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="admin-btn primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="deptEditModal" tabindex="-1" aria-labelledby="deptEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="deptEditForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="deptEditModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="edit-dept-name">Department Name</label>
                        <input id="edit-dept-name" name="name" type="text" placeholder="e.g. Quality Assurance">
                        <?php if (($modalState['open'] ?? '') === 'edit' && isset($validation['name'])) : ?>
                            <small class="admin-error" id="edit-dept-name-error"><?= esc($validation['name']) ?></small>
                        <?php else : ?>
                            <small class="admin-error" id="edit-dept-name-error" style="display:none;"></small>
                        <?php endif ?>
                    </div>
                    <div class="admin-form-group" style="margin-top:16px;">
                        <button type="button" class="admin-btn danger btn-sm" id="deptDeleteBtn">Delete Department</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="admin-btn primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deptDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Delete <strong id="deptDeleteName"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" id="deptDeleteForm" action="">
                    <?= csrf_field() ?>
                    <button type="submit" class="admin-btn danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalState = <?= json_encode($modalState) ?>;

    // Users modal
    const usersModal = document.getElementById('deptUsersModal');
    if (usersModal) {
        usersModal.addEventListener('show.bs.modal', function (e) {
            const btn   = e.relatedTarget;
            const name  = btn.getAttribute('data-name') ?? '';
            const users = JSON.parse(btn.getAttribute('data-users') ?? '[]');

            document.getElementById('deptUsersModalName').textContent = name;

            const tbody = document.getElementById('deptUsersTableBody');
            if (users.length === 0) {
                tbody.innerHTML = '<tr class="main-row"><td colspan="4" class="admin-muted">No users assigned to this department.</td></tr>';
                return;
            }

            tbody.innerHTML = users.map(u => `
                <tr class="main-row">
                    <td><strong>${esc(u.username)}</strong></td>
                    <td class="admin-muted">${esc(u.email)}</td>
                    <td><span class="admin-chip">${esc(u.group)}</span></td>
                    <td>${u.is_primary ? '<span class="admin-badge success">Primary</span>' : '<span class="admin-muted">—</span>'}</td>
                </tr>`).join('');
        });
    }

    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Edit modal
    const editModal = document.getElementById('deptEditModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            if (!btn) return;
            const id   = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            document.getElementById('edit-dept-name').value = name ?? '';
            document.getElementById('deptEditForm').action  = `<?= site_url('admin/departments/') ?>${id}`;
            document.getElementById('deptDeleteBtn').setAttribute('data-id', id);
            document.getElementById('deptDeleteBtn').setAttribute('data-name', name);
        });
    }

    // Delete button inside edit modal
    const deleteBtn = document.getElementById('deptDeleteBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const id   = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            document.getElementById('deptDeleteName').textContent = name;
            document.getElementById('deptDeleteForm').action = `<?= site_url('admin/departments/') ?>${id}/delete`;
            const deleteModal = new bootstrap.Modal(document.getElementById('deptDeleteModal'));
            bootstrap.Modal.getInstance(editModal)?.hide();
            deleteModal.show();
        });
    }

    // Restore modal after POST-redirect errors
    if (modalState.open === 'add') {
        new bootstrap.Modal(document.getElementById('deptAddModal')).show();
    } else if (modalState.open === 'edit' && modalState.id) {
        const oldName = <?= json_encode(old('name') ?? '') ?>;
        document.getElementById('edit-dept-name').value = oldName;
        document.getElementById('deptEditForm').action  = `<?= site_url('admin/departments/') ?>${modalState.id}`;
        document.getElementById('deptDeleteBtn').setAttribute('data-id', modalState.id);
        new bootstrap.Modal(editModal).show();
    }
})();
</script>

<?= $this->endSection() ?>
