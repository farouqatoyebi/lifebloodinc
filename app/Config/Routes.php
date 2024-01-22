<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// On-Boarding routes
$routes->get('/', 'HomepageController::index');
$routes->get('/login', 'AuthController::webLoginPage');
$routes->get('/register', 'AuthController::webRegisterPage');
$routes->get('/register/(:any)', 'AuthController::webRegisterPage');
$routes->get('/verify-otp/(:any)', 'AuthController::verifyWebUserOTP');
$routes->post('/register/account', 'AuthController::processUserRegistration');
$routes->post('/sign-in', 'AuthController::processUserLogin');
$routes->post('/confirm-user-otp', 'AuthController::processUserOtpVerification');
$routes->post('/resend-user-otp/(:any)', 'AuthController::resendUserOTPVerificationCode');
$routes->get('/registration-complete', 'AuthController::webAppRegistrationComplete');

// Dashboard 
$routes->get('/dashboard', 'DashboardController::index');
// $routes->get('/test-email', 'SendOutgoingEmailController::sendOutNewBloodRequestEmail');
// $routes->get('/test-receipt', 'SendOutgoingEmailController::sendOutPaymentReceiptForPaymentMade');

// Profile Pages
$routes->get('/profile', 'ProfilePageController::profileInformationPage');
$routes->post('/profile', 'ProfilePageController::processProfileCompletion');


$routes->get('/request-blood', 'DashboardController::requestForBloodDonation');
$routes->post('/submit-blood-request', 'DashboardController::processRequestForBloodDonation');

$routes->get('/browse-blood-requests', 'DashboardController::fetchAllNearByRequestsForWeb');
$routes->get('/accept-donors', 'DashboardController::viewAllRequestForOffers');
$routes->get('/delete-request/(:num)', 'DashboardController::deleteRequestForHospital');
$routes->post('/accept-request-breakdown/(:num)', 'DashboardController::getBreakdownToAcceptRequest');
$routes->post('/record-accepted-request', 'DashboardController::recordAcceptedOfferForRequest');

$routes->post('/get-new-offers-for-request/(:num)', 'DashboardController::checkNewOfferFromTime');
$routes->post('/get-time', 'DashboardController::getTime');

$routes->get('/browse-activities', 'DashboardController::getAllActivitiesSaved');
$routes->get('/review-activity/(:any)/(:num)', 'DashboardController::reviewTheActivity');
$routes->get('/withdraw-offer/(:num)', 'DashboardController::withdrawRequestOffer');
$routes->get('/my-activities', 'DashboardController::hospitalBloodRequestActivites');
$routes->get('/view-delivery-information/(:num)', 'DashboardController::viewHospitalDeliveryInformation');
$routes->post('/verify-delivery-otp/(:num)', 'DashboardController::verifyDeliveryOTPInformation');

$routes->get('/wallet', 'DashboardController::walletPage');
$routes->post('/process-withdrawal-disbursement', 'DashboardController::processWithdrawalDisbursement');

$routes->get('/payment-summary/(:num)', 'PaymentController::seePaymentBreakdownSummary');
$routes->post('/create-new-request/(:num)', 'PaymentController::createNewRequestFromWhatsLeft');
$routes->post('/begin-payment-process/(:num)', 'PaymentController::fetchPaymentInformationDetails');
$routes->post('/record-payment-information/(:num)', 'PaymentController::recordTransactionPaymentInformation');

$routes->get('/browse-blood-offers/(:num)', 'DashboardController::getAllBloodOffersFromDonors');
$routes->post('/send-request-offer/(:num)', 'DashboardController::sendRequestOfferForWeb');

// Inventory
$routes->get('/inventory', 'BloodGroupStockController::index');
$routes->get('/inventory-history/(:any)', 'BloodGroupStockController::stockHistory');
$routes->post('/add-to-inventory', 'BloodGroupStockController::addToStock');

// Settings
$routes->get('/settings', 'SettingsPageController::settings');

// Blood Bank set rates
$routes->get('/settings/set-rates', 'SettingsPageController::bloodBankSetRates');
$routes->post('/settings/set-rates', 'SettingsPageController::bloodBankStoreSetRates');
$routes->get('/settings/bank-information', 'SettingsPageController::BankInformationPage');
$routes->post('/settings/bank-information', 'SettingsPageController::storeBankInformation');

$routes->get('/hospital/(:any)', 'HospitalController::index');
$routes->post('/hospital/(:any)', 'HospitalController::recordVistForHospital');

// Hospitals visitors links
$routes->get('/visitors', 'DashboardController::allHospitalVisitors');
$routes->post('/visitor/details', 'HospitalController::fetchVisitorsDetails');
$routes->post('/view-visitor-details', 'HospitalController::fetchVisitorsDetailsPreview');
$routes->post('/get-visitor-medical-details', 'HospitalController::fetchVisitorsMedicalDetails');
$routes->post('/save-visitor-record', 'HospitalController::saveVisitorMedicalRecord');
$routes->post('/save-visitor-additional-record', 'HospitalController::saveVisitorAdditionalMedicalRecord');

// Notifications
$routes->get('/notifications', 'NotificationsController::index');
$routes->get('/get-notifications', 'NotificationsController::getNotifications');
$routes->get('/set-notifications', 'NotificationsController::setNotifications');

// Logout for WebApp
$routes->get('/logout', 'AuthController::webAppLogout');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
