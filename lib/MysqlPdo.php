<?php

class MysqlPdo
{
	public $db;

	public function __construct($config)
	{
		try {
			$db = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'], $config['user'], $config['password']);
			$this->db = $db;
			return $db;
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "</br>";
		}
	}
}
?>