<?php


R::debug(true);


//HOME route show nothing
$app->get('/csu/manage', $noAuth(), function () use ($app) {

			$data = array();
			$app->render('csu/index.html', $data);

});

$app->get('/csu', $noAuth(), function () use ($app) {

	die(":)");

});

$app->get('/csu/', $noAuth(), function () use ($app) {
	
		die(":)");
	
});

// Elimina tutti i players
$app
		->get('/csu/resetall', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra GET /resetall");

					try {
						R::exec("DROP TABLE csu_sentences");
					} catch (Exception $ex) {
						die($ex->getMessage());
					}

					$app->redirect("/csu/manage");

				});


// Registra le sentences
$app
		->post('/csu/savesentence', $noAuth(),
				function () use ($app) {

					$app->getLog()->debug("entra POST /csu/savesentence");
					$app->getLog()->debug(print_r($app->request()->post(), 1));

					$sentence = $app->request()->post("sentence");
					
					if(!$sentence){
						throw new Exception("Missing sentence");
					}
					

					if(trim($sentence) == ""){
						return;
					}

					$answer = R::dispense("csu_sentences");
					$answer->sentence = $sentence;
					$id = R::store($answer);


				});

// ritorna le ultime 10 sentences
$app
		->get('/csu/getlastsentences', $noAuth(),
				function () use ($app) {

					$currentPlayers = R::findAll("csu_sentences","ORDER BY id DESC LIMIT 10");

					if (!$currentPlayers) {
						$currentPlayers = array();
					}

					echo json_encode($currentPlayers);

				});
