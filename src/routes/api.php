<?php
/**
 * API Routes
 * Define all API endpoints here
 */

declare(strict_types=1);

use System\Response;
use System\Middleware;
use System\JWT;
use App\Api\Controllers\AuthController;
use App\Api\Controllers\UserController;
use App\Api\Controllers\ThemeController;
use App\Api\Controllers\PatientController;
use App\Api\Controllers\AppointmentController;
use App\Api\Controllers\DoctorController;
use App\Api\Controllers\VisitController;
use App\Api\Controllers\IPController;
use App\Api\Controllers\IPDController;
use App\Api\Controllers\TherapyController;
use App\Api\Controllers\RegistrationController;
use App\Api\Controllers\StaffController;
use App\Api\Controllers\BillingController;
use App\Api\Controllers\InventoryController;
use App\Api\Controllers\MenuController;
use App\Api\Controllers\SearchController;
use App\Api\Controllers\AnalyticsController;
use App\Api\Controllers\DashboardController;
use App\Api\Controllers\PharmacyController;
use App\Api\Controllers\BranchController;
use App\Api\Controllers\PerformanceController;

// Health check
$apiRouter->get('/api/health', function () {
    Response::success([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => $_ENV['APP_VERSION'] ?? '1.0.0'
    ]);
});

// API version
$apiRouter->get('/api/version', function () {
    Response::success([
        'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
        'api_version' => 'v1'
    ]);
});

// ============================================
// THEME ROUTES
// ============================================
$apiRouter->group(['prefix' => 'api/v1/theme'], function ($router) {
    $router->get('/settings', [ThemeController::class, 'getSettings']);
    $router->post('/settings', [ThemeController::class, 'updateSettings']);
    $router->post('/reset', [ThemeController::class, 'reset']);
});

// ============================================
// MENU ROUTES
// ============================================
$apiRouter->group(['prefix' => 'api/v1/menu', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/', [MenuController::class, 'getMenu']);
    $router->get('/badges', [MenuController::class, 'getBadges']);
    $router->post('/branch-switch', [MenuController::class, 'branchSwitch']);
});

// ============================================
// GLOBAL SEARCH
// ============================================
$apiRouter->group(['prefix' => 'api/v1', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/search', [SearchController::class, 'search']);
    $router->get('/search/patients', [SearchController::class, 'searchPatientsEndpoint']);
    $router->get('/search/appointments', [SearchController::class, 'searchAppointmentsEndpoint']);
});

// ============================================
// ANALYTICS & REPORTING
// ============================================
$apiRouter->group(['prefix' => 'api/v1/analytics', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);
});

// ============================================
// PERFORMANCE MONITORING (Admin only)
// ============================================
$apiRouter->group(['prefix' => 'api/v1/performance', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/metrics', [PerformanceController::class, 'getMetrics']);
    $router->get('/test-query', [PerformanceController::class, 'testQuery']);
    $router->post('/clear-cache', [PerformanceController::class, 'clearCache']);
    $router->get('/check-indexes', [PerformanceController::class, 'checkIndexes']);
    $router->get('/analyze-query', [PerformanceController::class, 'analyzeQuery']);
});

// ============================================
// PERFORMANCE MONITORING (Admin only)
// ============================================
$apiRouter->group(['prefix' => 'api/v1/performance', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/metrics', [PerformanceController::class, 'getMetrics']);
    $router->get('/test-query', [PerformanceController::class, 'testQuery']);
    $router->post('/clear-cache', [PerformanceController::class, 'clearCache']);
    $router->get('/check-indexes', [PerformanceController::class, 'checkIndexes']);
    $router->get('/analyze-query', [PerformanceController::class, 'analyzeQuery']);
});

// ============================================
// DASHBOARD API (Dynamic Data)
// ============================================
$apiRouter->group(['prefix' => 'api/v1/dashboard', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    $router->get('/stats', [DashboardController::class, 'getStats']);
    $router->get('/patient-overview', [DashboardController::class, 'getPatientOverviewByAge']);
    $router->get('/revenue-chart', [DashboardController::class, 'getRevenueChart']);
    $router->get('/patient-departments', [DashboardController::class, 'getPatientsByDepartment']);
    $router->get('/doctors-schedule', [DashboardController::class, 'getDoctorsSchedule']);
    $router->get('/reports', [DashboardController::class, 'getReports']);
    $router->get('/appointments', [DashboardController::class, 'getAppointments']);
    $router->get('/activity', [DashboardController::class, 'getRecentActivity']);
    $router->get('/recent-activity', [DashboardController::class, 'getRecentActivity']);
    $router->get('/calendar-events', [DashboardController::class, 'getCalendarEvents']);
    $router->get('/appointment-trends', [DashboardController::class, 'getAppointmentTrends']);
    $router->get('/departmental-trends', [DashboardController::class, 'getDepartmentalTrends']);
    $router->get('/doctor-trends', [DashboardController::class, 'getDoctorTrends']);
});

// ============================================
// SESSION-BASED API ROUTES (for web app)
// ============================================
$apiRouter->group(['prefix' => 'api/v1', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    // Patients CRUD (session auth for web)
    $router->get('/patients', [PatientController::class, 'index']);
    $router->get('/patients/stats', [PatientController::class, 'stats']);
    $router->get('/patients/search', [PatientController::class, 'search']);
    $router->get('/patients/contacts', [PatientController::class, 'contacts']);
    $router->get('/patients/family-lookup', [PatientController::class, 'familyLookup']);
    $router->get('/patients/search-by-emergency-mobile', [PatientController::class, 'searchByEmergencyMobile']);
    $router->get('/patients/{id}/medical-summary', [PatientController::class, 'medicalSummary']);
    $router->get('/patients/{id}', [PatientController::class, 'show']);
    $router->get('/patients/{id}/full', [PatientController::class, 'fullDetails']);
    $router->get('/patients/{id}/visits', [PatientController::class, 'visits']);
    $router->get('/patients/{id}/prescriptions', [PatientController::class, 'prescriptions']);
    $router->get('/patients/{id}/billing', [PatientController::class, 'billing']);
    $router->get('/patients/{id}/family', [PatientController::class, 'family']);
    $router->get('/patients/{id}/timeline', [PatientController::class, 'timeline']);
    $router->post('/patients', [PatientController::class, 'store']);
    $router->put('/patients/{id}', [PatientController::class, 'update']);
    $router->patch('/patients/{id}/status', [PatientController::class, 'toggleStatus']);
    $router->delete('/patients/{id}', [PatientController::class, 'destroy']);

    // Appointments CRUD
    $router->get('/appointments', [AppointmentController::class, 'index']);
    $router->get('/appointments/stats', [AppointmentController::class, 'stats']);
    $router->get('/appointments/providers', [AppointmentController::class, 'providers']);
    $router->get('/appointments/{id}', [AppointmentController::class, 'show']);
    $router->post('/appointments', [AppointmentController::class, 'store']);
    $router->patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

    // Doctors CRUD
    $router->get('/doctors', [DoctorController::class, 'index']);
    $router->get('/doctors/stats', [DoctorController::class, 'stats']);
    $router->get('/doctors/specialties', [DoctorController::class, 'specialties']);
    $router->get('/departments', [DoctorController::class, 'getDepartments']);
    $router->post('/doctors', [DoctorController::class, 'store']);
    $router->put('/doctors/{id}', [DoctorController::class, 'update']);
    $router->delete('/doctors/{id}', [DoctorController::class, 'destroy']);

    // Queue Management
    $router->get('/queue/active', [AppointmentController::class, 'activeQueue']);
    $router->post('/queue/call', [AppointmentController::class, 'callPatient']);
    $router->post('/queue/start', [AppointmentController::class, 'startSession']);
    $router->post('/queue/complete', [AppointmentController::class, 'completePatient']);

    // Visit & Clinical Management
    $router->get('/visits', [VisitController::class, 'index']);
    $router->get('/visits/stats', [VisitController::class, 'stats']);
    $router->get('/visits/{id}', [VisitController::class, 'show']);
    $router->post('/visits/{id}/close', [VisitController::class, 'closeVisit']);
    $router->get('/visits/{id}/vitals', [VisitController::class, 'getVitals']);
    $router->post('/visits/vitals', [VisitController::class, 'storeVitals']);
    $router->get('/visits/{id}/siddha-notes', [VisitController::class, 'getSiddhaNotes']);
    $router->post('/visits/siddha-notes', [VisitController::class, 'storeSiddhaNotes']);
    $router->get('/visits/{id}/clinical-notes', [VisitController::class, 'getClinicalNotes']);
    $router->post('/visits/clinical-notes', [VisitController::class, 'storeClinicalNote']);
    $router->get('/visits/{id}/prescriptions', [VisitController::class, 'getPrescriptions']);
    $router->post('/visits/prescriptions', [VisitController::class, 'storePrescription']);
    $router->post('/visits/prescriptions/share', [VisitController::class, 'sharePrescription']);

    // Prescription Print
    $router->get('/prescriptions/{id}/print', [VisitController::class, 'printPrescription']);

    // In-Patient (IPD) Management
    $router->get('/ipd/stats', [IPDController::class, 'getIPStats']);
    $router->get('/ipd/branch-info', [IPDController::class, 'getBranchInfo']);

    // Wards & Beds
    $router->get('/ipd/wards', [IPDController::class, 'getWards']);
    $router->post('/ipd/wards', [IPDController::class, 'storeWard']);
    $router->put('/ipd/wards/{id}', [IPDController::class, 'updateWard']);
    $router->put('/ipd/wards/{id}/status', [IPDController::class, 'updateWardStatus']);
    $router->delete('/ipd/wards/{id}', [IPDController::class, 'deleteWard']);
    $router->get('/ipd/beds', [IPDController::class, 'getBeds']);
    $router->post('/ipd/beds', [IPDController::class, 'storeBed']);
    $router->post('/ipd/beds/status', [IPDController::class, 'updateBedStatus']);
    $router->post('/ipd/transfer-bed', [IPDController::class, 'transferBed']);
    $router->post('/ipd/deallocate-bed', [IPDController::class, 'deallocateBed']);

    // Admissions
    $router->get('/ipd/wards', [IPDController::class, 'getWards']);
    $router->get('/ipd/beds', [IPDController::class, 'getBeds']);
    $router->get('/ipd/admissions', [IPDController::class, 'getAdmissions']);
    $router->post('/ipd/admissions', [IPDController::class, 'storeAdmission']);
    $router->post('/ipd/admissions/quick', [IPDController::class, 'storeQuickAdmission']);
    $router->get('/ipd/admissions/{id}', [IPDController::class, 'getAdmissionDetails']);

    // Nursing Notes
    $router->get('/ipd/admissions/{id}/nursing-notes', [IPDController::class, 'getNursingNotes']);
    $router->post('/ipd/nursing-notes', [IPDController::class, 'storeNursingNote']);

    // Doctor Rounds
    $router->get('/ipd/admissions/{id}/rounds', [IPDController::class, 'getDoctorRounds']);
    $router->post('/ipd/rounds', [IPDController::class, 'storeDoctorRound']);

    // Discharge Summary
    $router->get('/ipd/admissions/{id}/discharge-summary', [IPDController::class, 'getDischargeSummary']);
    $router->post('/ipd/discharge-summary', [IPDController::class, 'storeDischargeSummary']);

    // IP Billing
    $router->get('/ipd/admissions/{id}/billing-items', [IPDController::class, 'getBillingItems']);
    $router->post('/ipd/billing-items', [IPDController::class, 'storeBillingItem']);

    // Therapy Management
    $router->get('/therapy/sessions', [TherapyController::class, 'sessions']);
    $router->post('/therapy/sessions', [TherapyController::class, 'storeSession']);
    $router->post('/therapy/sessions/status', [TherapyController::class, 'updateStatus']);
    $router->get('/therapy/protocols', [TherapyController::class, 'protocols']);
    $router->post('/therapy/protocols', [TherapyController::class, 'storeProtocol']);
    $router->get('/therapy/rules', [TherapyController::class, 'rules']);
    $router->post('/therapy/consumables', [TherapyController::class, 'logConsumable']);

    // Registration Management
    $router->get('/registrations', [RegistrationController::class, 'index']);
    $router->post('/registrations', [RegistrationController::class, 'store']);

    // Staff Management
    $router->get('/staff', [StaffController::class, 'index']);
    $router->post('/staff', [StaffController::class, 'store']);
    $router->delete('/staff/{id}', [StaffController::class, 'destroy']);
    $router->get('/staff/roles', [StaffController::class, 'roles']);

    // Billing & Finance
    $router->get('/billing/init', [BillingController::class, 'init']);
    $router->get('/billing/invoices', [BillingController::class, 'invoices']);
    $router->get('/billing/invoices/{id}/print', [BillingController::class, 'printInvoice']);
    $router->get('/billing/invoices/{id}', [BillingController::class, 'show']);
    $router->post('/billing/invoices', [BillingController::class, 'store']);
    $router->get('/billing/payments', [BillingController::class, 'payments']);
    $router->get('/billing/packages', [BillingController::class, 'packages']);
    $router->get('/billing/dcr', [BillingController::class, 'dcr']);
    $router->get('/billing/items', [BillingController::class, 'items']);

    // Inventory & Pharmacy
    $router->get('/inventory/products', [InventoryController::class, 'products']);
    $router->get('/inventory/products/{id}', [InventoryController::class, 'getProduct']);
    $router->get('/inventory/products/{id}/overview', [InventoryController::class, 'getProductOverview']);
    $router->get('/inventory/products/{id}/po-history', [InventoryController::class, 'getProductPOHistory']);
    $router->get('/inventory/products/{id}/grn-history', [InventoryController::class, 'getProductGRNHistory']);
    $router->get('/inventory/products/{id}/price-history', [InventoryController::class, 'getProductPriceHistory']);
    $router->get('/inventory/products/{id}/suppliers', [InventoryController::class, 'getProductSuppliers']);
    $router->get('/inventory/products/{id}/stock', [InventoryController::class, 'getProductStock']);
    $router->post('/inventory/products', [InventoryController::class, 'storeProduct']);
    $router->get('/inventory/suppliers', [InventoryController::class, 'suppliers']);
    $router->get('/inventory/consumables', [InventoryController::class, 'consumables']);
    $router->get('/inventory/purchase-orders', [InventoryController::class, 'purchaseOrders']);
    $router->get('/inventory/purchase-orders/{id}', [InventoryController::class, 'getPurchaseOrder']);
    $router->get('/inventory/purchase-orders/{id}/items', [InventoryController::class, 'getPOItems']);
    $router->get('/inventory/remote-items', [InventoryController::class, 'remoteItems']);
    $router->post('/inventory/purchase-orders', [InventoryController::class, 'storePurchaseOrder']);
    $router->get('/inventory/grn', [InventoryController::class, 'getGRNs']);
    $router->get('/inventory/grn/{id}', [InventoryController::class, 'getGRN']);
    $router->post('/inventory/grn', [InventoryController::class, 'storeGRN']);
    $router->get('/inventory/stock-products', [InventoryController::class, 'getStockProducts']);
    $router->get('/inventory/batches', [InventoryController::class, 'getAllBatches']);
    $router->get('/inventory/warehouses', [InventoryController::class, 'warehouses']);
    $router->get('/inventory/warehouses/{id}', [InventoryController::class, 'getWarehouse']);
    $router->post('/inventory/warehouses', [InventoryController::class, 'storeWarehouse']);
    $router->put('/inventory/warehouses/{id}', [InventoryController::class, 'updateWarehouse']);
    $router->delete('/inventory/warehouses/{id}', [InventoryController::class, 'deleteWarehouse']);
    $router->get('/inventory/categories', [InventoryController::class, 'categories']);
    $router->get('/inventory/categories/{id}', [InventoryController::class, 'getCategory']);
    $router->post('/inventory/categories', [InventoryController::class, 'storeCategory']);
    $router->put('/inventory/categories/{id}', [InventoryController::class, 'updateCategory']);
    $router->delete('/inventory/categories/{id}', [InventoryController::class, 'deleteCategory']);
    $router->get('/inventory/units', [InventoryController::class, 'units']);
    $router->get('/inventory/units/{id}', [InventoryController::class, 'getUnit']);
    $router->post('/inventory/units', [InventoryController::class, 'storeUnit']);
    $router->put('/inventory/units/{id}', [InventoryController::class, 'updateUnit']);
    $router->delete('/inventory/units/{id}', [InventoryController::class, 'deleteUnit']);

    // Pharmacy Branches
    $router->get('/pharmacy/branches', [PharmacyController::class, 'branches']);
    $router->get('/pharmacy/branches/{branchId}', [PharmacyController::class, 'branchDetails']);
    $router->get('/pharmacy/branches/{branchId}/inventory', [PharmacyController::class, 'branchInventory']);
    $router->get('/pharmacy/branches/{branchId}/sales', [PharmacyController::class, 'branchSales']);
    $router->get('/pharmacy/branches/{branchId}/expiry-alerts', [PharmacyController::class, 'branchExpiryAlerts']);
    $router->get('/pharmacy/branches/{branchId}/users', [PharmacyController::class, 'branchUsers']);
    $router->get('/pharmacy/branches/{branchId}/po', [PharmacyController::class, 'branchPO']);
    $router->get('/pharmacy/branches/{branchId}/grn', [PharmacyController::class, 'branchGRN']);
    $router->get('/pharmacy/branches/{branchId}/appointments', [PharmacyController::class, 'branchAppointments']);
    $router->get('/pharmacy/branches/{branchId}/analytics', [PharmacyController::class, 'branchAnalytics']);

    // Purchase Quotations
    $router->get('/quotations', [InventoryController::class, 'quotations']);
    $router->get('/quotations/supplier/{supplierId}', [InventoryController::class, 'getSupplierQuotations']);
    $router->get('/quotations/compare', [InventoryController::class, 'compareQuotations']);
    $router->get('/quotations/latest-price/{supplier_id}/{product_id}', [InventoryController::class, 'getLatestPrice']);
    $router->get('/quotations/{id}', [InventoryController::class, 'getQuotation']);
    $router->post('/quotations', [InventoryController::class, 'storeQuotation']);
    $router->put('/quotations/{id}', [InventoryController::class, 'updateQuotation']);
    $router->delete('/quotations/{id}', [InventoryController::class, 'deleteQuotation']);

    // Melina Products (Remote Database)
    $router->get('/inventory/melina-products', [InventoryController::class, 'melinaProducts']);
    $router->post('/inventory/sync-melina-products', [InventoryController::class, 'syncMelinaProducts']);
});

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================
$apiRouter->group(['prefix' => 'api/v1'], function ($router) {

    // Auth routes with rate limiting
    $router->post('/auth/login', [AuthController::class, 'login'], [
        [Middleware::class, 'rateLimit', [10, 60]]
    ]);

    $router->post('/auth/register', [AuthController::class, 'register'], [
        [Middleware::class, 'rateLimit', [5, 60]]
    ]);

    $router->post('/auth/forgot-password', [AuthController::class, 'forgotPassword'], [
        [Middleware::class, 'rateLimit', [3, 60]]
    ]);

    $router->post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    $router->post('/auth/refresh', [AuthController::class, 'refreshToken']);

    // OTP Auth Routes
    $router->post('/auth/otp/generate', [AuthController::class, 'generateOTP']);
    $router->post('/auth/otp/verify', [AuthController::class, 'verifyOTP']);
});

$apiRouter->group(['prefix' => 'api/v1', 'middleware' => [[Middleware::class, 'auth']]], function ($router) {
    // Users Management
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->post('/users', [UserController::class, 'store']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);

    // Roles Management
    $router->get('/roles', [\App\Api\Controllers\RoleController::class, 'index']);
    $router->post('/roles', [\App\Api\Controllers\RoleController::class, 'store']);
    $router->put('/roles/{id}', [\App\Api\Controllers\RoleController::class, 'update']);
    $router->delete('/roles/{id}', [\App\Api\Controllers\RoleController::class, 'destroy']);

    // Branch Management
    $router->get('/branches', [BranchController::class, 'index']);
    $router->get('/branches/{id}', [BranchController::class, 'show']);
    $router->post('/branches', [BranchController::class, 'store']);
    $router->put('/branches/{id}', [BranchController::class, 'update']);
    $router->delete('/branches/{id}', [BranchController::class, 'destroy']);
    $router->get('/branches/{id}/users', [BranchController::class, 'getBranchUsers']);
    $router->post('/branches/assign-user', [BranchController::class, 'assignUser']);
    $router->post('/branches/create-admin', [BranchController::class, 'createBranchAdmin']);

    // Auth Me - also needed by session
    $router->get('/auth/me', [AuthController::class, 'me']);
});

$apiRouter->group(['prefix' => 'api/v1', 'middleware' => [[Middleware::class, 'jwtAuth']]], function ($router) {
    // These routes still strictly require JWT for pure API apps
    $router->post('/auth/logout', [AuthController::class, 'logout']);

    // Profile
    $router->get('/profile', [UserController::class, 'profile']);
    $router->put('/profile', [UserController::class, 'updateProfile']);
    $router->put('/profile/password', [UserController::class, 'updatePassword']);
});

// 404 for undefined routes
$apiRouter->any('/api/{path}', function () {
    Response::notFound('API endpoint not found');
});
