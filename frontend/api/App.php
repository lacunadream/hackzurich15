<?php
	require 'Database.php';
	
	class App {
		
		const MAIL_HOSTNAME = 'ch01.veon.ch';
		const MAIL_USERNAME = 'hackzurich@netinvasion-mail.ch';
		const MAIL_PASSWORD = 'ojhfoq3123a!';
		const MAIL_SENDER_ADDRESS = 'no-reply@codonate.waboodoo.ch';
		const MAIL_SENDER_NAME = 'Codonation';
		const MAIL_SUBJECT = 'Your donated items are needed!';
		
		private $database;
		private $user = null;
		
		public function __construct($email, $password) {
			$this->loggedIn = false;
			$this->database = new Database();
			$this->database->connect('localhost', 'waboodoo', 'sHxWVH', 'dbwaboodoo_hackzuri'); // Hardcoding super important db credentials! Yay!!
			
			if ($email && $password) {
				$this->user = $this->database->getUser($email, $password);
			}
		}
		
		public function checkCredentials($email, $password) {
			$user = $this->database->getUser($email, $password);
			return ['status' => 'success', 'valid' => ($user != null), 'verified' => ($user != null && $user['verified'])];
		}
		
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

		public function createOffer($type, $amount, $city, $country) {
			if (!$this->user) {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			// TODO: Check that $type is valid
			// TODO: Check that amount is a valid positive integer etc.
			
			$this->database->createOffer($this->user['id'], $type, $amount, $city, $country);
			return ['status' => 'success'];
		}
		
		/**
		 *	Returns the list of all the items the user is currently offering
		 */
		public function getOffers() {
			if (!$this->user) {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			$offers = $this->database->getOffers($this->user['id']);
			foreach ($offers as &$offer) {
				unset($offer['user']);
				
				if ($offer['requested_by']) {
					$requester = $this->database->getUserById($offer['requested_by']);
					
					if ($requester['type'] == 'organisation') {
						$name = $requester['organisation_name'];
					} else {
						$name = $requester['first_name'].' '.$requester['last_name'];
					}
					
					$offer['email'] = $requester['email'];
					if (!empty($requester['website'])) {
						$offer['website'] = $requester['website'];
					} else {
						$offer['website'] = null;
					}
					
					$offer['requested_by'] = $name;
				}
				
			}
			
			return $offers;
		}
		
		public function deleteOffer($id) {
			if (!$this->user) {
				return ['status' => 'error', 'message' => 'Not logged in'];
			}
			
			$offer = $this->database->getOffer($id);
			if (!$offer) {
				return ['status' => 'error', 'message' => 'Offer does not exist'];
			}
			if ($offer['requested_by']) {
				return ['status' => 'error', 'message' => 'Offer is locked'];
			}
			
			$this->database->deleteOffer($id);
			
			return ['status' => 'success'];
		}
		
		public function updateOffer($id, $changes) {
			if (!$this->user) {
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
			if (!$this->user) {
				return ['status' => 'error', 'message' => 'Not logged in'];
			} else if (!$this->user['verified']) {
				return ['status' => 'error', 'message' => 'Only allowed for verified users'];
			}
			
			return $this->database->countAvailableItems($type);
		}
		
		public function requestItems($type, $amount, $message) {
			if (!$this->user) {
				return ['status' => 'error', 'message' => 'Not logged in'];
			} else if (!$this->user['verified']) {
				return ['status' => 'error', 'message' => 'Only allowed for verified users'];
			}
			
			$usersToInform = $this->database->requestItems($this->user['id'], $type, $amount);
			
			$this->initMailer();
			
			foreach ($usersToInform as $userId => $requestedAmount) {
				$user = $this->database->getUserById($userId);
				if ($user) {
					
					$body = '<img src="http://codonation.waboodoo.ch/assets/img/mail_logo.png" alt="Codonation"><br><br>';
					if ($user['type'] == 'organisation') {
						$body .= 'Dear '.$user['organisation_name'];
					} else {
						$body .= 'Dear '.$user['first_name'].' '.$user['last_name'];
					}
					$body .= '<br><br>';
					$body .= 'A request was made for your donated items.<br><br>';
					$body .= '<table cellpadding="3">';
					$body .= '<tr><td>Item</td><td>'.$type.'</td></tr>';
					$body .= '<tr><td>Amount</td><td>'.$requestedAmount.'</td></tr>';
					$body .= '<tr><td>Requester</td><td>';
					if ($this->user['type'] == 'organisation') {
						$body .= $this->user['organisation_name'];
					} else {
						$body .= $this->user['first_name'].' '.$this->user['last_name'];
					}
					$body .= '<br>'.$this->user['description'];
					$body .= '</td></tr>';
					$body .= '<tr><td><br>Message</td><td><br><b>'.$message.'</b><br><br></td></tr>';
					$body .= '<tr><td>Requester address</td><td>';
					$body .= $this->user['street'].'<br>'.$this->user['zip'].' '.$this->user['city'].'<br>'.$this->user['country'].'<br>';
					
					if ($this->user['phone']) {
						$body .= '<br>'.$this->user['phone'];
					}
					$body .= '<br>'.$this->user['email'];
					if ($this->user['website']) {
						$body .= '<br><a href="'.$this->user['website'].'">'.$this->user['website'].'</a>';
					}
					$body .= '</td></tr>';
					
					$body .= '</table><br>';
					
					$body .= 'Please send your items to the address above or contact the requester to arrange some other way of delivery.<br><br>';
					$body .= 'Thank you for your effort.<br><br>';
					$body .= 'Kind regards,<br>Codonation';
					
					Mailer::sendMail(self::MAIL_SENDER_ADDRESS, self::MAIL_SENDER_NAME, $user['email'], self::MAIL_SUBJECT, $body);
				}
			}
			
			return [
				'involved_users' => count($usersToInform),
				'reserved_amount' => array_sum($usersToInform),
				'status' => 'success'
			];
		}
		
		private function initMailer() {
			require 'Mailer.php';
			Mailer::init(self::MAIL_HOSTNAME, self::MAIL_USERNAME, self::MAIL_PASSWORD);
		}
		
	}
?>