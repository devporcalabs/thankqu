<?php

use App\Http\Controllers\ApiDispatcherController;
use App\Http\Controllers\MidtransController;
use App\Models\Saving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/login.html', function () {
    return view('login');
});
Route::get('/login', function () {
    return view('login');
});

Route::get('/register.html', function () {
    return view('register');
});
Route::get('/register', function () {
    return view('register');
});

Route::get('/dashboard.html', function () {
    return view('dashboard');
});
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/admin.html', function () {
    return view('admin');
});
Route::get('/admin', function () {
    return view('admin');
});

Route::get('/verify.php', function (Request $request) {
    $cert = trim($request->query('cert', ''));
    $saving = null;
    $found = false;

    if (! empty($cert)) {
        $saving = Saving::with('user')
            ->where('cert_number', $cert)
            ->first();
        if ($saving) {
            $found = true;
        }
    }

    return view('verify', [
        'found' => $found,
        'saving' => $saving,
        'cert' => $cert,
    ]);
});

Route::get('/verify', function (Request $request) {
    return redirect()->to('/verify.php?'.http_build_query($request->all()));
});

// API Routes mapping to PHP legacy filenames
Route::any('/api.php', [ApiDispatcherController::class, 'dispatchAction']);
Route::any('/api/action', [ApiDispatcherController::class, 'dispatchAction']);
Route::post('/get-snap-token.php', [MidtransController::class, 'getSnapToken']);
Route::post('/midtrans-webhook.php', [MidtransController::class, 'webhook']);
Route::post('/api/midtrans/webhook', [MidtransController::class, 'webhook']);
Route::post('/api/midtrans/snap-token', [MidtransController::class, 'getSnapToken']);
Route::get('/get-client-key.php', function () {
    return response()->json([
        'client_key' => config('services.midtrans.client_key') ?: env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-PLACEHOLDER')
    ]);
});

Route::get('/debug-plans', function () {
    return response()->json(
        \App\Models\SavingsPlan::select('id', 'plan_code', 'package_name', 'target_amount', 'collected_amount', 'status')->get()
    );
});
