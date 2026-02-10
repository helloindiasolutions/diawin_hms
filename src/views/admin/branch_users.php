<?php
$pageTitle = "Branch Users";
$branchId = $params['branch_id'] ?? null;
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <button class="btn btn-sm btn-light me-3" onclick="history.back()">
            <i class="ri-arrow-left-line"></i> Back
        </button>
        <h2 class="main-content-title fs-24 mb-1 d-inline-block" id="branchName">Branch Users</h2>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/branches">Branches</a></li>
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </div>
    <div>
        <button class="btn btn-primary" onclick="showAddUserModal()">
            <i class="ri-user-add-line me-2"></i>Add User
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Roles</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="userFullName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="userUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="userMobile">
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="userPassword">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="userRole" required>
                            <option value="">Select Role</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="userStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save User</button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>
<script>
    const branchId = <?= json_encode($branchId) ?>;
    let userModal;
    let availableRoles = [];

    document.addEventListener('DOMContentLoaded', () => {
        userModal = new bootstrap.Modal(document.getElementById('userModal'));
        loadBranchUsers();
        loadRoles();
    });

    async function loadBranchUsers() {
        try {
            const res = await fetch(`/api/v1/branches/${branchId}/users`);
            const data = await res.json();
            
            if (data.success && data.data.users) {
                renderUsers(data.data.users);
            }
        } catch (e) {
            showError('Failed to load users');
        }
    }

    async function loadRoles() {
        try {
            const res = await fetch('/api/v1/roles');
            const data = await res.json();
            
            if (data.success && data.data.roles) {
                availableRoles = data.data.roles;
                const select = document.getElementById('userRole');
                select.innerHTML = '<option value="">Select Role</option>' + 
                    data.data.roles.map(role => 
                        `<option value="${role.role_id}">${escapeHtml(role.name)}</option>`
                    ).join('');
            }
        } catch (e) {
            console.error('Failed to load roles');
        }
    }

    function renderUsers(users) {
        const tbody = document.querySelector('#usersTable tbody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                            <span class="fw-bold">${getInitials(user.full_name)}</span>
                        </div>
                        <div>
                            <h6 class="mb-0 fs-14">${escapeHtml(user.full_name)}</h6>
                        </div>
                    </div>
                </td>
                <td>${escapeHtml(user.username)}</td>
                <td>${escapeHtml(user.email || 'N/A')}</td>
                <td>${escapeHtml(user.mobile || 'N/A')}</td>
                <td>
                    ${user.roles ? user.roles.split(',').map(role => 
                        `<span class="badge bg-primary-transparent">${escapeHtml(role)}</span>`
                    ).join(' ') : 'N/A'}
                </td>
                <td>${formatDateTime(user.last_login)}</td>
                <td>
                    <span class="badge ${user.is_active ? 'bg-success' : 'bg-secondary'}">
                        ${user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editUser(${user.user_id})">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.user_id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function showAddUserModal() {
        document.getElementById('userModalTitle').textContent = 'Add User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('passwordField').style.display = 'block';
        document.getElementById('userPassword').required = true;
        userModal.show();
    }

    async function editUser(userId) {
        try {
            const res = await fetch(`/api/v1/users/${userId}`);
            const data = await res.json();
            
            if (data.success && data.data.user) {
                const user = data.data.user;
                document.getElementById('userModalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = user.user_id;
                document.getElementById('userFullName').value = user.full_name;
                document.getElementById('userUsername').value = user.username;
                document.getElementById('userEmail').value = user.email || '';
                document.getElementById('userMobile').value = user.mobile || '';
                document.getElementById('userStatus').value = user.is_active ? '1' : '0';
                document.getElementById('passwordField').style.display = 'none';
                document.getElementById('userPassword').required = false;
                userModal.show();
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to load user details', 'error');
        }
    }

    async function saveUser() {
        const userId = document.getElementById('userId').value;
        const data = {
            branch_id: branchId,
            full_name: document.getElementById('userFullName').value,
            username: document.getElementById('userUsername').value,
            email: document.getElementById('userEmail').value,
            mobile: document.getElementById('userMobile').value,
            is_active: parseInt(document.getElementById('userStatus').value),
            role_id: parseInt(document.getElementById('userRole').value)
        };

        if (!userId) {
            data.password = document.getElementById('userPassword').value;
        }

        try {
            const url = userId ? `/api/v1/users/${userId}` : '/api/v1/users';
            const method = userId ? 'PUT' : 'POST';
            
            const res = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            
            if (result.success) {
                userModal.hide();
                Swal.fire('Success', 'User saved successfully', 'success');
                loadBranchUsers();
            } else {
                Swal.fire('Error', result.message || 'Failed to save user', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to save user: ' + e.message, 'error');
        }
    }

    async function deleteUser(userId) {
        const result = await Swal.fire({
            title: 'Delete User?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/api/v1/users/${userId}`, { method: 'DELETE' });
                const data = await res.json();
                
                if (data.success) {
                    Swal.fire('Deleted!', 'User deleted successfully', 'success');
                    loadBranchUsers();
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete user', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Failed to delete user', 'error');
            }
        }
    }

    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return 'Never';
        const date = new Date(dateStr);
        return date.toLocaleString('en-IN');
    }

    function showError(message) {
        document.querySelector('#usersTable tbody').innerHTML = `
            <tr><td colspan="8" class="text-center py-4">
                <div class="alert alert-danger">${message}</div>
            </td></tr>
        `;
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>
