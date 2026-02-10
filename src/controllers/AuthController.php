<?php
/**
 * Web Auth Controller - Handles authentication for web routes
 */

declare(strict_types=1);

namespace App\Controllers;

use System\Session;
use System\Security;
use System\Database;
use System\Logger;
use System\Router;
use App\Services\ThemeService;

class AuthController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate input
        if (empty($username) || empty($password)) {
            Session::flash('error', 'Please enter username and password');
            $this->storeOldInput();
            Router::redirect($this->baseUrl('/login'));
            return;
        }

        // Security check
        if (Security::detectSqlInjection($username) || Security::detectXss($username)) {
            Logger::security('Injection attempt in login', ['username' => $username]);
            Session::flash('error', 'Invalid credentials');
            Router::redirect($this->baseUrl('/login'));
            return;
        }

        // Find user by username or email
        $user = $this->db->fetch(
            "SELECT user_id, branch_id, username, password_hash, full_name, email, mobile, is_active 
             FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$username, $username]
        );

        // Verify user exists and password matches
        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            Logger::warning('Failed login attempt', ['username' => $username]);
            Session::flash('error', 'Invalid username or password');
            $this->storeOldInput();
            Router::redirect($this->baseUrl('/login'));
            return;
        }

        // Check if user is active
        if (!$user['is_active']) {
            Session::flash('error', 'Your account has been deactivated. Please contact administrator.');
            Router::redirect($this->baseUrl('/login'));
            return;
        }

        // Update last login
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'user_id = ?', [$user['user_id']]);

        // Get user roles
        $roles = $this->db->fetchAll(
            "SELECT r.role_id, r.name, ur.branch_id 
             FROM user_roles ur 
             JOIN roles r ON ur.role_id = r.role_id 
             WHERE ur.user_id = ?",
            [$user['user_id']]
        );

        // Prepare user data for session
        $userData = [
            'id' => $user['user_id'],
            'branch_id' => $user['branch_id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'roles' => array_column($roles, 'name'),
            'role_ids' => array_column($roles, 'role_id'),
            'role' => !empty($roles) ? $roles[0]['name'] : null, // Primary role for middleware
        ];

        // Login user
        Session::login($user['user_id'], $userData);

        // Load theme settings from DB to session
        ThemeService::loadToSession();

        // Set flag to clear localStorage on next page load (so DB theme takes precedence)
        $_SESSION['clear_theme_localstorage'] = true;

        Logger::info('User logged in', ['user_id' => $user['user_id'], 'username' => $user['username']]);

        // Role-Based Redirection Logic
        // Get user roles for redirection (using the same data we already have)
        $roleNames = array_column($roles, 'name');

        // Determine the redirect URL based on role
        $redirectUrl = $this->getRoleBasedRedirectUrl($roleNames);

        // Check if there was an intended URL (but only for admin roles)
        $intendedUrl = Session::get('intended_url');
        Session::delete('intended_url');

        // Only use intended URL for admin roles, others go to their role-specific page
        $isAdmin = in_array('admin', $roleNames) || in_array('super_admin', $roleNames);
        if ($intendedUrl && $isAdmin && strpos($intendedUrl, '/api/') === false) {
            Router::redirect($intendedUrl);
        } else {
            Router::redirect($redirectUrl);
        }
    }

    /**
     * Get redirect URL based on user roles
     */
    private function getRoleBasedRedirectUrl(array $roleNames): string
    {
        // Check each role and return the appropriate URL
        // Role names from database: Doctor, Nurse, Pharmacist, Receptionist, admin, super_admin

        if (in_array('Doctor', $roleNames)) {
            // Doctors go to their Workspace/OPD
            return $this->baseUrl('/opd/workspace');
        }

        if (in_array('Nurse', $roleNames)) {
            // Nurses go directly to appointments/OPD list
            return $this->baseUrl('/appointments');
        }

        if (in_array('Receptionist', $roleNames)) {
            // Receptionists go directly to appointments
            return $this->baseUrl('/appointments');
        }

        if (in_array('Pharmacist', $roleNames)) {
            // Pharmacists go to dispensing
            return $this->baseUrl('/pharmacy/dispensing');
        }

        // Admin and Super Admin go to dashboard
        // Default fallback is also dashboard
        return $this->baseUrl('/dashboard');
    }

    /**
     * Handle registration
     */
    public function register(): void
    {
        $fullName = Security::sanitizeInput($_POST['full_name'] ?? '');
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validate
        $errors = [];

        if (strlen($fullName) < 2) {
            $errors['full_name'] = 'Full name must be at least 2 characters';
        }

        if (Security::detectXss($fullName)) {
            $errors['full_name'] = 'Invalid characters in name';
        }

        if (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username can only contain letters, numbers and underscores';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $this->storeOldInput();
            Router::redirect($this->baseUrl('/register'));
            return;
        }

        // Check if username exists
        $existing = $this->db->fetch("SELECT user_id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            Session::flash('errors', ['username' => 'Username already taken']);
            $this->storeOldInput();
            Router::redirect($this->baseUrl('/register'));
            return;
        }

        // Check if email exists
        $existing = $this->db->fetch("SELECT user_id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Session::flash('errors', ['email' => 'Email already registered']);
            $this->storeOldInput();
            Router::redirect($this->baseUrl('/register'));
            return;
        }

        // Create user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'password_hash' => Security::hashPassword($password),
            'full_name' => $fullName,
            'email' => $email,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Logger::info('New user registered', ['user_id' => $userId, 'username' => $username]);

        Session::flash('success', 'Registration successful! Please log in.');
        Router::redirect($this->baseUrl('/login'));
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $userId = Session::getUserId();
        Session::logout();
        Logger::info('User logged out', ['user_id' => $userId]);
        Router::redirect($this->baseUrl('/login'));
    }

    /**
     * Handle OTP callback and convert JWT to Session
     */
    public function otpCallback(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            Router::redirect($this->baseUrl('/login'));
            return;
        }

        try {
            // Validate the JWT token
            $decoded = \System\JWT::validateAccessToken($token);
            if (!$decoded) {
                Session::flash('error', 'Invalid or expired verification session');
                Router::redirect($this->baseUrl('/login'));
                return;
            }

            $payload = \System\JWT::getPayload($token);
            $userId = (int) ($payload['user_id'] ?? 0);
            $userType = $payload['user_type'] ?? 'user';

            if (!$userId) {
                Session::flash('error', 'Invalid token data');
                Router::redirect($this->baseUrl('/login'));
                return;
            }

            // PATIENT LOGIN FLOW
            if ($userType === 'patient') {
                $patient = $this->db->fetch(
                    "SELECT patient_id, branch_id, mrn, title, first_name, last_name, primary_email, primary_mobile, is_active 
                     FROM patients WHERE patient_id = ?",
                    [$userId]
                );

                if (!$patient || !$patient['is_active']) {
                    Session::flash('error', 'Patient account not found or inactive');
                    Router::redirect($this->baseUrl('/login'));
                    return;
                }

                $fullName = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? ''));

                $userData = [
                    'id' => $patient['patient_id'],
                    'type' => 'patient', // Mark as patient
                    'branch_id' => $patient['branch_id'],
                    'username' => $patient['mrn'],
                    'full_name' => $fullName,
                    'email' => $patient['primary_email'],
                    'mobile' => $patient['primary_mobile'],
                    'roles' => ['Patient'],
                    'role' => 'Patient'
                ];

                // Set web session
                Session::login($userId, $userData);
                ThemeService::loadToSession();

                Logger::info('Patient logged in via OTP Bridge', ['patient_id' => $userId]);

                // Redirect to Patient Dashboard
                Router::redirect($this->baseUrl('/patients/dashboard'));
                return;
            }

            // USER (DOCTOR/STAFF) LOGIN FLOW
            // Get full user data for web session
            $user = $this->db->fetch(
                "SELECT user_id, branch_id, username, full_name, email, mobile, is_active FROM users WHERE user_id = ?",
                [$userId]
            );

            if (!$user || !$user['is_active']) {
                Session::flash('error', 'Account is no longer active');
                Router::redirect($this->baseUrl('/login'));
                return;
            }

            // Get user roles
            $roles = $this->db->fetchAll(
                "SELECT r.role_id, r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = ?",
                [$userId]
            );

            $userData = [
                'id' => $user['user_id'],
                'type' => 'user',
                'branch_id' => $user['branch_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'mobile' => $user['mobile'],
                'roles' => array_column($roles, 'name'),
                'role_ids' => array_column($roles, 'role_id'),
                'role' => !empty($roles) ? $roles[0]['name'] : null, // Primary role for middleware
            ];

            // Set web session
            Session::login($userId, $userData);
            ThemeService::loadToSession();

            Logger::info('User logged in via OTP Bridge', ['user_id' => $userId]);

            // Role-Based Redirection Logic
            $roleNames = array_column($roles, 'name');
            $roleNamesLower = array_map('strtolower', $roleNames);

            if (in_array('doctor', $roleNamesLower)) {
                // Doctors go to their Workspace/OPD
                Router::redirect($this->baseUrl('/opd/workspace'));
            } elseif (in_array('patient', $roleNamesLower)) {
                // Keep existing just in case a user has 'Patient' role
                Router::redirect($this->baseUrl('/patients/dashboard'));
            } elseif (in_array('front_office', $roleNamesLower) || in_array('receptionist', $roleNamesLower)) {
                // Reception goes to Appointments
                Router::redirect($this->baseUrl('/appointments'));
            } elseif (in_array('pharmacy', $roleNamesLower) || in_array('pharmacist', $roleNamesLower)) {
                Router::redirect($this->baseUrl('/pharmacy/dispensing'));
            } elseif (in_array('admin', $roleNamesLower) || in_array('super_admin', $roleNamesLower)) {
                // Admin goes to main dashboard
                Router::redirect($this->baseUrl('/dashboard'));
            } else {
                // Default fallback
                Router::redirect($this->baseUrl('/dashboard'));
            }

        } catch (\Exception $e) {
            Logger::error('OTP Bridge Error: ' . $e->getMessage());
            Session::flash('error', 'Authentication failed');
            Router::redirect($this->baseUrl('/login'));
        }
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword(): void
    {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address');
            Router::redirect($this->baseUrl('/forgot-password'));
            return;
        }

        // Check user exists (but don't reveal this to prevent enumeration)
        $user = $this->db->fetch("SELECT user_id, email FROM users WHERE email = ?", [$email]);

        if ($user) {
            // TODO: Generate reset token and send email
            // $token = bin2hex(random_bytes(32));
            // Store token in password_resets table
            // Send email with reset link
            Logger::info('Password reset requested', ['email' => $email]);
        }

        // Always show success to prevent email enumeration
        Session::flash('success', 'If the email exists, a reset link has been sent');
        Router::redirect($this->baseUrl('/forgot-password'));
    }

    /**
     * Get base URL
     */
    private function baseUrl(string $path = ''): string
    {
        return ($_ENV['BASE_PATH'] ?? '') . $path;
    }

    /**
     * Store old input for form repopulation
     */
    private function storeOldInput(): void
    {
        $input = $_POST;
        unset($input['password'], $input['password_confirmation'], $input['_csrf_token']);
        Session::flash('old_input', $input);
    }
}
