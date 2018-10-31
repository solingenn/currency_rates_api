<?php

class DbConn
{
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '';
	private $name = 'currency_exchange';

	// Connect method
	public function connect()
	{
		$conn_str = "mysql:host=$this->host; dbname=$this->name";
		$dbConn = new PDO($conn_str, $this->user, $this->pass);
		$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $dbConn;
	}
}