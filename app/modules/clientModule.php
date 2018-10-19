<?php
class clientModule extends appCtrl{


	public $DB;


	public function __construct()
	{

		
		$this->DB = new Database();
        $this->DB->table = 'clients';

	}



	public function isClient($client_id, $vendor_id)
	{
	
		$this->DB->table = 'vendor_clients';
		if($id = $this->DB->build('S')->Colums('id')->Where("vendor_id = ". $vendor_id )->Where( "client_id = ". $client_id )->go()->returnData())
		{
			$result = true;
		}

		else {
			$result = false;
		}

		$this->DB->table = 'clients';
		return $result;

	}

	public function addClient($clientID, $vendorID)
	{

	
			$this->DB->table = 'vendor_clients';
			$data['vendor_id'] = (int) $vendorID;
			$data['client_id'] = (int) $clientID;

			if($result = $this->DB->insert($data))
			{
				
			}
			else {
				$result = false;
			}

			$this->DB->table = 'clients';
			return $result;

	}

	public function removeClient($clientID, $vendorID)
	{




	}



	public function pluckIdByCivilId($civilno)
	{

		if($user_id =$this->DB->pluck('user_id')->Where("civilno = '".$civilno."'"))
		{
			return $user_id;
		}

		else {
			return false;
		}


	}


	

}