<?php

//HOME route show nothing
$app->get('/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('index.html', $data);

		});

	$app->get('/', $noAuth(), function () use ($app) {

		die(";)");

	});




/////////////////////// RESET //////////////////////////////////////////////



	function loadFixtures(){


		for ($i = 1; $i < 6; $i++) {
			createAnswer(1,$i);
		}

		for ($i = 1; $i < 6; $i++) {
			createAnswer(2,$i);
		}

		for ($i = 1; $i < 5; $i++) {
			createAnswer(3,$i);
		}

		for ($i = 1; $i < 4; $i++) {
			createAnswer(4,$i);
		}

		for ($i = 1; $i < 5; $i++) {
			createAnswer(5,$i);
		}
	};

	function createAnswer($domanda,$risposta){


		$answer = R::dispense("answers");

		$answer->domanda = $domanda;
		$answer->risposte = $risposta;
		$answer->qualeAPP = "APP2";
		$answer->quante = 0;
		$answer->posizione = 0;

		R::store($answer);

	};


// Elimina tutti i risultati
$app->get('/resetall', $noAuth(),
				function () use ($app) {

					R::exec("DROP TABLE countries");
					R::exec("DROP TABLE answers");

					loadFixtures();

					$app->redirect("/");

				});








/////////////////////// COUNTRIES //////////////////////////////////////////////

// Mostra form di test
$app
		->get('/registercountry', $noAuth(),
				function () use ($app) {

					$data = array();
					$data["countries"] = R::getAll('select * from countries');

					$app->render('registercountry.html', $data);

				});

// Registra le country
$app
		->post('/registercountry', $noAuth(),
				function () use ($app) {

					$country_name = $app->request()->post("country");

					// cerca country
					$country = R::findOne('countries', ' name = :name ', array(
							':name' => $country_name));

					// sen non trova country crea oppure aggiorna
					if (!$country) {
						$country = R::dispense("countries");
						$country->name = $country_name;
						$country->count = 1;
					} else {
						$country->count = $country->count + 1;
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
					$data["answers"] = R::find('answers', ' qualeAPP = :qualeAPP ', array(
							':qualeAPP' => "APP1"));

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
					$simulate = $app->request()->post("simulate");

					if ($simulate) {
						$app->redirect("/app1/registeranswer");
					}

				});

/////////////////////// APP 2 //////////////////////////////////////////////

// Registra le risposte
$app
		->get('/app2/registeranswer', $noAuth(),
				function () use ($app) {

					$data = array();
					$data["answers"] = R::find('answers', ' qualeAPP = :qualeAPP ', array(
							':qualeAPP' => "APP2"));
					$app->render('app2/form.html', $data);

				});

// Registra le risposte
$app
		->post('/app2/registeranswer', $noAuth(),

				function () use ($app) {



					// La risposta è pregenerata
					$answer = R::findOne('answers', '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(
							':qualeAPP' => "APP2",
							':domanda' => $app->request()->post("domanda"),
							':risposte' => $app->request()->post("risposte")
					)

							);



					if($answer){

						$answer->quante  = $answer->quante+1;

						R::store($answer);

					}else{
						//throw new Exception("Risposta Non generata");
						die("ko");
					}

					// If ajax request
					if ($app->request()->isXhr()) {

						$response = $app->request()->params();


						// computo delle percentuali
						$allanswers = R::find('answers', '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(
							':qualeAPP' => "APP2",
							':domanda' => $app->request()->post("domanda")
						));

						// Estrai il totale di risposte per questa domanda

						$totalAnswers = 0;
						foreach ($allanswers as $answer) {
							$totalAnswers+= $answer->quante;
						}


						$risposte = array();

						foreach ($allanswers as $answer) {

							$risposta = array();

							$risposta["risposta"] = $answer->risposte;
							$risposta["quante"] = $answer->quante;
							$risposta["percent"] = $answer->quante / $totalAnswers * 100;



							$risposte[] =  $risposta;
						}


						$response ["risposte"] = $risposte;



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

// Registra le risposte
$app
		->get('/getstatus', $noAuth(),
				function () use ($app) {

					$last = R::getAll('select * from answers ORDER BY id DESC LIMIT 1');
					echo json_encode($last);

				});

// Registra le risposte
$app
->get('/lastresponse', $noAuth(),
		function () use ($app) {

			$data = array();
			$app->render('lastresponse.html', $data);

		});