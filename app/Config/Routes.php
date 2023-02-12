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
//Cors
$routes->options('(:any)', 'CorsOptions::index');
$routes->options('(:any)/(:any)', 'CorsOptions::index');
$routes->options('(:any)/(:any)/(:any)', 'CorsOptions::index');
$routes->options('(:any)/(:any)/(:any)/(:any)', 'CorsOptions::index');
$routes->options('(:any)/(:any)/(:any)/(:any)/(:any)', 'CorsOptions::index');
$routes->options('(:any)/(:any)/(:any)/(:any)/(:any)/(:any)', 'CorsOptions::index');

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/ctl/cancelWaitTrade', 'CTL::cancelWaitTrade');
$routes->get('/ctl/mailWaitTrade', 'CTL::mailWaitTrade');
$routes->get('/test', 'Home::index');

//$routes->post('/editor', 'Editor::editor');
//文字編輯器使用
//$r->addRoute('POST', '/editor', 'ControllerAdmin@editor');
$routes->post('/editor/upload', 'Editor::editor');
$routes->options('/editor/upload', 'Editor::getOptions');
$routes->options('/', 'Editor::getOptions');
/**交易取消訂單**/
$routes->get('/order_cancel/(:num)', 'TradeCancel::cancelAndStockBack');


/**前端-不用權限***/
$routes->get('/goods', 'Front\Goods::getList');
$routes->get('/goods/(:num)', 'Front\Goods::getData/$1');
$routes->get('/column', 'Front\Column::getList');
$routes->get('/news', 'Front\News::getList');
$routes->get('/news/category', 'Front\News::getCategoryList');
$routes->get('/question', 'Front\Question::getList');
$routes->get('/question/category', 'Front\Question::getCategoryList');
$routes->get('/carousel', 'Front\Carousel::getList');
$routes->get('/mascot', 'Front\Mascot::getList');
$routes->get('/kol', 'Front\Kol::getList');
$routes->get('/promote', 'Front\Promote::getList');
$routes->get('/advertisement', 'Front\Advertisement::getList');
$routes->get('/advertisement/category', 'Front\Advertisement::getCategoryList');
$routes->get('/menu', 'Front\Menu::getList');
$routes->get('/menu/category', 'Front\Menu::getCategoryList');
$routes->get('/weblink', 'Front\WebLink::getList');
$routes->get('/weblink/category', 'Front\WebLink::getCategoryList');
$routes->get('/payment', 'Front\Payment::getList');
$routes->get('/shipping', 'Front\Shipping::getList');
$routes->get('/zipcode', 'Front\Zipcode::getList');
$routes->post('/contactUs', 'Front\ContactUs::send');

$routes->get('/homepage', 'Front\Homepage::getList');

/**管理端-登入***/
$routes->group('admin', [], function ($routes) {
    $routes->post('login', 'Admin\Login::login');
});
/**管理端***/
$routes->group('admin', ["filter"=>"AdminAuth"], function ($routes) {
    //登出
    $routes->get('logout', 'Admin\Login::logout');
    //通用欄位
    $routes->post('column', 'Admin\Column::getList');
    $routes->post('column/replace', 'Admin\Column::replaceData');
    $routes->post('column/image', 'Admin\Column::uploadImage');
    //News Category
    $routes->get('news/category', 'Admin\News\Category::getList');
    $routes->put('news/category', 'Admin\News\Category::create');
    $routes->patch('news/category', 'Admin\News\Category::update/$1');
    $routes->delete('news/category/(:num)', 'Admin\News\Category::del/$1');
    $routes->patch('news/category/updateSeqBatch', 'Admin\News\Category::updateSeqBatch');
    //News
    $routes->get('news', 'Admin\News\News::getList');
    $routes->put('news', 'Admin\News\News::create');
    $routes->patch('news', 'Admin\News\News::update/$1');
    $routes->delete('news/(:num)', 'Admin\News\News::del/$1');
    $routes->post('news/image/(:num)', 'Admin\News\News::uploadImage/$1/$2');
    $routes->patch('news/updateSeqBatch', 'Admin\News\News::updateSeqBatch');
    //Question Category
    $routes->get('question/category', 'Admin\Question\Category::getList');
    $routes->put('question/category', 'Admin\Question\Category::create');
    $routes->patch('question/category', 'Admin\Question\Category::update');
    $routes->delete('question/category/(:num)', 'Admin\Question\Category::del/$1');
    $routes->patch('question/category/updateSeqBatch', 'Admin\Question\Category::updateSeqBatch');
    //Question
    $routes->get('question', 'Admin\Question\Question::getList');
    $routes->put('question', 'Admin\Question\Question::create');
    $routes->patch('question', 'Admin\Question\Question::update');
    $routes->delete('question/(:num)', 'Admin\Question\Question::del/$1');
    $routes->patch('question/updateSeqBatch', 'Admin\Question\Question::updateSeqBatch');
    //carousel
    $routes->get('carousel', 'Admin\Carousel\Carousel::getList');
    $routes->put('carousel', 'Admin\Carousel\Carousel::create');
    $routes->patch('carousel', 'Admin\Carousel\Carousel::update');
    $routes->delete('carousel/(:num)', 'Admin\Carousel\Carousel::del/$1');
    $routes->post('carousel/image/(:num)', 'Admin\Carousel\Carousel::uploadImage/$1/$2');
    $routes->patch('carousel/updateSeqBatch', 'Admin\Carousel\Carousel::updateSeqBatch');
    //mascot
    $routes->get('mascot', 'Admin\Mascot\Mascot::getList');
    $routes->put('mascot', 'Admin\Mascot\Mascot::create');
    $routes->patch('mascot', 'Admin\Mascot\Mascot::update');
    $routes->delete('mascot/(:num)', 'Admin\Mascot\Mascot::del/$1');
    $routes->post('mascot/image/(:num)', 'Admin\Mascot\Mascot::uploadImage/$1/$2');
    $routes->patch('mascot/updateSeqBatch', 'Admin\Mascot\Mascot::updateSeqBatch');
    //kol
    $routes->get('kol', 'Admin\Kol\Kol::getList');
    $routes->put('kol', 'Admin\Kol\Kol::create');
    $routes->patch('kol', 'Admin\Kol\Kol::update');
    $routes->delete('kol/(:num)', 'Admin\Kol\Kol::del/$1');
    $routes->post('kol/image/(:num)', 'Admin\Kol\Kol::uploadImage/$1/$2');
    $routes->patch('kol/updateSeqBatch', 'Admin\Kol\Kol::updateSeqBatch');
    //promote
    $routes->get('promote', 'Admin\Promote\Promote::getList');
    $routes->put('promote', 'Admin\Promote\Promote::create');
    $routes->patch('promote', 'Admin\Promote\Promote::update');
    $routes->delete('promote/(:num)', 'Admin\Promote\Promote::del/$1');
    $routes->post('promote/image/(:num)', 'Admin\Promote\Promote::uploadImage/$1/$2');
    $routes->patch('promote/updateSeqBatch', 'Admin\Promote\Promote::updateSeqBatch');
    //Advertisement Category
    $routes->get('advertisement/category', 'Admin\Advertisement\Category::getList');
    $routes->put('advertisement/category', 'Admin\Advertisement\Category::create');
    $routes->patch('advertisement/category', 'Admin\Advertisement\Category::update/$1');
    $routes->delete('advertisement/category/(:num)', 'Admin\Advertisement\Category::del/$1');
    $routes->patch('advertisement/category/updateSeqBatch', 'Admin\Advertisement\Category::updateSeqBatch');
    //Advertisement
    $routes->get('advertisement', 'Admin\Advertisement\Advertisement::getList');
    $routes->put('advertisement', 'Admin\Advertisement\Advertisement::create');
    $routes->patch('advertisement', 'Admin\Advertisement\Advertisement::update/$1');
    $routes->delete('advertisement/(:num)', 'Admin\Advertisement\Advertisement::del/$1');
    $routes->post('advertisement/image/(:num)', 'Admin\Advertisement\Advertisement::uploadImage/$1/$2');
    $routes->patch('advertisement/updateSeqBatch', 'Admin\Advertisement\Advertisement::updateSeqBatch');
    //Menu Category
    $routes->get('menu/category', 'Admin\Menu\Category::getList');
    $routes->put('menu/category', 'Admin\Menu\Category::create');
    $routes->patch('menu/category', 'Admin\Menu\Category::update/$1');
    $routes->delete('menu/category/(:num)', 'Admin\Menu\Category::del/$1');
    $routes->patch('menu/category/updateSeqBatch', 'Admin\Menu\Category::updateSeqBatch');
    //Menu
    $routes->get('menu', 'Admin\Menu\Menu::getList');
    $routes->put('menu', 'Admin\Menu\Menu::create');
    $routes->patch('menu', 'Admin\Menu\Menu::update/$1');
    $routes->delete('menu/(:num)', 'Admin\Menu\Menu::del/$1');
    $routes->post('menu/image/(:num)', 'Admin\Menu\Menu::uploadImage/$1/$2');
    $routes->patch('menu/updateSeqBatch', 'Admin\Menu\Menu::updateSeqBatch');
    //goods
    $routes->get('goods', 'Admin\Goods\Goods::getList');
    $routes->put('goods', 'Admin\Goods\Goods::create');
    $routes->patch('goods', 'Admin\Goods\Goods::update');
    $routes->delete('goods/(:num)', 'Admin\Goods\Goods::del/$1');
    $routes->post('goods/image/(:num)', 'Admin\Goods\Goods::uploadImage/$1/$2');
    $routes->patch('goods/updateSeqBatch', 'Admin\Goods\Goods::updateSeqBatch');
    //Menu2Goods
    $routes->patch('menu2goods/menu/(:num)', 'Admin\Menu2Goods\RelationSetting::setMenu/$1');
    $routes->patch('menu2goods/goods/(:num)', 'Admin\Menu2Goods\RelationSetting::setGoods/$1');
    $routes->patch('menu2goods/add', 'Admin\Menu2Goods\RelationSetting::add');

    //color
    $routes->get('color', 'Admin\Color\Color::getList');
    $routes->put('color', 'Admin\Color\Color::create');
    $routes->patch('color', 'Admin\Color\Color::update');
//    $routes->delete('color/(:num)', 'Admin\Color\Color::del/$1');
    //size
    $routes->get('size', 'Admin\Size\Size::getList');
    $routes->put('size', 'Admin\Size\Size::create');
    $routes->patch('size', 'Admin\Size\Size::update');
//    $routes->delete('size/(:num)', 'Admin\Size\Size::del/$1');
    //Stock
    $routes->get('goods/stock/(:num)', 'Admin\Goods\Stock::getList/$1');
    $routes->post('goods/stock', 'Admin\Goods\Stock::create');
    $routes->patch('goods/stock/updateSeqBatch', 'Admin\Goods\Stock::updateSeqBatch');
    //Picture
    $routes->get('goods/picture/(:num)', 'Admin\Goods\Picture::getList/$1');
    $routes->post('goods/picture/(:num)/(:num)/(:num)', 'Admin\Goods\Picture::create/$1/$2/$3');
    $routes->delete('goods/picture/(:num)', 'Admin\Goods\Picture::del/$1');
    $routes->patch('goods/picture/updateSeqBatch', 'Admin\Goods\Picture::updateSeqBatch');
    //CustomGoodsSpecCategory
    $routes->get('customgoods/category/(:num)', 'Admin\CustomGoods\Category::getList/$1');
    $routes->put('customgoods/category', 'Admin\CustomGoods\Category::create');
    $routes->patch('customgoods/category', 'Admin\CustomGoods\Category::update/$1');
    $routes->delete('customgoods/category/(:num)', 'Admin\CustomGoods\Category::del/$1');
    $routes->patch('customgoods/category/updateSeqBatch', 'Admin\CustomGoods\Category::updateSeqBatch');
    //CustomGoodsSpec
    $routes->get('customgoods/spec/(:num)', 'Admin\CustomGoods\Spec::getList/$1');
    $routes->put('customgoods/spec', 'Admin\CustomGoods\Spec::create');
    $routes->patch('customgoods/spec', 'Admin\CustomGoods\Spec::update/$1');
    $routes->delete('customgoods/spec/(:num)', 'Admin\CustomGoods\Spec::del/$1');
    $routes->patch('customgoods/spec/updateSeqBatch', 'Admin\CustomGoods\Spec::updateSeqBatch');
    //CustomGoodsStock
    $routes->get('customgoods/stock/(:num)', 'Admin\CustomGoods\Stock::getList/$1');
    $routes->post('customgoods/stock', 'Admin\CustomGoods\Stock::create');
    //CustomGoodsSpecPicture
    $routes->get('customgoods/picture/(:num)', 'Admin\CustomGoods\Picture::getList/$1');
    $routes->post('customgoods/picture/(:num)', 'Admin\CustomGoods\Picture::create/$1');
    $routes->delete('customgoods/picture/(:num)', 'Admin\CustomGoods\Picture::del/$1');
    $routes->patch('customgoods/picture/updateSeqBatch', 'Admin\CustomGoods\Picture::updateSeqBatch');
    //CustomGoodsChangePrice
    $routes->get('customgoods/changeprice/(:num)', 'Admin\CustomGoods\ChangePrice::getList/$1');
    $routes->put('customgoods/changeprice', 'Admin\CustomGoods\ChangePrice::create');
    $routes->patch('customgoods/changeprice', 'Admin\CustomGoods\ChangePrice::update/$1');
    $routes->delete('customgoods/changeprice/(:num)', 'Admin\CustomGoods\ChangePrice::del/$1');
    //CustomGoodsSpecBlacklist
    $routes->get('customgoods/blacklist/(:num)', 'Admin\CustomGoods\Blacklist::getList/$1');
    $routes->put('customgoods/blacklist', 'Admin\CustomGoods\Blacklist::create');
    $routes->patch('customgoods/blacklist', 'Admin\CustomGoods\Blacklist::update/$1');
    $routes->delete('customgoods/blacklist/(:num)', 'Admin\CustomGoods\Blacklist::del/$1');
    //discount
    $routes->get('discount', 'Admin\Discount\Discount::getList');
    $routes->put('discount', 'Admin\Discount\Discount::create');
    $routes->patch('discount', 'Admin\Discount\Discount::update');
    $routes->delete('discount/(:num)', 'Admin\Discount\Discount::del/$1');
    $routes->post('discount/image/(:num)', 'Admin\Discount\Discount::uploadImage/$1/$2');
    //coupon
    $routes->get('coupon', 'Admin\Coupon\Coupon::getList');
    $routes->put('coupon', 'Admin\Coupon\Coupon::create');
    $routes->patch('coupon', 'Admin\Coupon\Coupon::update');
    $routes->delete('coupon/(:num)', 'Admin\Coupon\Coupon::del/$1');
    //WebLink Category
    $routes->get('weblink/category', 'Admin\WebLink\Category::getList');
    $routes->put('weblink/category', 'Admin\WebLink\Category::create');
    $routes->patch('weblink/category', 'Admin\WebLink\Category::update/$1');
    $routes->delete('weblink/category/(:num)', 'Admin\WebLink\Category::del/$1');
    $routes->patch('weblink/category/updateSeqBatch', 'Admin\WebLink\Category::updateSeqBatch');
    //WebLink
    $routes->get('weblink', 'Admin\WebLink\WebLink::getList');
    $routes->put('weblink', 'Admin\WebLink\WebLink::create');
    $routes->patch('weblink', 'Admin\WebLink\WebLink::update/$1');
    $routes->delete('weblink/(:num)', 'Admin\WebLink\WebLink::del/$1');
    $routes->patch('weblink/updateSeqBatch', 'Admin\WebLink\WebLink::updateSeqBatch');
    //Payment
    $routes->get('payment', 'Admin\Payment\Payment::getList');
    $routes->patch('payment', 'Admin\Payment\Payment::update/$1');
    //Shipping
    $routes->get('shipping', 'Admin\Shipping\Shipping::getList');
    $routes->put('shipping', 'Admin\Shipping\Shipping::create');
    $routes->patch('shipping', 'Admin\Shipping\Shipping::update/$1');
    $routes->delete('shipping/(:num)', 'Admin\Shipping\Shipping::del/$1');
    $routes->get('shipping/type', 'Admin\Shipping\Shipping::getTypeList');
    //Trade
    $routes->get('trade', 'Admin\Trade\Trade::getList');
    $routes->patch('trade', 'Admin\Trade\Trade::update');
    $routes->get('trade/HCT/(:num)', 'Admin\Trade\Trade::sendHCT/$1');//新竹貨運 出貨 列印
    $routes->post('trade/HCT/getLabelImage', 'Admin\Trade\Trade::getHCTLabelImage');//新竹貨運 轉圖檔
    $routes->post('trade/HCT/getHCTInfo', 'Admin\Trade\Trade::getHCTInfo');//新竹貨運 貨況
    $routes->get('trade/ECLogistics/(:num)', 'Admin\Trade\Trade::sendECLogistics/$1');//711或全家貨到付款-列印出貨單-出貨狀態修改
    //Member
    $routes->get('member', 'Admin\Member\Member::getList');
});
/**會員端***/
$routes->group('member', [], function ($routes) {
    $routes->post('login', 'Member\Login::login');
    $routes->post('register', 'Member\Login::register');
    $routes->post('forgetPassword', 'Member\Login::forgetPassword');
});
$routes->group('member', ["filter"=>"MemberAuth"], function ($routes) {
    //myInfo
    $routes->get('my', 'Member\Member::getMy');
    $routes->post('my', 'Member\Member::updateMy');
    $routes->post('password', 'Member\Member::updatePassword');
    //我的常用收件資訊
    $routes->get('receiver', 'Member\Receiver::getList');
    $routes->put('receiver', 'Member\Receiver::create');
    $routes->delete('receiver/(:num)', 'Member\Receiver::del/$1');
    $routes->patch('receiver/updateSeqBatch', 'Member\Receiver::updateSeqBatch');
    //我的收藏清單
    $routes->get('favorite', 'Member\Favorite::getList');
    $routes->put('favorite', 'Member\Favorite::create');
    $routes->delete('favorite/(:num)', 'Member\Favorite::del/$1');
    //購物車
    $routes->get('shoppingCart', 'Member\ShoppingCart::getList');
    $routes->put('shoppingCart', 'Member\ShoppingCart::create');
    $routes->delete('shoppingCart/(:num)', 'Member\ShoppingCart::del/$1');
    //收銀台、結帳
    $routes->get('711Map', 'Member\Checkout::get711Map');
    $routes->get('FamilyMap', 'Member\Checkout::getFamilyMap');
    $routes->post('cashier', 'Member\Checkout::cashier');
    $routes->post('checkout', 'Member\Checkout::checkout');
    //訂單
    $routes->get('trade', 'Member\Trade::getList');
    $routes->get('trade/payment/(:num)', 'Member\Trade::getPayment/$1');
    $routes->get('trade/cancel/(:num)', 'Member\Trade::cancel/$1');
    $routes->get('trade/HCT/(:num)', 'Member\Trade::getHCTInfo/$1');//查詢新竹貨運
});
//非會員 收銀台、結帳
$routes->get('nonMember/711Map', 'Member\Checkout::get711Map');
$routes->get('nonMember/FamilyMap', 'Member\Checkout::getFamilyMap');
$routes->post('nonMember/cashier', 'Member\Checkout::cashier');
$routes->post('nonMember/checkout', 'Member\Checkout::checkout');
//非會員 查詢訂單
$routes->post('nonMember/trade', 'Front\NonMember::getTrade');

/**第三方回覆***/
$routes->group('ECPay', [], function ($routes) {
    $routes->post('notify/ServerReplyAIO', 'ECReply::getAIO');
    $routes->post('notify/ServerReplyLogistics', 'ECReply::getLogistics');
    $routes->post('map/redirect', 'ECReply::redirectMapInfo');
});
$routes->post('/JKOPay/notify', 'JKOReply::reply');
$routes->post('/LineReply/notify', 'LineReply::reply');
$routes->post('/PCHome/notify/(:alphanum)', 'PCHomeReply::reply/$1');



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
