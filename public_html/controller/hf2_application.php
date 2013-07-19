<?php




function loadFixtures() {

	for ($i = 1; $i < 6; $i++) {
		createAnswer(1, $i);
	}

	for ($i = 1; $i < 6; $i++) {
		createAnswer(2, $i);
	}

	for ($i = 1; $i < 5; $i++) {
		createAnswer(3, $i);
	}

	for ($i = 1; $i < 4; $i++) {
		createAnswer(4, $i);
	}

	for ($i = 1; $i < 5; $i++) {
		createAnswer(5, $i);
	}
}



function createAnswer($domanda, $risposta, $app="APP2") {

	$answer = R::dispense("app2");

	$answer->domanda = $domanda;
	$answer->risposte = "" . $risposta;
	$answer->qualeAPP = $app;
	$answer->quante = 0;
	$answer->posizione = 0;

	R::store($answer);

}







/////////////////////// ADMIN HOME //////////////////////////////////////////////



$app->get('/hf2/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hf2/index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////


// Elimina tutti i risultati
$app
		->get('/hf2/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hf2/resetall");

					try {
						R::exec("DROP TABLE countries");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE answers");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE app2");
						
						loadFixtures();
						
					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/hf2/manage");

				});




/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app->get('/hf2/registercountry', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /hf2/registercountry");

			$data = array();
			$data["countries"] = R::getAll('select * from countries');

			$app->render('hf2/registercountry.html', $data);

});


// Registra le country
$app
		->post('/hf2/registercountry', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf2/registercountry");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('countries', ' name = :name ', array(':name' => $country_name));

					// sen non trova country crea oppure aggiorna
					if (!$country) {
						$country = R::dispense("countries");
						$country->name = $country_name;
						$country->count = 1;
					} else {
						$country->count = $country->count + 1;
					}

					R::store($country);

					$app->redirect("/hf2/registercountry");

				});






/////////////////////// APP 1 //////////////////////////////////////////////

// SHOW THE FORM
$app
		->get('/hf2/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /app1/registeranswer");

					$data = array();
					$data["answers"] = R::find('answers', ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP1"));

					$app->render('hf2/app1/form.html', $data);

				});


// REGISTER ANSWER APP1
$app
		->post('/hf2/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf2/app1/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					if ($app->request()->post("risposte") . "" !== "null") {

						$answer = R::dispense("answers");

						$answer->domanda = $app->request()->post("domanda");
						$answer->risposte = $app->request()->post("risposte");
						$answer->qualeAPP = $app->request()->post("qualeAPP");
						$answer->posizione = $app->request()->post("posizione");

						$id = R::store($answer);

						$app->getLog()->debug("salvato id $id");

					}

					// Se stiamo simlando i forms
					$simulate = $app->request()->post("simulate");

					if ($simulate) {
						$app->redirect("/hf2/app1/registeranswer");
					}

				});







/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/hf2/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hf2/app2/registeranswer");

					$data = array();
					$data["answers"] = R::find('app2', ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP2"));
					$app->render('hf2/app2/form.html', $data);

				});

// Registra le risposte
$app
		->post('/hf2/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf2/app2/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					//if($app->request()->post("risposte")){

					// save in answers
					$answer = R::dispense("answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = $app->request()->post("qualeAPP");
					$answer->posizione = $app->request()->post("posizione");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					//}

					// Saves in indexed table

					// La risposta è pregenerata
					$answer = R::findOne('app2', '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(':qualeAPP' => "APP2", ':domanda' => $app->request()->post("domanda"), ':risposte' => $app->request()->post("risposte")));

					if ($answer) {

						$app->getLog()->debug("trovata answer " . $answer->id);

						$answer->quante = $answer->quante + 1;

						R::store($answer);

						$app->getLog()->debug("aggiornato count a: " . $answer->quante);

					} else {
						$app->getLog()->error("answer non trovata esci");
						//throw new Exception("Risposta Non generata");
						die("ko");
					}

					// If ajax request
					if ($app->request()->isXhr()) {

						$response = $app->request()->params();

						echo json_encode($response);
						exit();

					} else {

						// Se stiamo simlando i forms
						//$simulate = $app->request()->post("simulate");
						//if ($simulate) {
						//	$app->redirect("/app2/registeranswer");
						//}

					}

				});




// GET THE STATUS
$app
		->get('/hf2/getstatus', $noAuth(),
				function () use ($app) {

					$last_answer = R::getRow('select * from answers where id=(SELECT MAX(id)  FROM answers)');

					if ($last_answer) {

						if ($last_answer["qualeAPP"] == "APP2") {

							// computo delle percentuali
							$allanswers = R::find('app2', '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));

							// Estrai il totale di risposte per questa domanda

							$totalAnswers = 0;
							foreach ($allanswers as $answer) {
								$totalAnswers += $answer->quante;
							}

							$risposte = array();

							foreach ($allanswers as $answer) {

								$risposta = array();

								$risposta["risposta"] = $answer->risposte;
								$risposta["quante"] = $answer->quante;
								if ($totalAnswers > 0) {
									$risposta["percent"] = $answer->quante / $totalAnswers * 100;
								} else {
									$risposta["percent"] = 0;
								}

								$risposte[] = $risposta;
							}

							$last_answer["stats"] = $risposte;

						}

					} else {
						$last_answer = array();
					}

					echo json_encode($last_answer);

				});





// SHOW THE LAST RESPONSE
$app->get('/hf2/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hf2/lastresponse.html', $data);

});
