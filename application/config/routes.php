<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//ADD YOUR ROUTES
// $route['api/master/user_all'] = 'Master/user_all';

// GET
$route['api/master/role-list'] = 'Api/roleList';
$route['api/master/admin-list'] = 'Api/adminList';

// POST
$route['api/master/question'] = 'Api/getQuestion';
$route['api/master/register-peserta'] = 'Api/registerPeserta';
$route['api/master/verif-peserta'] = 'Api/verifPeserta';
$route['api/master/question-semboyan'] = 'Api/questionSemboyan';

$route['api/master/nilai-juri'] = 'Api/penilaianJuri';
$route['api/master/summary'] = 'Api/submitPupukRecap';


$route['api/master/insert-juri'] = 'Api/juriMaster';
$route['api/master/auth'] = 'Api/login';
$route['api/master/auth-panitia'] = 'Api/loginPanitia';
$route['api/master/ins-session'] = 'Api/createPinSession';
$route['api/master/session'] = 'Api/sessionList';
$route['api/master/update-session'] = 'Api/sessionUpdate';
$route['api/master/submit'] = 'Api/insertJawabanPupuk';
$route['api/master/create-sandi'] = 'Api/createSoalSandi';
$route['api/master/insert-account'] = 'Api/insertAccount';
$route['api/master/update-account'] = 'Api/updateAccountPanitia';
$route['api/master/update-password'] = 'Api/updatePassword';
$route['api/master/ins-puk'] = 'Api/upSoalPupuk';
$route['api/master/create-semboyan'] = 'Api/createSoalSemboyan';
$route['api/master/jawaban-pupuk'] = 'Api/jawabanPupukToRekap';