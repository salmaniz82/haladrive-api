<?php
class vehicleModule {


	public $DB;


	public function __construct()
	{

		$this->DB = new Database();
        $this->DB->table = 'vehicles';
	}









	public function is_available($vehicle_id)
	{
		$id = (int) $this->getID();	
		$enabled = '1';

		if($data = $this->DB->pluck('id')->Where("is_available = '".$enabled."'"))
		{
	
			return $data;				
		}

		else {	

			return false;
		}

	}


	public function pluckVendor_id($vehicle_id)
	{
		return $this->DB->pluck('user_id')->where('vehicle_id = $vehicle_id');
	}


	
	
}