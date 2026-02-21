<?php


include_once ($_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'application/config/config.php');

class Bootstrap {
public function getBootstrap() {

}

public function runBootstrap() {
$this->getBootstrap(); // تشغيل الاتصال بقاعدة البيانات
}
}

// استدعاء الكلاس Bootstrap
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();


global $con;



// include file
if (!function_exists('get_file')) {
function get_file($dir) {

$filePath = $_SERVER['DOCUMENT_ROOT'] . BASEDIR . 'application/views/default/' . $dir . '.php';

if (file_exists($filePath)) {
return  $filePath;
} else {
echo "Error: File '$dir.php' not found!";
}
}
}

// Post Form
if (!function_exists('POST')) {
function POST($name) {

return $name = $_POST ["$name"];

}
}





function POST($name, $default = null, $type = 'string') {
    if (!isset($_POST[$name])) {
        return $default; // إذا لم يكن الحقل موجودًا، نعيد القيمة الافتراضية
    }

    $value = trim($_POST[$name]); // إزالة الفراغات من البداية والنهاية
    
    switch ($type) {
        case 'int': 
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : $default;
        
        case 'float': 
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float) $value : $default;
        
        case 'string': 
            return filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS); 
        
        case 'raw': 
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); // يسمح بجميع الحروف ولكن يمنع XSS
        
        default: 
            return $default;
    }
}





function SRM($method) {
    return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
}





function load_url($url, $time) {
    echo "<script>
        setTimeout(function() {
            window.location.href = '$url';
        }, $time);
    </script>";
}



global $con;





Route::set('', function() {
View::make('Root');
});


Route::set('bootstrap', function() {
View::make('bootstrap');
});







Route::set('builder', function() {
View::make('builder');
});






Route::set('savecode', function() {
View::make('savecode');
});














/*Dashboard/mobile_header*/
Route::set('mobile_header', function() {
View::make('mobile_header');
});





/*Admin/admin_header*/
Route::set('admin_header', function() {
View::make('Admin/admin_header');
});


/*Admin/admin_footer*/
Route::set('admin_footer', function() {
View::make('Admin/admin_footer');
});


/*Admin/dashboard*/
Route::set('dashboard', function() {
View::make('Admin/dashboard');
});


/*Admin/admin_nav_top*/
Route::set('admin_nav_top', function() {
View::make('Admin/admin_nav_top');
});


/*Admin/admin_nav_left*/
Route::set('admin_nav_left', function() {
View::make('Admin/admin_nav_left');
});


/*files/sql/get/os_settings*/
Route::set('os_settings', function() {
View::make('files/sql/get/os_settings');
});

 
/*files/sql/get/get_login*/
Route::set('get_login', function() {
View::make('files/sql/get/get_login');
});

 
/*files/sql/get/session*/
Route::set('session', function() {
View::make('files/sql/get/session');
});

 
/*Admin/app_settings*/
Route::set('app_settings', function() {
View::make('Admin/app_settings');
});

 
/*Admin/packages*/
Route::set('packages', function() {
View::make('Admin/packages');
});

  
/*files/sql/get/getCity*/
Route::set('getCity', function() {
View::make('files/sql/get/getCity');
});

 
/*files/sql/insert/newCity*/
Route::set('newCity', function() {
View::make('files/sql/insert/newCity');
});

 
/*files/sql/get/getBoxing*/
Route::set('getBoxing', function() {
View::make('files/sql/get/getBoxing');
});

 
/*files/sql/insert/newBoxing*/
Route::set('newBoxing', function() {
View::make('files/sql/insert/newBoxing');
});

 
/*files/sql/get/getState*/
Route::set('getState', function() {
View::make('files/sql/get/getState');
});

 
/*files/sql/insert/newState*/
Route::set('newState', function() {
View::make('files/sql/insert/newState');
});

 
/*files/sql/get/getPackage*/
Route::set('getPackage', function() {
View::make('files/sql/get/getPackage');
});

 
/*files/sql/insert/newPackage*/
Route::set('newPackage', function() {
View::make('files/sql/insert/newPackage');
});

 
/*files/sql/update/editPackage*/
Route::set('editPackage', function() {
View::make('files/sql/update/editPackage');
});

 
/*files/sql/update/editSettings*/
Route::set('editSettings', function() {
View::make('files/sql/update/editSettings');
});

 
/*files/sql/update/editCity*/
Route::set('editCity', function() {
View::make('files/sql/update/editCity');
});

 
/*files/sql/update/editState*/
Route::set('editState', function() {
View::make('files/sql/update/editState');
});

 
/*files/sql/update/editBoxing*/
Route::set('editBoxing', function() {
View::make('files/sql/update/editBoxing');
});

 
/*files/sql/get/getType*/
Route::set('getType', function() {
View::make('files/sql/get/getType');
});

 
/*files/sql/get/newType*/
Route::set('newType', function() {
View::make('files/sql/get/newType');
});

 
/*files/sql/update/editType*/
Route::set('editType', function() {
View::make('files/sql/update/editType');
});

 
/*files/sql/get/getCategory*/
Route::set('getCategory', function() {
View::make('files/sql/get/getCategory');
});

 
/*files/sql/update/editCategory*/
Route::set('editCategory', function() {
View::make('files/sql/update/editCategory');
});

 
/*files/sql/insert/newCategory*/
Route::set('newCategory', function() {
View::make('files/sql/insert/newCategory');
});

 
/*files/sql/get/getSubCategory*/
Route::set('getSubCategory', function() {
View::make('files/sql/get/getSubCategory');
});

 
/*files/sql/insert/newSubCategory*/
Route::set('newSubCategory', function() {
View::make('files/sql/insert/newSubCategory');
});

 
/*files/sql/update/editSubCategory*/
Route::set('editSubCategory', function() {
View::make('files/sql/update/editSubCategory');
});

 
/*Admin/stocks*/
Route::set('stocks', function() {
View::make('Admin/stocks');
});

 
/*files/sql/insert/newStock*/
Route::set('newStock', function() {
View::make('files/sql/insert/newStock');
});

 
/*files/sql/get/getStock*/
Route::set('getStock', function() {
View::make('files/sql/get/getStock');
});

 
/*files/sql/update/editStock*/
Route::set('editStock', function() {
View::make('files/sql/update/editStock');
});

 
/*files/sql/update/editStockImage*/
Route::set('editStockImage', function() {
View::make('files/sql/update/editStockImage');
});

 
/*files/sql/unlink/delete_image*/
Route::set('delete_image', function() {
View::make('files/sql/unlink/delete_image');
});

 
/*files/sql/update/set_main_image*/
Route::set('set_main_image', function() {
View::make('files/sql/update/set_main_image');
});

 
/*testing*/
Route::set('testing', function() {
View::make('testing');
});

 
/*files/sql/get/load_subcategories*/
Route::set('load_subcategories', function() {
View::make('files/sql/get/load_subcategories');
});

 
/*files/sql/get/load_categories*/
Route::set('load_categories', function() {
View::make('files/sql/get/load_categories');
});

 
/*files/sql/get/load_products*/
Route::set('load_products', function() {
View::make('files/sql/get/load_products');
});

 
/*connect*/
Route::set('connect', function() {
View::make('connect');
});

 
/*site_footer*/
Route::set('site_footer', function() {
View::make('site_footer');
});

 
/*mobile_menu*/
Route::set('mobile_menu', function() {
View::make('mobile_menu');
});

 
/*search_menu*/
Route::set('search_menu', function() {
View::make('search_menu');
});

 
/*site_header*/
Route::set('site_header', function() {
View::make('site_header');
});

 
/*short_by_category*/
Route::set('short_by_category', function() {
View::make('short_by_category');
});

 
/*site_slider*/
Route::set('site_slider', function() {
View::make('site_slider');
});

 
/*files/sql/insert/newSlider*/
Route::set('newSlider', function() {
View::make('files/sql/insert/newSlider');
});

 
/*files/sql/update/editSlider*/
Route::set('editSlider', function() {
View::make('files/sql/update/editSlider');
});

 
/*files/sql/insert/newSection*/
Route::set('newSection', function() {
View::make('files/sql/insert/newSection');
});

 
/*files/sql/update/editSection*/
Route::set('editSection', function() {
View::make('files/sql/update/editSection');
});

 
/*files/sql/get/getBrand*/
Route::set('getBrand', function() {
View::make('files/sql/get/getBrand');
});

 
/*files/sql/insert/newBrand*/
Route::set('newBrand', function() {
View::make('files/sql/insert/newBrand');
});

 
/*files/sql/update/editBrand*/
Route::set('editBrand', function() {
View::make('files/sql/update/editBrand');
});

 
/*Admin/sections*/
Route::set('sections', function() {
View::make('Admin/sections');
});

 
/*files/sql/get/getSection*/
Route::set('getSection', function() {
View::make('files/sql/get/getSection');
});

 
/*Admin/promotions*/
Route::set('promotions', function() {
View::make('Admin/promotions');
});

 
/*files/sql/get/getSlider*/
Route::set('getSlider', function() {
View::make('files/sql/get/getSlider');
});

 
/*files/sql/insert/add_to_section*/
Route::set('add_to_section', function() {
View::make('files/sql/insert/add_to_section');
});

 
/*files/sql/insert/add_to_slider*/
Route::set('add_to_slider', function() {
View::make('files/sql/insert/add_to_slider');
});

 
/*files/sql/unlink/delete_product_section*/
Route::set('delete_product_section', function() {
View::make('files/sql/unlink/delete_product_section');
});

 
/*files/sql/unlink/delete_product_slider*/
Route::set('delete_product_slider', function() {
View::make('files/sql/unlink/delete_product_slider');
});

 
/*files/sql/update/edit_option_group*/
Route::set('edit_option_group', function() {
View::make('files/sql/update/edit_option_group');
});

 
/*files/sql/unlink/delete_option_group*/
Route::set('delete_option_group', function() {
View::make('files/sql/unlink/delete_option_group');
});

 
/*files/sql/unlink/delete_value*/
Route::set('delete_value', function() {
View::make('files/sql/unlink/delete_value');
});

 
/*files/sql/update/update_value*/
Route::set('update_value', function() {
View::make('files/sql/update/update_value');
});

 
/*files/sql/update/update_group_name*/
Route::set('update_group_name', function() {
View::make('files/sql/update/update_group_name');
});

 
/*product_details*/
Route::set('product_details', function() {
View::make('product_details');
});

 
/*files/sql/get/filter_products*/
Route::set('filter_products', function() {
View::make('files/sql/get/filter_products');
});

 
/*files/sql/get/get_subcategories*/
Route::set('get_subcategories', function() {
View::make('files/sql/get/get_subcategories');
});

 
/*site_link*/
Route::set('site_link', function() {
View::make('site_link');
});

 
/*files/sql/insert/add_to_cart*/
Route::set('add_to_cart', function() {
View::make('files/sql/insert/add_to_cart');
});

 
/*myCart*/
Route::set('myCart', function() {
View::make('myCart');
});

 
/*files/sql/update/update_cart*/
Route::set('update_cart', function() {
View::make('files/sql/update/update_cart');
});

 
/*files/sql/unlink/remove_from_cart*/
Route::set('remove_from_cart', function() {
View::make('files/sql/unlink/remove_from_cart');
});

 
/*checkout*/
Route::set('checkout', function() {
View::make('checkout');
});

 
/*files/sql/insert/place_order*/
Route::set('place_order', function() {
View::make('files/sql/insert/place_order');
});

 
/*order_confirmation*/
Route::set('order_confirmation', function() {
View::make('order_confirmation');
});

 
/*shop*/
Route::set('shop', function() {
View::make('shop');
});

 
/*files/sql/get/fetch_categories*/
Route::set('fetch_categories', function() {
View::make('files/sql/get/fetch_categories');
});

 
/*files/sql/get/fetch_brands*/
Route::set('fetch_brands', function() {
View::make('files/sql/get/fetch_brands');
});

 
/*files/sql/get/fetch_products*/
Route::set('fetch_products', function() {
View::make('files/sql/get/fetch_products');
});

 
/*files/sql/get/fetch_subcategories*/
Route::set('fetch_subcategories', function() {
View::make('files/sql/get/fetch_subcategories');
});

 
/*files/sql/unlink/dataUnlink*/
Route::set('dataUnlink', function() {
View::make('files/sql/unlink/dataUnlink');
});

 
/*icart*/
Route::set('icart', function() {
View::make('icart');
});

 


/*App/tee*/
Route::set('tee', function() {
View::make('App/tee');
});
 
/*Admin/users*/
Route::set('users', function() {
View::make('Admin/users');
});

 
/*files/sql/get/getUser*/
Route::set('getUser', function() {
View::make('files/sql/get/getUser');
});

 
/*files/sql/update/editPassword*/
Route::set('editPassword', function() {
View::make('files/sql/update/editPassword');
});

 
/*files/sql/update/saveUserDocuments*/
Route::set('saveUserDocuments', function() {
View::make('files/sql/update/saveUserDocuments');
});

 
/*files/sql/update/editUser*/
Route::set('editUser', function() {
View::make('files/sql/update/editUser');
});

 
/*files/sql/insert/newUser*/
Route::set('newUser', function() {
View::make('files/sql/insert/newUser');
});

 
/*files/sql/get/search_stock*/
Route::set('search_stock', function() {
View::make('files/sql/get/search_stock');
});

 
/*files/sql/insert/new_stock_package*/
Route::set('new_stock_package', function() {
View::make('files/sql/insert/new_stock_package');
});

 
/*files/sql/insert/add_to_package*/
Route::set('add_to_package', function() {
View::make('files/sql/insert/add_to_package');
});

 
/*files/sql/get/get_states_package*/
Route::set('get_states_package', function() {
View::make('files/sql/get/get_states_package');
});

 
/*files/sql/update/update_order_state*/
Route::set('update_order_state', function() {
View::make('files/sql/update/update_order_state');
});

 
/*files/sql/update/config_orders*/
Route::set('config_orders', function() {
View::make('files/sql/update/config_orders');
});

 
/*files/sql/get/print_sticker*/
Route::set('print_sticker', function() {
View::make('files/sql/get/print_sticker');
});

 
/*Admin/shipping*/
Route::set('shipping', function() {
View::make('Admin/shipping');
});

 
/*Admin/agency*/
Route::set('agency', function() {
View::make('Admin/agency');
});

 
/*files/sql/insert/new_agency*/
Route::set('new_agency', function() {
View::make('files/sql/insert/new_agency');
});

 
/*files/sql/update/edit_agency*/
Route::set('edit_agency', function() {
View::make('files/sql/update/edit_agency');
});

 
/*files/sql/get/get_agency*/
Route::set('get_agency', function() {
View::make('files/sql/get/get_agency');
});

 
/*files/sql/insert/newShipping*/
Route::set('newShipping', function() {
View::make('files/sql/insert/newShipping');
});

 
/*files/sql/get/getShipping*/
Route::set('getShipping', function() {
View::make('files/sql/get/getShipping');
});

 
/*Admin/pricing*/
Route::set('pricing', function() {
View::make('Admin/pricing');
});

 

 
/*files/sql/unlink/tarifs_delete*/
Route::set('tarifs_delete', function() {
View::make('files/sql/unlink/tarifs_delete');
});

 
/*files/sql/update/tarifs_update*/
Route::set('tarifs_update', function() {
View::make('files/sql/update/tarifs_update');
});

 
/*files/sql/get/gp*/
Route::set('gp', function() {
View::make('files/sql/get/gp');
});

 
/*files/sql/get/getGb*/
Route::set('getGb', function() {
View::make('files/sql/get/getGb');
});

 
/*files/sql/insert/new_tarif*/
Route::set('new_tarif', function() {
View::make('files/sql/insert/new_tarif');
});

 
/*files/sql/get/getDp*/
Route::set('getDp', function() {
View::make('files/sql/get/getDp');
});

 
/*files/sql/get/dp*/
Route::set('dp', function() {
View::make('files/sql/get/dp');
});

 
/*files/sql/insert/new_tarif_delivery*/
Route::set('new_tarif_delivery', function() {
View::make('files/sql/insert/new_tarif_delivery');
});

 
/*files/sql/get/upl*/
Route::set('upl', function() {
View::make('files/sql/get/upl');
});

 
/*files/sql/insert/new_tarif_user*/
Route::set('new_tarif_user', function() {
View::make('files/sql/insert/new_tarif_user');
});

 
/*Admin/invoice*/
Route::set('invoice', function() {
View::make('Admin/invoice');
});

 
/*files/sql/insert/newInvoice*/
Route::set('newInvoice', function() {
View::make('files/sql/insert/newInvoice');
});

 
/*files/sql/get/print_invoice*/
Route::set('print_invoice', function() {
View::make('files/sql/get/print_invoice');
});

 
/*files/sql/get/getInvoice*/
Route::set('getInvoice', function() {
View::make('files/sql/get/getInvoice');
});

 
/*files/sql/insert/newDeliveryInvoice*/
Route::set('newDeliveryInvoice', function() {
View::make('files/sql/insert/newDeliveryInvoice');
});

 
/*files/sql/get/getDeliveryInvoice*/
Route::set('getDeliveryInvoice', function() {
View::make('files/sql/get/getDeliveryInvoice');
});

 
/*files/sql/get/print_delivery_invoice*/
Route::set('print_delivery_invoice', function() {
View::make('files/sql/get/print_delivery_invoice');
});

 
/*Admin/deliveryInvoice*/
Route::set('deliveryInvoice', function() {
View::make('Admin/deliveryInvoice');
});

 
/*Admin/login_account*/
Route::set('login_account', function() {
View::make('Admin/login_account');
});

 
/*files/sql/update/dataUpdate*/
Route::set('dataUpdate', function() {
View::make('files/sql/update/dataUpdate');
});

 
/*files/sql/insert/newUserPricing*/
Route::set('newUserPricing', function() {
View::make('files/sql/insert/newUserPricing');
});

 
/*Admin/scan*/
Route::set('scan', function() {
View::make('Admin/scan');
});

 
/*files/sql/get/check_code*/
Route::set('check_code', function() {
View::make('files/sql/get/check_code');
});

 
/*files/sql/unlink/empty_scan*/
Route::set('empty_scan', function() {
View::make('files/sql/unlink/empty_scan');
});

 
/*Admin/scan_by_state*/
Route::set('scan_by_state', function() {
View::make('Admin/scan_by_state');
});

 
/*files/sql/get/check_code_state*/
Route::set('check_code_state', function() {
View::make('files/sql/get/check_code_state');
});

 
/*Admin/scan_by_delivery*/
Route::set('scan_by_delivery', function() {
View::make('Admin/scan_by_delivery');
});

 
/*files/sql/get/check_code_delivery*/
Route::set('check_code_delivery', function() {
View::make('files/sql/get/check_code_delivery');
});

 
/*files/sql/get/check_exp*/
Route::set('check_exp', function() {
View::make('files/sql/get/check_exp');
});

 
/*Admin/import*/
Route::set('import', function() {
View::make('Admin/import');
});

 
/*files/sql/update/save_data*/
Route::set('save_data', function() {
View::make('files/sql/update/save_data');
});

 
/*files/sql/get/admin_dashboard*/
Route::set('admin_dashboard', function() {
View::make('files/sql/get/admin_dashboard');
});

 
/*files/sql/get/user_dashboard*/
Route::set('user_dashboard', function() {
View::make('files/sql/get/user_dashboard');
});

 
/*Admin/a_nav_left*/
Route::set('a_nav_left', function() {
View::make('Admin/a_nav_left');
});

 
/*Admin/u_nav_left*/
Route::set('u_nav_left', function() {
View::make('Admin/u_nav_left');
});

 
/*Admin/d_nav_left*/
Route::set('d_nav_left', function() {
View::make('Admin/d_nav_left');
});

 
/*Admin/logout*/
Route::set('logout', function() {
View::make('Admin/logout');
});

 
/*files/sql/get/delivery_dashboard*/
Route::set('delivery_dashboard', function() {
View::make('files/sql/get/delivery_dashboard');
});

 
/*Admin/logs_data*/
Route::set('logs_data', function() {
View::make('Admin/logs_data');
});

 
/*Admin/pickup*/
Route::set('pickup', function() {
View::make('Admin/pickup');
});

 
/*files/sql/insert/newLog*/
Route::set('newLog', function() {
View::make('files/sql/insert/newLog');
});

 
/*files/sql/get/getLogs*/
Route::set('getLogs', function() {
View::make('files/sql/get/getLogs');
});

 
/*files/sql/get/print_log*/
Route::set('print_log', function() {
View::make('files/sql/get/print_log');
});

 
/*Admin/outLogUser*/
Route::set('outLogUser', function() {
View::make('Admin/outLogUser');
});

 
/*Admin/outLogDelivery*/
Route::set('outLogDelivery', function() {
View::make('Admin/outLogDelivery');
});

 
/*files/sql/update/updateLog*/
Route::set('updateLog', function() {
View::make('files/sql/update/updateLog');
});

 
/*files/sql/get/screen*/
Route::set('screen', function() {
View::make('files/sql/get/screen');
});

 
/*Admin/register*/
Route::set('register', function() {
View::make('Admin/register');
});

 
/*files/sql/insert/new_account*/
Route::set('new_account', function() {
View::make('files/sql/insert/new_account');
});

 
/*Admin/claim*/
Route::set('claim', function() {
View::make('Admin/claim');
});

 
/*files/sql/get/getClaim*/
Route::set('getClaim', function() {
View::make('files/sql/get/getClaim');
});

 
/*files/sql/insert/newClaim*/
Route::set('newClaim', function() {
View::make('files/sql/insert/newClaim');
});

 
/*Admin/pickup_client*/
Route::set('pickup_client', function() {
View::make('Admin/pickup_client');
});

 
/*files/sql/get/getPickupClient*/
Route::set('getPickupClient', function() {
View::make('files/sql/get/getPickupClient');
});

 
/*files/sql/insert/new_pickup_client*/
Route::set('new_pickup_client', function() {
View::make('files/sql/insert/new_pickup_client');
});

 
/*files/sql/update/editPermission*/
Route::set('editPermission', function() {
View::make('files/sql/update/editPermission');
});

 
/*files/sql/update/updateClaim*/
Route::set('updateClaim', function() {
View::make('files/sql/update/updateClaim');
});

 
/*files/sql/get/get_shipping*/
Route::set('get_shipping', function() {
View::make('files/sql/get/get_shipping');
});

 
/*Admin/staffs*/
Route::set('staffs', function() {
View::make('Admin/staffs');
});

 
/*files/sql/insert/newUserAide*/
Route::set('newUserAide', function() {
View::make('files/sql/insert/newUserAide');
});

 
/*files/sql/get/get_staffs*/
Route::set('get_staffs', function() {
View::make('files/sql/get/get_staffs');
});

 
/*Admin/s_nav_left*/
Route::set('s_nav_left', function() {
View::make('Admin/s_nav_left');
});

 
/*files/sql/get/aide_dashboard*/
Route::set('aide_dashboard', function() {
View::make('files/sql/get/aide_dashboard');
});

 
/*files/sql/insert/newNews*/
Route::set('newNews', function() {
View::make('files/sql/insert/newNews');
});

 
/*files/sql/get/getNews*/
Route::set('getNews', function() {
View::make('files/sql/get/getNews');
});

 
/*files/sql/update/editNews*/
Route::set('editNews', function() {
View::make('files/sql/update/editNews');
});

 
/*files/sql/get/user_alert*/
Route::set('user_alert', function() {
View::make('files/sql/get/user_alert');
});

 
/*files/sql/get/delivery_alert*/
Route::set('delivery_alert', function() {
View::make('files/sql/get/delivery_alert');
});

 
/*files/sql/get/aide_alert*/
Route::set('aide_alert', function() {
View::make('files/sql/get/aide_alert');
});

 
/*files/sql/get/get_export*/
Route::set('get_export', function() {
View::make('files/sql/get/get_export');
});

 
/*files/sql/get/config_pickup*/
Route::set('config_pickup', function() {
View::make('files/sql/get/config_pickup');
});

 
/*files/sql/update/pickup_state*/
Route::set('pickup_state', function() {
View::make('files/sql/update/pickup_state');
});

 
/*files/sql/get/package_export*/
Route::set('package_export', function() {
View::make('files/sql/get/package_export');
});

 
/*files/sql/get/exp_log*/
Route::set('exp_log', function() {
View::make('files/sql/get/exp_log');
});

 
/*files/sql/get/testu*/
Route::set('testu', function() {
View::make('files/sql/get/testu');
});

 
/*files/sql/update/change_location*/
Route::set('change_location', function() {
View::make('files/sql/update/change_location');
});

 
/*files/sql/get/card_order*/
Route::set('card_order', function() {
View::make('files/sql/get/card_order');
});

 
/*Admin/data_boxing*/
Route::set('data_boxing', function() {
View::make('Admin/data_boxing');
});

 
/*files/sql/get/get_boxing*/
Route::set('get_boxing', function() {
View::make('files/sql/get/get_boxing');
});

 
/*files/sql/get/fetch_orders*/
Route::set('fetch_orders', function() {
View::make('files/sql/get/fetch_orders');
});

 
/*files/sql/get/add_package_api*/
Route::set('add_package_api', function() {
View::make('files/sql/get/add_package_api');
});

 
/*files/sql/get/change_order_state_api*/
Route::set('change_order_state_api', function() {
View::make('files/sql/get/change_order_state_api');
});

 
/*files/sql/get/api_config*/
Route::set('api_config', function() {
View::make('files/sql/get/api_config');
});

 
/*Admin/api_doc*/
Route::set('api_doc', function() {
View::make('Admin/api_doc');
});

 
/*files/sql/get/ps*/
Route::set('ps', function() {
View::make('files/sql/get/ps');
});

 
/*Admin/api_list*/
Route::set('api_list', function() {
View::make('Admin/api_list');
});

 
/*files/sql/get/get_price_site*/
Route::set('get_price_site', function() {
View::make('files/sql/get/get_price_site');
});

 
/*files/sql/get/get_shipping_price*/
Route::set('get_shipping_price', function() {
View::make('files/sql/get/get_shipping_price');
});

 
/*files/sql/get/tracking_fetch*/
Route::set('tracking_fetch', function() {
View::make('files/sql/get/tracking_fetch');
});

 
/*files/sql/get/get_bank_name*/
Route::set('get_bank_name', function() {
View::make('files/sql/get/get_bank_name');
});

 
/*p_sticker*/
Route::set('p_sticker', function() {
View::make('p_sticker');
});

 
/*files/sql/get/sticker_small*/
Route::set('sticker_small', function() {
View::make('files/sql/get/sticker_small');
});

 
/*files/sql/get/sticker_papper*/
Route::set('sticker_papper', function() {
View::make('files/sql/get/sticker_papper');
});

 
/*files/sql/update/update_state_stock*/
Route::set('update_state_stock', function() {
View::make('files/sql/update/update_state_stock');
});

 
/*Dashboard/app_start*/
Route::set('app_start', function() {
View::make('App/app_start');
});

 
/*files/sql/update/change_fee*/
Route::set('change_fee', function() {
View::make('files/sql/update/change_fee');
});

 
/*Admin/expenses*/
Route::set('expenses', function() {
View::make('Admin/expenses');
});

 
/*files/sql/get/expenses_action*/
Route::set('expenses_action', function() {
View::make('files/sql/get/expenses_action');
});

 
/*files/sql/update/r_stock*/
Route::set('r_stock', function() {
View::make('files/sql/update/r_stock');
});

 
/*files/sql/update/edit_state_rank*/
Route::set('edit_state_rank', function() {
View::make('files/sql/update/edit_state_rank');
});

