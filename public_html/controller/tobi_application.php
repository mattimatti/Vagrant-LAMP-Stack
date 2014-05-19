<?php
// 19/05/2014





/**
 * Build a defined number of questions and answers.
 * @param string $app
 */
function tobi_loadFixtures($app = "APPTOBI") {

	$maxQuestions = 25;
	$maxAnswers = 3;
	
	for ($x = 1; $x <= $maxQuestions; $x++) {
		for ($i = 1; $i <= $maxAnswers; $i++) {
			tobi_createAnswer($x, $i, $app);
		}
	}


}

function tobi_createAnswer($domanda, $risposta, $app = "APPTOBI") {

	$answer = R::dispense("tobi_app");

	$answer->domanda = $domanda;
	$answer->risposte = "" . $risposta;
	$answer->qualeAPP = $app;
	$answer->quante = 0;
	$answer->posizione = 0;

	R::store($answer);

}

/////////////////////// ADMIN HOME //////////////////////////////////////////////

$app->get('/tobi/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('tobi/index.html', $data);

		});

/////////////////////// RESET //////////////////////////////////////////////

// Elimina tutti i risultati
$app
		->get('/tobi/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /tobi/resetall");

					try {
						R::exec("DROP TABLE tobi_answers");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}
					try {
						R::exec("DROP TABLE tobi_app");

						tobi_loadFixtures();

					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/tobi/manage");

				});



// Registra le risposte
$app
		->get('/tobi/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /tobi/APPTOBI/registeranswer");

					$maxQuestions = 25;
					
					
					$data = array();
					$data["maxQuestions"] = $maxQuestions;
					$data["answers"] = R::find("tobi_app", ' qualeAPP = :qualeAPP ', array(':qualeAPP' => "APPTOBI"));
					$app->render('tobi/form.html', $data);

				});

// Registra le risposte
$app
		->post('/tobi/registeranswer', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /tobi/registeranswer");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					
					// save in answers
					$answer = R::dispense("tobi_answers");

					$answer->domanda = $app->request()->post("domanda");
					$answer->risposte = $app->request()->post("risposte");
					$answer->qualeAPP = "APPTOBI";
					$answer->posizione = $app->request()->post("posizione");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					// Saves in indexed table

					// La risposta è pregenerata
					$answer = R::findOne("tobi_app", '  qualeAPP = :qualeAPP AND domanda = :domanda AND risposte = :risposte ', array(':qualeAPP' => "APPTOBI", ':domanda' => $app->request()->post("domanda"), ':risposte' => $app->request()->post("risposte")));

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

					die($app->request()->post("posizione"));
					
// 					// If ajax request
// 					if ($app->request()->isXhr()) {

// 						$response = $app->request()->params();

// 						echo json_encode($response);
// 						exit();

// 					}else{
						
// 					}

				});

// GET THE STATUS
$app
		->get('/tobi/getstatus', $noAuth(),
				function () use ($app) {

					$last_answer = R::getRow('select * from tobi_answers where id=(SELECT MAX(id)  FROM tobi_answers)');
					
					unset($last_answer['qualeAPP']);

					if ($last_answer) {

							// computo delle percentuali
							$allanswers = R::find("tobi_app", '  qualeAPP = :qualeAPP AND domanda = :domanda ', array(':qualeAPP' => $last_answer["qualeAPP"], ':domanda' => $last_answer["domanda"]));


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


					} else {
						$last_answer = array();
					}

					echo json_encode($last_answer);

				});

// SHOW THE LAST RESPONSE
$app->get('/tobi/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('tobi/lastresponse.html', $data);

		});
