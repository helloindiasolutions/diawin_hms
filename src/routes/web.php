<?php
/**
 * Web Routes
 * Define all web page routes here
 */

declare(strict_types=1);

use System\Response;
use System\Middleware;
use System\Session;
use System\Router;
use System\Security;

// Home route
$router->get('/', function () {
    if (Session::isLoggedIn()) {
        Router::redirect(($_ENV['BASE_PATH'] ?? '') . '/dashboard');
    } else {
        Router::redirect(($_ENV['BASE_PATH'] ?? '') . '/login');
    }
});

// Guest routes (only for non-authenticated users)
$router->group(['middleware' => [[Middleware::class, 'guest']]], function ($router) {

    $router->get('/login', function () {
        view('auth.login');
    });

    $router->get('/portal', function () {
        view('auth.portal');
    });

    $router->post('/login', function () {
        Middleware::csrf([]);
        require_once SRC_PATH . '/controllers/AuthController.php';
        (new \App\Controllers\AuthController())->login();
    });

    $router->get('/login/otp-callback', function () {
        require_once SRC_PATH . '/controllers/AuthController.php';
        (new \App\Controllers\AuthController())->otpCallback();
    });
});

// Authenticated routes
$router->group(['middleware' => [[Middleware::class, 'auth']]], function ($router) {

    // ============================================
    // 1. DYNAMIC MODULE PLACEHOLDERS (HIGHEST PRIORITY)
    // ============================================
    // These must come before parameterized routes to avoid ID hijacking
    $router->get('/patients/dashboard', function () {
        view('patients.dashboard');
    });
    $router->get('/patients/family', function () {
        view('patients.family');
    });
    $router->get('/opd/my-patients', function () {
        view('opd.my_patients');
    });
    $router->get('/opd/workspace', function () {
        view('opd.workspace');
    });
    $router->get('/opd/print_prescription', function () {
        view('opd.print_prescription');
    });
    $router->get('/opd/siddha/pulse-diagnosis', function () {
        view('visits.siddha_notes', ['active_tab' => 'pulse']);
    });
    $router->get('/opd/siddha/prakriti', function () {
        view('visits.siddha_notes', ['active_tab' => 'prakriti']);
    });
    $router->get('/opd/siddha/anupanam', function () {
        view('visits.siddha_notes', ['active_tab' => 'anupanam']);
    });
    $router->get('/opd/siddha/tongue-examination', function () {
        view('visits.siddha_notes', ['active_tab' => 'tongue']);
    });
    $router->get('/opd/treatment-plans', function () {
        view('opd.treatment_plans');
    });
    $router->get('/ip/discharge-summary', function () {
        view('ip.discharge_summary');
    });
    $router->get('/ip/billing', function () {
        view('ip.billing');
    });

    $router->get('/billing/refunds', function () {
        view('billing.refunds');
    });
    $router->get('/billing/outstanding', function () {
        view('billing.outstanding');
    });
    $router->get('/pharmacy/dispensing', function () {
        view('pharmacy.dispensing');
    });
    $router->get('/pharmacy/expiry-alerts', function () {
        view('pharmacy.expiry_alerts');
    });
    $router->get('/therapy/booking', function () {
        view('therapy.booking');
    });
    $router->get('/therapy/consumables', function () {
        view('therapy.consumables');
    });
    $router->get('/therapy/billing', function () {
        view('therapy.billing');
    });
    $router->get('/reports/doctor-revenue', function () {
        view('reports.doctor_revenue');
    });
    $router->get('/reports/department-revenue', function () {
        view('reports.department_revenue');
    });
    $router->get('/reports/pharmacy-sales', function () {
        view('reports.pharmacy_sales');
    });
    $router->get('/reports/outstanding', function () {
        view('reports.outstanding');
    });
    $router->get('/reports/therapy', function () {
        view('reports.therapy');
    });
    $router->get('/admin/branches/add', function () {
        view('admin.branches_create');
    });
    $router->get('/admin/branches/config', function () {
        view('admin.branches_config');
    });
    $router->get('/admin/branches/staff-assignment', function () {
        view('admin.branches_staff');
    });
    $router->get('/admin/branches/transfer', function () {
        view('admin.branches_transfer');
    });
    $router->get('/admin/reports', function () {
        view('admin.reports');
    });

    // ============================================
    // 2. CORE SYSTEM ROUTES
    // ============================================
    $router->get('/dashboard', function () {
        view('dashboard.index');
    });

    $router->get('/dashboard/analytics', function () {
        view('dashboard.analytics');
    });

    $router->get('/logout', function () {
        Session::logout();
        Session::flash('success', 'You have been logged out successfully.');
        Router::redirect(($_ENV['BASE_PATH'] ?? '') . '/login');
    });

    $router->post('/logout', function () {
        Middleware::csrf([]);
        Session::logout();
        Response::success(null, 'Logged out successfully');
    });

    $router->get('/profile', function () {
        view('profile.index');
    });

    $router->get('/profile/settings', function () {
        view('profile.settings');
    });

    // PATIENTS
    $router->get('/patients', function () {
        view('patients.index');
    });
    $router->get('/patients/create', function () {
        view('patients.create');
    });
    $router->get('/patients/contacts', function () {
        view('patients.contacts');
    });

    // INVENTORY
    $router->get('/products', function () {
        view('inventory.products');
    });
    $router->get('/products/create', function () {
        view('inventory.product_create');
    });
    $router->get('/products/{id}', function ($id) {
        view('inventory.product_details', ['product_id' => $id]);
    });
    $router->get('/inventory/categories', function () {
        view('inventory.categories');
    });
    $router->get('/inventory/units', function () {
        view('inventory.units');
    });
    $router->get('/suppliers', function () {
        view('inventory.suppliers');
    });
    $router->get('/consumables', function () {
        view('inventory.consumables');
    });
    $router->get('/pharmacy-categories', function () {
        view('inventory.categories');
    });
    $router->get('/purchase-quotations', function () {
        view('inventory.purchase_quotations');
    });
    $router->get('/purchase-quotations/create', function () {
        view('inventory.quotation_create');
    });
    $router->get('/purchase-orders', function () {
        view('inventory.purchase_orders_list');
    });
    $router->get('/purchase-orders/create', function () {
        view('inventory.purchase_orders');
    });
    $router->get('/grn', function () {
        view('inventory.grn_list');
    });
    $router->get('/grn/create', function () {
        view('inventory.grn');
    });
    $router->get('/grn/view', function () {
        view('inventory.grn');
    });
    $router->get('/inventory/batches', function () {
        view('inventory.batches');
    });
    $router->get('/inventory/stock', function () {
        view('inventory.stock_new');
    });
    $router->get('/inventory/warehouses', function () {
        view('inventory.warehouses');
    });
    $router->get('/consumables/stock', function () {
        view('inventory.consumable_stock');
    });
    $router->get('/consumables/movements', function () {
        view('inventory.consumable_movements');
    });

    // BILLING
    $router->get('/invoices', function () {
        view('billing.invoices');
    });
    $router->get('/invoices/create', function () {
        view('billing.invoice_create');
    });
    $router->get('/billing/print', function () {
        view('billing.print');
    });
    $router->get('/payments', function () {
        view('billing.payments');
    });
    $router->get('/packages', function () {
        view('billing.packages');
    });
    $router->get('/dcr', function () {
        view('billing.dcr');
    });

    // PHARMACY
    $router->get('/pharmacy', function () {
        view('pharmacy.index');
    });
    $router->get('/pharmacy/{id}', function ($id) {
        view('pharmacy.details', ['branch_id' => $id]);
    });

    // REGISTRATION & APPOINTMENTS
    $router->get('/registrations', function () {
        view('registrations.index');
    });
    $router->get('/registrations/create', function () {
        view('registrations.create');
    });
    $router->get('/registrations/slip', function () {
        require_once SRC_PATH . '/controllers/PrintController.php';
        (new \App\Controllers\PrintController())->registrationSlip();
    });
    $router->get('/print/case-sheet', function () {
        require_once SRC_PATH . '/controllers/PrintController.php';
        (new \App\Controllers\PrintController())->caseSheet();
    });
    $router->get('/appointments', function () {
        view('appointments.index');
    });
    $router->get('/appointments/calendar', function () {
        view('appointments.calendar');
    });
    $router->get('/queue', function () {
        view('appointments.queue');
    });
    $router->get('/appointments/{id}', function ($id) {
        view('appointments.details', ['appointment_id' => $id]);
    });

    // VISITS & CLINICAL
    $router->get('/visits', function () {
        view('visits.index');
    });
    $router->get('/visits/vitals', function () {
        view('visits.vitals');
    });
    $router->get('/visits/clinical-notes', function () {
        view('visits.clinical_notes');
    });
    $router->get('/visits/siddha-notes', function () {
        view('visits.siddha_notes');
    });
    $router->get('/visits/prescriptions', function () {
        view('visits.prescriptions');
    });
    $router->get('/visits/case-sheet', function () {
        view('visits.case_sheet');
    });
    $router->get('/prescriptions', function () {
        view('visits.prescriptions');
    });

    // IN-PATIENT (IP)
    $router->get('/admissions', function () {
        view('ip.admissions');
    });
    $router->get('/ip/admissions', function () {
        view('ip.admissions');
    });
    $router->get('/ip/admission-details', function () {
        view('ip.admission_details');
    });
    $router->get('/ip/wards', function () {
        view('ip.wards');
    });
    $router->get('/ip/ward-details', function () {
        view('ip.ward_details');
    });
    $router->get('/ip/beds', function () {
        view('ip.beds');
    });
    $router->get('/ip/allocations', function () {
        view('ip.allocations');
    });
    $router->get('/beds/allocations', function () {
        view('ip.allocations');
    });
    $router->get('/ip/nursing-notes', function () {
        view('ip.nursing_notes');
    });
    $router->get('/ip/rounds', function () {
        view('ip.rounds');
    });

    // THERAPY
    $router->get('/therapy/sessions', function () {
        view('therapy.sessions');
    });
    $router->get('/therapy/protocols', function () {
        view('therapy.protocols');
    });
    $router->get('/therapy/rules', function () {
        view('therapy.rules');
    });
    $router->get('/therapy/booking', function () {
        view('therapy.booking');
    });
    $router->get('/therapy/consumables', function () {
        view('therapy.consumables');
    });
    $router->get('/therapy/billing', function () {
        view('therapy.billing');
    });

    // STAFF & ADMIN
    $router->get('/staff', function () {
        view('staff.index');
    });
    $router->get('/staff/create', function () {
        view('staff.create');
    });
    $router->get('/doctors', function () {
        view('doctors.index');
    });
    $router->get('/doctors/create', function () {
        view('doctors.create');
    });
    $router->get('/doctors/specialties', function () {
        view('doctors.specialties');
    });
    $router->get('/branches', function () {
        view('admin.branches');
    });
    $router->get('/branches/{id}/users', function ($id) {
        view('admin.branch_users', ['branch_id' => $id]);
    });
    $router->get('/users', function () {
        view('admin.users');
    });
    $router->get('/users/create', function () {
        view('admin.users_create');
    });
    $router->get('/users/{id}/edit', function ($id) {
        view('admin.users_edit', ['user_id' => $id]);
    });
    $router->get('/roles', function () {
        view('admin.roles');
    });
    $router->get('/audit-logs', function () {
        view('admin.audit_logs');
    });

    // REPORTS
    $router->get('/reports/daily-revenue', function () {
        view('reports.daily_revenue');
    });
    $router->get('/reports/patient-counts', function () {
        view('reports.patient_counts');
    });
    $router->get('/reports/inventory', function () {
        view('reports.inventory');
    });

    // COMMUNICATION
    $router->get('/whatsapp/templates', function () {
        view('communication.whatsapp_templates');
    });
    $router->get('/whatsapp/logs', function () {
        view('communication.whatsapp_logs');
    });
    $router->get('/whatsapp/scheduled', function () {
        view('communication.whatsapp_scheduled');
    });
    $router->get('/call-logs', function () {
        view('communication.call_logs');
    });

    // HR
    $router->get('/attendance', function () {
        view('hr.attendance');
    });
    $router->get('/payroll', function () {
        view('hr.payroll');
    });

    // ============================================
    // 3. PARAMETERIZED ROUTES (LOWEST PRIORITY)
    // ============================================
    // Catch-all for patients
    $router->get('/patients/{id}', function ($id) {
        view('patients.show', ['patient_id' => $id]);
    });
    $router->get('/patients/{id}/edit', function ($id) {
        view('patients.edit', ['patient_id' => $id]);
    });

    // Catch-all for generic pages
    $router->get('/pages/{page}', function ($page) {
        $pagePath = ROOT_PATH . '/pages/' . Security::sanitizeFilename($page) . '.php';
        if (file_exists($pagePath)) {
            require_once $pagePath;
        } else {
            abort(404, 'Page not found');
        }
    });

});

// Error pages (with app layout for logged-in users)
$router->get('/error/401', function () {
    http_response_code(401);
    if (\System\Session::isLoggedIn()) {
        view('errors.401');
    } else {
        \System\Router::redirect(($_ENV['BASE_PATH'] ?? '') . '/login');
    }
});

$router->get('/error/403', function () {
    http_response_code(403);
    if (\System\Session::isLoggedIn()) {
        view('errors.403');
    } else {
        \System\Router::redirect(($_ENV['BASE_PATH'] ?? '') . '/login');
    }
});

$router->get('/error/404', function () {
    http_response_code(404);
    if (\System\Session::isLoggedIn()) {
        view('errors.404');
    } else {
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>404</h1><p>Page not found</p><a href="' . (($_ENV['BASE_PATH'] ?? '') . '/login') . '">Go to Login</a></body></html>';
    }
});

$router->get('/error/500', function () {
    http_response_code(500);
    if (\System\Session::isLoggedIn()) {
        view('errors.500');
    } else {
        echo '<!DOCTYPE html><html><head><title>500 Server Error</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>500</h1><p>Internal Server Error</p><a href="' . (($_ENV['BASE_PATH'] ?? '') . '/login') . '">Go to Login</a></body></html>';
    }
});
