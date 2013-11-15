<?php


/**
 * Build a defined number of questions and answers.
 * @param string $app
 */
function hfdallas2_loadFixtures($app = "APP2") {

	$maxQuestions = 9;
	$maxAnswers = 8;
	
	for ($x = 1; $x <= $maxQuestions; $x++) {
		for ($i = 1; $i <= $maxAnswers; $i++) {
			hfdallas2_createAnswer($x, $i, $app);
		}
	}


}

function hfdallas2_createAnswer($domanda, $risposta, $app = "APP2") {

	$answer = R::dispense("hfdallas2_app2");

	$answer->domanda = $domanda;
	$answer->risposte = "" . $risposta;
	$answer->qualeAPP = $app;
	$answer->quante = 0;
	$answer->posizione = 0;

	R::store($answer);

}

/////////////////////// ADMIN HOME //////////////////////////////////////////////

$app->get('/hfdallas2/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hfdallas2/index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////

// Elimina tutti i risultati
$app
		->get('/hfdallas2/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hfdallas2/resetall");

					try {
						R::exec("DROP TABLE hfdallas2_countries");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hfdallas2_answers");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hfdallas2_app2");

						hfdallas2_loadFixtures();

					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/hfdallas2/manage");

				});

/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app->get('/hfdallas2/registercountry', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /hfdallas2/registercountry");

			$data = array();
			$data["countries"] = R::getAll('select * from hfdallas2_countries');

			$app->render('hfdallas2/registercountry.html', $data);

		});

// Registra le country
$app
		->post('/hfdallas2/registercountry', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas2/registercountry");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('hfdallas2_countries', ' name = :name ', array(':name' => $country_name));

					// sen non trova country crea oppure aggiorna
					if (!$country) {
						$country = R::dispense("hfdallas2_countries");
						$country->name = $country_name;
						$country->count = 1;
					} else {
						$country->count = $country->count + 1;
					}

					R::store($country);

					$app->redirect("/hfdallas2/registercountry");

				});

/////////////////////// APP 1 //////////////////////////////////////////////

// SHOW THE FORM
$app
		->get('/hfdallas2/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /app1/registeranswer");

					$data = array();
					$data["answers"] = R::find('hfdallas2_answers', ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP1"));

					$app->render('hfdallas2/app1/form.html', $data);

				});

// REGISTER ANSWER APP1
$app
		->post('/hfdallas2/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas2/app1/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					if ($app->request()->post("risposte") . "" !== "null") {

						$answer = R::dispense("hfdallas2_answers");

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
						$app->redirect("/hfdallas2/app1/registeranswer");
					}

				});

/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/hfdallas2/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hfdallas2/app2/registeranswer");

					$data = array();
					$data["answers"] = R::find("hfdallas2_app2", ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP2"));
					$app->render('hfdallas2/app2/form.html', $data);

				});

// Registra le risposte
$app
		->post('/hfdallas2/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hfdallas2/app2/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					//if($app->request()->post("risposte")){

					// save in answers
					$answer = R::dispense("hfdallas2_answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = $app->request()->post("qualeAPP");
					$answer->posizione = $app->request()->post("posizione");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					//}

					// Saves in indexed table

					// La risposta è pregenerata
					$answer = R::findOne("hfdallas2_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(':qualeAPP' => "APP2", ':domanda' => $app->request()->post("domanda"), ':risposte' => $app->request()->post("risposte")));

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
		->get('/hfdallas2/getstatus', $noAuth(),
				function () use ($app) {

					$last_answer = R::getRow('select * from hfdallas2_answers where id=(SELECT MAX(id)  FROM hfdallas2_answers)');

					if ($last_answer) {

						if ($last_answer["qualeAPP"] == "APP2") {

							// computo delle percentuali
							$allanswers = R::find("hfdallas2_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));
							
							$otheranswers = R::find("hfdallas_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));

							$allanswers = array_merge($allanswers, $otheranswers);
							
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
$app->get('/hfdallas2/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hfdallas2/lastresponse.html', $data);

		});
