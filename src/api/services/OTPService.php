<?php
/**
 * OTP Authentication Service
 * Handles OTP generation, verification, and cleanup
 */

namespace App\Api\Services;

use System\Database;
use System\Logger;

class OTPService
{
    private Database $db;
    private int $otpLength = 6;
    private int $otpExpiryMinutes = 5;
    private int $maxAttempts = 3;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->otpLength = (int) ($_ENV['OTP_LENGTH'] ?? 6);
        $this->otpExpiryMinutes = (int) ($_ENV['OTP_EXPIRY_MINUTES'] ?? 5);
        $this->maxAttempts = (int) ($_ENV['OTP_MAX_ATTEMPTS'] ?? 3);
    }

    /**
     * Generate OTP for mobile number
     * 
     * @param string $mobileNumber Mobile number with country code
     * @param string $purpose Purpose: login, registration, password_reset
     * @return array OTP details
     * @throws \Exception
     */
    public function generateOTP(string $mobileNumber, string $purpose = 'login'): array
    {
        try {
            // Clean up expired OTPs first
            $this->cleanupExpiredOTPs();

            // Check rate limiting - max 3 OTPs per 15 minutes
            $recentCount = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM otp_verifications 
                 WHERE mobile_number = ? 
                 AND purpose = ? 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
                [$mobileNumber, $purpose]
            );

            if ($recentCount >= 3) {
                throw new \Exception('Too many OTP requests. Please try again after 15 minutes.');
            }

            // Generate random OTP
            $otpCode = $this->generateRandomOTP();

            // Calculate expiry time
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->otpExpiryMinutes} minutes"));

            // Store in database
            $otpId = $this->db->insert('otp_verifications', [
                'mobile_number' => $mobileNumber,
                'otp_code' => $otpCode,
                'purpose' => $purpose,
                'expires_at' => $expiresAt,
                'attempts' => 0
            ]);

            // Send OTP via SMS (placeholder - integrate with SMS gateway)
            $this->sendOTPViaSMS($mobileNumber, $otpCode);

            Logger::info('OTP generated', [
                'otp_id' => $otpId,
                'mobile_number' => $mobileNumber,
                'purpose' => $purpose,
                'expires_at' => $expiresAt
            ]);

            return [
                'success' => true,
                'otp_id' => $otpId,
                'mobile_number' => $mobileNumber,
                'expires_at' => $expiresAt,
                'expires_in_seconds' => $this->otpExpiryMinutes * 60,
                'message' => 'OTP sent successfully'
            ];

        } catch (\Exception $e) {
            Logger::error('OTP generation failed', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify OTP code
     * 
     * @param string $mobileNumber Mobile number
     * @param string $otpCode OTP code to verify
     * @param string $purpose Purpose
     * @return bool True if valid
     * @throws \Exception
     */
    public function verifyOTP(string $mobileNumber, string $otpCode, string $purpose = 'login'): bool
    {
        try {
            // Get the latest unverified OTP for this mobile and purpose
            $currentTime = date('Y-m-d H:i:s');

            $otp = $this->db->fetch(
                "SELECT * FROM otp_verifications 
                 WHERE mobile_number = ? 
                 AND purpose = ? 
                 AND verified_at IS NULL 
                 AND expires_at > ?
                 ORDER BY created_at DESC 
                 LIMIT 1",
                [$mobileNumber, $purpose, $currentTime]
            );

            if (!$otp) {
                Logger::debug('OTP not found or expired', ['mobile' => $mobileNumber, 'time' => $currentTime]);
                throw new \Exception('Invalid or expired OTP');
            }

            // Check max attempts
            if ($otp['attempts'] >= $this->maxAttempts) {
                throw new \Exception('Maximum OTP verification attempts exceeded');
            }

            // Increment attempts
            $this->db->update(
                'otp_verifications',
                ['attempts' => $otp['attempts'] + 1],
                'otp_id = ?',
                [$otp['otp_id']]
            );

            // Verify OTP code
            if (trim((string) $otp['otp_code']) !== trim((string) $otpCode)) {
                Logger::warning('Invalid OTP attempt', [
                    'otp_id' => $otp['otp_id'],
                    'mobile_number' => $mobileNumber,
                    'attempts' => $otp['attempts'] + 1
                ]);
                throw new \Exception('Invalid OTP code');
            }

            // Mark as verified
            $this->db->update(
                'otp_verifications',
                ['verified_at' => date('Y-m-d H:i:s')],
                'otp_id = ?',
                [$otp['otp_id']]
            );

            Logger::info('OTP verified successfully', [
                'otp_id' => $otp['otp_id'],
                'mobile_number' => $mobileNumber,
                'purpose' => $purpose
            ]);

            return true;

        } catch (\Exception $e) {
            Logger::error('OTP verification failed', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Cleanup expired OTPs
     * 
     * @return int Number of deleted records
     */
    public function cleanupExpiredOTPs(): int
    {
        try {
            $currentTime = date('Y-m-d H:i:s');
            $deleted = $this->db->delete(
                'otp_verifications',
                "expires_at < '{$currentTime}' OR (verified_at IS NOT NULL AND verified_at < DATE_SUB('{$currentTime}', INTERVAL 1 DAY))"
            );

            if ($deleted > 0) {
                Logger::info('Expired OTPs cleaned up', ['count' => $deleted]);
            }

            return $deleted;

        } catch (\Exception $e) {
            Logger::error('OTP cleanup failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Generate random OTP code
     * 
     * @return string OTP code
     */
    private function generateRandomOTP(): string
    {
        $min = pow(10, $this->otpLength - 1);
        $max = pow(10, $this->otpLength) - 1;
        return str_pad((string) random_int($min, $max), $this->otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via SMS gateway
     * 
     * @param string $mobileNumber Mobile number
     * @param string $otpCode OTP code
     * @return bool Success status
     */
    /**
     * Send OTP via WhatsApp
     */
    private function sendOTPViaSMS(string $mobileNumber, string $otpCode): bool
    {
        $apiSecret = $_ENV['WHATS_API'] ?? '';
        $accountId = $_ENV['WHATS_UNIQUE_ID'] ?? '';

        if (empty($apiSecret) || empty($accountId)) {
            Logger::warning('WhatsApp credentials missing, falling back to log');
            Logger::info('OTP (MOCK)', ['mobile' => $mobileNumber, 'code' => $otpCode]);
            return true;
        }

        $message = "Your Melina login OTP is: *{$otpCode}*\n\nThis OTP is valid for {$this->otpExpiryMinutes} minutes.\n\nDo not share this OTP with anyone.";

        $data = [
            'secret' => $apiSecret,
            'account' => $accountId,
            'recipient' => '+91' . $mobileNumber,
            'type' => 'text',
            'message' => $message,
            'priority' => 1
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://billmsg.com/api/send/whatsapp');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Logger::error('WhatsApp curl error: ' . $error);
                return false;
            }

            if ($httpCode === 200) {
                Logger::info('WhatsApp OTP sent', ['recipient' => $mobileNumber]);
                return true;
            }

            Logger::error('WhatsApp API failed', ['code' => $httpCode]);
            return false;

        } catch (\Exception $e) {
            Logger::error('WhatsApp exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get OTP statistics for monitoring
     * 
     * @param string|null $mobileNumber Optional mobile number filter
     * @return array Statistics
     */
    public function getOTPStats(?string $mobileNumber = null): array
    {
        $where = $mobileNumber ? "WHERE mobile_number = ?" : "";
        $params = $mobileNumber ? [$mobileNumber] : [];

        $stats = $this->db->fetch(
            "SELECT 
                COUNT(*) as total_generated,
                SUM(CASE WHEN verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified_count,
                SUM(CASE WHEN expires_at < NOW() AND verified_at IS NULL THEN 1 ELSE 0 END) as expired_count,
                AVG(attempts) as avg_attempts
             FROM otp_verifications 
             {$where}
             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            $params
        );

        return $stats ?: [
            'total_generated' => 0,
            'verified_count' => 0,
            'expired_count' => 0,
            'avg_attempts' => 0
        ];
    }
}
