<?php




//questions route
$app->get('/admin/questions/', $authAdmin('admin'), function () use ($app) {

  $data = array();
  $data["categories"] = R::find("questioncategory");
  
  $app->render('admin/questioncategories.html', $data);    
  
});





//cleanup all results
$app->get('/admin/cleanup/', $authAdmin('admin'), function () use ($app) {

  
  R::exec( 'update question set question_count = 0' );
  R::exec( 'update answer set answer_count = 0' );
  R::exec( 'delete from interview_answer' );
  R::exec( 'delete from interview' );
  
  $data = array();
  $app->render('admin/cleansuccess.html', $data);    
  
});








//questions route
$app->get('/admin/questioncategory/add/', $authAdmin('admin'), function () use ($app) {


  $category  = R::dispense("questioncategory");
  $category->name="New Patient";
  R::store($category);

  $app->redirect('/admin/questions/'.$category->id);    
  
});





//questions route
$app->get('/admin/questions/:category_id', $authAdmin('admin'), function ($category_id) use ($app) {

  if(!$category_id)$category_id = 1;
  
  $category  = R::findOne("questioncategory","id = :category_id",array(":category_id" => $category_id));
  
  $data = array();
  $data["category_id"] = $category->id;
  $data["categories"] = R::find("questioncategory");
  $data["category"] = $category;
  $data["questions"] = R::find('question',' questioncategory_id = :questioncategory_id', 
                array( ':questioncategory_id' => $category_id )
               );

  $app->render('admin/questions.html', $data);    
  
});
















//questions route
$app->post('/admin/questions/:category_id', $authAdmin('admin'), function ($category_id) use ($app) {

  if(!$category_id)$category_id = 1;
  
  $category  = R::findOne("questioncategory","id = :category_id",array(":category_id" => $category_id));
  
  $category->import($app->request()->post());
  
  
  R::store($category);

  $app->redirect("/admin/questions/".$category_id); 
  
});
















//answers route
$app->get('/admin/question/detail/:question_id', $authAdmin('admin'), function ($question_id) use ($app) {


  $question = R::findOne('question',' id = :question_id' , 
                array( ':question_id' => $question_id )
               );
  
  
  
  $data = array();
  $data["question"] = $question;
  $data["category"] = $question->questioncategory;
  $data["answers"] = $question->getAnswers();

  $app->render('admin/answers.html', $data);    
  
});












//questions route
$app->post('/admin/question/detail/:question_id', $authAdmin('admin'), function ($question_id) use ($app) {

  $question = R::findOne('question',' id = :question_id', 
                array( ':question_id' => $question_id )
               );
  
  $question->import($app->request()->post());
  
  
  R::store($question);

  $app->redirect("/admin/question/detail/".$question_id); 
  
});

















//answers route
$app->get('/admin/question/delete/:question_id', $authAdmin('admin'), function ($question_id) use ($app) {

  
  $answers = R::find('answer',' question_id = :question_id', 
                array( ':question_id' => $question_id )
               );
  
   R::trashAll($answers);
  

  $question = R::findOne('question',' id = :question_id', 
                array( ':question_id' => $question_id )
               );
  
  $category_id = $question->questioncategory_id;
  
  
  R::trash($question);
  
  
   $app->redirect("/admin/questions/".$category_id);
  
});




















//questions route
$app->get('/admin/questions/add/:category_id', $authAdmin('admin'), function ($category_id) use ($app) {

  if(!$category_id)throw new Exception("no cat id!");
  
  $category  = R::findOne("questioncategory","id = :category_id",array(":category_id" => $category_id));
  
  $questions = R::find('question',' questioncategory_id = :questioncategory_id', 
                array( ':questioncategory_id' => $category_id )
               );
  $maxstep = count($questions);
  if($maxstep==0)$maxstep=1;
  
  $question =  R::dispense( 'question' );
  $question->questioncategory_id = $category_id;
  $question->question_en = "New Question";
  $question->question_fr = "New Question";
  $question->step = $maxstep;
  R::store($question);

  $app->redirect("/admin/question/detail/". $question->id);
  
});



















//questions route
$app->get('/admin/answers/add/:question_id', $authAdmin('admin'), function ($question_id) use ($app) {

  if(!$question_id)throw new Exception("no question_id!");
  
  $question  = R::findOne("question","id = :question_id",array(":question_id" => $question_id));

  
  
  $answers = R::find('answer',' question_id = :question_id', 
                array( ':question_id' => $question_id )
               );
               
               
  $maxstep = count($answers);
  $maxstep++;
  
  
  
  $answer =  R::dispense( 'answer' );
  $answer->questioncategory_id = $question->questioncategory_id;
  $answer->question_id = $question->id;
  $answer->answer_en = "New Answer";
  $answer->answer_fr = "New Answer";
  $answer->sorting = $maxstep;
  R::store($answer);

  //$app->redirect("/admin/question/detail/".$question_id);
  
   $app->redirect("/admin/answer/detail/".$answer->id);

});





//questions route
$app->get('/admin/answers/addsubanswer/:answer_id', $authAdmin('admin'), function ($answer_id) use ($app) {

  if(!$answer_id)throw new Exception("no answer_id!");

  $answer_parent  = R::findOne("answer","id = :id",array(":id" => $answer_id));

  $answers = $answer_parent->subAnswers();
               
               
  $order = count($answers);
  $order++;
  //dump($answer_parent);
  //R::debug(true);
  
  
  $answer =  R::dispense( 'answer' );
  $answer->questioncategory_id = $answer_parent->questioncategory_id;
  $answer->question_id = $answer_parent->question_id;
  $answer->answer_en = "New Answer";
  $answer->answer_id = $answer_parent->id;
  $answer->answer_fr = "New Answer";
  $answer->sorting = $order;
  
  R::store($answer);

 // $app->redirect("/admin/answer/detail/".$answer_id);
  
  $app->redirect("/admin/answer/detail/".$answer->id);
  
});









//questions route
$app->get('/admin/answer/delete/:answer_id', $authAdmin('admin'), function ($answer_id) use ($app) {

  if(!$answer_id)throw new Exception("no answer_id!");
  
  $answer  = R::findOne("answer","id = :answer_id",array(":answer_id" => $answer_id));
  
  $question_id = $answer->question_id;
  
  R::trash($answer);

  $app->redirect("/admin/question/detail/".$question_id);
  
});














//questions route
$app->get('/admin/answer/detail/:answer_id', $authAdmin('admin'), function ($answer_id) use ($app) {

  if(!$answer_id)throw new Exception("no answer_id!");
  
  $answer  = R::findOne("answer","id = :answer_id",array(":answer_id" => $answer_id));
  $question = R::findOne("question","id = :question_id",array(":question_id" => $answer->question_id));
  $category  = R::findOne("questioncategory","id = :category_id",array(":category_id" => $question->questioncategory_id));
  
  //dump($answer->subAnswers());
  
  
  $data = array();
  $data["answer"] = $answer;
  $data["category"] = $category;
  $data["question"] = $question;
  $data["answers"] = $answer->subAnswers();
  
  $app->render('admin/answer.html', $data); 
  
});
















//questions route
$app->post('/admin/questions/sort/', $authAdmin('admin'), function () use ($app) {

  $elms = $app->request()->post("elm");
  
  for ($i = 0; $i < count($elms); $i++) {
     $question = R::findOne("question","id = :question_id",array(":question_id" => $elms[$i]));
     $question->step = $i+1;
     R::store($question);
  }

});














//answer sorting route
$app->post('/admin/answers/sort/', $authAdmin('admin'), function () use ($app) {

  $elms = $app->request()->post("elm");
  
  for ($i = 0; $i < count($elms); $i++) {
     $answer = R::findOne("answer","id = :answer_id",array(":answer_id" => $elms[$i]));
     $answer->sorting = $i+1;
     R::store($answer);
  }

});














//questions route
$app->post('/admin/answer/detail/:answer_id', $authAdmin('admin'), function ($answer_id) use ($app) {

  if(!$answer_id)throw new Exception("no answer_id!");
  
  $answer  = R::findOne("answer","id = :answer_id",array(":answer_id" => $answer_id));

  $answer->import($app->request()->post());
  
  R::store($answer);
  
  
  if($answer->answer_id != 0){
  	$app->redirect("/admin/answer/detail/".$answer->answer_id);  
  }else{
  	$app->redirect("/admin/question/detail/".$answer->question_id);  
  }
  
  

   
  
  
  
});






