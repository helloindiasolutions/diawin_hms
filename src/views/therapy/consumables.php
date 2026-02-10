<?php
/**
 * Therapy Consumables Usage
 * Inventory tracking for Siddha/Ayurvedic Oils and Herbs
 */
$pageTitle = "Therapy Consumables";
ob_start();
?>

<div class="d-md-flex align-items-center justify-content-between page-header-breadcrumb mb-4">
    <div>
        <h2 class="main-content-title fs-24 mb-1">Consumables Usage</h2>
        <span class="text-muted fs-12">Track usage of Oils (Thailam), Herbal Powders, and other supplies during
            therapy.</span>
    </div>
    <div class="btn-list">
        <button class="btn btn-outline-primary btn-wave" onclick="loadStock()">
            <i class="ri-refresh-line align-middle me-1"></i> Check Stock
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <div class="card-title">Log Usage for Session</div>
            </div>
            <div class="card-body">
                <form id="consumableLogForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Session Reference</label>
                            <select class="form-select" name="session_id" id="sessionSelect" required>
                                <option value="">Select Active Session...</option>
                                <option value="1">#101 - Sakthi Kumar (Elakizhi)</option>
                                <option value="2">#102 - Meera (Abhyanga)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Consumable Item</label>
                            <select class="form-select" name="consumable_id" id="itemSelect" required>
                                <option value="">Select Item...</option>
                                <optgroup label="Siddha Oils (Thailam)">
                                    <option value="1">Dhanwantharam Thailam (ml)</option>
                                    <option value="2">Pinda Thailam (ml)</option>
                                    <option value="3">Mahanarayana Thailam (ml)</option>
                                </optgroup>
                                <optgroup label="Herbal Powders (Choornam)">
                                    <option value="4">Kottamchukkadi Choornam (g)</option>
                                    <option value="5">Kolakulathadi Choornam (g)</option>
                                </optgroup>
                                <optgroup label="Others">
                                    <option value="6">Cotton Roll (nos)</option>
                                    <option value="7">Disposable Sheet (nos)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Quantity Used</label>
                            <input type="number" step="0.01" class="form-control" name="qty_used" id="qtyInput"
                                placeholder="Enter amount..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unit</label>
                            <input type="text" class="form-control" id="unitDisplay" value="ml" readonly>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-add-circle-line me-1"></i> Log Usage
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header border-bottom justify-content-between">
                <div class="card-title">Recent Usage History</div>
                <div class="d-flex">
                    <button class="btn btn-sm btn-outline-light"><i class="ri-file-excel-line me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0">
                        <thead class="bg-light">
                            <tr class="fs-11 text-uppercase">
                                <th>Date/Time</th>
                                <th>Session</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Logged By</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="usageHistoryTable" class="fs-13">
                            <!-- Simulation Data -->
                            <tr>
                                <td>Today, 10:30 AM</td>
                                <td>#101</td>
                                <td class="fw-semibold">Dhanwantharam Thailam</td>
                                <td><span class="badge bg-primary-transparent">150 ml</span></td>
                                <td>Anand</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-danger-light"><i
                                            class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Today, 09:15 AM</td>
                                <td>#102</td>
                                <td class="fw-semibold">Kolakulathadi Choornam</td>
                                <td><span class="badge bg-secondary-transparent">250 g</span></td>
                                <td>Mala</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-danger-light"><i
                                            class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('itemSelect').addEventListener('change', (e) => {
        const item = e.target.value;
        const unitDisplay = document.getElementById('unitDisplay');
        if (['1', '2', '3'].includes(item)) unitDisplay.value = 'ml';
        else if (['4', '5'].includes(item)) unitDisplay.value = 'g';
        else unitDisplay.value = 'nos';
    });

    document.getElementById('consumableLogForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('/api/v1/therapy/consumables', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                showToast('Consumable usage logged successfully', 'success');
                e.target.reset();
            } else {
                showToast(data.message || 'Error logging usage', 'error');
            }
        } catch (err) {
            showToast('Network error', 'error');
        }
    });

    function loadStock() {
        showToast('Current stock levels: Dhanwantharam Oil (12L), Podi (4.5kg)', 'info');
    }
</script>

<?php $content = ob_get_clean();
require_once ROOT_PATH . '/src/views/layouts/app.php'; ?>