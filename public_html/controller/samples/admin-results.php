<?php


//results route
$app->get('/admin/results/:user_id', $authAdmin('admin'), function ($user_id) use ($app) {

  $data = array();
  
  $app->render('admin/results.html', $data);    
  
});


//results route
$app->get('/admin/map/', $authAdmin('admin'), function () use ($app) {
  //$app->render('admin/map.html', array()); 
	$app->render('admin/raphael.html', array()); 
});


//results route
$app->map('/admin/results/', $authAdmin('admin'), function () use ($app) {

	 $data = array();
	
	
	 if($app->request()->isPost()){

			 	$filter = " 1=1 ";
			 	$params = array();
			 	
			 	
			 	$area = $app->request()->post("area");
			 	if($area){
			 		
			 		$data["selected_area"] = R::findOne('areas','name = :area', array(":area" => $area));
			 		$filter.= "AND area = :area ";
			 		$params[":area"] = $area;
			 	}
		
			  $region = $app->request()->post("region");
		    if($region){
		    	$data["selected_region"] = R::findOne("region","name = :region ", array(":region"=>$region));
		      $filter.= "AND region = :region ";
		      $params[":region"] = $region;
		    }	 	
			 	
		    
		    $city = $app->request()->post("city");
		    if($city){
		    	$data["selected_city"] = R::findOne("cities","name = :city ", array(":city"=>$city));
		    	
		      $filter.= "AND city = :city ";
		      $params[":city"] = $city;
		    }   
			 	

        $case = $app->request()->post("case");
        if($case){
        	$data["selected_case"] = R::findOne("questioncategory","id = ? ", array($case));
          $filter.= "AND questioncategory_id = :case ";
          $params[":case"] = $case;
        }else{
        	$app->flashNow("error","You must select a case study");
        }	    
		    
		    
		    
		    $representative = $app->request()->post("representative");
		    
		    if($representative){

		    	$cond = "AND (";
		    	 $filter.= "AND (";
		    	foreach ($representative as $rep_id) {
		    		$filter.= " user_id = " .$rep_id . " OR";	
		    		$cond.= " id = " .$rep_id . " OR"; 
		    	}

		    	$filter.= ") ";
		    	$cond.= ") ";
		    	$filter = str_replace("OR)", ")", $filter);
		    	$cond = str_replace("OR)", ")", $cond);
		    
		    	//dump($cond);
		    	$data["selected_representative"] = R::find("user"," 1=1 ".$cond, array());

		    }
		

		    // fetch interviews based on parameters
			 	$interviews = R::find("interview", $filter, $params );
			 	$data["interviews"] = $interviews;
			 	
			 	
			 	// fetch all questions from the case.
			 	$questions = R::find("question", "questioncategory_id = :case", array(":case"=>$case) );
			 	$data["questions"] = $questions;
			 	
			 	
			 	// store a reference od all the ids
			 	$interviews_ids = array();
			 	
			 	foreach ($interviews as $interview) {
			 		$interviews_ids[] = $interview->id;
			 	}
			 	
			 	$data["interviews_ids"] = implode(",",$interviews_ids);
			 	

			 	//dump($filter);
	 	 
	 }else{
	 	
	 	$data["interviews"] = R::find("interview");
	 	
	 }


  
  $users = R::getAll("select * from user where role = 'representative'");

  $data["regions"] = R::getAll("select * from region where name in(select distinct(region) from interview)");
  $data["areas"] = R::findAll("areas");
  
  $data["cities"] = R::getAll("select * from cities where name in(select distinct(city) from interview)");
  
  $data["users"] = $users;
  $data["cases"] = R::findAll("questioncategory");
  $app->render('admin/results_search.html', $data); 

  
  
   
  
  
  
})->via('GET','POST');




function replace_last_occurrence($haystack, $needle) {
    $pos = 0;
    $last = 0;

    while($pos !== false) {
        $pos = strpos($haystack, $needle, $pos);
        $last = $pos;
    }

    substr_replace($haystack, "", $last, strlen($needle));
}
