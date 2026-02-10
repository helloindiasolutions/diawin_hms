<?php

namespace App\Config;

/**
 * MenuConfig - Hardcoded menu structure for HMS Sidebar
 * 
 * This configuration defines the complete menu hierarchy for the Hospital Management System.
 * Menu structure supports up to 3 levels: Menu Group (Level 1), Menu Item (Level 2), Submenu (Level 3)
 * 
 * Each menu item has the following properties:
 * - menu_key: Unique identifier for the menu item
 * - menu_label: Display text shown in the sidebar
 * - menu_icon: RemixIcon class (e.g., 'ri-dashboard-line')
 * - route_path: URL path for navigation (null for groups with children)
 * - menu_level: Hierarchy level (1, 2, or 3)
 * - required_permission: Permission key required to view this item (null = always visible)
 * - badge_source: Identifier for real-time counter badge (null = no badge)
 * - children: Array of nested menu items
 */
class MenuConfig
{

    /**
     * Get the complete menu structure
     * 
     * @return array Complete menu hierarchy
     */
    public static function getMenuStructure(): array
    {
        return [
            // Dashboard - Only for Doctor and Admin roles (NOT Receptionist, Nurse, Pharmacist)
            [
                'menu_key' => 'dashboard',
                'menu_label' => 'Dashboard',
                'menu_icon' => 'ri-dashboard-line',
                'route_path' => '/dashboard',
                'menu_level' => 1,
                'required_permission' => ['super_admin', 'admin', 'Doctor'],
                'badge_source' => null,
                'children' => []
            ],

            // Patient Portal - Patients Only
            [
                'menu_key' => 'patient_portal',
                'menu_label' => 'Patient Portal',
                'menu_icon' => 'ri-user-smile-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Patient', 'patient'],
                'children' => [
                    [
                        'menu_key' => 'patient_dashboard',
                        'menu_label' => 'My Health Dashboard',
                        'menu_icon' => 'ri-heart-pulse-line',
                        'route_path' => '/patients/dashboard',
                        'menu_level' => 2,
                        'required_permission' => ['Patient', 'patient'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'patient_family',
                        'menu_label' => 'My Family',
                        'menu_icon' => 'ri-team-line',
                        'route_path' => '/patients/family',
                        'menu_level' => 2,
                        'required_permission' => ['Patient', 'patient'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Front Office Module - Accessible by Receptionist and Admin roles
            [
                'menu_key' => 'front_office',
                'menu_label' => 'Front Office',
                'menu_icon' => 'ri-service-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Receptionist', 'admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'registration_desk',
                        'menu_label' => 'Registration Desk',
                        'menu_icon' => 'ri-user-add-line',
                        'route_path' => '/registrations/create',
                        'menu_level' => 2,
                        'required_permission' => ['Receptionist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'appointment_booking',
                        'menu_label' => 'Appointments',
                        'menu_icon' => 'ri-calendar-event-line',
                        'route_path' => '/appointments',
                        'menu_level' => 2,
                        'required_permission' => ['Receptionist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'appointment_calendar',
                        'menu_label' => 'Calendar',
                        'menu_icon' => 'ri-calendar-todo-line',
                        'route_path' => '/appointments/calendar',
                        'menu_level' => 2,
                        'required_permission' => ['Receptionist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'token_management',
                        'menu_label' => 'Queue',
                        'menu_icon' => 'ri-list-unordered',
                        'route_path' => '/queue',
                        'menu_level' => 2,
                        'required_permission' => ['Receptionist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                ]
            ],

            // OPD Module - Accessible by Doctor, Nurse and Admin roles
            [
                'menu_key' => 'opd',
                'menu_label' => 'OPD',
                'menu_icon' => 'ri-stethoscope-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Doctor', 'Nurse', 'admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'opd_today',
                        'menu_label' => 'Today\'s OPD List',
                        'menu_icon' => 'ri-file-list-3-line',
                        'route_path' => '/visits',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'my_patients',
                        'menu_label' => 'My Patients',
                        'menu_icon' => 'ri-user-heart-line',
                        'route_path' => '/opd/my-patients',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'super_admin', 'admin'],
                        'badge_source' => 'my_patients',
                        'children' => []
                    ],
                    [
                        'menu_key' => 'clinical_workspace',
                        'menu_label' => 'Siddha Clinical Console',
                        'menu_icon' => 'ri-ancient-gate-line',
                        'route_path' => '/opd/workspace',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'super_admin', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'vitals_entry',
                        'menu_label' => 'Vitals Entry',
                        'menu_icon' => 'ri-heart-pulse-line',
                        'route_path' => '/visits/vitals',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'clinical_notes',
                        'menu_label' => 'Clinical Notes',
                        'menu_icon' => 'ri-file-text-line',
                        'route_path' => '/visits/clinical-notes',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'prescriptions',
                        'menu_label' => 'Prescriptions',
                        'menu_icon' => 'ri-file-list-line',
                        'route_path' => '/prescriptions',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'treatment_plans',
                        'menu_label' => 'Treatment Plans',
                        'menu_icon' => 'ri-health-book-line',
                        'route_path' => '/opd/treatment-plans',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // IPD Module (Provision) - Accessible by Nurse, Doctor and Admin roles
            [
                'menu_key' => 'ipd',
                'menu_label' => 'IPD',
                'menu_icon' => 'ri-hotel-bed-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Nurse', 'Doctor', 'admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'admissions',
                        'menu_label' => 'Admissions',
                        'menu_icon' => 'ri-login-box-line',
                        'route_path' => '/admissions',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'wards',
                        'menu_label' => 'Wards',
                        'menu_icon' => 'ri-building-line',
                        'route_path' => '/ip/wards',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'bed_management',
                        'menu_label' => 'Bed Management',
                        'menu_icon' => 'ri-hotel-bed-line',
                        'route_path' => '/ip/beds',
                        'menu_level' => 2,
                        'required_permission' => ['Nurse', 'admin'],
                        'badge_source' => 'available_beds',
                        'children' => []
                    ],
                    [
                        'menu_key' => 'bed_allocations',
                        'menu_label' => 'Bed Allocations',
                        'menu_icon' => 'ri-layout-grid-line',
                        'route_path' => '/ip/allocations',
                        'menu_level' => 2,
                        'required_permission' => ['Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'nursing_notes',
                        'menu_label' => 'Nursing Notes',
                        'menu_icon' => 'ri-nurse-line',
                        'route_path' => '/ip/nursing-notes',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'doctor_rounds',
                        'menu_label' => 'Doctor Rounds',
                        'menu_icon' => 'ri-walk-line',
                        'route_path' => '/ip/rounds',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'discharge_summary',
                        'menu_label' => 'Discharge Summary',
                        'menu_icon' => 'ri-logout-box-line',
                        'route_path' => '/ip/discharge-summary',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'ip_billing',
                        'menu_label' => 'IP Billing',
                        'menu_icon' => 'ri-bill-line',
                        'route_path' => '/ip/billing',
                        'menu_level' => 2,
                        'required_permission' => ['Nurse', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],



            // Billing & Finance Module
            [
                'menu_key' => 'billing',
                'menu_label' => 'Billing & Finance',
                'menu_icon' => 'ri-money-dollar-circle-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Biller', 'biller', 'Billing', 'billing', 'Branch Admin', 'branch_admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'centralized_billing',
                        'menu_label' => 'Centralized Billing',
                        'menu_icon' => 'ri-file-list-3-line',
                        'route_path' => '/invoices/create',
                        'menu_level' => 2,
                        'required_permission' => 'view_billing',
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'invoices',
                        'menu_label' => 'Invoices',
                        'menu_icon' => 'ri-file-text-line',
                        'route_path' => '/invoices',
                        'menu_level' => 2,
                        'required_permission' => 'view_billing',
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'payments',
                        'menu_label' => 'Payments',
                        'menu_icon' => 'ri-secure-payment-line',
                        'route_path' => '/payments',
                        'menu_level' => 2,
                        'required_permission' => 'view_billing',
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'refunds_adjustments',
                        'menu_label' => 'Refunds & Adjustments',
                        'menu_icon' => 'ri-refund-2-line',
                        'route_path' => '/billing/refunds',
                        'menu_level' => 2,
                        'required_permission' => 'view_billing',
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'outstanding_bills',
                        'menu_label' => 'Outstanding Bills',
                        'menu_icon' => 'ri-alarm-warning-line',
                        'route_path' => '/billing/outstanding',
                        'menu_level' => 2,
                        'required_permission' => 'view_billing',
                        'badge_source' => 'outstanding_bills',
                        'children' => []
                    ],
                    [
                        'menu_key' => 'dcr',
                        'menu_label' => 'DCR (Daily Collection Report)',
                        'menu_icon' => 'ri-file-chart-line',
                        'route_path' => '/dcr',
                        'menu_level' => 2,
                        'required_permission' => ['Biller', 'biller', 'Billing', 'billing', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

          

            // Inventory Module (Provision) - Accessible by Pharmacist and Admin roles
            [
                'menu_key' => 'inventory',
                'menu_label' => 'Inventory',
                'menu_icon' => 'ri-archive-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Pharmacist', 'admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'prescription_dispensing',
                        'menu_label' => 'Prescription Dispensing',
                        'menu_icon' => 'ri-medicine-bottle-line',
                        'route_path' => '/pharmacy/dispensing',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'expiry_alerts',
                        'menu_label' => 'Expiry Alerts',
                        'menu_icon' => 'ri-error-warning-line',
                        'route_path' => '/pharmacy/expiry-alerts',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => 'expiry_alerts',
                        'children' => []
                    ],
                    [
                        'menu_key' => 'purchase_quotations',
                        'menu_label' => 'Purchase Quotations',
                        'menu_icon' => 'ri-file-list-3-line',
                        'route_path' => '/purchase-quotations',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'purchase_orders',
                        'menu_label' => 'Purchase Orders',
                        'menu_icon' => 'ri-shopping-bag-line',
                        'route_path' => '/purchase-orders',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'grn',
                        'menu_label' => 'GRN (Goods Receipt Note)',
                        'menu_icon' => 'ri-inbox-line',
                        'route_path' => '/grn',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'inventory_stock',
                        'menu_label' => 'Inventory & Stock',
                        'menu_icon' => 'ri-stack-line',
                        'route_path' => '/inventory/stock',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'warehouse_locations',
                        'menu_label' => 'Warehouse/Store Locations',
                        'menu_icon' => 'ri-building-4-line',
                        'route_path' => '/inventory/warehouses',
                        'menu_level' => 2,
                        'required_permission' => ['Pharmacist', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Therapy Module
            [
                'menu_key' => 'therapy',
                'menu_label' => 'Therapy',
                'menu_icon' => 'ri-hand-heart-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'therapy_sessions',
                        'menu_label' => 'Therapy Sessions',
                        'menu_icon' => 'ri-calendar-check-line',
                        'route_path' => '/therapy/sessions',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'therapy_protocols',
                        'menu_label' => 'Therapy Protocols',
                        'menu_icon' => 'ri-book-2-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'kizhi',
                                'menu_label' => 'Kizhi',
                                'menu_icon' => 'ri-leaf-line',
                                'route_path' => '/therapy/protocols?type=kizhi',
                                'menu_level' => 3,
                                'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'varmam',
                                'menu_label' => 'Varmam',
                                'menu_icon' => 'ri-hand-coin-line',
                                'route_path' => '/therapy/protocols?type=varmam',
                                'menu_level' => 3,
                                'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'abhyanga',
                                'menu_label' => 'Abhyanga',
                                'menu_icon' => 'ri-drop-line',
                                'route_path' => '/therapy/protocols?type=abhyanga',
                                'menu_level' => 3,
                                'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'other_protocols',
                                'menu_label' => 'Other Protocols',
                                'menu_icon' => 'ri-more-line',
                                'route_path' => '/therapy/protocols',
                                'menu_level' => 3,
                                'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'menu_key' => 'therapy_rules',
                        'menu_label' => 'Therapy Rules',
                        'menu_icon' => 'ri-file-shield-line',
                        'route_path' => '/therapy/rules',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'session_booking',
                        'menu_label' => 'Session Booking',
                        'menu_icon' => 'ri-calendar-event-line',
                        'route_path' => '/therapy/booking',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'consumables_usage',
                        'menu_label' => 'Consumables Usage',
                        'menu_icon' => 'ri-flask-line',
                        'route_path' => '/therapy/consumables',
                        'menu_level' => 2,
                        'required_permission' => ['Doctor', 'doctor', 'Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'therapy_billing',
                        'menu_label' => 'Therapy Billing',
                        'menu_icon' => 'ri-money-dollar-box-line',
                        'route_path' => '/therapy/billing',
                        'menu_level' => 2,
                        'required_permission' => ['Nurse', 'nurse', 'Therapist', 'therapist', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Masters Module - Centralized Master Data Management
            [
                'menu_key' => 'masters',
                'menu_label' => 'Masters',
                'menu_icon' => 'ri-database-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Branch Admin', 'branch_admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'product_masters',
                        'menu_label' => 'Product Masters',
                        'menu_icon' => 'ri-shopping-bag-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => 'view_masters',
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'products_list',
                                'menu_label' => 'Products/Medicine',
                                'menu_icon' => 'ri-medicine-bottle-line',
                                'route_path' => '/products',
                                'menu_level' => 3,
                                'required_permission' => 'view_masters',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'product_categories',
                                'menu_label' => 'Categories',
                                'menu_icon' => 'ri-folder-line',
                                'route_path' => '/inventory/categories',
                                'menu_level' => 3,
                                'required_permission' => 'view_masters',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'product_units',
                                'menu_label' => 'Units',
                                'menu_icon' => 'ri-ruler-line',
                                'route_path' => '/inventory/units',
                                'menu_level' => 3,
                                'required_permission' => 'view_masters',
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'menu_key' => 'supplier_master',
                        'menu_label' => 'Suppliers',
                        'menu_icon' => 'ri-truck-line',
                        'route_path' => '/suppliers',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'doctor_master',
                        'menu_label' => 'Doctors',
                        'menu_icon' => 'ri-user-heart-line',
                        'route_path' => '/doctors',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'staff_master',
                        'menu_label' => 'Staff',
                        'menu_icon' => 'ri-user-2-line',
                        'route_path' => '/staff',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'therapy_masters',
                        'menu_label' => 'Therapy Masters',
                        'menu_icon' => 'ri-hand-heart-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => 'view_masters',
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'therapy_protocols_master',
                                'menu_label' => 'Therapy Protocols',
                                'menu_icon' => 'ri-book-2-line',
                                'route_path' => '/therapy/protocols',
                                'menu_level' => 3,
                                'required_permission' => 'view_masters',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'therapy_rules_master',
                                'menu_label' => 'Therapy Rules',
                                'menu_icon' => 'ri-file-shield-line',
                                'route_path' => '/therapy/rules',
                                'menu_level' => 3,
                                'required_permission' => 'view_masters',
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'menu_key' => 'package_master',
                        'menu_label' => 'Packages',
                        'menu_icon' => 'ri-gift-2-line',
                        'route_path' => '/packages',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ] 
                ]
            ],
              // Pharmacy Module - Accessible by Pharmacist and Admin roles
            [
                'menu_key' => 'pharmacy',
                'menu_label' => 'Branch Pharmacy',
                'menu_icon' => 'ri-capsule-line',
                'route_path' => '/pharmacy',
                'menu_level' => 1,
                'required_permission' => ['Pharmacist', 'admin'],
                'badge_source' => null,
                'children' => []
            ],

            // Patient Management Module - Accessible by Receptionist, Front Office, Nurse, Doctor, Admin
            [
                'menu_key' => 'patient_management',
                'menu_label' => 'Patient Management',
                'menu_icon' => 'ri-user-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Front Office', 'front_office', 'Receptionist', 'receptionist', 'Doctor', 'doctor', 'Nurse', 'nurse', 'Branch Admin', 'branch_admin', 'Admin', 'admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'patients',
                        'menu_label' => 'Patients',
                        'menu_icon' => 'ri-user-3-line',
                        'route_path' => '/patients',
                        'menu_level' => 2,
                        'required_permission' => ['Front Office', 'front_office', 'Receptionist', 'receptionist', 'Doctor', 'doctor', 'Nurse', 'nurse', 'Branch Admin', 'branch_admin', 'Admin', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'family_management',
                        'menu_label' => 'Family Management',
                        'menu_icon' => 'ri-group-line',
                        'route_path' => '/patients/family',
                        'menu_level' => 2,
                        'required_permission' => ['Front Office', 'front_office', 'Receptionist', 'receptionist', 'Doctor', 'doctor', 'Nurse', 'nurse', 'Branch Admin', 'branch_admin', 'Admin', 'admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Reports & Analytics Module
            [
                'menu_key' => 'reports',
                'menu_label' => 'Reports & Analytics',
                'menu_icon' => 'ri-bar-chart-box-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'daily_revenue_report',
                        'menu_label' => 'Daily Revenue Report',
                        'menu_icon' => 'ri-line-chart-line',
                        'route_path' => '/reports/daily-revenue',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'patient_statistics',
                        'menu_label' => 'Patient Statistics',
                        'menu_icon' => 'ri-pie-chart-line',
                        'route_path' => '/reports/patient-counts',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'doctor_wise_revenue',
                        'menu_label' => 'Doctor-wise Revenue',
                        'menu_icon' => 'ri-user-star-line',
                        'route_path' => '/reports/doctor-revenue',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'department_wise_revenue',
                        'menu_label' => 'Department-wise Revenue',
                        'menu_icon' => 'ri-building-2-line',
                        'route_path' => '/reports/department-revenue',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'pharmacy_sales',
                        'menu_label' => 'Pharmacy Sales',
                        'menu_icon' => 'ri-shopping-basket-line',
                        'route_path' => '/reports/pharmacy-sales',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'inventory_reports',
                        'menu_label' => 'Inventory Reports',
                        'menu_icon' => 'ri-archive-drawer-line',
                        'route_path' => '/reports/inventory',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'outstanding_reports',
                        'menu_label' => 'Outstanding Reports',
                        'menu_icon' => 'ri-file-warning-line',
                        'route_path' => '/reports/outstanding',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'therapy_reports',
                        'menu_label' => 'Therapy Reports',
                        'menu_icon' => 'ri-heart-3-line',
                        'route_path' => '/reports/therapy',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin', 'Biller', 'biller', 'Billing', 'billing'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Communication Module
            [
                'menu_key' => 'communication',
                'menu_label' => 'Communication',
                'menu_icon' => 'ri-message-3-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => ['Front Office', 'front_office', 'Branch Admin', 'branch_admin'],
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'whatsapp',
                        'menu_label' => 'WhatsApp',
                        'menu_icon' => 'ri-whatsapp-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => 'view_communication',
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'whatsapp_templates',
                                'menu_label' => 'Templates',
                                'menu_icon' => 'ri-file-copy-line',
                                'route_path' => '/whatsapp/templates',
                                'menu_level' => 3,
                                'required_permission' => 'view_communication',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'whatsapp_scheduled',
                                'menu_label' => 'Scheduled Tasks',
                                'menu_icon' => 'ri-timer-line',
                                'route_path' => '/whatsapp/scheduled',
                                'menu_level' => 3,
                                'required_permission' => 'view_communication',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'whatsapp_logs',
                                'menu_label' => 'Logs',
                                'menu_icon' => 'ri-file-list-line',
                                'route_path' => '/whatsapp/logs',
                                'menu_level' => 3,
                                'required_permission' => 'view_communication',
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'menu_key' => 'call_logs',
                        'menu_label' => 'Call Logs',
                        'menu_icon' => 'ri-phone-line',
                        'route_path' => '/call-logs',
                        'menu_level' => 2,
                        'required_permission' => ['Front Office', 'front_office', 'Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Staff & HR Module
            [
                'menu_key' => 'staff_hr',
                'menu_label' => 'Staff & HR',
                'menu_icon' => 'ri-team-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_hr',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'attendance',
                        'menu_label' => 'Attendance',
                        'menu_icon' => 'ri-calendar-check-line',
                        'route_path' => '/attendance',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'payroll',
                        'menu_label' => 'Payroll',
                        'menu_icon' => 'ri-money-dollar-circle-line',
                        'route_path' => '/payroll',
                        'menu_level' => 2,
                        'required_permission' => ['Branch Admin', 'branch_admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Administration Module
            [
                'menu_key' => 'administration',
                'menu_label' => 'Administration',
                'menu_icon' => 'ri-settings-3-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => 'view_administration',
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'branch_management',
                        'menu_label' => 'Branch Management',
                        'menu_icon' => 'ri-building-line',
                        'route_path' => null,
                        'menu_level' => 2,
                        'required_permission' => 'manage_branches',
                        'badge_source' => null,
                        'children' => [
                            [
                                'menu_key' => 'branch_list',
                                'menu_label' => 'Branch List',
                                'menu_icon' => 'ri-list-check',
                                'route_path' => '/branches',
                                'menu_level' => 3,
                                'required_permission' => 'manage_branches',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'add_branch',
                                'menu_label' => 'Add Branch',
                                'menu_icon' => 'ri-add-circle-line',
                                'route_path' => '/admin/branches/add',
                                'menu_level' => 3,
                                'required_permission' => 'manage_branches',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'branch_configuration',
                                'menu_label' => 'Branch Configuration',
                                'menu_icon' => 'ri-settings-4-line',
                                'route_path' => '/admin/branches/config',
                                'menu_level' => 3,
                                'required_permission' => 'manage_branches',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'branch_staff_assignment',
                                'menu_label' => 'Branch Staff Assignment',
                                'menu_icon' => 'ri-user-settings-line',
                                'route_path' => '/admin/branches/staff-assignment',
                                'menu_level' => 3,
                                'required_permission' => 'manage_branches',
                                'badge_source' => null,
                                'children' => []
                            ],
                            [
                                'menu_key' => 'branch_transfer',
                                'menu_label' => 'Branch Transfer',
                                'menu_icon' => 'ri-exchange-box-line',
                                'route_path' => '/admin/branches/transfer',
                                'menu_level' => 3,
                                'required_permission' => 'manage_branches',
                                'badge_source' => null,
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'menu_key' => 'users_roles',
                        'menu_label' => 'Users & Roles',
                        'menu_icon' => 'ri-shield-user-line',
                        'route_path' => '/users',
                        'menu_level' => 2,
                        'required_permission' => ['Super Admin', 'super_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'admin_reports',
                        'menu_label' => 'Reports',
                        'menu_icon' => 'ri-file-chart-line',
                        'route_path' => '/admin/reports',
                        'menu_level' => 2,
                        'required_permission' => ['Super Admin', 'super_admin'],
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'audit_logs',
                        'menu_label' => 'Audit Logs',
                        'menu_icon' => 'ri-history-line',
                        'route_path' => '/admin/audit-logs',
                        'menu_level' => 2,
                        'required_permission' => ['Super Admin', 'super_admin'],
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ],

            // Settings Module - Always visible to all users
            [
                'menu_key' => 'settings',
                'menu_label' => 'Settings',
                'menu_icon' => 'ri-settings-2-line',
                'route_path' => null,
                'menu_level' => 1,
                'required_permission' => null,
                'badge_source' => null,
                'children' => [
                    [
                        'menu_key' => 'profile',
                        'menu_label' => 'Profile',
                        'menu_icon' => 'ri-user-settings-line',
                        'route_path' => '/profile',
                        'menu_level' => 2,
                        'required_permission' => null,
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'user_settings',
                        'menu_label' => 'Settings',
                        'menu_icon' => 'ri-settings-5-line',
                        'route_path' => '/profile/settings',
                        'menu_level' => 2,
                        'required_permission' => null,
                        'badge_source' => null,
                        'children' => []
                    ],
                    [
                        'menu_key' => 'logout',
                        'menu_label' => 'Logout',
                        'menu_icon' => 'ri-logout-box-line',
                        'route_path' => '/logout',
                        'menu_level' => 2,
                        'required_permission' => null,
                        'badge_source' => null,
                        'children' => []
                    ]
                ]
            ]
        ];
    }
}
