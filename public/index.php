<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Auth.php';
require_once __DIR__ . '/../app/Helpers/Format.php';
require_once __DIR__ . '/../app/Helpers/Flash.php';
require_once __DIR__ . '/../app/Helpers/Pagination.php';
require_once __DIR__ . '/../app/Helpers/CsvExporter.php';

// Controllers
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Controllers/MoneyInController.php';
require_once __DIR__ . '/../app/Controllers/ExpenseController.php';
require_once __DIR__ . '/../app/Controllers/FixedExpenseController.php';
require_once __DIR__ . '/../app/Controllers/BankAccountController.php';
require_once __DIR__ . '/../app/Controllers/TransactionController.php';
require_once __DIR__ . '/../app/Controllers/LoanController.php';
require_once __DIR__ . '/../app/Controllers/RepaymentController.php';
require_once __DIR__ . '/../app/Controllers/LiabilityController.php';
require_once __DIR__ . '/../app/Controllers/ReceivableController.php';
require_once __DIR__ . '/../app/Controllers/ReportController.php';
require_once __DIR__ . '/../app/Controllers/DirectoryController.php';
require_once __DIR__ . '/../app/Controllers/BrandController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/ProfileController.php';
require_once __DIR__ . '/../app/Controllers/SettingController.php';

// Security Headers

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Extract Relative Path for Router
$path = $_GET['route'] ?? null;

if (!$path) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = dirname($scriptName);
    
    // Strip /public suffix to find base folder (e.g. /fin)
    $baseDir = preg_replace('#/public$#', '', $scriptDir);

    // Strip base directory prefix if present in URI
    if ($baseDir !== '/' && $baseDir !== '\\' && !empty($baseDir) && strpos($uri, $baseDir) === 0) {
        $uri = substr($uri, strlen($baseDir));
    }

    // Strip /public or /index.php prefix if present
    if (strpos($uri, '/public') === 0) {
        $uri = substr($uri, 7);
    }
    if (strpos($uri, '/index.php') === 0) {
        $uri = substr($uri, 10);
    }

    $path = '/' . trim($uri, '/');
}

if (empty($path)) {
    $path = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

// Route Dispatcher
if ($path === '/' || $path === '/login') {
    if ($method === 'POST') {
        AuthController::login();
    } else {
        AuthController::showLogin();
    }
} elseif ($path === '/logout') {
    AuthController::logout();
} elseif ($path === '/dashboard') {
    DashboardController::index();
} elseif ($path === '/money-in/create') {
    MoneyInController::create();
} elseif ($path === '/money-in/store' && $method === 'POST') {
    MoneyInController::store();
} elseif ($path === '/expenses/create') {
    ExpenseController::create();
} elseif ($path === '/expenses/store' && $method === 'POST') {
    ExpenseController::store();
} elseif ($path === '/fixed-expenses') {
    FixedExpenseController::index();
} elseif ($path === '/fixed-expenses/create') {
    FixedExpenseController::create();
} elseif ($path === '/fixed-expenses/store' && $method === 'POST') {
    FixedExpenseController::store();
} elseif (preg_match('#^/fixed-expenses/([0-9]+)/edit$#', $path, $matches)) {
    FixedExpenseController::edit((int)$matches[1]);
} elseif (preg_match('#^/fixed-expenses/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    FixedExpenseController::update((int)$matches[1]);
} elseif (preg_match('#^/fixed-expenses/([0-9]+)/delete$#', $path, $matches) && $method === 'POST') {
    FixedExpenseController::delete((int)$matches[1]);
} elseif (preg_match('#^/fixed-expenses/([0-9]+)/pay$#', $path, $matches) && $method === 'POST') {
    FixedExpenseController::pay((int)$matches[1]);
} elseif ($path === '/fixed-expenses/send-notifications' && $method === 'POST') {
    FixedExpenseController::sendEmailNotifications();
} elseif ($path === '/bank-accounts') {
    BankAccountController::index();
} elseif ($path === '/bank-accounts/create') {
    BankAccountController::create();
} elseif ($path === '/bank-accounts/store' && $method === 'POST') {
    BankAccountController::store();
} elseif (preg_match('#^/bank-accounts/([0-9]+)/edit$#', $path, $matches)) {
    BankAccountController::edit((int)$matches[1]);
} elseif (preg_match('#^/bank-accounts/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    BankAccountController::update((int)$matches[1]);
} elseif ($path === '/transactions') {
    TransactionController::index();
} elseif (preg_match('#^/transactions/([0-9]+)/edit$#', $path, $matches)) {
    TransactionController::edit((int)$matches[1]);
} elseif (preg_match('#^/transactions/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    TransactionController::update((int)$matches[1]);
} elseif (preg_match('#^/transactions/([0-9]+)/delete$#', $path, $matches) && $method === 'POST') {
    TransactionController::delete((int)$matches[1]);
} elseif ($path === '/loans') {
    LoanController::index();
} elseif ($path === '/loans/create') {
    LoanController::create();
} elseif ($path === '/loans/store' && $method === 'POST') {
    LoanController::store();
} elseif (preg_match('#^/loans/([0-9]+)$#', $path, $matches)) {
    LoanController::show((int)$matches[1]);
} elseif ($path === '/repayments/create') {
    RepaymentController::create();
} elseif ($path === '/repayments/store' && $method === 'POST') {
    RepaymentController::store();
} elseif ($path === '/liabilities') {
    LiabilityController::index();
} elseif ($path === '/receivables') {
    ReceivableController::index();
} elseif ($path === '/reports/daily') {
    ReportController::daily();
} elseif ($path === '/reports/monthly') {
    ReportController::monthly();
} elseif ($path === '/reports/brand') {
    ReportController::brandReport();
} elseif ($path === '/reports/loans') {
    ReportController::loansReport();
} elseif ($path === '/directory') {
    DirectoryController::index();
} elseif ($path === '/brands') {
    BrandController::index();
} elseif ($path === '/brands/create') {
    BrandController::create();
} elseif ($path === '/brands/store' && $method === 'POST') {
    BrandController::store();
} elseif (preg_match('#^/brands/([0-9]+)/edit$#', $path, $matches)) {
    BrandController::edit((int)$matches[1]);
} elseif (preg_match('#^/brands/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    BrandController::update((int)$matches[1]);
} elseif ($path === '/users') {
    UserController::index();
} elseif ($path === '/users/create') {
    UserController::create();
} elseif ($path === '/users/store' && $method === 'POST') {
    UserController::store();
} elseif (preg_match('#^/users/([0-9]+)/edit$#', $path, $matches)) {
    UserController::edit((int)$matches[1]);
} elseif (preg_match('#^/users/([0-9]+)$#', $path, $matches) && $method === 'POST') {
    UserController::update((int)$matches[1]);
} elseif (preg_match('#^/users/([0-9]+)/delete$#', $path, $matches) && $method === 'POST') {
    UserController::delete((int)$matches[1]);
} elseif ($path === '/profile') {
    ProfileController::index();
} elseif ($path === '/profile/update' && $method === 'POST') {
    ProfileController::update();
} elseif ($path === '/settings/smtp' && $method === 'POST') {
    SettingController::updateSmtp();
} elseif ($path === '/settings/smtp-test' && $method === 'POST') {
    SettingController::testSmtp();
} elseif ($path === '/settings') {
    if ($method === 'POST') {
        SettingController::update();
    } else {
        SettingController::index();
    }
} elseif ($path === '/api/bank-accounts') {

    require_once __DIR__ . '/api/bank-accounts.php';
} else {
    http_response_code(404);
    echo "<div style='text-align:center;margin-top:100px;font-family:sans-serif;'>";
    echo "<h1>404 Page Not Found</h1>";
    echo "<p>The requested URL <code>" . e($path) . "</code> was not found.</p>";
    echo "<p><a href='" . BASE_URL . "/dashboard' style='color:#0284c7;font-weight:bold;'>Return to Dashboard</a></p>";
    echo "</div>";
}
