<?php






//HOME route
$app->get('/admin/', $authAdmin('admin'), function () use ($app) {

	$data = array();
	
	$app->render('admin/index.html', $data);    
	
})->name('admin');


