<?php
	/**
	 *	Provides the interface to the MySQL database.
	 */
	class Database { // TODO: Re-implement this, as this will not scale well
		
		private $mysqli;
		
		public function connect($hostname, $username, $password, $database) {
			$this->mysqli = new mysqli($hostname, $username, $password, $database);
			if ($this->mysqli->connect_errno) {
				die('DB connection error');
			}
		}
		
		public function disconnect() { // TODO: Actually use this at some point. Why did we implement this?
			$this->mysqli->close();
			$this->mysqli = null;
		}
		
		
		
		/////////////////// - USERS - ///////////////////
		
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

	public function login($email, $hashedPassword) {
			if ($this->database->testCredentials($email, $hashedPassword)) {				
				return $this->database->updateSession($email);
			}			
			return ['status' => 'error', 'message' => 'Invalid username or password'];
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
		
		public function getUser($email, $password) {
			$hashedPassword = self::hashPassword($password);
			
			$statement = $this->mysqli->prepare("SELECT * FROM `users` WHERE `email` = ? AND `hashed_password` = ?");
			$statement->bind_param('ss', $email, $hashedPassword);
			$statement->execute();
			$result = $statement->get_result();
			
			if ($user = $result->fetch_assoc()) {
				return $user;
			}
			return [];
		}
		
		public function getUserById($userId) {
			$statement = $this->mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
			$statement->bind_param('s', $userId);
			$statement->execute();
			$result = $statement->get_result();
			
			if ($user = $result->fetch_assoc()) {
				return $user;
			}
			return [];
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
		
		
		
		public function countAvailableItems($type) {
			$statement = $this->mysqli->prepare("SELECT COALESCE(SUM(`amount`), 0) as `amount` FROM `offers` WHERE `type` = ?");
			$statement->bind_param('s', $type);
			$statement->execute();
			$result = $statement->get_result();
			if ($offer = $result->fetch_assoc()) {
				return intval($offer['amount']);
			}
			return 0;
		}
		
		public function requestItems($userId, $type, $amount) { // TODO: This should be done as a transaction
			
			$amountSoFar = 0;
			$usersToInform = []; // Key = user id, value = amount of items requested
			
			$i = 0;
			while ($amountSoFar < $amount && $i < 10) {
				$i++;
				$statement = $this->mysqli->prepare("SELECT * FROM `offers` WHERE `type` = ? AND `requested_by` IS NULL ORDER BY `amount` DESC LIMIT 10");
				$statement->bind_param('s', $type);
				$statement->execute();
				
				$result = $statement->get_result();
				
				if ($result->num_rows == 0) {
					break;
				}
				
				while (($offer = $result->fetch_assoc()) && $amountSoFar < $amount) {
					
					if (!isset($usersToInform[$offer['user']])) {
						$usersToInform[$offer['user']] = 0;
					}
					
					if ($amountSoFar + $offer['amount'] > $amount) {
						
						$diff = $amount - $amountSoFar;
						$leftOver = $offer['amount'] - $diff;
						
						// Split the offer in two parts (one requested and one still free)
						$statement2 = $this->mysqli->prepare("INSERT INTO `offers` (`type`, `amount`, `city`, `country`, `user`, `requested_by`) VALUES (?, ?, ?, ?, ?, ?);");
						$statement2->bind_param('ssssdd', $offer['type'], $diff, $offer['city'], $offer['country'], $offer['user'], $userId);
						$statement2->execute();
						
						
						$statement2 = $this->mysqli->prepare("UPDATE `offers` SET `amount` = ? WHERE `id` = ?");
						$statement2->bind_param('dd', $leftOver, $offer['id']);
						$statement2->execute();
						
						$usersToInform[$offer['user']] += $diff;
						$amountSoFar = $amount;
					} else {
						
						// Mark the offer as requested
						$statement2 = $this->mysqli->prepare("UPDATE `offers` SET `requested_by` = ? WHERE `id` = ?");
						$statement2->bind_param('dd', $userId, $offer['id']);
						$statement2->execute();
						
						
						$usersToInform[$offer['user']] += $offer['amount'];
						$amountSoFar += $offer['amount'];
					}
				}
			}
			
			return $usersToInform;
		}
		
		
		
		
		private static function hashPassword($password) {
			return sha1($password); // TODO: Use a better (and salted!) hashing function
		}
		
	}
?>