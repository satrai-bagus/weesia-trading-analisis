<?php

use App\Http\Controllers\AdminLiveSessionController;
use App\Http\Controllers\AdminEducationArticleController;
use App\Http\Controllers\AdminPaymentOrderController;
use App\Http\Controllers\AdminPaymentSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentOrderController;
use App\Http\Controllers\SignalUnlockController;
use App\Http\Controllers\TradeSignalController;
use App\Http\Controllers\UserSignalPositionController;
use App\Http\Controllers\WhatsAppTestController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/landing/market-tickers', [LandingController::class, 'marketTickers'])
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('landing.market-tickers');

Route::get('/landing/featured-signal', [LandingController::class, 'featuredSignal'])
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->name('landing.featured-signal');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/admin/signals', [DashboardController::class, 'adminSignals'])->name('admin.signals');
    Route::get('/user', [DashboardController::class, 'user'])->name('user.dashboard');
    Route::get('/posisi', [DashboardController::class, 'positions'])->name('user.positions');
    Route::get('/arsip', [DashboardController::class, 'archive'])->name('user.archive');
    Route::get('/live', [DashboardController::class, 'live'])->name('user.live');
    Route::get('/edukasi', [EducationController::class, 'index'])->name('education.index');
    Route::get('/edukasi/{educationArticle}', [EducationController::class, 'show'])->name('education.show');
    Route::get('/market/prices', [DashboardController::class, 'marketPrices'])->name('market.prices');
    Route::post('/admin/signals', [TradeSignalController::class, 'store'])->name('signals.store');
    Route::patch('/admin/signals/{tradeSignal}/status', [TradeSignalController::class, 'updateStatus'])->name('signals.status');
    Route::patch('/admin/signals/{tradeSignal}', [TradeSignalController::class, 'update'])->name('signals.update');
    Route::delete('/admin/signals/{tradeSignal}', [TradeSignalController::class, 'destroy'])->name('signals.destroy');
    Route::post('/signals/{tradeSignal}/unlock', [SignalUnlockController::class, 'unlock'])->name('signals.unlock');
    Route::post('/signals/{tradeSignal}/positions', [UserSignalPositionController::class, 'store'])->name('signals.positions.store');
    Route::delete('/signals/{tradeSignal}/positions', [UserSignalPositionController::class, 'destroy'])->name('signals.positions.destroy');
    Route::post('/alerts/seen', [AlertController::class, 'markSeen'])->name('alerts.seen');
    Route::post('/admin/wa/test', [WhatsAppTestController::class, 'send'])->name('wa.test');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/top-up', [AdminUserController::class, 'topUp'])->name('admin.users.topup');
    Route::post('/admin/users/{user}/subscription', [AdminUserController::class, 'extendSubscription'])->name('admin.users.subscription');

    Route::get('/buy', [PaymentOrderController::class, 'checkout'])->name('checkout');
    Route::post('/buy', [PaymentOrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [PaymentOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [PaymentOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/proof', [PaymentOrderController::class, 'uploadProof'])->name('orders.proof');
    Route::delete('/orders/{order}', [PaymentOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/admin/orders', [AdminPaymentOrderController::class, 'index'])->name('admin.orders');
    Route::post('/admin/orders/{order}/approve', [AdminPaymentOrderController::class, 'approve'])->name('admin.orders.approve');
    Route::post('/admin/orders/{order}/reject', [AdminPaymentOrderController::class, 'reject'])->name('admin.orders.reject');

    Route::get('/admin/payment-settings', [AdminPaymentSettingController::class, 'edit'])->name('admin.payment-settings.edit');
    Route::post('/admin/payment-settings', [AdminPaymentSettingController::class, 'update'])->name('admin.payment-settings.update');

    Route::get('/admin/live-sessions', [AdminLiveSessionController::class, 'index'])->name('admin.live-sessions');
    Route::post('/admin/live-sessions', [AdminLiveSessionController::class, 'store'])->name('admin.live-sessions.store');
    Route::patch('/admin/live-sessions/{liveSession}', [AdminLiveSessionController::class, 'update'])->name('admin.live-sessions.update');
    Route::post('/admin/live-sessions/{liveSession}/cancel', [AdminLiveSessionController::class, 'cancel'])->name('admin.live-sessions.cancel');
    Route::delete('/admin/live-sessions/{liveSession}', [AdminLiveSessionController::class, 'destroy'])->name('admin.live-sessions.destroy');

    Route::get('/admin/education', [AdminEducationArticleController::class, 'index'])->name('admin.education');
    Route::post('/admin/education', [AdminEducationArticleController::class, 'store'])->name('admin.education.store');
    Route::patch('/admin/education/{educationArticle}', [AdminEducationArticleController::class, 'update'])->name('admin.education.update');
    Route::delete('/admin/education/{educationArticle}', [AdminEducationArticleController::class, 'destroy'])->name('admin.education.destroy');
    Route::post('/admin/education-categories', [AdminEducationArticleController::class, 'storeCategory'])->name('admin.education-categories.store');
    Route::patch('/admin/education-categories/{educationCategory}', [AdminEducationArticleController::class, 'updateCategory'])->name('admin.education-categories.update');
    Route::delete('/admin/education-categories/{educationCategory}', [AdminEducationArticleController::class, 'destroyCategory'])->name('admin.education-categories.destroy');
});
