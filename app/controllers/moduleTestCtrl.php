<?php class moduleTestCtrl extends appCtrl {



	public function is_available()
	{


		$vehicleModule = $this->load('module', 'vehicle');

		$vehicle_id = 59;

		var_dump($vehicleModule->pluckVendor_id($vehicle_id));



	}



}