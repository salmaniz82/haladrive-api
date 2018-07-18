<?php ob_start(); session_start();
require_once 'framework/mvc.class.php';
$route = new Route();


$route->get('/', function() {

	$data = "Welcome to Haladrive API";
	view::responseJson($data, 200);
});



// JWT AUTHENTICATION CHECKING

$route->get('/jwt/check', 'jwtauthCtrl@check');
// for testing only
$route->post('/jwt/login', 'jwtauthCtrl@login');

$route->get('/jwt/validate', 'jwtauthCtrl@validateToken');

$route->get('/jwt/admin', 'jwtauthCtrl@adminOnlyProtected');


// LANGUAGE TESTING
$route->get('/lang', 'langCtrl@listall');

$route->get('/lang/add','langCtrl@addInterface');

$route->post('/lang/add','langCtrl@save');

$route->post('/lang/debug','langCtrl@debugpost');

$route->put('/lang/{id}', 'langCtrl@update');

$route->put('/lang/test', 'langCtrl@test');



// VALIDATION TESTING
$route->get('/features','featuresCtrl@index');

$route->post('/features','featuresCtrl@save');

$route->get('/features/{id}','featuresCtrl@single');

$route->put('/features/{id}','featuresCtrl@update');

$route->delete('/features/{id}','featuresCtrl@delete');



/*CLIENTS API */
$route->get('/api/clients', 'clientsCtrl@index');

$route->get('/api/clients/civilno', 'clientsCtrl@byCivilNo');

$route->get('/api/clients/{id}', 'clientsCtrl@single');

$route->post('/api/clients', 'clientsCtrl@save');

$route->put('/api/clients/{id}', 'clientsCtrl@update');

$route->delete('/api/clients/{id}',	'clientsCtrl@delete');

// vendor clients
$route->post('/api/vclients', 'vclientsCtrl@save');


/* Finance API */
$route->get('/api/finance', 		'financeCtrl@index');

$route->get('/api/finance/{id}', 	'financeCtrl@single');

$route->post('/api/finance', 		'financeCtrl@save');

$route->put('/api/finance/{id}', 	'financeCtrl@update');

$route->delete('/api/finance/{id}',	'financeCtrl@delete');



/* Insurance API */
$route->get('/api/insurance', 			'insuranceCtrl@index');

$route->get('/api/insurance/{id}', 		'insuranceCtrl@single');

$route->post('/api/insurance', 			'insuranceCtrl@save');

$route->put('/api/insurance/{id}', 		'insuranceCtrl@update');

$route->delete('/api/insurance/{id}',	'insuranceCtrl@delete');



/* Global Values */

$route->get('/api/global', 'globalCtrl@index');

$route->get('/api/global/{id}', 'globalCtrl@single');

$route->post('/api/global', 'globalCtrl@save');

$route->put('/api/global/{id}', 'globalCtrl@update');

$route->delete('/api/global/{id}', 'globalCtrl@delete');



/* Global Section */
$route->get('/api/gsection', 'gsectionCtrl@index');

$route->get('/api/gsection/{id}', 'gsectionCtrl@single');

$route->get('/api/gsection/slug/{slug}', 'gsectionCtrl@bySlug');

$route->post('/api/gsection', 'gsectionCtrl@save');

$route->put('/api/gsection/{id}', 'gsectionCtrl@update');

$route->delete('/api/gsection/{id}', 'gsectionCtrl@delete');

$route->get('/api/gval', 'helperCtrl@index');

$route->get('/api/globaljson', 'helperCtrl@loadGlobalJson');


// brand models

$route->get('/api/gbrands', 'brandCtrl@index');

$route->get('/api/gbrands/{id}', 'brandCtrl@collection');

$route->post('/api/gbrands', 'brandCtrl@save');

$route->delete('/api/gbrands/{id}', 'brandCtrl@delete');

$route->put('/api/gbrands/{id}', 'brandCtrl@update');


/*vehicles controllers*/
$route->get('/api/vehicles', 'vehiclesCtrl@index');

$route->get('/api/vehicles/q/{params}', 'testCtrl@vehicleQuery');

$route->get('/api/vehicles/available/{id}', 'vehiclesCtrl@is_available'); 

$route->get('/api/vehicles/{id}', 'vehiclesCtrl@single');

$route->post('/api/vehicles', 'vehiclesCtrl@save');

$route->post('/api/vehicles/uploadslides/{vehicle_id}', 'vehiclesCtrl@manageSlides');


$route->post('/api/vehicles/d', 'vehiclesCtrl@saved');

$route->put('/api/vehicles/{id}', 'vehiclesCtrl@update');

$route->delete('/api/vehicles/{id}', 'vehiclesCtrl@delete');


/* BOOKINGS CONTROLLERS */

$route->get('/api/booking', 'bookingCtrl@index');

$route->get('/api/booking/{id}', 'bookingCtrl@single');

$route->put('/api/booking/{id}', 'bookingCtrl@update');

$route->get('/api/bookingcross/{id}', 'bookingCtrl@crossFire');

$route->post('/api/booking', 'bookingCtrl@save');

$route->delete('/api/booking/{id}', 'bookingCtrl@delete');



// INVOICE

$route->get('/api/invoice', 'invoiceCtrl@index');

$route->delete('/api/invoice/{id}', 'invoiceCtrl@delete');



// VEHICLE INSURACEN POLICIES
$route->get('/api/vinsurance/{id}', 'vInsuranceCtrl@index');

$route->post('/api/vinsurance', 'vInsuranceCtrl@save');

$route->put('/api/vinsurance/{id}', 'vInsuranceCtrl@update');

$route->delete('/api/vinsurance/{id}', 'vInsuranceCtrl@delete');


// vehicle finance

$route->get('/api/vfinance/{id}', 'vFinanceCtrl@index');

$route->post('/api/vfinance', 'vFinanceCtrl@save');

$route->put('/api/vfinance/{id}', 'vFinanceCtrl@update');

$route->delete('/api/vfinance/{id}', 'vFinanceCtrl@delete');



// testing route
$route->get('/checkuser', 'testCtrl@checkUserInfo');


// maintenance
$route->get('/api/maintain/{id}', 'maintenanceCtrl@index');

$route->post('/api/maintain', 'maintenanceCtrl@save');

$route->put('/api/maintain/{id}', 'maintenanceCtrl@update');

$route->delete('/api/maintain/{id}', 'maintenanceCtrl@delete');

$route->otherwise( function() {

    $data['message'] = 'Request Not found';
    View::responseJson($data, 404);

});