<?php

function hf_loadFixtures($app = "APP2") {

	for ($i = 1; $i < 6; $i++) {
		hf_createAnswer(1, $i, $app);
	}

	for ($i = 1; $i < 6; $i++) {
		hf_createAnswer(2, $i, $app);
	}

	for ($i = 1; $i < 5; $i++) {
		hf_createAnswer(3, $i, $app);
	}

	for ($i = 1; $i < 4; $i++) {
		hf_createAnswer(4, $i, $app);
	}

	for ($i = 1; $i < 5; $i++) {
		hf_createAnswer(5, $i, $app);
	}
}

function hf_createAnswer($domanda, $risposta, $app = "APP2") {

	$answer = R::dispense("hf_app2");

	$answer->domanda = $domanda;
	$answer->risposte = "" . $risposta;
	$answer->qualeAPP = $app;
	$answer->quante = 0;
	$answer->posizione = 0;

	R::store($answer);

}

/////////////////////// ADMIN HOME //////////////////////////////////////////////

$app->get('/hf/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hf/index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////

// Elimina tutti i risultati
$app->get('/hf/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hf/resetall");

					try {
						R::exec("DROP TABLE hf_countries");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hf_answers");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE hf_app2");

						hf_loadFixtures();

					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/hf/manage");

				});

/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app->get('/hf/registercountry', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /hf/registercountry");

			$data = array();
			$data["countries"] = R::getAll('select * from hf_countries');

			$app->render('hf/registercountry.html', $data);

		});

// Registra le country
$app->post('/hf/registercountry', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf/registercountry");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('hf_countries', ' name = :name ', array(':name' => $country_name));

					// sen non trova country crea oppure aggiorna
					if (!$country) {
						$country = R::dispense("hf_countries");
						$country->name = $country_name;
						$country->count = 1;
					} else {
						$country->count = $country->count + 1;
					}

					R::store($country);

					$app->redirect("/hf/registercountry");

				});

/////////////////////// APP 1 //////////////////////////////////////////////

// SHOW THE FORM
$app
		->get('/hf/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /app1/registeranswer");

					$data = array();
					$data["answers"] = R::find('hf_answers', ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP1"));

					$app->render('hf/app1/form.html', $data);

				});

// REGISTER ANSWER APP1
$app
		->post('/hf/app1/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf/app1/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					if ($app->request()->post("risposte") . "" !== "null") {

						$answer = R::dispense("hf_answers");

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
						$app->redirect("/hf/app1/registeranswer");
					}

				});

/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/hf/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /hf/app2/registeranswer");

					$data = array();
					$data["answers"] = R::find("hf_app2", ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APP2"));
					$app->render('hf/app2/form.html', $data);

				});

// Registra le risposte
$app
		->post('/hf/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /hf/app2/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					//if($app->request()->post("risposte")){

					// save in answers
					$answer = R::dispense("hf_answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = $app->request()->post("qualeAPP");
					$answer->posizione = $app->request()->post("posizione");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					//}

					// Saves in indexed table

					// La risposta è pregenerata
					$answer = R::findOne("hf_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(':qualeAPP' => "APP2", ':domanda' => $app->request()->post("domanda"), ':risposte' => $app->request()->post("risposte")));

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
		->get('/hf/getstatus', $noAuth(),
				function () use ($app) {

					$last_answer = R::getRow('select * from hf_answers where id=(SELECT MAX(id)  FROM hf_answers)');

					if ($last_answer) {

						if ($last_answer["qualeAPP"] == "APP2") {

							// computo delle percentuali
							$allanswers = R::find("hf_app2", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));

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
$app->get('/hf/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('hf/lastresponse.html', $data);

		});
