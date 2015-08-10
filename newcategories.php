<?php
require_once '/var/www/html/magento/app/Mage.php';
umask(0);
Mage::app('default');

$categories = file_get_contents('/var/www/html/magento/shell/Brand_20List.txt');
$categories = explode("\n", $categories);

foreach($categories as $categoryName) {
    $categoryName = trim($categoryName);

    if (empty($categoryName)) {
        continue;
    }

    $separator = '-';
    $catUrl = Mage::helper('catalog/product_url')->format($categoryName);
    $url = preg_replace('#[^0-9a-z]+#i', $separator, $catUrl);
    $url = strtolower($url);
    $url = trim($url);

    $parentCategory = Mage::getModel('catalog/category')->load(211);
    $general['name'] = $categoryName;
    $general['path'] = $parentCategory->getPath(); // catalog path
    $general['meta_title'] = $categoryName;  //page title
	$general['landing_page'] = '';
    $general['display_mode'] = "PRODUCTS"; //static block and the products are shown on the page
    $general['is_active'] = 1;
    $general['is_anchor'] = 1;
    $general['include_in_menu'] = 0;
    $general['url_key'] = $url;

    saveCategory($general);

    unset($general);

	if ($categoryName == '4MOMS') 
		die('only one');
}


function saveCategory($general) {
    $category = Mage::getModel('catalog/category');
	$category->setStoreId(0);
    $category->addData($general);

	try {
        $category->save();
        echo 'Success! Name: '. $general['name'] .' Id: '.$category->getId() . "\n";
    }
    catch (Exception $e){
        echo $e->getMessage();
    }
	
}

exit;