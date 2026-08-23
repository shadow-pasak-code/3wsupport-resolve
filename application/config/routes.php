<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Auth (ทุก role ใช้ controller เดียวกัน)
|--------------------------------------------------------------------------
*/
$route['login']  = 'admin/auth/login';
$route['logout'] = 'admin/auth/logout';

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

$route['admin/dashboard']        = 'admin/dashboard/index';
$route['admin/dashboard/report'] = 'admin/dashboard/report';

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
$route['admin']                          = 'admin/dashboard/index';
$route['admin/dashboard']                = 'admin/dashboard/index';
$route['admin/tickets']                   = 'admin/tickets/index';
$route['admin/tickets/detail/(:num)']     = 'admin/tickets/detail/$1';
$route['admin/tickets/approve/(:num)']    = 'admin/tickets/approve/$1';
$route['admin/tickets/reject/(:num)']     = 'admin/tickets/reject/$1';
$route['admin/tickets/assign/(:num)']     = 'admin/tickets/assign/$1';
$route['admin/tickets/send_quote/(:num)'] = 'admin/tickets/send_quote/$1';
$route['admin/tickets/close/(:num)']      = 'admin/tickets/close/$1';

$route['admin/tickets/quotation/(:num)']      = 'admin/tickets/quotation/$1';
$route['admin/tickets/save_quotation/(:num)'] = 'admin/tickets/save_quotation/$1';
$route['quotation/partner/(:num)'] = 'quotation/partner/$1';

$route['admin/devices']                  = 'admin/devices/index';
$route['admin/devices/add']              = 'admin/devices/add';
$route['admin/devices/edit/(:num)']      = 'admin/devices/edit/$1';
$route['admin/devices/delete/(:num)']    = 'admin/devices/delete/$1';
$route['admin/technicians']              = 'admin/technicians/index';
$route['admin/technicians/add']          = 'admin/technicians/add';
$route['admin/technicians/edit/(:num)']  = 'admin/technicians/edit/$1';
$route['admin/technicians/delete/(:num)']= 'admin/technicians/delete/$1';
$route['admin/partners']                 = 'admin/partners/index';
$route['admin/partners/add']             = 'admin/partners/add';
$route['admin/partners/edit/(:num)']     = 'admin/partners/edit/$1';
$route['admin/partners/delete/(:num)']   = 'admin/partners/delete/$1';
$route['admin/repair_categories']                     = 'admin/repair_categories/index';
$route['admin/repair_categories/add']                 = 'admin/repair_categories/add';
$route['admin/repair_categories/edit/(:num)']         = 'admin/repair_categories/edit/$1';
$route['admin/repair_categories/toggle_active/(:num)']= 'admin/repair_categories/toggle_active/$1';
$route['admin/faq']                      = 'admin/faq/index';
$route['admin/faq/add']                  = 'admin/faq/add';
$route['admin/faq/edit/(:num)']          = 'admin/faq/edit/$1';
$route['admin/customers']                = 'admin/customers/index';
$route['admin/customers/add']            = 'admin/customers/add';
$route['admin/customers/edit/(:num)']    = 'admin/customers/edit/$1';
$route['admin/customers/delete/(:num)']  = 'admin/customers/delete/$1';
/*
|--------------------------------------------------------------------------
| Technician Routes
|--------------------------------------------------------------------------
*/
$route['tech']                            = 'technician/tickets/index';
$route['tech/tickets']                    = 'technician/tickets/index';
$route['tech/tickets/detail/(:num)']      = 'technician/tickets/detail/$1';
$route['tech/tickets/accept/(:num)']      = 'technician/tickets/accept/$1';
$route['tech/tickets/complete/(:num)']    = 'technician/tickets/complete/$1';
$route['tech/tickets/escalate/(:num)']    = 'technician/tickets/escalate/$1';
$route['tech/tickets/update_date/(:num)'] = 'technician/tickets/update_date/$1';
$route['tech/tickets/send_update/(:num)'] = 'technician/tickets/send_update/$1';
$route['tech/tickets/start_repair/(:num)'] = 'technician/tickets/start_repair/$1';
$route['tech/tickets/quote/(:num)'] = 'technician/tickets/quote/$1';

/*
|--------------------------------------------------------------------------
| Partner Routes
|--------------------------------------------------------------------------
*/
$route['partner']                        = 'partner/tickets/index';
$route['partner/tickets']                = 'partner/tickets/index';
$route['partner/tickets/detail/(:num)']  = 'partner/tickets/detail/$1';
$route['partner/tickets/quote/(:num)']   = 'partner/tickets/quote/$1';
$route['partner/tickets/complete/(:num)']= 'partner/tickets/complete/$1';
$route['partner/tickets/accept/(:num)']      = 'partner/tickets/accept/$1';
$route['partner/tickets/update_date/(:num)'] = 'partner/tickets/update_date/$1';
$route['partner/tickets/escalate/(:num)']    = 'partner/tickets/escalate/$1';
/*
|--------------------------------------------------------------------------
| Line Webhook (POST จาก LINE server)
|--------------------------------------------------------------------------
*/
$route['webhook/line'] = 'api/line_webhook/handle';
/*
|--------------------------------------------------------------------------
| Manage Profile
|--------------------------------------------------------------------------
*/
$route['admin/profile']          = 'admin/profile/index';
$route['admin/profile/update']   = 'admin/profile/update';
$route['tech/profile']           = 'technician/profile/index';
$route['tech/profile/update']    = 'technician/profile/update';
$route['partner/profile']        = 'partner/profile/index';
$route['partner/profile/update'] = 'partner/profile/update';
/*
|--------------------------------------------------------------------------
| Manage Quotation
|--------------------------------------------------------------------------
*/
$route['admin/quotation/(:num)'] = 'admin/quotation/view/$1';
$route['quotation/view/(:num)'] = 'quotation/view/$1';
/*
|--------------------------------------------------------------------------
| Manage Customers
|--------------------------------------------------------------------------
*/
$route['admin/customers/reset_line/(:num)'] = 'admin/customers/reset_line/$1';
$route['admin/devices/check_sn'] = 'admin/devices/check_sn';
/*
|--------------------------------------------------------------------------
| Manage Equipment
|--------------------------------------------------------------------------
*/
$route['admin/equipment']                = 'admin/equipment/index';
$route['admin/equipment/add']            = 'admin/equipment/add';
$route['admin/equipment/edit/(:num)']    = 'admin/equipment/edit/$1';
$route['admin/equipment/delete/(:num)']  = 'admin/equipment/delete/$1';
$route['admin/equipment/get_list']       = 'admin/equipment/get_list';
/*
|--------------------------------------------------------------------------
| Manage History
|--------------------------------------------------------------------------
*/
$route['admin/history']      = 'admin/history/index';
$route['admin/tickets/modal/(:num)'] = 'admin/tickets/modal/$1';
$route['tech/history'] = 'technician/history/index';
/*
|--------------------------------------------------------------------------
| Manage Notify
|--------------------------------------------------------------------------
*/
$route['admin/tickets/notify_complete/(:num)'] = 'admin/tickets/notify_complete/$1';

