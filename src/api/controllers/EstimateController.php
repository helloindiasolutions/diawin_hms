<?php
/**
 * Estimate Controller
 * Handles estimate management endpoints
 */

namespace App\Api\Controllers;

use System\Response;
use System\Security;
use System\Logger;
use Api\Services\EstimateService;
use Middleware\PermissionMiddleware;

class EstimateController
{
    private EstimateService $estimateService;
    private PermissionMiddleware $permission;
    
    public function __construct()
    {
        $this->estimateService = new EstimateService();
        $this->permission = new PermissionMiddleware();
    }
    
    /**
     * Create new estimate
     * POST /api/estimates
     */
    public function create(): void
    {
        $this->permission->require('estimates', 'create');
        
        $input = jsonInput();
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        // Validate
        $errors = [];
        if (empty($input['patient_id'])) {
            $errors['patient_id'] = 'Patient ID is required';
        }
        if (empty($input['items']) || !is_array($input['items'])) {
            $errors['items'] = 'At least one item is required';
        }
        
        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }
        
        try {
            $estimate = $this->estimateService->createEstimate(
                (int)$input['patient_id'],
                $input['items'],
                $jwtUser['user_id'],
                $jwtUser['branch_id'] ?? null
            );
            
            Response::created($estimate, 'Estimate created successfully');
            
        } catch (\Exception $e) {
            Logger::error('Estimate creation failed in controller', [
                'error' => $e->getMessage()
            ]);
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Get estimate details
     * GET /api/estimates/:id
     */
    public function show(int $id): void
    {
        $this->permission->require('estimates', 'read');
        
        try {
            $estimate = $this->estimateService->getEstimate($id);
            
            if (!$estimate) {
                Response::notFound('Estimate not found');
                return;
            }
            
            Response::success($estimate);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Update estimate
     * PUT /api/estimates/:id
     */
    public function update(int $id): void
    {
        $this->permission->require('estimates', 'update');
        
        $input = jsonInput();
        
        if (empty($input['items']) || !is_array($input['items'])) {
            Response::validationError(['items' => 'At least one item is required']);
            return;
        }
        
        try {
            $success = $this->estimateService->updateEstimate($id, $input['items']);
            
            if ($success) {
                $estimate = $this->estimateService->getEstimate($id);
                Response::success($estimate, 'Estimate updated successfully');
            } else {
                Response::error('Failed to update estimate', 400);
            }
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Approve estimate
     * POST /api/estimates/:id/approve
     */
    public function approve(int $id): void
    {
        $this->permission->require('estimates', 'approve');
        
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        try {
            $success = $this->estimateService->approveEstimate($id, $jwtUser['user_id']);
            
            if ($success) {
                Response::success(null, 'Estimate approved successfully');
            } else {
                Response::error('Failed to approve estimate', 400);
            }
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Convert estimate to invoice
     * POST /api/estimates/:id/convert
     */
    public function convert(int $id): void
    {
        $this->permission->require('estimates', 'convert');
        
        $input = jsonInput();
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        
        $invoiceType = $input['invoice_type'] ?? 'service';
        
        if (!in_array($invoiceType, ['pharmacy', 'service', 'package', 'room'])) {
            Response::validationError(['invoice_type' => 'Invalid invoice type']);
            return;
        }
        
        try {
            $invoice = $this->estimateService->convertToInvoice(
                $id,
                $invoiceType,
                $jwtUser['user_id']
            );
            
            Response::success($invoice, 'Estimate converted to invoice successfully');
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Get estimate metrics
     * GET /api/estimates/metrics
     */
    public function metrics(): void
    {
        $this->permission->require('estimates', 'read');
        
        $jwtUser = $GLOBALS['jwt_user'] ?? null;
        $branchId = $_GET['branch_id'] ?? $jwtUser['branch_id'] ?? null;
        
        $dateRange = null;
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $dateRange = [$_GET['start_date'], $_GET['end_date']];
        }
        
        try {
            $metrics = $this->estimateService->getEstimateMetrics(
                $branchId ? (int)$branchId : null,
                $dateRange
            );
            
            Response::success($metrics);
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
