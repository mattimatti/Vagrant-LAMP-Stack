<?php







//users route
$app->get('/admin/users/', $authAdmin('admin'), function () use ($app) {

  $data = array();
  
  $maxrows = 1000;
  
  $totalrecords = R::count("user");
  
  $totalpages = round($totalrecords/$maxrows);
  if($totalrecords % $maxrows != 0){
  	$totalpages +=1;
  }
  
  $start_idx  = $app->request()->get("start");
  $end_idx  = $app->request()->get("end");
  
  if(!$start_idx)$start_idx = 0;
  if(!$end_idx)$end_idx = 1;
  
  $start_row  = $start_idx * $maxrows;
  $end_row    = $end_idx * $maxrows;


  $users = R::getAll("select * from user where role='representative' LIMIT ?,? ",array($start_row,$end_row));

  
  $data["users"] = $users;
  $data["totalpages"] = $totalpages;
  
  $data["start_idx"] = $start_idx;

  
  $data["start_row"] = $start_row;
  $data["end_row"] = $end_row;
  
  $data["has_paging"] = ($maxrows < $totalrecords);
  
  //echo "<pre>";
  //print_r($data);
  //die();
  
  $app->render('admin/users.html', $data);    
  
});



//users add route
$app->get('/admin/users/delete/:user_id', $authAdmin('admin'), function ($user_id) use ($app) {

	$user  = R::findOne("user","id=:user_id",array(":user_id"=>$user_id));
	R::trash($user);
	
	$app->redirect('/admin/users/');    
  
});




//users add route
$app->get('/admin/users/add/', $authAdmin('admin'), function () use ($app) {
	
	$data = array();
	
	if($app->request()->isPost()){
		
			$user  = R::dispense("user");
			$user->import($app->request()->post());
			$user->role = "representative";
			
		  try{
		    
		    R::store($user);
		    $app->redirect('/admin/users/'); 
		    
		  }catch(Exception $ex){
		    $app->flashNow("error",$ex->getMessage());
		    $data["user"] = $user;
		  }  
			
	}

  $app->render('/admin/user_insert.html',$data);    
  
})->via('GET','POST');








//users route
$app->map('/admin/users/detail/:id', $authAdmin('admin'), function ($id) use ($app) {

	$data = array();
	
	
  if($app->request()->isPost()){
  	
	  $user  = R::findOne("user", "id = :user_id", array(":user_id"=>$id));
	  $user->import($app->request()->post());
	  try{
	    
	    R::store($user);
	    $app->redirect('/admin/users/'); 
	    
	  }catch(Exception $ex){
	    
	    $app->flashNow("error",$ex->getMessage());
	      $data["user"] =$user;
	  }  	
  	
  	
  	
  }else{
  	$data["user"] = R::findOne("user"," id = :user_id",array(":user_id"=>$id));
  }

  $app->render('/admin/user_insert.html',$data);    
  
})->via('GET','POST');












