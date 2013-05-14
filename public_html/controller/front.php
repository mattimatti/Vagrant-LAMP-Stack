<?php

//HOME route show nothing
$app->get('/', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////

// Elimina tutti i risultati
$app
		->get('/resetall', $noAuth(),
				function () use ($app) {

					R::exec("DROP TABLE countries");
					R::exec("DROP TABLE answers");

					$app->redirect("/");

				});



/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app
->get('/registercountry', $noAuth(),
		function () use ($app) {

			$data = array();
			$data["countries"] = R::getAll( 'select * from countries');

			$app->render('registercountry.html', $data);

		});





// Registra le country
$app
		->post('/registercountry', $noAuth(),
				function () use ($app) {


					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('countries',' name = :name ',
					array( ':name'=>$country_name )
					);

					// sen non trova country crea oppure aggiorna
					if(!$country){
						$country = R::dispense("countries");
						$country->name = $country_name;
						$country->count = 1;
					}else{
						$country->count  = $country->count+1;
					}

					R::store($country);

					$app->redirect("/registercountry");

				});





/////////////////////// APP 1 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$data = array();
					$data["answers"] = R::find('answers',' qualeAPP = :qualeAPP ',
							array( ':qualeAPP'=>"APP1" )
					);



					$app->render('app1/form.html', $data);

				});




// Registra le risposte
$app
		->post('/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$answer = R::dispense("answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = $app->request()->post("qualeAPP");
					$answer->posizione = $app->request()->post("posizione");

					R::store($answer);

					// Se stiamo simlando i forms
					$simulate =$app->request()->post("simulate");

					if($simulate){
						$app->redirect("/app1/registeranswer");
					}



				});




/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
->get('/app2/registeranswer', $noAuth(),
		function () use ($app) {

			$data = array();
			$data["answers"] = R::find('answers',' qualeAPP = :qualeAPP ',
							array( ':qualeAPP'=>"APP2" )
					);
			$app->render('app2/form.html', $data);

		});




// Registra le risposte
$app
->post('/app2/registeranswer', $noAuth(),
		function () use ($app) {

			$answer = R::dispense("answers");

			$answer->domanda = $app->request()->post("domanda");
			$answer->risposte = $app->request()->post("risposte");
			$answer->qualeAPP = $app->request()->post("qualeAPP");
			$answer->posizione = $app->request()->post("posizione");

			R::store($answer);

			// Se stiamo simlando i forms
			$simulate =$app->request()->post("simulate");

			if($simulate){
				$app->redirect("/app2/registeranswer");
			}



		});



// Registra le risposte
$app
->get('/getstatus', $noAuth(),
		function () use ($app) {


			$last = R::getAll( 'select * from answers ORDER BY id DESC LIMIT 1');
			echo json_encode($last);

		});










//show a form to add metadata of the interviewed
$app
		->get('/start/:category_id/:step', $authApplication(),
				function ($category_id, $step) use ($app) {

					$interview = R::dispense("interview");

					$interview->area = $_SESSION["area"];
					$interview->region = $_SESSION["region"];
					$interview->user_id = $_SESSION["user_id"];
					$interview->last_access = R::isoDateTime();
					$interview->questioncategory_id = $category_id;

					R::store($interview);

					$_SESSION["interview_id"] = $interview->id;

					$app->redirect("/question/" . $category_id . "/" . $step);

				});

//show a form to add metadata of the interviewed
$app->get('/end', $authApplication(), function () use ($app) {

			$data = array();

			$app->render('end.html', $data);

		});

//show a form to add metadata of the interviewed
$app
		->get('/references', $authApplication(),
				function () use ($app) {

					$data = array();

					$app->render('references.html', $data);

				});

//HOME route
$app
		->get('/question/:category_id/:step', $authApplication(),
				function ($category_id, $step) use ($app) {

					$category = R::findOne('questioncategory', ' id = :quid ', array(
							':quid' => $category_id));

					// fetch all the questions in this category
					$questions = R::find('question', ' questioncategory_id = :questioncategory_id',
							array(
									':questioncategory_id' => $category_id));

					// get the count
					$questions_count = count($questions);

					// find the next step
					$nextstep = $step + 1;

					//echo($nextstep);
					// die();
					$last = false;
					// if not exceedes bounds walk to next ow go to root
					if ($nextstep <= $questions_count) {
						$nextPageName = '/question/' . $category_id . '/' . $nextstep . '';
					} else {
						$nextPageName = '/end';
						$last = true;
					}

					// the question
					$question = R::findOne('question', ' questioncategory_id = :questioncategory_id AND step = :step ',
							array(
									':questioncategory_id' => $category_id, ':step' => $step));

					$data = array();
					$data["nextpage"] = $nextPageName;
					$data["question"] = $question;
					//$data["answers"] = $answers;
					$data["category"] = $category;
					$data["last"] = $last;
					$data["cases"] = R::find('questioncategory', ' id != :quid ', array(
							':quid' => $category_id));

					$app->render('question/question.html', $data);

				});
