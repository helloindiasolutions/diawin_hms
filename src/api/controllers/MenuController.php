<?php
/**
 * Menu API Controller
 * 
 * Handles API endpoints for the HMS sidebar menu system.
 * Provides menu structure, badge counters, and branch switching functionality.
 */

declare(strict_types=1);

namespace App\Api\Controllers;

use System\Response;
use System\Database;
use System\Logger;
use App\Services\MenuService;
use App\Services\BadgeService;

class MenuController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/v1/menu
     */
    public function getMenu(): void
    {
        // Start session if not started (index.php already does this but safety first)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get user context from session
        $userId = $_SESSION['user_id'] ?? null;
        $userData = $_SESSION['user_data'] ?? [];
        $branchId = $userData['branch_id'] ?? null;

        // Validate user is authenticated
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        // AUTO-PATCH: Ensure branchId is set
        if (!$branchId) {
            // Priority 1: User's assigned branch
            $branchId = $this->db->fetchColumn("SELECT branch_id FROM users WHERE user_id = ?", [$userId]);

            // Priority 2: Any branch
            if (!$branchId) {
                $branchId = $this->db->fetchColumn("SELECT branch_id FROM branches LIMIT 1");
            }

            if ($branchId) {
                $branchId = (int) $branchId;
                if (!isset($_SESSION['user_data']) || !is_array($_SESSION['user_data'])) {
                    $_SESSION['user_data'] = [];
                }
                $_SESSION['user_data']['branch_id'] = $branchId;
            }
        }

        try {
            // Get branch information (even if branchId is still null, we'll try to find one)
            if (!$branchId) {
                $branch = $this->db->fetch("SELECT branch_id as id, name, code FROM branches LIMIT 1");
                if ($branch) {
                    $branchId = (int) $branch['id'];
                }
            } else {
                $branch = $this->db->fetch(
                    "SELECT branch_id as id, name, code FROM branches WHERE branch_id = ?",
                    [$branchId]
                );
            }

            if (!$branch) {
                // Absolute failsafe for development
                $branch = ["id" => 1, "name" => "Default Branch", "code" => "DEF01"];
                $branchId = 1;
            }

            // Initialize MenuService and get filtered menu
            $menuService = new MenuService($this->db, (int) $userId, (int) $branchId);
            $menus = $menuService->getUserMenu();

            Response::json([
                'success' => true,
                'data' => [
                    'menus' => $menus,
                    'current_branch' => $branch
                ]
            ]);

        } catch (\Exception $e) {
            Logger::error('Menu API error: ' . $e->getMessage());
            Response::json(['success' => false, 'message' => 'Internal Error'], 500);
        }
    }

    /**
     * GET /api/v1/menu/badges
     */
    public function getBadges(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $branchId = $_SESSION['user_data']['branch_id'] ?? null;

        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Failsafe branch context
        if (!$branchId) {
            $branchId = $this->db->fetchColumn("SELECT branch_id FROM branches LIMIT 1");
            if ($branchId) {
                $branchId = (int) $branchId;
                $_SESSION['user_data']['branch_id'] = $branchId;
            } else {
                $branchId = 1; // Dev fallback
            }
        }

        try {
            $badgeService = new BadgeService($this->db, (int) $userId, (int) $branchId);
            $badges = $badgeService->getAllBadges();

            Response::json([
                'success' => true,
                'data' => $badges
            ]);
        } catch (\Exception $e) {
            Response::json(['success' => true, 'data' => []]); // Return empty instead of error to keep sidebar quiet
        }
    }

    /**
     * POST /api/v1/menu/branch-switch
     */
    public function branchSwitch(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $branchId = isset($input['branch_id']) ? (int) $input['branch_id'] : -1;

        // Check if user is Super Admin
        $userRoles = $_SESSION['user_data']['roles'] ?? [];
        $superAdminRoles = ['SUPER_ADMIN', 'super_admin', 'Super Admin', 'System Administrator'];
        $isSuperAdmin = !empty(array_intersect($userRoles, $superAdminRoles));

        // Only Super Admin can switch to "All Branches" (branch_id = 0)
        if ($branchId === 0 && !$isSuperAdmin) {
            Response::json(['success' => false, 'message' => 'Only Super Admin can view all branches'], 403);
            return;
        }

        // Validate branch exists (except for 0 which means "All")
        if ($branchId > 0) {
            $branch = $this->db->fetch("SELECT branch_id FROM branches WHERE branch_id = ? AND is_active = 1", [$branchId]);
            if (!$branch) {
                Response::json(['success' => false, 'message' => 'Invalid Branch'], 400);
                return;
            }
        }

        // Allow 0 for "All Branches" (Super Admin only) or positive branch ID
        if ($branchId >= 0) {
            $_SESSION['user_data']['branch_id'] = $branchId === 0 ? null : $branchId;
            Response::json(['success' => true, 'message' => $branchId === 0 ? 'Viewing all branches' : 'Switched to branch']);
        } else {
            Response::json(['success' => false, 'message' => 'Invalid Branch ID'], 400);
        }
    }
}
