<?php class moduleTestCtrl extends appCtrl {



	public function is_available()
	{


		$vehicleModule = $this->load('module', 'vehicle');

		$vehicle_id = 59;

		var_dump($vehicleModule->pluckVendor_id($vehicle_id));


	}

	public function updateMileage()
    {

        $id = 12139;
        $vehicleModule = $this->load('module', 'vehicle');
        $data['mileage'] = '321900';
        $result = $vehicleModule->updateMileage($data, $id);

    }


    public function loadgump()
    {

    	$this->load('external', 'gump.class');
    	$gump = new GUMP();

    }


    public function testcli()
    {

        $clientModule = $this->load('module', 'client');
        $client_id = 500;        
        $vendor_id = 500;

        if($clientModule->addClient($client_id, $vendor_id))
        {
            echo "done";
        }
        else {
            echo "failed";
        }


    }






}