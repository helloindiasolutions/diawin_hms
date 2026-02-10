<?php
/**
 * API Auth Controller with JWT
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Response;
use System\Security;
use System\Logger;
use System\JWT;
use System\Database;
use App\Api\Services\OTPService;

class AuthController
{
    private Database $db;
    private OTPService $otpService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->otpService = new OTPService();
    }

    /**
     * Login and return JWT tokens
     */
    public function login(): void
    {
        $input = jsonInput();

        // Validate
        if (empty($input['username']) || empty($input['password'])) {
            Response::validationError([
                'username' => empty($input['username']) ? 'Username or email is required' : null,
                'password' => empty($input['password']) ? 'Password is required' : null
            ]);
            return;
        }

        $username = Security::sanitizeInput($input['username']);
        $password = $input['password'];

        // Check for SQL injection
        if (Security::detectSqlInjection($username) || Security::detectXss($username)) {
            Logger::security('Injection attempt in API login', ['username' => $username]);
            Response::error('Invalid credentials', 401);
            return;
        }

        // Fetch user from database
        $user = $this->db->fetch(
            "SELECT user_id, branch_id, username, password_hash, full_name, email, mobile, is_active 
             FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$username, $username]
        );

        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            Logger::warning('Failed API login attempt', ['username' => $username]);
            Response::error('Invalid credentials', 401);
            return;
        }

        // Check if user is active
        if (!$user['is_active']) {
            Response::error('Account deactivated', 403);
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

        // Generate JWT tokens
        $tokens = JWT::generateTokenPair([
            'user_id' => $user['user_id'],
            'branch_id' => $user['branch_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'roles' => array_column($roles, 'name')
        ]);

        Logger::info('User logged in via API', ['user_id' => $user['user_id']]);

        Response::success([
            ...$tokens,
            'user' => [
                'id' => $user['user_id'],
                'branch_id' => $user['branch_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'mobile' => $user['mobile'],
                'roles' => array_column($roles, 'name')
            ]
        ], 'Login successful');
    }

    /**
     * Register new user
     */
    public function register(): void
    {
        $input = jsonInput();

        // Validate
        $errors = [];
        if (empty($input['full_name']) || strlen($input['full_name']) < 2) {
            $errors['full_name'] = 'Full name must be at least 2 characters';
        }
        if (empty($input['username']) || strlen($input['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }
        if (!empty($input['username']) && !preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers and underscores';
        }
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        if (empty($input['password']) || strlen($input['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        if (($input['password'] ?? '') !== ($input['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $fullName = Security::sanitizeInput($input['full_name']);
        $username = Security::sanitizeInput($input['username']);
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);

        // Check XSS
        if (Security::detectXss($fullName)) {
            Logger::security('XSS attempt in API registration', ['full_name' => $fullName]);
            Response::error('Invalid input', 400);
            return;
        }

        // Check if username exists
        $existing = $this->db->fetch("SELECT user_id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            Response::validationError(['username' => 'Username already taken']);
            return;
        }

        // Check if email exists
        $existing = $this->db->fetch("SELECT user_id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Response::validationError(['email' => 'Email already registered']);
            return;
        }

        // Create user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'password_hash' => Security::hashPassword($input['password']),
            'full_name' => $fullName,
            'email' => $email,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Logger::info('New user registered via API', ['user_id' => $userId, 'username' => $username]);

        Response::created([
            'user' => [
                'id' => $userId,
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email
            ]
        ], 'Registration successful');
    }

    /**
     * Logout (invalidate token)
     */
    public function logout(): void
    {
        $user = $GLOBALS['jwt_user'] ?? null;
        Logger::info('User logged out via API', ['user_id' => $user['user_id'] ?? null]);
        Response::success(null, 'Logged out successfully');
    }

    /**
     * Get current user info
     */
    public function me(): void
    {
        $jwtUser = $GLOBALS['jwt_user'] ?? null;

        if (!$jwtUser) {
            Response::unauthorized();
            return;
        }

        // Get fresh user data from database
        $user = $this->db->fetch(
            "SELECT user_id, branch_id, username, full_name, email, mobile, is_active, last_login, created_at 
             FROM users WHERE user_id = ?",
            [$jwtUser['user_id']]
        );

        if (!$user) {
            Response::unauthorized();
            return;
        }

        // Get user roles
        $roles = $this->db->fetchAll(
            "SELECT r.role_id, r.name 
             FROM user_roles ur 
             JOIN roles r ON ur.role_id = r.role_id 
             WHERE ur.user_id = ?",
            [$user['user_id']]
        );

        Response::success([
            'user' => [
                'id' => $user['user_id'],
                'branch_id' => $user['branch_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'mobile' => $user['mobile'],
                'is_active' => (bool) $user['is_active'],
                'last_login' => $user['last_login'],
                'created_at' => $user['created_at'],
                'roles' => array_column($roles, 'name')
            ]
        ]);
    }

    /**
     * Refresh access token
     */
    public function refreshToken(): void
    {
        $input = jsonInput();
        $refreshToken = $input['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            Response::validationError(['refresh_token' => 'Refresh token is required']);
            return;
        }

        // Validate refresh token
        $decoded = JWT::validateRefreshToken($refreshToken);

        if (!$decoded) {
            Response::error('Invalid or expired refresh token', 401);
            return;
        }

        // Verify user still exists and is active
        $payload = (array) $decoded->data;
        $user = $this->db->fetch(
            "SELECT user_id, is_active FROM users WHERE user_id = ?",
            [$payload['user_id']]
        );

        if (!$user || !$user['is_active']) {
            Response::error('User not found or deactivated', 401);
            return;
        }

        // Generate new tokens
        $tokens = JWT::generateTokenPair($payload);

        Response::success($tokens, 'Token refreshed');
    }

    /**
     * Forgot password
     */
    public function forgotPassword(): void
    {
        $input = jsonInput();
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Valid email is required']);
            return;
        }

        // Check user exists (but don't reveal this)
        $user = $this->db->fetch("SELECT user_id FROM users WHERE email = ?", [$email]);

        if ($user) {
            // TODO: Generate reset token and send email
            Logger::info('Password reset requested via API', ['email' => $email]);
        }

        // Always return success to prevent email enumeration
        Response::success(null, 'If the email exists, a reset link has been sent');
    }

    /**
     * Reset password
     */
    public function resetPassword(): void
    {
        $input = jsonInput();

        $token = $input['token'] ?? '';
        $password = $input['password'] ?? '';
        $confirmation = $input['password_confirmation'] ?? '';

        if (empty($token)) {
            Response::validationError(['token' => 'Reset token is required']);
            return;
        }

        if (strlen($password) < 8) {
            Response::validationError(['password' => 'Password must be at least 8 characters']);
            return;
        }

        if ($password !== $confirmation) {
            Response::validationError(['password_confirmation' => 'Passwords do not match']);
            return;
        }

        // TODO: Validate token from password_resets table and update password

        Response::success(null, 'Password has been reset');
    }

    /**
     * Generate OTP for mobile number
     * POST /api/auth/otp/generate
     */
    public function generateOTP(): void
    {
        $input = jsonInput();

        $mobileNumber = Security::sanitizeInput($input['mobile_number'] ?? '');
        $purpose = Security::sanitizeInput($input['purpose'] ?? 'login');

        // Validate mobile number
        if (empty($mobileNumber)) {
            Response::validationError(['mobile_number' => 'Mobile number is required']);
            return;
        }

        // Validate purpose
        if (!in_array($purpose, ['login', 'registration', 'password_reset'])) {
            Response::validationError(['purpose' => 'Invalid purpose']);
            return;
        }

        if ($purpose === 'login') {
            // 1. Try finding in USERS table
            $user = $this->db->fetch(
                "SELECT u.user_id, r.name as role_name 
                 FROM users u
                 JOIN user_roles ur ON u.user_id = ur.user_id
                 JOIN roles r ON ur.role_id = r.role_id
                 WHERE u.mobile = ? AND u.is_active = 1 LIMIT 1",
                [$mobileNumber]
            );

            if ($user) {
                // User found - check for admin restrictions
                $adminRoles = ['admin', 'super_admin', 'system_admin', 'administrator'];
                if (in_array(strtolower($user['role_name']), $adminRoles)) {
                    Response::error('Administrative accounts must login with Username & Password.', 403);
                    return;
                }
            } else {
                // 2. User not found, try finding in PATIENTS table
                $patient = $this->db->fetch(
                    "SELECT patient_id FROM patients WHERE primary_mobile = ? AND is_active = 1 LIMIT 1",
                    [$mobileNumber]
                );

                if (!$patient) {
                    Response::error('Mobile number not registered in our system.', 404);
                    return;
                }
            }
        }

        try {
            $result = $this->otpService->generateOTP($mobileNumber, $purpose);

            Response::success([
                'mobile_number' => $result['mobile_number'],
                'expires_at' => $result['expires_at'],
                'expires_in_seconds' => $result['expires_in_seconds']
            ], $result['message']);

        } catch (\Exception $e) {
            Logger::error('OTP generation failed in controller', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage()
            ]);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Verify OTP code
     * POST /api/auth/otp/verify
     */
    public function verifyOTP(): void
    {
        $input = jsonInput();

        $mobileNumber = Security::sanitizeInput($input['mobile_number'] ?? '');
        $otpCode = Security::sanitizeInput($input['otp_code'] ?? '');
        $purpose = Security::sanitizeInput($input['purpose'] ?? 'login');

        // Validate inputs
        $errors = [];
        if (empty($mobileNumber)) {
            $errors['mobile_number'] = 'Mobile number is required';
        }
        if (empty($otpCode)) {
            $errors['otp_code'] = 'OTP code is required';
        }
        if (!in_array($purpose, ['login', 'registration', 'password_reset'])) {
            $errors['purpose'] = 'Invalid purpose';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        try {
            $verified = $this->otpService->verifyOTP($mobileNumber, $otpCode, $purpose);

            if ($verified) {
                // For login purpose, find user and generate JWT
                if ($purpose === 'login') {
                    // 1. Try finding in USERS table
                    $user = $this->db->fetch(
                        "SELECT user_id, branch_id, username, full_name, email, mobile, is_active 
                         FROM users WHERE mobile = ? LIMIT 1",
                        [$mobileNumber]
                    );

                    if ($user) {
                        if (!$user['is_active']) {
                            Response::error('Account deactivated', 403);
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

                        // Generate USER tokens
                        $tokens = JWT::generateTokenPair([
                            'user_id' => $user['user_id'],
                            'user_type' => 'user', // Explicit type
                            'branch_id' => $user['branch_id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'roles' => array_column($roles, 'name')
                        ]);

                        Logger::info('User logged in via OTP', ['user_id' => $user['user_id']]);

                        Response::success([
                            ...$tokens,
                            'user' => [
                                'id' => $user['user_id'],
                                'type' => 'user',
                                'branch_id' => $user['branch_id'],
                                'username' => $user['username'],
                                'full_name' => $user['full_name'],
                                'email' => $user['email'],
                                'mobile' => $user['mobile'],
                                'roles' => array_column($roles, 'name')
                            ]
                        ], 'OTP verified and logged in successfully');
                        return;
                    }

                    // 2. Try finding in PATIENTS table
                    $patient = $this->db->fetch(
                        "SELECT patient_id, mrn, title, first_name, last_name, primary_email, primary_mobile, branch_id
                         FROM patients WHERE primary_mobile = ? AND is_active = 1 LIMIT 1",
                        [$mobileNumber]
                    );

                    if ($patient) {
                        // Generate PATIENT tokens
                        $fullName = trim(($patient['title'] ? $patient['title'] . ' ' : '') . $patient['first_name'] . ' ' . ($patient['last_name'] ?? ''));

                        $tokens = JWT::generateTokenPair([
                            'user_id' => $patient['patient_id'], // Uses patient_id as user_id in token but distinguishes via user_type
                            'user_type' => 'patient', // Explicit type
                            'branch_id' => $patient['branch_id'],
                            'username' => $patient['mrn'], // Use MRN as username
                            'email' => $patient['primary_email'],
                            'roles' => ['Patient'] // Virtual role
                        ]);

                        Logger::info('Patient logged in via OTP', ['patient_id' => $patient['patient_id']]);

                        Response::success([
                            ...$tokens,
                            'user' => [
                                'id' => $patient['patient_id'],
                                'type' => 'patient',
                                'branch_id' => $patient['branch_id'],
                                'username' => $patient['mrn'],
                                'full_name' => $fullName,
                                'email' => $patient['primary_email'],
                                'mobile' => $patient['primary_mobile'],
                                'roles' => ['Patient']
                            ]
                        ], 'OTP verified and logged in successfully');
                        return;
                    }

                    Response::error('User not found with this mobile number', 404);
                    return;

                } else {
                    // For other purposes, just confirm verification
                    Response::success([
                        'verified' => true,
                        'mobile_number' => $mobileNumber,
                        'purpose' => $purpose
                    ], 'OTP verified successfully');
                }
            }

        } catch (\Exception $e) {
            Logger::error('OTP verification failed in controller', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage()
            ]);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Get OTP statistics (admin only)
     * GET /api/auth/otp/stats
     */
    public function getOTPStats(): void
    {
        // TODO: Add admin permission check

        $mobileNumber = $_GET['mobile_number'] ?? null;

        try {
            $stats = $this->otpService->getOTPStats($mobileNumber);
            Response::success($stats);

        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
