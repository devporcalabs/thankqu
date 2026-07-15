<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiDispatcherController extends Controller
{
    public function dispatchAction(Request $request)
    {
        $action = trim($request->query('action', ''));

        switch ($action) {
            case 'login':
                return app(AuthController::class)->login($request);
            case 'register':
                return app(AuthController::class)->register($request);
            
            // Client Dashboard Actions
            case 'get_dashboard_data':
                return app(DashboardController::class)->getDashboardData($request);
            case 'create_savings':
                return app(DashboardController::class)->createSavings($request);
            case 'save_penyaluran':
                return app(DashboardController::class)->savePenyaluran($request);
            case 'submit_vote':
                return app(DashboardController::class)->submitVote($request);
            case 'get_refund_simulation':
                return app(DashboardController::class)->getRefundSimulation($request);
            case 'request_cancellation':
                return app(DashboardController::class)->requestCancellation($request);
            
            // Admin Actions
            case 'get_admin_data':
                return app(AdminController::class)->getAdminData($request);
            case 'toggle_user_role':
                return app(AdminController::class)->toggleUserRole($request);
            case 'delete_user':
                return app(AdminController::class)->deleteUser($request);
            case 'add_livestock':
                return app(AdminController::class)->addLivestock($request);
            case 'edit_livestock':
                return app(AdminController::class)->editLivestock($request);
            case 'delete_livestock':
                return app(AdminController::class)->deleteLivestock($request);
            case 'add_location':
                return app(AdminController::class)->addLocation($request);
            case 'edit_location':
                return app(AdminController::class)->editLocation($request);
            case 'delete_location':
                return app(AdminController::class)->deleteLocation($request);
            case 'update_timeline':
                return app(AdminController::class)->updateTimeline($request);
            case 'save_certificate':
                return app(AdminController::class)->saveCertificate($request);
            case 'approve_transaction':
                return app(AdminController::class)->approveTransaction($request);
            case 'check_midtrans_status':
                return app(MidtransController::class)->checkMidtransStatus($request);
            case 'simulate_payment':
                return app(MidtransController::class)->simulatePayment($request);
            case 'upload_image':
                return app(AdminController::class)->uploadImage($request);
            
            // New Admin Actions
            case 'talangi_slot':
                return app(AdminController::class)->talangiSlot($request);
            case 'process_bulk_pipeline':
                return app(AdminController::class)->processBulkPipeline($request);
            case 'get_settings':
                return app(AdminController::class)->getSettings($request);
            case 'save_settings':
                return app(AdminController::class)->saveSettings($request);
            case 'approve_refund':
                return app(AdminController::class)->approveRefund($request);
            case 'get_activity_logs':
                return app(AdminController::class)->getActivityLogs($request);

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Action ' . $action . ' not found.',
                ], 404);
        }
    }
}
