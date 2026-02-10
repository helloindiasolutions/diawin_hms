<?php
$pageTitle = "Product Categories";
ob_start();
?>

<!-- Main Content Header -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-semibold fs-20 mb-1">Product Categories</h1>
        <p class="text-muted mb-0 fs-13">Manage product categories and classifications</p>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" onclick="openCategoryModal()">
            <i class="ri-add-line me-1"></i> Add Category
        </button>
    </div>
</div>

<!-- Categories Table -->
<div class="card custom-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="categoriesTable">
                <thead class="bg-light">
                    <tr>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3 ps-3">Category Name</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Description</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Products Count</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3">Status</th>
                        <th class="fs-12 fw-semibold text-muted border-0 py-3 text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-16 fw-semibold" id="categoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="category_id" name="category_id">

                    <div class="mb-3">
                        <label for="category_name" class="form-label fs-13 fw-semibold">Category Name <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_description" class="form-label fs-13 fw-semibold">Description</label>
                        <textarea class="form-control" id="category_description" name="category_description"
                            rows="3"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="category_status" name="category_status"
                            checked>
                        <label class="form-check-label fs-13" for="category_status">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveCategory()">Save Category</button>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let categoryModal;

    // Initialize immediately - works for both initial load AND SPA navigation
    (function initCategoriesPage() {
        const modalEl = document.getElementById('categoryModal');
        if (!modalEl) {
            console.log('Categories script skipped - not on categories page');
            return;
        }
        console.log('Categories page: Initializing...');
        categoryModal = new bootstrap.Modal(modalEl);
        loadCategories();
    })();

    async function loadCategories() {
        try {
            const response = await fetch('/api/v1/inventory/categories');
            const result = await response.json();

            const tbody = document.getElementById('categoriesTableBody');

            if (result.success && result.data.categories.length > 0) {
                tbody.innerHTML = result.data.categories.map(cat => `
                <tr>
                    <td class="fs-13 fw-medium ps-3">${cat.name}</td>
                    <td class="fs-12 text-muted">${cat.description || '-'}</td>
                    <td class="fs-12">${cat.products_count || 0}</td>
                    <td>
                        <span class="badge bg-${cat.status === 'active' ? 'success' : 'secondary'}-transparent text-${cat.status === 'active' ? 'success' : 'secondary'} fs-10">
                            ${cat.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-icon btn-light" onclick="editCategory(${cat.id})">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light" onclick="deleteCategory(${cat.id})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            } else {
                tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted fs-13">
                        No categories found. Click "Add Category" to create one.
                    </td>
                </tr>
            `;
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            document.getElementById('categoriesTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger fs-13">
                    Failed to load categories
                </td>
            </tr>
        `;
        }
    }

    function openCategoryModal() {
        document.getElementById('categoryModalLabel').textContent = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('category_id').value = '';
        categoryModal.show();
    }

    async function editCategory(id) {
        try {
            const response = await fetch(`/api/v1/inventory/categories/${id}`);
            const result = await response.json();

            if (result.success) {
                const cat = result.data;
                document.getElementById('categoryModalLabel').textContent = 'Edit Category';
                document.getElementById('category_id').value = cat.id;
                document.getElementById('category_name').value = cat.name;
                document.getElementById('category_description').value = cat.description || '';
                document.getElementById('category_status').checked = cat.status === 'active';
                categoryModal.show();
            }
        } catch (error) {
            console.error('Error loading category:', error);
            Swal.fire('Error', 'Failed to load category details', 'error');
        }
    }

    async function saveCategory() {
        const form = document.getElementById('categoryForm');
        const formData = new FormData(form);
        const id = document.getElementById('category_id').value;

        const data = {
            name: formData.get('category_name'),
            description: formData.get('category_description'),
            status: formData.has('category_status') ? 'active' : 'inactive'
        };

        try {
            const url = id ? `/api/v1/inventory/categories/${id}` : '/api/v1/inventory/categories';
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: id ? 'Category updated successfully' : 'Category created successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
                categoryModal.hide();
                loadCategories();
            } else {
                Swal.fire('Error', result.message || 'Failed to save category', 'error');
            }
        } catch (error) {
            console.error('Error saving category:', error);
            Swal.fire('Error', 'An error occurred while saving the category', 'error');
        }
    }

    async function deleteCategory(id) {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "This will not delete products in this category",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/api/v1/inventory/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                    loadCategories();
                } else {
                    Swal.fire('Error', data.message || 'Failed to delete category', 'error');
                }
            } catch (error) {
                console.error('Error deleting category:', error);
                Swal.fire('Error', 'An error occurred while deleting the category', 'error');
            }
        }
    }
</script>

<?php $scripts = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>