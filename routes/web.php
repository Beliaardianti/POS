<?php

use Illuminate\Support\Facades\Route;

//route home
Route::get('/', function () {
    return \Inertia\Inertia::render('Auth/Login');
})->middleware('guest');


//prefix "apps"
Route::prefix('apps')->group(function () {

    //middleware "auth"
    Route::group(['middleware' => ['auth']], function () {

        //route dashboard
        Route::get('dashboard', App\Http\Controllers\Apps\DashboardController::class)->name('apps.dashboard');
        // Route untuk menampilkan profile (GET)
        Route::get('/profile', [App\Http\Controllers\Apps\ProfileController::class, 'show'])->name('apps.profile.show');

        // Route untuk update profile (PUT)
        Route::put('/profile', [App\Http\Controllers\Apps\ProfileController::class, 'update'])->name('apps.profile.update');

        // Route untuk update password (PUT)
        Route::put('/profile/password', [App\Http\Controllers\Apps\ProfileController::class, 'updatePassword'])->name('apps.profile.password');
        Route::get('/permissions', \App\Http\Controllers\Apps\PermissionController::class)->name('apps.permissions.index')
            ->middleware('permission:permissions.index');

        //route resource roles
        Route::resource('/roles', \App\Http\Controllers\Apps\RoleController::class, ['as' => 'apps'])
            ->middleware('permission:roles.index|roles.create|roles.edit|roles.delete');

        //route resource users
        Route::resource('/users', \App\Http\Controllers\Apps\UserController::class, ['as' => 'apps'])
            ->middleware('permission:users.index|users.create|users.edit|users.delete');

        //route resource categories
        Route::resource('/categories', \App\Http\Controllers\Apps\CategoryController::class, ['as' => 'apps'])
            ->middleware('permission:categories.index|categories.create|categories.edit|categories.delete');

        // PRODUCTS CUSTOM ROUTES - HARUS DI ATAS RESOURCE ROUTE
        Route::get('/products/export', [\App\Http\Controllers\Apps\ProductController::class, 'export'])->name('apps.products.export');
        Route::get('/products/low-stock', [\App\Http\Controllers\Apps\ProductController::class, 'getLowStockProducts'])->name('apps.products.lowstock');
        Route::get('/products/expired', [\App\Http\Controllers\Apps\ProductController::class, 'getExpiredProducts'])->name('apps.products.expired');

        // PRODUCTS RESOURCE ROUTE - HARUS DI BAWAH CUSTOM ROUTES
        Route::resource('/products', \App\Http\Controllers\Apps\ProductController::class, ['as' => 'apps'])
            ->middleware('permission:products.index|products.create|products.edit|products.delete');

        //route resource customers
        Route::resource('/customers', \App\Http\Controllers\Apps\CustomerController::class, ['as' => 'apps'])
            ->middleware('permission:customers.index|customers.create|customers.edit|customers.delete');

        //route transaction
        Route::get('/transactions', [\App\Http\Controllers\Apps\TransactionController::class, 'index'])->name('apps.transactions.index');

        //route transaction searchProduct
        Route::post('/transactions/searchProduct', [\App\Http\Controllers\Apps\TransactionController::class, 'searchProduct'])->name('apps.transactions.searchProduct');

        //route transaction addToCart
        Route::post('/transactions/addToCart', [\App\Http\Controllers\Apps\TransactionController::class, 'addToCart'])->name('apps.transactions.addToCart');

        //route transaction destroyCart
        Route::post('/transactions/destroyCart', [\App\Http\Controllers\Apps\TransactionController::class, 'destroyCart'])->name('apps.transactions.destroyCart');

        //route transaction store
        Route::post('/transactions/store', [\App\Http\Controllers\Apps\TransactionController::class, 'store'])->name('apps.transactions.store');

        //route transaction print
        Route::get('/transactions/print', [\App\Http\Controllers\Apps\TransactionController::class, 'print'])->name('apps.transactions.print');

        //route sales index
        Route::get('/sales', [\App\Http\Controllers\Apps\SaleController::class, 'index'])->middleware('permission:sales.index')->name('apps.sales.index');

        //route sales filter
        Route::get('/sales/filter', [\App\Http\Controllers\Apps\SaleController::class, 'filter'])->name('apps.sales.filter');

        //route sales export excel
        Route::get('/sales/export', [\App\Http\Controllers\Apps\SaleController::class, 'export'])->name('apps.sales.export');

        //route sales print pdf
        Route::get('/sales/pdf', [\App\Http\Controllers\Apps\SaleController::class, 'pdf'])->name('apps.sales.pdf');

        //route profits index
        Route::get('/profits', [\App\Http\Controllers\Apps\ProfitController::class, 'index'])->middleware('permission:profits.index')->name('apps.profits.index');

        //route profits filter
        Route::get('/profits/filter', [\App\Http\Controllers\Apps\ProfitController::class, 'filter'])->name('apps.profits.filter');

        //route profits export
        Route::get('/profits/export', [\App\Http\Controllers\Apps\ProfitController::class, 'export'])->name('apps.profits.export');

        //route profits pdf
        Route::get('/profits/pdf', [\App\Http\Controllers\Apps\ProfitController::class, 'pdf'])->name('apps.profits.pdf');
    });

    Route::middleware(['role:owner'])->prefix('approvals')->group(function () {
        Route::get('/', [\App\Http\Controllers\Apps\ApprovalController::class, 'index'])->name('apps.approvals.index')
            ->middleware('permission:approvals.view');
        Route::get('/history', [\App\Http\Controllers\Apps\ApprovalController::class, 'history'])->name('apps.approvals.history')
            ->middleware('permission:approvals.view');
        Route::get('/{approval}', [\App\Http\Controllers\Apps\ApprovalController::class, 'show'])->name('apps.approvals.show')
            ->middleware('permission:approvals.view');
        Route::post('/{approval}/approve', [\App\Http\Controllers\Apps\ApprovalController::class, 'approve'])->name('apps.approvals.approve')
            ->middleware('permission:approvals.approve');
        Route::post('/{approval}/reject', [\App\Http\Controllers\Apps\ApprovalController::class, 'reject'])->name('apps.approvals.reject')
            ->middleware('permission:approvals.reject');
    });

    //route approval requests for staff
    Route::prefix('approval-requests')->group(function () {
        Route::post('/refund', [\App\Http\Controllers\Apps\ApprovalController::class, 'requestRefund'])->name('apps.approval.request.refund')
            ->middleware('permission:approvals.request');
        Route::post('/void', [\App\Http\Controllers\Apps\ApprovalController::class, 'requestVoid'])->name('apps.approval.request.void')
            ->middleware('permission:approvals.request');
        Route::post('/discount', [\App\Http\Controllers\Apps\ApprovalController::class, 'requestDiscount'])->name('apps.approval.request.discount')
            ->middleware('permission:approvals.request');
    });

    //route transaction list for staff
    Route::get('/transactions/list', [\App\Http\Controllers\Apps\TransactionController::class, 'transactionList'])->name('apps.transactions.list');

    //route request approval from transaction
    Route::post('/transactions/request-refund', [\App\Http\Controllers\Apps\TransactionController::class, 'requestRefund'])->name('apps.transactions.request.refund');
    Route::post('/transactions/request-void', [\App\Http\Controllers\Apps\TransactionController::class, 'requestVoid'])->name('apps.transactions.request.void');
});
