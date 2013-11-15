<?php


/**
 * Build a defined number of questions and answers.
 * @param string $app
 */
function hfdallas_loadFixtures($app = "APP2") {

	$maxQuestions = 9;
	$maxAnswers = 8;
	
	for ($x = 1; $x <= $maxQuestions; $x++) {
		for ($i = 1; $i <= $maxAnswers; $i++) {
			hfdallas_createAnswer($x, $i, $app);
		}
	}


}

function hfdallas_createAnswer($domanda, $risposta, $app = "APP2") {

	$answer = R::dispense("hfdallas_app2");

	$answer->domanda = $domanda;
	$answer->risposte = "" . $risposta;
	$answer->qualeAPP = $app;
	$answer->quante = 0;
	$answer->posizione = 0;

	R::store($answer);

}

/////////////////////// ADMIN HOME //////////////////////////////////////////////

$app->get('/hfdallas/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hfdallas/index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////

// Elimina tutti i risultati
$app
		->get('/hfdallas/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hfdallas/resetall");

					try {
						R::exec("DROP TABLE hfdallas_countries");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hfdallas_answers");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hfdallas_app2");

						hfdallas_loadFixtures();

					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/hfdallas/manage");

				});

/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app->get('/hfdallas/registercountry', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /hfdallas/registercountry");

			$data = array();
			$data["countries"] = R::getAll('select * from hfdallas_countries');

			$app->render('hfdallas/registercountry.html', $data);

		});

// Registra le country
$app
		->post('/hfdallas/registercountry', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas/registercountry");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('hfdallas_countries', ' name = :name ', array(':name' => $country_name));

					// sen non trova country crea oppure aggiorna
					if (!$country) {
						$country = R::dispense("hfdallas_countries");
						$country->name = $country_name;
						$country->count = 1;
					} else {
						$country->count = $country->count + 1;
					}

					R::store($country);

					$app->redirect("/hfdallas/registercountry");

				});

/////////////////////// APP 1 //////////////////////////////////////////////

// SHOW THE FORM
$app
		->get('/hfdallas/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /app1/registeranswer");

					$data = array();
					$data["answers"] = R::find('hfdallas_answers', ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP1"));

					$app->render('hfdallas/app1/form.html', $data);

				});

// REGISTER ANSWER APP1
$app
		->post('/hfdallas/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas/app1/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					if ($app->request()->post("risposte") . "" !== "null") {

						$answer = R::dispense("hfdallas_answers");

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
						$app->redirect("/hfdallas/app1/registeranswer");
					}

				});

/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/hfdallas/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hfdallas/app2/registeranswer");

					$data = array();
					$data["answers"] = R::find("hfdallas_app2", ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP2"));
					$app->render('hfdallas/app2/form.html', $data);

				});

// Registra le risposte
$app
		->post('/hfdallas/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas/app2/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					//if($app->request()->post("risposte")){

					// save in answers
					$answer = R::dispense("hfdallas_answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = $app->request()->post("qualeAPP");
					$answer->posizione = $app->request()->post("posizione");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					//}

					// Saves in indexed table

					// La risposta è pregenerata
					$answer = R::findOne("hfdallas_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(':qualeAPP' => "APP2", ':domanda' => $app->request()->post("domanda"), ':risposte' => $app->request()->post("risposte")));

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
		->get('/hfdallas/getstatus', $noAuth(),
				function () use ($app) {

					$last_answer = R::getRow('select * from hfdallas_answers where id=(SELECT MAX(id)  FROM hfdallas_answers)');

					if ($last_answer) {

						if ($last_answer["qualeAPP"] == "APP2") {

							// computo delle percentuali
							$allanswers = R::find("hfdallas_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));
							$otheranswers = R::find("hfdallas_app", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));
							
							$allanswers = array_merge($allanswers, $otheranswers);

							// Estrai il totale di risposte per questa domanda
							$indexed = array();
							foreach ($allanswers as $answer) {
								if(!isset($indexed[$answer->risposte])){
									$indexed[$answer->risposte] = $answer;
								}else{
									$elm = $indexed[$answer->risposte];
									$elm->quante += $answer->quante;
							
									$indexed[$answer->risposte] = $elm;
								}
							}
								
								
							$allanswers = array_values($indexed);

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
$app->get('/hfdallas/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hfdallas/lastresponse.html', $data);

		});
