<?php

class vehicleModule extends appCtrl {


	public $DB;

	public function __construct()
	{

		$this->DB = new Database();
        $this->DB->table = 'vehicles';
	}


	public function is_available($vehicle_id)
	{
		
		$enabled = '1';

		if($data = $this->DB->pluck('id')->Where("is_available = " . $enabled . " AND id = ". $vehicle_id))
		{
	
			return $data;				
		}

		else {	

			return false;
		}

	}


	public function pluckVendor_id($vehicle_id)
	{
		
		return $this->DB->pluck('user_id')->where("id = $vehicle_id");

	}

	public function updateMileage(array $data, $vehicle_id)
    {

        if($result = $this->DB->update($data, $vehicle_id))
        {
           return true;
        }
        else {

            return false;
        }

    }

	
}