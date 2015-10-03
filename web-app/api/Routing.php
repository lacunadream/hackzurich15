<?php
	require 'App.php';

	class Routing {
		
		private $app;
		
		public function __construct($sessionId) {
			$this->app = new App($sessionId);
		}
		
		public function route($method, $path, $args) {
			
			// GET /test
			if ($method == 'GET' && $path == ['test']) {
				return $this->app->test();
			}
			
			// POST /login
			if ($method == 'POST' && $path == ['login']) {
				return $this->app->login($args['email'], $args['hashed_password']);
			}
			
			// POST /logout
			if ($method == 'POST' && $path == ['logout']) {
				return $this->app->logout();
			}
			
			// POST /register/organisation
			// POST /register/individual
			if ($method == 'POST' && count($path) == 2 && $path[0] == 'register') {
				if ($path[1] == 'organisation') {
					return $this->app->registerOrganisation($args['email'], $args['password'], $args['organisation_name'], $args['description'], $args['country'], $args['city'], $args['street'], $args['zip'], $args['phone'], $args['website']);
				} else if ($path[1] == 'individual') {
					return $this->app->registerIndividual($args['email'], $args['password'], $args['first_name'], $args['last_name'], $args['description'], $args['country'], $args['city'], $args['street'], $args['zip'], $args['phone'], $args['website']);
				}
			}
			
			if (count($path) > 0 && $path[0] == 'offers') {
				
				// GET /offers
				if ($method == 'GET' && count($path) == 1) {
					return $this->app->getOffers();
				}
				
				if ($method == 'POST' && count($path) == 2) {
					
					// POST /offers/create
					if ($path[1] == 'create') {
						return $this->app->createOffer($args['type'], $args['amount'], $args['city'], $args['country']);
					}
					
					// POST /offers/update
					if ($path[1] == 'update') {
						return $this->app->updateOffer($args['id'], $args);
					}
				
					// POST /offers/delete
					if ($path[1] == 'delete') {
						return $this->app->deleteOffer($args['id']);
					}
					
				}
				
			}
			
			return ['status' => 'error', 'message' => 'Invalid request'];
		}
		
	}
?>