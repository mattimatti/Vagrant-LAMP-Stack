<?php
//vota
$app->get('/quiz/getresults/:question_id/:answers_ids', function ($question_id, $answers_ids) use ($app) {
  
    $results = array();
    
    
    
    
    // ===========================================================
    
     try{

     	        // if no session set this can't work.
     	        if(!isset($_SESSION["interview_id"])) throw new Exception("Interview Outdated");
     	
     	
              // update how many times the question has been answered
              $question = R::findOne('question',' id = :question_id ', 
                            array( ':question_id' => $question_id )
                           ); 
              $question->question_count++;
              R::store($question);
              
              $how_many_participants = $question->question_count;
          
          
              // ===========================================================
              
              // update all answers counters
              $answers_array = explode("-",$answers_ids);
              
              
              //die(print_r($answers_array));
              foreach ($answers_array as $answers_id){
                
                    $answer = R::findOne('answer',' id = :answers_id ', 
                                array( ':answers_id' => $answers_id )
                               ); 
                               
                    $answer->answer_count++;
                    R::store($answer);
                    
                    
	                  $interview_answer = R::dispense("interview_answer");
	                  $interview_answer->interview_id = $_SESSION["interview_id"];
	                  $interview_answer->question_id = $question_id;
	                  $interview_answer->answer_id = $answers_id;
	                  $interview_answer->questioncategory_id = $question->questioncategory_id;
                    
                    R::store($interview_answer);
                    
              }
          
              // ===========================================================
              
              
              //  fetch the answers        
              $answers = R::getAll('SELECT id,answer_count FROM answer WHERE question_id = :question_id ', 
                          array( ':question_id' => $question_id)
                        );  

              // get total answers
              $answers_count = 0;
              foreach ($answers as $answerr){   
                $answers_count += $answerr["answer_count"];
              }     
                        
    
              $results["success"] = true;
              $results["totalCounter"] = $how_many_participants;
             // $results["numAnswers"] = count($answers);    
              $results["numAnswers"] = $answers_count;    
              $results["answers"] = $answers;   
              
              
              

     }catch(Exception $ex){
      
             $results["success"] = false;
      
     }
              
    echo json_encode($results);

});