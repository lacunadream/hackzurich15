<?php
	require 'App.php';

	/**
	 *	Provides the REST API to the App object.
	 */
	class Routing {
		
		private $app;
		
		public function __construct($email, $password) {
			$this->app = new App($email, $password);
		}
		
		public function route($method, $path, $args) {
			
			// POST /login	// TODO: This should be GET?
			if ($method == 'POST' && $path == ['login']) {
				return $this->app->checkCredentials($args['email'], $args['password']);
			}
			
			// GET /user/verified
			if ($method == 'GET' && $path == ['user', 'verified']) {
				return $this->app->isVerified();
			}
			
			// POST /register/organisation
			// POST /register/individual
			if ($method == 'POST' && count($path) == 2 && $path[0] == 'register') {
				if ($path[1] == 'organisation') {
					return $this->app->registerOrganisation($args['email'], $args['password'], $args['organisation_name'], $args['description'], $args['country'], $args['city'], $args['street'], $args['zip'], $args['phone'], $args['website']);
				} else if ($path[1] == 'individual') {
					return $this->app->registerIndividual($args['email'], $args['password'], $args['first_name'], $args['last_name'], isset($args['description']) ? $args['description'] : '', $args['country'], $args['city'], $args['street'], $args['zip'], $args['phone'], isset($args['website']) ? $args['website'] : '');
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
			
			// GET /query
			if ($method == 'GET' && $path == ['query'] && isset($_GET['type'])) {
				return $this->app->countAvailableItems($_GET['type']);
			}
			
			// POST /request
			if ($method == 'POST' && $path == ['request']) {
				return $this->app->requestItems($args['type'], $args['amount'], $args['message']);
			}
			
			
			return ['status' => 'error', 'message' => 'Invalid request'];
		}
		
	}
?>