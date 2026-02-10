<?php
/**
 * Queue Management - Professional OPD Flow Tracker
 */
$pageTitle = 'Queue Management';
ob_start();
?>
<link rel="stylesheet" href="<?= asset('libs/sweetalert2/sweetalert2.min.css') ?>">
<style>
    .queue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .queue-card {
        border: 0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .queue-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    }

    .queue-card .card-body {
        padding: 1.5rem;
    }

    /* Status Accents */
    .status-line {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
    }

    .status-line.waiting {
        background: #ffc107;
    }

    .status-line.called {
        background: #0dcaf0;
    }

    .status-line.in-progress {
        background: #198754;
    }

    .token-display {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -1px;
    }

    .token-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #9ca3af;
        font-weight: 600;
    }

    /* Active State Pulse */
    .queue-card.active-state {
        border: 1px solid rgba(var(--primary-rgb), 0.2);
    }

    .queue-card.active-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        border-radius: 16px;
        box-shadow: inset 0 0 0 2px rgba(13, 202, 240, 0.3);
        animation: pulse-border 2s infinite;
    }

    @keyframes pulse-border {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .avatar-soft {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .action-btn-group .btn {
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }
</style>

<?php $styles = ob_get_clean();
ob_start(); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title fw-bold fs-22 mb-1">Queue Management</h1>
        <p class="text-muted mb-0 fs-13">Live patient flow & cabin coordination</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-white shadow-sm text-primary fw-medium" onclick="loadQueue()">
            <i class="ri-refresh-line me-1 spin-hover"></i> Refresh Live
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar-lg bg-warning-transparent rounded-circle me-3 flex-shrink-0">
                    <i class="ri-user-smile-line fs-24 text-warning"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark" id="qWaitCount">0</h3>
                    <div class="text-muted fs-12">Waiting Room</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar-lg bg-info-transparent rounded-circle me-3 flex-shrink-0">
                    <i class="ri-megaphone-line fs-24 text-info"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark" id="qCallCount">0</h3>
                    <div class="text-muted fs-12">Called / Moving</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar-lg bg-success-transparent rounded-circle me-3 flex-shrink-0">
                    <i class="ri-stethoscope-line fs-24 text-success"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark" id="qInCount">0</h3>
                    <div class="text-muted fs-12">In Consultation</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center p-3">
                <div class="avatar-lg bg-primary-transparent rounded-circle me-3 flex-shrink-0">
                    <i class="ri-timer-flash-line fs-24 text-primary"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">12m</h3>
                    <div class="text-muted fs-12">Avg. Wait Time</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Queue Content -->
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold fs-16 mb-0 text-dark">Current Queue</h5>
            <div class="text-muted fs-12">
                <i class="ri-wifi-line text-success me-1"></i> Live Sync Active
            </div>
        </div>

        <div id="queueList">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-3 text-muted">Loading queue data...</div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('libs/sweetalert2/sweetalert2.min.js') ?>"></script>
<?php $content = ob_get_clean();
ob_start(); ?>

<script>
    let queueInterval;

    const initQueue = () => {
        console.log('Initializing Queue...');
        loadQueue();

        // Clear existing interval if any
        if (queueInterval) clearInterval(queueInterval);

        // Auto refresh every 15 seconds
        queueInterval = setInterval(loadQueue, 15000);
    };

    // SPA Hook
    if (typeof Melina !== 'undefined') {
        Melina.onPageLoad(initQueue);
        if (Melina.onPageUnload) {
            Melina.onPageUnload(() => {
                if (queueInterval) clearInterval(queueInterval);
            });
        }
    } else {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQueue);
        } else {
            initQueue();
        }
    }

    async function loadQueue() {
        if (!document.getElementById('queueList')) return;

        try {
            const res = await fetch('/api/v1/queue/active');
            const data = await res.json();

            if (data.success) {
                renderQueue(data.data.queue);
            }
        } catch (e) {
            console.error('Queue load error', e);
        }
    }

    function renderQueue(queue) {
        const list = document.getElementById('queueList');
        if (!list) return;

        if (!queue.length) {
            list.innerHTML = `
                <div class="card border-0 shadow-sm py-5 text-center bg-white rounded-4">
                    <div class="mb-3">
                        <div class="avatar-xl bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="ri-cup-line fs-32 text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">All Caught Up!</h5>
                    <p class="text-muted mb-0">There are no patients currently waiting in the queue.</p>
                </div>
            `;
            updateCounts(0, 0, 0);
            return;
        }

        // Sort: in-progress, called, waiting
        const statusOrder = { 'in-progress': 1, 'called': 2, 'waiting': 3 };
        queue.sort((a, b) => (statusOrder[a.status] || 9) - (statusOrder[b.status] || 9));

        let html = '<div class="queue-grid">';

        queue.forEach(q => {
            const statusClass = q.status; // waiting, called, in-progress
            const isCalled = q.status === 'called';
            const isActive = q.status === 'in-progress';

            let actionButtons = '';

            if (q.status === 'waiting') {
                actionButtons = `
                    <button class="btn btn-primary w-100 fw-medium" onclick="callNext('${q.queue_id}')">
                        <i class="ri-megaphone-line me-2"></i>Call Patient
                    </button>
                `;
            } else if (q.status === 'called') {
                actionButtons = `
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-fill fw-medium" onclick="startSession('${q.queue_id}')">
                            <i class="ri-door-open-line me-1"></i> Start
                        </button>
                        <button class="btn btn-light flex-fill text-muted" onclick="recall('${q.queue_id}')" title="Recall">
                            <i class="ri-repeat-line"></i>
                        </button>
                    </div>
                `;
            } else if (q.status === 'in-progress') {
                actionButtons = `
                    <button class="btn btn-outline-success w-100 fw-medium" onclick="completeSession('${q.queue_id}')">
                        <i class="ri-check-double-line me-2"></i>Complete Visit
                    </button>
                `;
            }

            html += `
                <div class="queue-card ${isCalled ? 'active-card' : ''}">
                    <div class="status-line ${statusClass}"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="text-center">
                                <div class="token-label">Token</div>
                                <div class="token-display text-${statusClass === 'waiting' ? 'secondary' : 'primary'}">${q.token_no}</div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-icon btn-sm btn-ghost-light text-muted rounded-circle" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="cancelQueue('${q.queue_id}')">Remove from Queue</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold fs-16 text-dark mb-1 text-truncate">${escapeHtml(q.first_name)} ${escapeHtml(q.last_name)}</h6>
                            <div class="d-flex align-items-center text-muted fs-13 mb-1">
                                <i class="ri-hospital-line me-1 text-primary alert-primary rounded-circle p-1" style="font-size:10px"></i>
                                ${escapeHtml(q.provider_name || 'General OPD')}
                            </div>
                            <div class="d-flex align-items-center text-muted fs-12">
                                <i class="ri-time-line me-1"></i> Wait: ${calculateWaitTime(q.created_at)}
                            </div>
                        </div>
                        
                        <div class="action-btn-group">
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        list.innerHTML = html;

        updateCounts(
            queue.filter(x => x.status === 'waiting').length,
            queue.filter(x => x.status === 'in-progress').length,
            queue.filter(x => x.status === 'called').length
        );
    }

    function updateCounts(wait, inProgress, called) {
        const wEl = document.getElementById('qWaitCount');
        const iEl = document.getElementById('qInCount');
        const cEl = document.getElementById('qCallCount');
        if (wEl) wEl.textContent = wait;
        if (iEl) iEl.textContent = inProgress;
        if (cEl) cEl.textContent = called;
    }

    function calculateWaitTime(createdAt) {
        const mins = Math.floor((new Date() - new Date(createdAt)) / 60000);
        return mins < 1 ? 'Just added' : mins + ' mins ago';
    }

    function escapeHtml(str) { return str ? String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]) : ''; }

    // Global Functions for Interaction
    window.loadQueue = loadQueue;
    window.callNext = async (id) => {
        try {
            await fetch('/api/v1/queue/call', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ queue_id: id }) });
            loadQueue();
        } catch (e) { }
    };

    window.startSession = async (id) => {
        try {
            await fetch('/api/v1/queue/start', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ queue_id: id }) });
            loadQueue();
        } catch (e) { }
    };

    window.completeSession = async (id) => {
        const result = await Swal.fire({
            title: 'Complete Visit?',
            text: 'Are you sure the consultation is finished?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Finished',
            width: 400
        });
        if (result.isConfirmed) {
            try {
                await fetch('/api/v1/queue/complete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ queue_id: id }) });
                Swal.fire({ icon: 'success', title: 'Completed', timer: 1000, showConfirmButton: false });
                loadQueue();
            } catch (e) { }
        }
    };

    window.recall = (id) => window.callNext(id);

    window.cancelQueue = async (id) => {
        const result = await Swal.fire({
            title: 'Remove Patient?',
            text: 'Remove from queue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Remove',
            width: 400
        });
        if (result.isConfirmed) {
            try {
                await fetch('/api/v1/queue/complete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ queue_id: id, status: 'cancelled' }) });
                loadQueue();
            } catch (e) { }
        }
    };
</script>

<?php $scripts = ob_get_clean();
include __DIR__ . '/../layouts/app.php'; ?>