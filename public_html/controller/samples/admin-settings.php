<?php


//results route
$app->get('/admin/settings/', $authAdmin('admin'), function () use ($app) {

  $data = array();
  $user = R::findOne("user","role=:role ", array( ":role" => "admin"));
  $data["user"] = $user;
  
  $app->render('admin/settings.html', $data);    
  
});


//results route
$app->post('/admin/settings/', $authAdmin('admin'), function () use ($app) {

	
	$user = R::findOne("user","role=:role ", array( ":role" => "admin"));
	
	
  if(trim($app->request()->post("newpassword")) == ""){
    
      $app->flashNow('error', "New Password empty");
      $data = array();
      $app->render('admin/settings.html', $data); 
      return;
    
  }
	
	
	if($app->request()->post("newpassword") != $app->request()->post("newpassword-confirm")){
		
		  $app->flashNow('error', "New Password doesn't match");
		  $data = array();
		  $app->render('admin/settings.html', $data); 
		  return;
		
	}
	
	
	
	if($user->password == $app->request()->post("oldpass")){
		
		
		$user->password = $app->request()->post("newpassword");
		
		R::store($user);
		
		$app->flashNow('success', "Password changed");
		
		
	}else{
		
		  $app->flashNow('error', 'Old Password doesnt match');
		
	}
	
  $data = array();

  $app->render('admin/settings.html', $data);    
  
});


