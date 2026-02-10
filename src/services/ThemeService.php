<?php
/**
 * Theme Settings Service
 * Simple DB-based theme storage - loads on login, applies server-side
 */

declare(strict_types=1);

namespace App\Services;

use System\Database;

class ThemeService
{
    private static ?ThemeService $instance = null;
    private $db;
    
    // Default theme settings
    private static array $defaults = [
        'theme_mode' => 'light',
        'direction' => 'ltr',
        'nav_layout' => 'vertical',
        'vertical_style' => 'default',
        'menu_style' => 'light',
        'header_style' => 'light',
        'page_style' => 'flat',
        'layout_width' => 'fullwidth',
        'menu_position' => 'fixed',
        'header_position' => 'fixed',
        'primary_rgb' => '78, 172, 76',
        'toggled' => 'close'
    ];
    
    private function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public static function getInstance(): ThemeService
    {
        if (self::$instance === null) {
            self::$instance = new ThemeService();
        }
        return self::$instance;
    }
    
    /**
     * Get theme settings from DB
     */
    public function getSettings(): array
    {
        try {
            $sql = "SELECT setting_key, setting_value FROM theme_settings WHERE branch_id IS NULL";
            $results = $this->db->fetchAll($sql);
            
            $settings = self::$defaults;
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (\Exception $e) {
            // If table doesn't exist, return defaults
            return self::$defaults;
        }
    }
    
    /**
     * Save theme settings to DB
     */
    public function saveSettings(array $settings, ?int $userId = null): bool
    {
        try {
            foreach ($settings as $key => $value) {
                if (array_key_exists($key, self::$defaults)) {
                    $sql = "INSERT INTO theme_settings (branch_id, setting_key, setting_value, updated_by, updated_at) 
                            VALUES (NULL, ?, ?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), 
                                                    updated_by = VALUES(updated_by),
                                                    updated_at = NOW()";
                    $this->db->query($sql, [$key, $value, $userId]);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Load theme into session (call on login)
     */
    public static function loadToSession(): void
    {
        $service = self::getInstance();
        $_SESSION['theme'] = $service->getSettings();
    }
    
    /**
     * Get theme from session (with fallback to defaults)
     */
    public static function fromSession(): array
    {
        return $_SESSION['theme'] ?? self::$defaults;
    }
    
    /**
     * Update session theme (call when switcher changes)
     */
    public static function updateSession(array $settings): void
    {
        $current = self::fromSession();
        $_SESSION['theme'] = array_merge($current, $settings);
    }
    
    /**
     * Get HTML attributes for <html> tag
     */
    public static function getHtmlAttributes(): string
    {
        $theme = self::fromSession();
        
        $attrs = [
            'lang' => 'en',
            'dir' => $theme['direction'] ?? 'ltr',
            'data-nav-layout' => $theme['nav_layout'] ?? 'vertical',
            'data-theme-mode' => $theme['theme_mode'] ?? 'light',
            'data-header-styles' => $theme['header_style'] ?? 'light',
            'data-menu-styles' => $theme['menu_style'] ?? 'light',
            'data-width' => $theme['layout_width'] ?? 'fullwidth',
            'data-page-style' => $theme['page_style'] ?? 'flat',
            'data-toggled' => $theme['toggled'] ?? 'close',
        ];
        
        // Add vertical style only for vertical layout
        if (($theme['nav_layout'] ?? 'vertical') === 'vertical') {
            $attrs['data-vertical-style'] = $theme['vertical_style'] ?? 'default';
        }
        
        // Add menu/header positions
        if (!empty($theme['menu_position'])) {
            $attrs['data-menu-position'] = $theme['menu_position'];
        }
        if (!empty($theme['header_position'])) {
            $attrs['data-header-position'] = $theme['header_position'];
        }
        
        $html = '';
        foreach ($attrs as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        return trim($html);
    }
    
    /**
     * Get inline style for primary color
     */
    public static function getPrimaryColorStyle(): string
    {
        $theme = self::fromSession();
        $primaryRgb = $theme['primary_rgb'] ?? '78, 172, 76';
        return "--primary-rgb: {$primaryRgb};";
    }
    
    /**
     * Get defaults
     */
    public static function getDefaults(): array
    {
        return self::$defaults;
    }
    
    /**
     * Reset to defaults
     */
    public function resetToDefaults(?int $userId = null): bool
    {
        $_SESSION['theme'] = self::$defaults;
        return $this->saveSettings(self::$defaults, $userId);
    }
}
