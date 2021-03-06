<?php

//HOME route show nothing
$app->get('/freegame/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('freegame/index.html', $data);

		});

// Elimina tutti i players
$app->get('/freegame/resetall', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /resetall");

			try {
				R::exec("DROP TABLE freegame_players");
			} catch (Exception $ex) {
				die($ex->getMessage());
			}

			$app->redirect("/freegame/manage");

		});

//Form di simulazione del servizio
$app->get('/freegame/saveplayers', $noAuth(), function () use ($app) {

			$app->getLog()->debug("entra GET /freegame/saveplayers");

			$data = array();
			$data["players"] = R::find('freegame_players');
			$app->render('freegame/form.html', $data);

		});

// Registra i giocatori
$app
		->post('/freegame/saveplayers', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /freegame/saveplayers");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$answer = R::dispense("freegame_players");

					$answer->freegame = $app->request()->post("freegame");
					$answer->player1 = $app->request()->post("player1");
					$answer->player2 = $app->request()->post("player2");

					$id = R::store($answer);

					$app->getLog()->debug("salvato id $id");

					// Se stiamo simlando i forms
					$simulate = $app->request()->post("simulate");

					if ($simulate) {
						$app->redirect("/freegame/saveplayers");
					}

				});

// ritorna la ultima fila di giocatori
$app
		->get('/freegame/getplayers', $noAuth(),
				function () use ($app) {

					$currentPlayers = R::getRow('select * from freegame_players where id=(SELECT MAX(id)  FROM freegame_players)');

					if (!$currentPlayers) {
						$currentPlayers = array();
					}

					echo json_encode($currentPlayers);

				});

// Registra le risposte
$app->get('/freegame/lastresponse', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('freegame/lastresponse.html', $data);

		});
