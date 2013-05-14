<?php


//HOME route
$app->get('/', $authApplication(), function () use ($app) {
	
    
	   if(isset($_SESSION["area"]) && isset($_SESSION["region"])){
	   	
		    $data = array();
		    $data["cases"]  = R::findAll("questioncategory");
		    $app->render('index.html', $data);    
	   	
	   	
	   }else{
	   	
		    $data = array();
		    $data["regions"] = R::findAll("region");
		    $data["areas"] = R::findAll("areas");
		    //$data["cities"] = R::findAll("cities");   
		    
		    $app->render('question/start.html', $data);
	   	
	   }

	
	
	
	
	

    

});





//Once created the new interview
$app->post('/startinterview', $authApplication(), function () use ($app) {

  
  $interview  = R::dispense("interview");
  
  
  $_SESSION["area"] = $app->request()->post("area");
  $_SESSION["region"] = $app->request()->post("region");
  
  
  $app->redirect("/");   
    

});




//show a form to add metadata of the interviewed
$app->get('/start/:category_id/:step', $authApplication(), function ($category_id, $step) use ($app) {

  $interview  = R::dispense("interview");

  $interview->area = $_SESSION["area"];
  $interview->region = $_SESSION["region"];
  $interview->user_id = $_SESSION["user_id"];
  $interview->last_access = R::isoDateTime();
  $interview->questioncategory_id = $category_id;

  R::store($interview);
  
  $_SESSION["interview_id"] = $interview->id;
  

  $app->redirect("/question/".$category_id."/".$step);    
  
});








//show a form to add metadata of the interviewed
$app->get('/end', $authApplication(), function () use ($app) {

    $data = array();  
    
    $app->render('end.html', $data);    

});

//show a form to add metadata of the interviewed
$app->get('/references', $authApplication(), function () use ($app) {

    $data = array();  
    
    $app->render('references.html', $data);    

});











//HOME route
$app->get('/question/:category_id/:step', $authApplication(), function ($category_id, $step) use ($app) {

  
  $category = R::findOne('questioncategory',' id = :quid ', 
                array( ':quid' => $category_id )
               );
  
  
  
  
  // fetch all the questions in this category
  $questions = R::find('question',' questioncategory_id = :questioncategory_id', 
                array( ':questioncategory_id' => $category_id )
               );

               
   
     
               // get the count
  $questions_count = count($questions);
  
       
  
  // find the next step
  $nextstep = $step + 1 ;
  
  //echo($nextstep);       
  // die();
  $last = false;
  // if not exceedes bounds walk to next ow go to root
  if($nextstep <= $questions_count){
    $nextPageName = '/question/'. $category_id . '/'.$nextstep . '';
  } else{
    $nextPageName = '/end';
    $last = true;
  }
  
  // the question
  $question = R::findOne('question',' questioncategory_id = :questioncategory_id AND step = :step ', 
                array( ':questioncategory_id' => $category_id, ':step' => $step )
               );
           
               
  $data = array();
  $data["nextpage"] = $nextPageName;
  $data["question"] = $question;
  //$data["answers"] = $answers;
  $data["category"] = $category;
  $data["last"] = $last;
  $data["cases"]  = R::find('questioncategory',' id != :quid ', 
                array( ':quid' => $category_id )
               );
  
  
  $app->render('question/question.html', $data);    

});