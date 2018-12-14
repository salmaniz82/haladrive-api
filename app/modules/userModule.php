<?php 
class userModule extends appCtrl{

	public $DB;
	
	public function __construct()
	{
		$this->DB = new Database();
		$this->DB->table = 'users';
	}


	public function addNewUser($data)
	{


		


	}


}