<?php
/**
 * Theme API Controller
 * Simple save/load - no real-time sync
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Response;
use App\Services\ThemeService;

class ThemeController
{
    private ThemeService $themeService;
    
    public function __construct()
    {
        $this->themeService = ThemeService::getInstance();
    }
    
    /**
     * GET /api/v1/theme/settings
     */
    public function getSettings(): void
    {
        Response::json([
            'success' => true,
            'data' => ThemeService::fromSession()
        ]);
    }
    
    /**
     * POST /api/v1/theme/settings
     * Save theme settings to DB and session
     */
    public function updateSettings(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input) || !is_array($input)) {
            Response::json(['success' => false, 'message' => 'Invalid input'], 400);
            return;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $defaults = ThemeService::getDefaults();
        
        // Filter valid settings
        $settings = [];
        foreach ($input as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $settings[$key] = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (empty($settings)) {
            Response::json(['success' => false, 'message' => 'No valid settings'], 400);
            return;
        }
        
        // Update session immediately
        ThemeService::updateSession($settings);
        
        // Save to DB
        $this->themeService->saveSettings($settings, $userId);
        
        Response::json([
            'success' => true,
            'message' => 'Settings saved'
        ]);
    }
    
    /**
     * POST /api/v1/theme/reset
     */
    public function reset(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $this->themeService->resetToDefaults($userId);
        
        Response::json([
            'success' => true,
            'data' => ThemeService::getDefaults()
        ]);
    }
}
