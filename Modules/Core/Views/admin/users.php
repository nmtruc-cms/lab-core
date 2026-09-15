<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\Core\Views\admin\partials\page_header') ?>

<?php $users        = $users ?? []; ?>
<?php $nameMap      = $nameMap ?? []; ?>
<?php $departmentMap= $departmentMap ?? []; ?>
<?php $magicLinkMap = $magicLinkMap ?? []; ?>
<?php $groups       = $groups ?? []; ?>
<?php $permissions  = $permissions ?? []; ?>
<?php $permissionMap= $permissionMap ?? []; ?>
<?php $departments  = $departments ?? []; ?>
<?php $validation   = $validation ?? []; ?>
<?php $modalState   = is_array($modalState ?? null) ? $modalState : []; ?>
<?php
$permissionGroups = [];
foreach ($permissions as $permission => $description) {
    $scope = explode('.', (string) $permission)[0] ?? 'other';
    $permissionGroups[$scope][(string) $permission] = (string) $description;
}
?>

<?php if (session('message')) : ?>
    <div class="admin-alert <?= session('message_type') === 'danger' ? 'danger' : 'success' ?>"><?= esc(session('message')) ?></div>
<?php endif ?>

<?php if (session('magic_link_url')) : ?>
    <div class="admin-alert info" id="magicLinkBanner" style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;">
        <div style="flex:1;min-width:0;">
            <strong>Activation link for <?= esc(session('magic_link_for') ?? 'user') ?></strong>
            <p style="margin:6px 0 8px;font-size:.84rem;opacity:.8;">Share this link with the user. It expires in 72 hours and can only be used once.</p>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" id="magicLinkInput" readonly
                    value="<?= esc(session('magic_link_url')) ?>"
                    style="flex:1;font-size:.8rem;padding:6px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.15);color:inherit;min-width:0;">
                <button type="button" class="admin-btn secondary" onclick="copyMagicLink()" id="copyMagicBtn">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
        </div>
        <button type="button" onclick="document.getElementById('magicLinkBanner').remove()" style="background:none;border:none;cursor:pointer;opacity:.7;font-size:1.2rem;padding:0;line-height:1;" title="Dismiss">&times;</button>
    </div>
<?php endif ?>

<div class="results-card">
    <div class="ims-master-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3>Users</h3>
            <span>Manage user accounts and access rights.</span>
        </div>
        <?php if (lab_core_can('core.users.create')) : ?>
            <div class="ims-toolbar-actions">
                <button type="button" class="admin-btn primary"
                    data-bs-toggle="modal" data-bs-target="#userAddModal">
                    Add New User
                </button>
            </div>
        <?php endif ?>
    </div>

    <div style="overflow-x:auto;">
        <table class="results-table app-responsive-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Departments</th>
                    <th>Status</th>
                    <?php if (lab_core_can('core.users.update')) : ?>
                        <th style="width:90px;">Action</th>
                    <?php endif ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <?php
                    $groups_user  = $user->getGroups() ?? [];
                    $depts        = $departmentMap[$user->id] ?? [];
                    $names        = $nameMap[$user->id] ?? ['first_name' => '', 'last_name' => ''];
                    $fullName     = trim($names['first_name'] . ' ' . $names['last_name']);
                    $initials     = '';
                    if ($names['first_name'] !== '') $initials .= strtoupper(substr($names['first_name'], 0, 1));
                    if ($names['last_name'] !== '')  $initials .= strtoupper(substr($names['last_name'], 0, 1));
                    if ($initials === '') $initials = strtoupper(substr((string) ($user->username ?? 'U'), 0, 2));
                    $magicUrl     = $magicLinkMap[$user->id] ?? null;
                    $currentDeptId = $depts[0]['department_id'] ?? 0;
                    $directPermissions = $permissionMap[$user->id] ?? [];
                    ?>
                    <tr class="main-row">
                        <td data-label="User">
                            <div class="admin-user-cell">
                                <div class="nav-avatar"><?= esc($initials) ?></div>
                                <div>
                                    <strong><?= $fullName !== '' ? esc($fullName) : esc($user->username ?? 'N/A') ?></strong>
                                    <span class="admin-muted">@<?= esc($user->username ?? '') ?></span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Email"><?= esc($user->email ?? '-') ?></td>
                        <td data-label="Role">
                            <?php if ($groups_user !== []) : ?>
                                <span class="admin-chip"><?= esc($groups_user[0]) ?></span>
                            <?php else : ?>
                                <span class="admin-muted">—</span>
                            <?php endif ?>
                        </td>
                        <td data-label="Departments">
                            <?php if ($depts === []) : ?>
                                <span class="admin-muted">Not assigned</span>
                            <?php else : ?>
                                <?php foreach ($depts as $dept) : ?>
                                    <span class="admin-chip <?= $dept['is_primary'] ? '' : 'muted' ?>"><?= esc($dept['name']) ?></span>
                                <?php endforeach ?>
                            <?php endif ?>
                        </td>
                        <td data-label="Status">
                            <span class="admin-badge <?= $user->active ? 'success' : '' ?>">
                                <?= $user->active ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <?php if (lab_core_can('core.users.update')) : ?>
                            <td data-label="Action">
                                <div class="ims-action-group">
                                    <?php if ($magicUrl !== null) : ?>
                                        <button type="button" class="ims-action-btn" title="Copy activation link"
                                            onclick="copyToClipboard(this, <?= esc(json_encode($magicUrl)) ?>)">
                                            <i class="fa-solid fa-link"></i>
                                        </button>
                                    <?php else : ?>
                                        <form method="post"
                                            action="<?= esc(site_url('admin/users/' . $user->id . '/magic-link')) ?>"
                                            style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="ims-action-btn" title="Generate activation link"
                                                style="opacity:.45;">
                                                <i class="fa-solid fa-link"></i>
                                            </button>
                                        </form>
                                    <?php endif ?>
                                    <button type="button" class="ims-action-btn" title="Edit user"
                                        data-bs-toggle="modal" data-bs-target="#userEditModal"
                                        data-id="<?= esc((string) $user->id) ?>"
                                        data-first-name="<?= esc($names['first_name']) ?>"
                                        data-last-name="<?= esc($names['last_name']) ?>"
                                        data-role="<?= esc($groups_user[0] ?? '') ?>"
                                        data-dept-id="<?= esc((string) $currentDeptId) ?>"
                                        data-permissions='<?= esc(json_encode($directPermissions, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES)) ?>'
                                        data-active="<?= $user->active ? '1' : '0' ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="userAddModal" tabindex="-1" aria-labelledby="userAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= site_url('admin/users') ?>" id="userAddForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="userAddModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $isAdd = ($modalState['open'] ?? '') === 'add'; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group">
                            <label for="add-first-name">First Name <span style="color:#ef4444;">*</span></label>
                            <input id="add-first-name" name="first_name" type="text"
                                value="<?= $isAdd ? esc(old('first_name') ?? '') : '' ?>"
                                placeholder="John">
                            <?php if ($isAdd && isset($validation['first_name'])) : ?>
                                <small class="admin-error"><?= esc($validation['first_name']) ?></small>
                            <?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="add-last-name">Last Name <span style="color:#ef4444;">*</span></label>
                            <input id="add-last-name" name="last_name" type="text"
                                value="<?= $isAdd ? esc(old('last_name') ?? '') : '' ?>"
                                placeholder="Doe">
                            <?php if ($isAdd && isset($validation['last_name'])) : ?>
                                <small class="admin-error"><?= esc($validation['last_name']) ?></small>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="add-email">Email <span style="color:#ef4444;">*</span></label>
                        <input id="add-email" name="email" type="email"
                            value="<?= $isAdd ? esc(old('email') ?? '') : '' ?>"
                            placeholder="john.doe@example.com">
                        <?php if ($isAdd && isset($validation['email'])) : ?>
                            <small class="admin-error"><?= esc($validation['email']) ?></small>
                        <?php endif ?>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group">
                            <label for="add-role">Role <span style="color:#ef4444;">*</span></label>
                            <select id="add-role" name="role">
                                <option value="">— select —</option>
                                <?php foreach (array_keys($groups) as $alias) : ?>
                                    <option value="<?= esc($alias) ?>"
                                        <?= ($isAdd && old('role') === $alias) ? 'selected' : '' ?>>
                                        <?= esc($alias) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <?php if ($isAdd && isset($validation['role'])) : ?>
                                <small class="admin-error"><?= esc($validation['role']) ?></small>
                            <?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="add-department">Department</label>
                            <select id="add-department" name="department_id">
                                <option value="">— none —</option>
                                <?php foreach ($departments as $dept) : ?>
                                    <option value="<?= esc((string) $dept['id']) ?>"
                                        <?= ($isAdd && (int) old('department_id') === $dept['id']) ? 'selected' : '' ?>>
                                        <?= esc($dept['name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <p class="admin-muted" style="font-size:.82rem;margin-top:4px;">
                        Username will be auto-generated from the name. An activation link will be generated after saving.
                    </p>
                    <div class="admin-form-group" style="margin-top:14px;">
                        <label>Direct Permissions</label>
                        <div class="admin-muted" style="font-size:.82rem;margin-bottom:8px;">
                            Optional user-specific permissions in addition to the selected role.
                        </div>
                        <div class="user-permission-grid">
                            <?php foreach ($permissionGroups as $scope => $scopePermissions) : ?>
                                <div class="user-permission-section">
                                    <strong><?= esc(strtoupper((string) $scope)) ?></strong>
                                    <?php foreach ($scopePermissions as $permission => $description) : ?>
                                        <label class="user-permission-option">
                                            <input type="checkbox" name="permissions[]" value="<?= esc($permission) ?>"
                                                <?= ($isAdd && in_array($permission, (array) old('permissions', []), true)) ? 'checked' : '' ?>>
                                            <span>
                                                <span class="tool-id-badge"><?= esc($permission) ?></span>
                                                <small><?= esc($description) ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="admin-btn primary">Create &amp; Get Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" id="userEditForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="userEditModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $isEdit = ($modalState['open'] ?? '') === 'edit'; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group">
                            <label for="edit-first-name">First Name</label>
                            <input id="edit-first-name" name="first_name" type="text" placeholder="John">
                            <?php if ($isEdit && isset($validation['first_name'])) : ?>
                                <small class="admin-error"><?= esc($validation['first_name']) ?></small>
                            <?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-last-name">Last Name</label>
                            <input id="edit-last-name" name="last_name" type="text" placeholder="Doe">
                            <?php if ($isEdit && isset($validation['last_name'])) : ?>
                                <small class="admin-error"><?= esc($validation['last_name']) ?></small>
                            <?php endif ?>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group">
                            <label for="edit-role">Role</label>
                            <select id="edit-role" name="role">
                                <option value="">— none —</option>
                                <?php foreach (array_keys($groups) as $alias) : ?>
                                    <option value="<?= esc($alias) ?>"><?= esc($alias) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-department">Department</label>
                            <select id="edit-department" name="department_id">
                                <option value="">— none —</option>
                                <?php foreach ($departments as $dept) : ?>
                                    <option value="<?= esc((string) $dept['id']) ?>"><?= esc($dept['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-group" style="display:flex;align-items:center;gap:10px;">
                        <input type="checkbox" id="edit-active" name="active" value="1" style="width:auto;margin:0;">
                        <label for="edit-active" style="margin:0;cursor:pointer;">Active account</label>
                    </div>
                    <div class="admin-form-group" style="margin-top:14px;">
                        <label>Direct Permissions</label>
                        <div class="admin-muted" style="font-size:.82rem;margin-bottom:8px;">
                            Optional user-specific permissions in addition to the selected role.
                        </div>
                        <div class="user-permission-grid">
                            <?php foreach ($permissionGroups as $scope => $scopePermissions) : ?>
                                <div class="user-permission-section">
                                    <strong><?= esc(strtoupper((string) $scope)) ?></strong>
                                    <?php foreach ($scopePermissions as $permission => $description) : ?>
                                        <label class="user-permission-option">
                                            <input type="checkbox" name="permissions[]" value="<?= esc($permission) ?>" class="edit-permission-checkbox">
                                            <span>
                                                <span class="tool-id-badge"><?= esc($permission) ?></span>
                                                <small><?= esc($description) ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div style="border-top:1px solid var(--border-color);margin-top:16px;padding-top:14px;">
                        <p class="admin-muted" style="font-size:.82rem;margin:0 0 8px;">Activation link</p>
                        <form method="post" id="regenLinkForm" action="" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-btn secondary btn-sm">
                                <i class="fa-solid fa-rotate-right"></i> Regenerate activation link
                            </button>
                        </form>
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

<script>
(function () {
    const modalState = <?= json_encode($modalState) ?>;

    // Edit modal: populate from data-* on trigger button
    const editModal = document.getElementById('userEditModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            document.getElementById('edit-first-name').value = btn.getAttribute('data-first-name') ?? '';
            document.getElementById('edit-last-name').value  = btn.getAttribute('data-last-name') ?? '';
            document.getElementById('edit-active').checked   = btn.getAttribute('data-active') === '1';
            document.getElementById('userEditForm').action    = `<?= site_url('admin/users/') ?>${id}`;
            document.getElementById('regenLinkForm').action   = `<?= site_url('admin/users/') ?>${id}/magic-link`;
            const directPermissions = JSON.parse(btn.getAttribute('data-permissions') || '[]');
            document.querySelectorAll('.edit-permission-checkbox').forEach((checkbox) => {
                checkbox.checked = directPermissions.includes(checkbox.value);
            });
            const roleVal = btn.getAttribute('data-role') ?? '';
            const roleEl  = document.getElementById('edit-role');
            Array.from(roleEl.options).forEach(o => o.selected = (o.value === roleVal));
            const deptVal = btn.getAttribute('data-dept-id') ?? '0';
            const deptEl  = document.getElementById('edit-department');
            Array.from(deptEl.options).forEach(o => o.selected = (o.value === deptVal));
        });
    }

    // Restore modal after POST-redirect with validation errors
    if (modalState.open === 'add') {
        new bootstrap.Modal(document.getElementById('userAddModal')).show();
    } else if (modalState.open === 'edit' && modalState.id) {
        document.getElementById('userEditForm').action  = `<?= site_url('admin/users/') ?>${modalState.id}`;
        document.getElementById('regenLinkForm').action = `<?= site_url('admin/users/') ?>${modalState.id}/magic-link`;
        const fn = <?= json_encode(old('first_name') ?? '') ?>;
        const ln = <?= json_encode(old('last_name') ?? '') ?>;
        if (fn) document.getElementById('edit-first-name').value = fn;
        if (ln) document.getElementById('edit-last-name').value  = ln;
        const oldPermissions = <?= json_encode((array) old('permissions', [])) ?>;
        document.querySelectorAll('.edit-permission-checkbox').forEach((checkbox) => {
            checkbox.checked = oldPermissions.includes(checkbox.value);
        });
        new bootstrap.Modal(editModal).show();
    }

    // Magic link banner copy button
    window.copyMagicLink = function () {
        const input = document.getElementById('magicLinkInput');
        if (!input) return;
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = document.getElementById('copyMagicBtn');
            if (btn) { btn.textContent = '✓ Copied!'; setTimeout(() => btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy', 2000); }
        });
    };

    // Action column copy button
    window.copyToClipboard = function (btn, url) {
        navigator.clipboard.writeText(url).then(() => {
            const icon = btn.querySelector('i');
            if (icon) { icon.className = 'fa-solid fa-check'; btn.title = 'Copied!'; setTimeout(() => { icon.className = 'fa-solid fa-link'; btn.title = 'Copy activation link'; }, 2000); }
        });
    };
})();
</script>

<style>
.user-permission-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.user-permission-section {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 12px;
    background: rgba(255,255,255,.45);
}
.user-permission-section > strong {
    display: block;
    margin-bottom: 8px;
    font-size: .78rem;
    letter-spacing: .04em;
}
.user-permission-option {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 7px 0;
    cursor: pointer;
}
.user-permission-option input {
    width: auto;
    margin-top: 4px;
}
.user-permission-option span span {
    display: inline-block;
    margin-bottom: 3px;
}
.user-permission-option small {
    display: block;
    color: var(--muted-text);
    line-height: 1.35;
}
@media (max-width: 768px) {
    .user-permission-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?= $this->endSection() ?>
