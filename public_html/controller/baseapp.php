<?php
//HOME route show nothing
$app->get('/ping', $noAuth(), function () use ($app) {

			die("pong");

		});
$app->get('/', $noAuth(), function () use ($app) {

			die(";)");

		});
