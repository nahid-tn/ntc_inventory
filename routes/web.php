<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\Brand;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\OldProductSaleController;
use App\Http\Controllers\ProfitlossController;
use App\Http\Controllers\ProductReplacementController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerReportController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\IpsCalculatorController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\OldProductExchangeController;


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth','verified'])->name('dashboard');
Route::middleware(['auth','admin'])->group(function () { 
    Route::post('/profile/signature', [ProfileController::class, 'uploadSignature'])->name('profile.signature.upload');
    #category route----
    Route::get('admin/index', [AdminController::class, 'index'])->name('admin.index');
    Route::get('admin/categoryform', [AdminController::class, 'CategoryForm'])->name('admin.categoryform');
    Route::post('admin/create', [AdminController::class, 'Create'])->name('admin.create');
    Route::get('/admin/edit/{id}', [AdminController::class, 'Edit'])->name('admin.edit');
    Route::post('/admin/update/{id}', [AdminController::class, 'Update'])->name('admin.update');
    Route::get('/admin/delete/{id}', [AdminController::class, 'delete'])->name('admin.delete');
    Route::get('/category-autocomplete', [AdminController::class, 'autocomplete'])->name('admin.autocomplete');
    
    #Brand route----
    Route::get('brand/index', [BrandController::class, 'index'])->name('brand.index');
    Route::get('brand/brandform', [BrandController::class, 'BrandForm'])->name('brand.brandform');
    Route::post('brand/create', [BrandController::class, 'Create'])->name('brand.create');
    Route::get('/brand/edit/{id}', [BrandController::class, 'Edit'])->name('brand.edit');
    Route::put('/brand/update/{id}', [BrandController::class, 'update'])->name('brand.update');
    Route::get('brand/delete/{id}', [BrandController::class, 'destroy'])->name('brand.delete');
    Route::get('/brand/autocomplete', [BrandController::class, 'autocomplete'])->name('brand.autocomplete');
    Route::get('/brand/create', [BrandController::class, 'createPage'])->name('brand.create.page');
    
    #supplier route----
    Route::get('supplier/index', [SupplierController::class, 'index'])->name('supplier.index');
    Route::get('supplier/productform', [SupplierController::class, 'SupplierForm'])->name('supplier.supplierform');
    Route::post('supplier/create', [SupplierController::class, 'Create'])->name('supplier.create');
    Route::get('/supplier/edit/{id}', [SupplierController::class, 'Edit'])->name('supplier.edit');
    Route::post('/supplier/update/{id}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::get('supplier/delete/{id}', [SupplierController::class, 'destroy'])->name('supplier.delete');
    Route::get('/supplier-autocomplete', [SupplierController::class, 'autocomplete'])->name('supplier.autocomplete');
    Route::get('/supplier-ledger/pdf/{id}', [SupplierController::class,'ledgerPdf'] )->name('supplier.ledger.pdf');
    Route::get('/supplier/{id}/payments', [SupplierController::class, 'paymentList'])->name('supplier.payment.list');
    Route::get('/get-banks', [BankController::class, 'getBanks'])->name('get.banks');

    #product route----
    Route::get('product/index', [ProductController::class, 'index'])->name('product.index');
    Route::get('product/productform', [ProductController::class, 'ProductForm'])->name('product.productform');
    Route::get('/product-search', [ProductController::class, 'search'])->name('product.search');
    Route::post('/product/create', [ProductController::class, 'store'])
    ->name('product.create');
    Route::get('/product/edit/{id}', [ProductController::class, 'Edit'])->name('product.edit');
    Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/product-autocomplete', [ProductController::class, 'autocomplete'])->name('product.autocomplete');
    Route::get('/stock-report', [ProductController::class, 'stockReport'])->name('stock.report');
    Route::get('/product/check-name', [ProductController::class, 'checkName'])->name('product.check.name');
    
    #customer route----
    Route::get('customer/index', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('customer/customerform', [CustomerController::class, 'CustomerForm'])->name('customer.customerform');
    Route::get('customer/new-customer-form', [CustomerController::class, 'NewCustomerForm'])->name('customer.newcustomerform');
    Route::post('/customer/create',[CustomerController::class,'Create'])->name('customer.create');
    Route::post('/customer/store',[CustomerController::class,'store'])->name('customer.store');
    Route::get('/customer/edit/{id}', [CustomerController::class, 'Edit'])->name('customer.edit');
    Route::post('/customer/update/{id}', [CustomerController::class, 'update'])->name('customer.update');
    Route::get('customer/delete/{id}', [CustomerController::class, 'destroy'])->name('customer.delete');
    Route::get('/customer-autocomplete', [CustomerController::class, 'autocomplete'])->name('customer.autocomplete');

    // কাস্টমারের রিপোর্ট
    Route::get('/report/customers', [CustomerReportController::class, 'allCustomersReport'])->name('customerReport.customer_all');
    Route::get('/report/customer/{id}', [CustomerReportController::class, 'singleCustomerReport'])->name('customerReport.customer_single');
    Route::get('report/customer/pdf/{id}', [CustomerReportController::class, 'pdf'])->name('customerReport.report.pdf');
    Route::get('report/customer/excel/{customer}', [CustomerReportController::class, 'excel'])->name('customer.report.excel');
    Route::get('/report', [CustomerReportController::class, 'report'])->name('customer.report');
    Route::get('/due-reports', [CustomerReportController::class,'dueReports'])->name('reports.due_reports');
    Route::get('/due-reports/pdf', [CustomerReportController::class,'dueReportsPdf'])->name('reports.due_reports.pdf');

    #supplier route----
    Route::get('purchase', [PurchasesController::class, 'index'])->name('purchase.index');
    Route::get('purchase/form', [PurchasesController::class, 'PurchaseForm'])->name('purchase.form');
    Route::post('purchase/create', [PurchasesController::class, 'store'])->name('purchase.create');
    Route::get('purchase/{id}/edit', [PurchasesController::class, 'edit'])->name('purchase.edit');
    Route::put('purchase/{id}', [PurchasesController::class, 'update'])->name('purchase.update');
    Route::get('purchase/{id}/view', [PurchasesController::class, 'view'])->name('purchase.view');
    Route::get('purchase/{id}/pdf', [PurchasesController::class, 'downloadPdf'])->name('purchase.pdf');
    Route::get('purchase/delete/{id}', [PurchasesController::class, 'destroy'])->name('purchase.delete');
    Route::get('/purchase-autocomplete', [PurchasesController::class, 'autocomplete'])->name('supplier.autocomplete');
    Route::get('/purchase/dues', [PurchasesController::class, 'supplierDues'])->name('supplier.dues');
    Route::get('/autocomplete/supplier', [PurchasesController::class, 'supplierAutocomplete'])->name('autocomplete.supplier');
    Route::get('/autocomplete/invoice', [PurchasesController::class, 'invoiceAutocomplete'])->name('autocomplete.invoice');
    Route::post('/purchase/{id}/payment', [PurchasesController::class, 'addPayment'])->name('purchase.addPayment');
    
    Route::get('/purchase/{purchase}/return', [PurchasesController::class, 'createReturn'])->name('purchase.return_create');
    Route::post('/purchase/{purchase}/return', [PurchasesController::class, 'storeReturn'])->name('purchase.return.store');
    Route::get('/purchase/{id}/info', [PurchasesController::class, 'invoiceInfo']);
    Route::get('/purchase/{id}/invoice', [PurchasesController::class, 'showInvoice'])->name('purchase.invoice');

    // Route::get('/purchase-report-pdf', [PurchasesController::class, 'purchaseReportPdf'])->name('purchase.chronological_report_pdf');
    Route::get('/purchase-group-report-pdf', [PurchasesController::class, 'purchaseGroupReportPdf'])->name('purchase.group_report_pdf');

    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('purchase-orders/{id}/convert',[PurchaseOrderController::class, 'convertToPurchase'])->name('purchase-orders.convert');
    Route::get('purchase-orders/{id}/pdf',[PurchaseOrderController::class, 'pdf'])->name('purchase-orders.pdf');
    Route::get('/product-last-price/{id}', [ProductController::class, 'lastPrice']);
    
    #PurchaseReportController route----
    Route::get('/purchase/dashboard', [PurchaseReportController::class, 'dashboard'])->name('purchase.dashboard');
    Route::get('/purchase/report', [PurchaseReportController::class, 'report'])->name('purchase.report');
    Route::get('/purchase/report', [PurchaseReportController::class, 'index'])->name('reports.index');
    Route::get('/purchase/stock-report', [PurchaseReportController::class, 'stockReport'])->name('purchase.stock');
    Route::get('/purchase-report/{supplier}',[PurchaseReportController::class,'supplierReport'])->name('purchase.report.supplier');
    Route::get('/supplier-payments', [PurchaseReportController::class, 'supplierPayments'])->name('reports.supplier_payment');
    Route::get('/pdf', [PurchaseReportController::class, 'generatePDF'])->name('reports.pdf');


    #sale route----
    Route::get('/sale', [SalesController::class, 'index'])->name('sale.index');
    Route::get('/sale/create', [SalesController::class, 'createForm'])->name('sale.createform');
    Route::post('/sale/create', [SalesController::class, 'store'])->name('sale.store');
    Route::get('/sale/{id}/edit', [SalesController::class, 'edit'])->name('sale.edit');
    Route::put('/sale/{id}', [SalesController::class, 'update'])->name('sale.update');
    Route::get('/sale/{id}', [SalesController::class, 'view'])->name('sale.view');
    Route::get('/sale/{id}/pdf', [SalesController::class, 'downloadPdf'])->name('sale.pdf');
    Route::get('/sale/download-pdf/{id}', [SalesController::class, 'downloadPdf'])->name('sale.downloadPdf');
    Route::delete('/sale/{id}', [SalesController::class, 'destroy'])->name('sale.delete');
    Route::get('/sale-autocomplete', [SalesController::class, 'autocomplete'])->name('sale.autocomplete');
    Route::get('/sale-autocomplete', [SalesController::class, 'saleAutocomplete'])->name('sales.autocomplete');
    Route::post('/sale/{id}/receive-payment', [SalesController::class, 'receivePayment'])->name('sales.receivePayment');

    // Sale Partial Payment
    Route::get('sale/{id}/edit-partial', [SalesController::class, 'editPartial'])->name('sale.editPartial');
    Route::post('sale/{id}/save-partial', [SalesController::class, 'savePartialPayment'])->name('sale.savePartialPayment');
    Route::put('sale/partial-payment/{id}/update', [SalesController::class, 'updatePartialPayment'])->name('sale.updatePartialPayment');
    Route::delete('sale/partial-payment/{id}/delete', [SalesController::class, 'deletePartialPayment'])->name('sale.deletePartialPayment');
    Route::get('sale/{id}/partial-payment-pdf', [SalesController::class, 'partialPaymentPdf'])->name('sale.partialPaymentPdf');

    Route::get('/sales-report', [SalesReportController::class,'index'])->name('sales.report');
    Route::get('/sales-report/filter', [SalesReportController::class,'filter'])->name('sales.report.filter');
    Route::get('/report/dashboard',[SalesReportController::class,'dashboard'])->name('report.dashboard');
    Route::get('/report/profit',[SalesReportController::class,'profitReport'])->name('report.profit');

    Route::get('/oldsale', [OldProductSaleController::class, 'index'])->name('oldsale.index');
    Route::get('/oldsale/create', [OldProductSaleController::class, 'createForm'])->name('oldsale.createform');
    Route::post('/oldsale/create', [OldProductSaleController::class, 'store'])->name('oldsale.store');
    Route::get('/oldsale/{id}/view', [OldProductSaleController::class,'show'])->name('oldsale.show');
    Route::get('/oldsale/{id}/edit', [OldProductSaleController::class, 'edit'])->name('oldsale.edit');
    Route::put('/oldsale/{id}/update', [OldProductSaleController::class, 'update'])->name('oldsale.update');
    Route::delete('/oldsale/{id}/delete', [OldProductSaleController::class, 'destroy'])->name('delete');
    Route::get('/oldsale/{id}/invoice-pdf', [OldProductSaleController::class,'invoicePdf'])->name('oldsale.invoice.pdf');
    Route::get('/products-old-autocomplete', [OldProductSaleController::class, 'productsOldAutocomplete']);
    Route::get('/old-product-stock/{id}', [OldProductSaleController::class, 'getOldProductStock']);
    Route::get('/old-sale-report',[OldProductSaleController::class,'profitReport'])->name('oldsale.profitlos_report');
    Route::get('/old-products', [OldProductSaleController::class, 'oldProductIndexList'])->name('oldsale.old_index');


    Route::get('/profitloss', [ProfitlossController::class, 'profitLoss'])->name('ProfitLos.profitlos');
    Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('ProfitLos.report');
    Route::get('/reports/profit-loss', [ProfitlossController::class, 'profitLossReport'])->name('reports.profit_loss')->middleware('admin');
    Route::get('/reports/profit-loss/pdf', [ProfitlossController::class, 'profitLossPdf'])
    ->name('reports.profit_loss_pdf');




    // Sales Product Replacement Routes
    Route::prefix('replacement')->group(function() {
    Route::get('/', [ProductReplacementController::class, 'index'])->name('replacement.index');
    Route::get('/create', [ProductReplacementController::class, 'create'])->name('replacement.create');
    Route::post('/store', [ProductReplacementController::class, 'store'])->name('replacement.store');
    Route::get('/edit/{id}', [ProductReplacementController::class, 'edit'])->name('replacement.edit');
    Route::put('/update/{id}', [ProductReplacementController::class, 'update'])->name('replacement.update');
    Route::delete('/delete/{id}', [ProductReplacementController::class, 'destroy'])->name('replacement.destroy');
    Route::get('/view/{id}', [ProductReplacementController::class, 'view'])->name('replacement.view');
    Route::get('/old-products/{sale_id}', [ProductReplacementController::class, 'getOldProducts'])->name('replacement.old-products');
    Route::get('/replacement/{productReplacement}/invoice-pdf', [ProductReplacementController::class, 'invoicePdf'])->name('replacement.invoice.pdf');
    Route::get('/product-autocomplete', [ProductReplacementController::class, 'autocomplete'])->name('product.autocomplete');
    });

    Route::prefix('purchase-return')->group(function() {
    Route::get('/',[PurchaseReturnController::class,'index'])->name('purchase-return.index');
    Route::get('/purchase-search', [PurchaseReturnController::class, 'purchaseSearch'])->name('purchase-return.purchase-search');
    Route::get('/create',[PurchaseReturnController::class,'create'])->name('purchase-return.create');
    Route::post('/store',[PurchaseReturnController::class,'store'])->name('purchase-return.store');
    Route::get('/purchase-return/{id}/edit',[PurchaseReturnController::class, 'edit'])->name('purchase-return.edit');
    Route::put('/purchase-return/{id}',[PurchaseReturnController::class, 'update'])->name('purchase-return.update');
    Route::delete('/delete/{purchaseReturn}', [PurchaseReturnController::class,'destroy'])->name('purchase-return.delete');
    Route::get('/report',[PurchaseReturnController::class,'report'])->name('purchase-return.report');
    Route::get('/search', [PurchaseReturnController::class, 'purchaseSearch'])->name('purchase.search');
    Route::get('/purchase-products/{id}',[PurchaseReturnController::class,'purchaseProducts'])->name('purchase-return.products');
    Route::get('/purchase-return/{id}', [PurchaseReturnController::class, 'show'])->name('purchase-return.show');
    Route::get('/purchase-return/{id}/pdf', [PurchaseReturnController::class, 'exportPdf'])->name('purchase-return.pdf');
    Route::get('/purchase-return-report', [PurchaseReturnController::class, 'SuplierReport'])->name('purchase-return.suplierreport');
    Route::get('/purchase-return-report/pdf', [PurchaseReturnController::class, 'reportPdf'])->name('purchase-return.report_pdf');
    Route::get('/report-form', [PurchaseReturnController::class, 'reportForm'])->name('purchase-return.reportform');
    Route::post('/purchase-return-report', [PurchaseReturnController::class,'generate'])->name('purchase-return.reportgenerate');
    Route::get('/purchase-return-item-report-form',[PurchaseReturnController::class,'itemWiseReportForm'])->name('purchase-return.item-wise-report-form');
    Route::get('/purchase-return-item-report', [PurchaseReturnController::class, 'itemWiseReport'])->name('purchase-return.item-wise-report');
    Route::get('/purchase-return/purchase-return-product-details/{product_id}', [PurchaseReturnController::class, 'productReturnDetails'])->name('purchase-return.product-details');
    });

    Route::prefix('old-product-exchange')->group(function () {
        Route::get('/',[OldProductExchangeController::class,'index'])->name('old_product_exchange.index');
        Route::get('/create', [OldProductExchangeController::class, 'create'])->name('old_product_exchange.create');
        Route::post('/', [OldProductExchangeController::class, 'store'])->name('old_product_exchange.store');
        Route::get('/{id}', [OldProductExchangeController::class, 'show'])->name('old_product_exchange.show');
        Route::get('/{id}/edit', [OldProductExchangeController::class, 'edit'])->name('old_product_exchange.edit');
        Route::put('/{id}', [OldProductExchangeController::class, 'update'])->name('old_product_exchange.update');
        Route::delete('/{id}', [OldProductExchangeController::class, 'destroy'])->name('old_product_exchange.destroy');
        Route::get('/{id}/invoice', [OldProductExchangeController::class, 'invoice'])->name('old_product_exchange.invoice');
        Route::get('/product/{id}', [OldProductExchangeController::class, 'product'])->name('old_product_exchange.product');
    });

    // Route::get('/',[ExpenseController::class,'index'])->name('expense.index');
    Route::get('/expense',[ExpenseController::class,'index'])->name('expense.index');
    Route::get('/expense-createform', [ExpenseController::class, 'createform'])->name('expense.createform');
    Route::get('/create',[ExpenseController::class,'create'])->name('expense.create');
    Route::post('/store',[ExpenseController::class,'store'])->name('expense.store');
    Route::get('expense/show/{id}',[ExpenseController::class,'show'])->name('expense.view');
    Route::get('edit/{id}',[ExpenseController::class,'edit'])->name('expense.edit');
    Route::put('expense/update/{id}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('/destroy/{id}',[ExpenseController::class,'destroy'])->name('expense.destroy');
    Route::get('/expense/report', [ExpenseController::class, 'reportForm'])->name('expense.reportform');
    Route::get('/expense/report/pdf', [ExpenseController::class, 'reportPdf'])->name('expense.reportpdf');
    Route::get('/expense/report/viewreport', [ExpenseController::class, 'ViewReport']) ->name('expense.viewreport');

    Route::resource('suppliers', SupplierController::class);
    Route::post('supplier-payment', [SupplierPaymentController::class,'store'])->name('supplier.payment');
    Route::get('supplier-ledger/{id}', [SupplierPaymentController::class,'ledger'])->name('supplier.ledger');
    Route::get('/supplier-payment/create', [SupplierPaymentController::class,'create'])->name('supplier.payment.create');
    Route::post('supplier-payment/store', [SupplierPaymentController::class,'store'])->name('supplier.payment.store');
    Route::get('supplier-payment/{id}', [SupplierPaymentController::class,'show'])->name('supplier.payment.show');
    Route::get('supplier-payment/{id}/edit', [SupplierPaymentController::class,'edit'])->name('supplier.payment.edit');
    Route::put('supplier-payment/{id}', [SupplierPaymentController::class,'update'])->name('supplier.payment.update');
    Route::get('/supplier/{id}/invoices', [SupplierPaymentController::class,'supplierInvoices']);
    Route::get('/supplier-payment', [SupplierPaymentController::class, 'index'])->name('supplier.payment.index');
    Route::delete('/supplier-payment/{id}/delete', [SupplierPaymentController::class, 'destroy'])->name('supplier.payment.destroy');

    // IPS System Routes
    Route::get('/ips-calculator', [IpsCalculatorController::class, 'index'])->name('ips.form');
    Route::post('/ips-calculator', [IpsCalculatorController::class, 'calculate'])->name('ips.calculate');
});

    Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';