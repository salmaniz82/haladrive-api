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

	public function registerNewConsumer($data)
	{
		
		$data['password'] = sha1('123456');
		$data['role_id'] = 4;

		if($lastId = $this->DB->insert($data))
		{

			return $lastId;
		}
		else {
			 return false;
		}


	}


	public function emailExists($email)
	{

		if($user_id = $this->DB->pluck('email')->Where("email = '".$email."'"))
		{
			return true;
		}

		else {
			return false;
		}

	}



}