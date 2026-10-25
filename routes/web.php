<?php

use App\Http\Controllers\Admin\CafeTableController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShiftReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WaitingListController as AdminWaitingListController;
use App\Http\Controllers\Pos\WaitingListController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ShiftController;
use App\Http\Controllers\Pos\TableFloorController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Shared invoice print (admin + cashier) — both names for compatibility
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/admin/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('admin.invoices.print');

    // POS + shifts + tables (cashier + admin)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/shift', [ShiftController::class, 'index'])->name('shift.index');
        Route::post('/shift/start', [ShiftController::class, 'start'])->name('shift.start');
        Route::post('/shift/close', [ShiftController::class, 'close'])->name('shift.close');
        Route::get('/shift/{shift}', [ShiftController::class, 'show'])->name('shift.show');

        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/orders', [PosController::class, 'createOrder'])->name('orders.create');
        Route::get('/orders/{order}', [PosController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/items', [PosController::class, 'addItem'])->name('orders.items.add');
        Route::patch('/orders/{order}/items/{orderItem}', [PosController::class, 'updateItemQty'])->name('orders.items.update');
        Route::delete('/orders/{order}/items/{orderItem}', [PosController::class, 'removeItem'])->name('orders.items.remove');
        Route::post('/orders/{order}/discount', [PosController::class, 'applyDiscount'])->name('orders.discount');
        Route::post('/orders/{order}/table', [PosController::class, 'assignTable'])->name('orders.table');
        Route::post('/orders/{order}/save', [PosController::class, 'save'])->name('orders.save');
        Route::post('/orders/{order}/complete', [PosController::class, 'complete'])->name('orders.complete');
        Route::post('/orders/{order}/pay', [PosController::class, 'pay'])->name('orders.pay');
        Route::post('/orders/{order}/cancel', [PosController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/awaiting', [PosController::class, 'markAwaitingPayment'])->name('orders.awaiting');

        Route::get('/tables', [TableFloorController::class, 'index'])->name('tables');
        Route::post('/tables/{table}/open', [TableFloorController::class, 'open'])->name('tables.open');
        Route::post('/tables/{table}/free', [TableFloorController::class, 'free'])->name('tables.free');

        // Waiting List (Accessible by Cashier and Admin)
        Route::get('/waiting', [WaitingListController::class, 'index'])->name('waiting.index');
        Route::post('/waiting', [WaitingListController::class, 'store'])->name('waiting.store');
        Route::post('/waiting/{waiting}/seat', [WaitingListController::class, 'seat'])->name('waiting.seat');
        Route::post('/waiting/{waiting}/cancel', [WaitingListController::class, 'cancel'])->name('waiting.cancel');
    });

    // Admin only
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('items', ItemController::class)->except(['show']);
        Route::resource('tables', CafeTableController::class)->except(['show']);
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('waiting', [AdminWaitingListController::class, 'index'])->name('waiting.index');
        Route::post('waiting', [AdminWaitingListController::class, 'store'])->name('waiting.store');
        Route::post('waiting/{waiting}/seat', [AdminWaitingListController::class, 'seat'])->name('waiting.seat');
        Route::post('waiting/{waiting}/cancel', [AdminWaitingListController::class, 'cancel'])->name('waiting.cancel');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('shifts', [ShiftReviewController::class, 'index'])->name('shifts.index');
        Route::get('shifts/{shift}', [ShiftReviewController::class, 'show'])->name('shifts.show');
        Route::post('shifts/{shift}/review', [ShiftReviewController::class, 'review'])->name('shifts.review');
    });
});
