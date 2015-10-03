<?php
	require 'Database.php';
	
	class App {
		
		private $database;
		private $user = null; // Will be an array of user information if valid session id is provided
		
		public function __construct($sessionId) {
			$this->loggedIn = false;
			$this->database = new Database();
			$this->database->connect('localhost', 'waboodoo', 'sHxWVH', 'dbwaboodoo_hackzuri'); // Hardcoding super important db credentials in code! Yay!!
			
			if ($sessionId) {
				$this->user = $this->database->getSessionUser($sessionId);
			}
		}
		
		/*public function test() {
			return $this->database->createOffer(3, 'Doll', 9001, 'Paris', 'France');
		}*/
		
		public function registerOrganisation($email, $password, $organisationName, $description, $country, $city, $street, $zip, $phone, $website) {
			// TODO: Check if email address is not yet in use
			$this->database->createUser('organisation', $email, $password, null, null, $organisationName, $description, $country, $city, $street, $zip, $phone, $website);
			
			return ['status' => 'success'];
		}
		
		public function registerIndividual($email, $password, $firstName, $lastName, $description, $country, $city, $street, $zip, $phone, $website) {
			// TODO: Check if email address is not yet in use
			$this->database->createUser('individual', $email, $password, $firstName, $lastName, null, $description, $country, $city, $street, $zip, $phone, $website);
			
			return ['status' => 'success'];
		}

		public function login($email, $hashedPassword) {
			if ($this->database->testCredentials($email, $hashedPassword)) {				
				return $this->database->updateSession($email);
			}
			
			return ['status' => 'error', 'message' => 'Invalid username or password'];
		}
		
		public function createOffer($type, $amount, $city, $country) {
			if ($this->user) {
				$this->database->touchSession($this->user['email']);
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			// TODO: Check that $type is valid
			// TODO: Check that amount is a valid positive integer etc.
			
			return $this->database->createOffer($this->user['id'], $type, $amount, $city, $country);
		}
		
		/**
		 *	Returns the list of all the items the user is currently offering
		 */
		public function getOffers() {
			if ($this->user) {
				$this->database->touchSession($this->user['email']);
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			$offers = $this->database->getOffers($this->user['id']);
			foreach ($offers as &$offer) {
				unset($offer['user']);
			}
			
			return $offers;
		}
		
		public function deleteOffer($id) {
			if ($this->user) {
				$this->database->touchSession($this->user['email']);
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			if ($offer['requested_by']) {
				return ['status' => 'error', 'message' => 'Offer is locked'];
			}
			
			$this->database->deleteOffer($id);
			
			return ['status' => 'success'];
		}
		
		public function updateOffer($id, $changes) {
			if ($this->user) {
				$this->database->touchSession($this->user['email']);
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			$offer = $this->database->getOffer($id);
			if (!$offer) {
				return ['status' => 'error', 'message' => 'Offer does not exist'];
			}
			
			if ($offer['requested_by']) {
				return ['status' => 'error', 'message' => 'Offer is locked'];
			}
			
			$type = isset($changes['type']) ? $changes['type'] : $offer['type'];
			$amount = isset($changes['amount']) ? $changes['amount'] : $offer['amount'];
			$city = isset($changes['city']) ? $changes['city'] : $offer['city'];
			$country = isset($changes['country']) ? $changes['country'] : $offer['country'];
			
			$this->database->updateOffer($id, $type, $amount, $city, $country);
			
			return ['status' => 'success'];
		}
		
		
		public function countAvailableItems($type) {
			if ($this->user) {
				if ($this->user['verified']) {
					$this->database->touchSession($this->user['email']);
				} else {
					return ['status' => 'error', 'message' => 'Only allowed for verified users'];
				}
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			return $this->database->countAvailableItems($type);
		}
		
		public function requestItems($type, $amount, $message) {
			if ($this->user) {
				if ($this->user['verified']) {
					$this->database->touchSession($this->user['email']);
				} else {
					return ['status' => 'error', 'message' => 'Only allowed for verified users'];
				}
			} else {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			// TODO: Core part goes here
		}
		
	}
?>