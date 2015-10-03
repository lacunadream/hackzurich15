<?php
	class Database { // TODO: Re-implement this, as this will not scale well
	
		const SESSION_MAX_AGE = 3600; // 1 hour timeout
		
		private $mysqli;
		
		public function connect($hostname, $username, $password, $database) {
			$this->mysqli = new mysqli($hostname, $username, $password, $database);
			if ($this->mysqli->connect_errno) {
				die('DB connection error');
			}
		}
		
		public function disconnect() {
			$this->mysqli->close();
			$this->mysqli = null;
		}
		
		
		
		/////////////////// - USERS & SESSIONS - ///////////////////
		
		public function createUser($type, $email, $password, $firstName, $lastName, $organisationName, $description, $country, $city, $street, $zip, $phone, $website) {
			$hashedPassword = sha1($password);
			
			if ($type == 'organisation') {
				$statement = $this->mysqli->prepare("INSERT INTO `users` (`id`, `type`, `email`, `description`, `hashed_password`, `country`, `city`, `street`, `zip`, `phone`, `website`, `organisation_name`) VALUES (NULL, 'organisation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
				$statement->bind_param('ssssssssss', $email, $description, $hashedPassword, $country, $city, $street, $zip, $phone, $website, $organisationName);
			} else {
				$statement = $this->mysqli->prepare("INSERT INTO `users` (`id`, `type`, `email`, `description`, `hashed_password`, `country`, `city`, `street`, `zip`, `phone`, `website`, `first_name`, `last_name`) VALUES (NULL, 'individual', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
				$statement->bind_param('sssssssssss', $email, $description, $hashedPassword, $country, $city, $street, $zip, $phone, $website, $firstName, $lastName);
			}
			$statement->execute();
		}
		
		public function testCredentials($email, $hashedPassword) {
			$statement = $this->mysqli->prepare("SELECT `email` FROM `users` WHERE `email` = ? AND `hashed_password` = ?");
			$statement->bind_param('ss', $email, $hashedPassword);
			$statement->execute();
			
			$statement->bind_result($email);
			if ($statement->fetch()) {
				return true;
			}
			return false;
		}
		
		public function getSessionUser($sessionId) {
			$result = $this->mysqli->query("SELECT * FROM `users` WHERE `session_id` = '".$this->mysqli->real_escape_string($sessionId)."' AND  `session_timestamp` + ".self::SESSION_MAX_AGE." > NOW()");
			if ($user = $result->fetch_assoc()) {
				return $user;
			}
			return [];
		}
		
		public function updateSession($email) {
			$sessionId = sha1($email.rand(100000,999999)); // TODO: Generate better session keys
			
			$statement = $this->mysqli->prepare("UPDATE `users` SET `session_id` = ?, `session_timestamp` = NOW() WHERE `email` = ?");
			$statement->bind_param('ss', $sessionId, $email);
			$statement->execute();
			
			return $sessionId;
		}
		
		/**
		 *	Updates the user's session timestamp.
		 */
		public function touchSession($email) {
			$statement = $this->mysqli->prepare("UPDATE `users` SET `session_timestamp` = NOW() WHERE `email` = ?");
			$statement->bind_param('s', $email);
			$statement->execute();
		}
		
		
		
		
		/////////////////// - OFFERS - ///////////////////
		
		/**
		 *	Returns the offers of a given user (by user id)
		 */
		public function getOffers($userId) {
			$statement = $this->mysqli->prepare("SELECT * FROM `offers` WHERE `user` = ?");
			$statement->bind_param('d', $userId);
			$statement->execute();
			$result = $statement->get_result();
			
			$offers = [];
			
			while ($offer = $result->fetch_assoc()) {
				$offers[] = $offer;
			}
			
			return $offers;
		}
		
		public function getOffer($id) {
			$statement = $this->mysqli->prepare("SELECT * FROM `offers` WHERE `id` = ?");
			$statement->bind_param('d', $id);
			$statement->execute();
			$result = $statement->get_result();
			if ($offer = $result->fetch_assoc()) {
				return $offer;
			}
			return [];
		}
		
		public function createOffer($userId, $type, $amount, $city, $country) {
			$statement = $this->mysqli->prepare("INSERT INTO `offers` (`id`, `type`, `amount`, `city`, `country`, `user`) VALUES (NULL, ?, ?, ?, ?, ?);");
			$statement->bind_param('ssssd', $type, $amount, $city, $country, $userId);
			$statement->execute();
			return $statement->insert_id;
		}
		
		public function deleteOffer($offerId) {
			$statement = $this->mysqli->prepare("DELETE FROM `offers` WHERE `id` = ?");
			$statement->bind_param('d', $offerId);
			$statement->execute();
		}
		
		public function updateOffer($offerId, $type, $amount, $city, $country) {
			$statement = $this->mysqli->prepare("UPDATE `offers` SET `type` = ?, `amount` = ?, `city` = ?, `country` = ? WHERE `id` = ?");
			$statement->bind_param('sdssd', $type, $amount, $city, $country, $offerId);
			$statement->execute();
		}
		
	}
?>