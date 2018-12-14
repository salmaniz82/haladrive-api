<?php ob_start(); session_start();
require_once 'framework/mvc.class.php';
$route = new Route();


$route->get('/', function() {

	$data = "Welcome to Haladrive API";
	view::responseJson($data, 200);
	
});


$route->get('/jwt/check', 'jwtauthCtrl@check');

$route->post('/jwt/login', 'jwtauthCtrl@login');

$route->post('/jwt/register', 'jwtauthCtrl@register');

$route->post('/jwt/consumer-register', 'jwtauthCtrl@consumerRegister');


$route->post('/api/consumer/register', 'userCtrl@clientRegister');

$route->get('/jwt/validate', 'jwtauthCtrl@validateToken');

$route->get('/jwt/admin', 'jwtauthCtrl@adminOnlyProtected');


$route->get('/checkuser', 'testCtrl@checkUserInfo');


$route->get('/features', 'featuresCtrl@index');

$route->get('/features/{id}', 'featuresCtrl@single');

$route->post('/features', 'featuresCtrl@save');

$route->put('/features/{id}', 'featuresCtrl@update');

$route->delete('features/{id}', 'featuresCtrl@delete');





$route->get('/api/clients', 'clientsCtrl@index');

$route->get('/api/clients/civilno', 'clientsCtrl@byCivilNo');

$route->get('/api/clients/{id}', 'clientsCtrl@single');

$route->post('/api/clients', 'clientsCtrl@save');

$route->put('/api/clients/{id}', 'clientsCtrl@update');

$route->delete('/api/clients/{id}',	'clientsCtrl@delete');

$route->post('/api/vclients', 'vclientsCtrl@save');




$route->get('/api/finance', 		'financeCtrl@index');

$route->get('/api/finance/{id}', 	'financeCtrl@single');

$route->post('/api/finance', 		'financeCtrl@save');

$route->put('/api/finance/{id}', 	'financeCtrl@update');

$route->delete('/api/finance/{id}',	'financeCtrl@delete');




$route->get('/api/insurance', 			'insuranceCtrl@index');

$route->get('/api/insurance/{id}', 		'insuranceCtrl@single');

$route->post('/api/insurance', 			'insuranceCtrl@save');

$route->put('/api/insurance/{id}', 		'insuranceCtrl@update');

$route->delete('/api/insurance/{id}',	'insuranceCtrl@delete');





$route->get('/api/global', 'globalCtrl@index');

$route->get('/api/global/{id}', 'globalCtrl@single');

$route->post('/api/global', 'globalCtrl@save');

$route->put('/api/global/{id}', 'globalCtrl@update');

$route->delete('/api/global/{id}', 'globalCtrl@delete');




$route->get('/api/gsection', 'gsectionCtrl@index');

$route->get('/api/gsection/{id}', 'gsectionCtrl@single');

$route->get('/api/gsection/slug/{slug}', 'gsectionCtrl@bySlug');

$route->post('/api/gsection', 'gsectionCtrl@save');

$route->put('/api/gsection/{id}', 'gsectionCtrl@update');

$route->delete('/api/gsection/{id}', 'gsectionCtrl@delete');

$route->get('/api/gval', 'helperCtrl@index');

$route->get('/api/filters', 'helperCtrl@filters');

$route->get('/api/globaljson', 'helperCtrl@loadGlobalJson');






$route->get('/api/gbrands', 'brandCtrl@index');

$route->get('/api/gbrands/{id}', 'brandCtrl@collection');

$route->post('/api/gbrands', 'brandCtrl@save');

$route->delete('/api/gbrands/{id}', 'brandCtrl@delete');

$route->put('/api/gbrands/{id}', 'brandCtrl@update');



$route->get('/api/vehicles', 'vehiclesCtrl@index');

$route->get('/api/vehicles/q/{params}', 'testCtrl@vehicleQuery');

$route->get('/api/vehicles/b/{params}', 'testCtrl@vehicleQueryArray');

$route->get('/api/vehicles/available/{id}', 'vehiclesCtrl@is_available'); 

$route->get('/api/vehicles/{id}', 'vehiclesCtrl@single');

$route->post('/api/vehicles', 'vehiclesCtrl@save');

$route->post('/api/vehicles/uploadslides/{vehicle_id}', 'vehiclesCtrl@manageSlides');


$route->post('/api/vehicles/d', 'vehiclesCtrl@saved');

$route->put('/api/vehicles/{id}', 'vehiclesCtrl@update');

$route->delete('/api/vehicles/{id}', 'vehiclesCtrl@delete');




$route->get('/api/booking', 'bookingCtrl@index');

$route->get('/api/booking/{id}', 'bookingCtrl@single');

$route->put('/api/booking/{id}', 'bookingCtrl@update');

$route->get('/api/bookingcross/{id}', 'bookingCtrl@crossFire');

$route->post('/api/booking', 'bookingCtrl@commonBookingGateway');

$route->delete('/api/booking/{id}', 'bookingCtrl@delete');




$route->get('/api/invoice', 'invoiceCtrl@index');

$route->delete('/api/invoice/{id}', 'invoiceCtrl@delete');




$route->get('/api/vinsurance/{id}', 'vInsuranceCtrl@index');

$route->post('/api/vinsurance', 'vInsuranceCtrl@save');

$route->put('/api/vinsurance/{id}', 'vInsuranceCtrl@update');

$route->delete('/api/vinsurance/{id}', 'vInsuranceCtrl@delete');




$route->get('/api/vfinance/{id}', 'vFinanceCtrl@index');

$route->post('/api/vfinance', 'vFinanceCtrl@save');

$route->put('/api/vfinance/{id}', 'vFinanceCtrl@update');

$route->delete('/api/vfinance/{id}', 'vFinanceCtrl@delete');




$route->get('/api/maintain/{id}', 'maintenanceCtrl@index');

$route->post('/api/maintain', 'maintenanceCtrl@save');

$route->put('/api/maintain/{id}', 'maintenanceCtrl@update');

$route->delete('/api/maintain/{id}', 'maintenanceCtrl@delete');

$route->get('/faker/vehicles/{records}', 'fakerCtrl@vehicleMassInsert');

$route->get('/faker/options/{start_id}/{end_id}', 'fakerCtrl@optionsMassAttachment');


$route->otherwise( function() {

    $data['message'] = 'Request Not found';
    View::responseJson($data, 404);

});