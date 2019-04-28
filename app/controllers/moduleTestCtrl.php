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


    public function dateEvaluationTest()
    {

        
        $currentDateTime = $this->Dt_24();

        echo "Current Datetime" . $currentDateTime . "<br>";





        $bookingModule = $this->load('module', 'booking');

        $startDate = '2018-12-31';
        $endDate = '2019-01-05';


        $startTime = "09:00 PM";
        $endTime = "02:00 PM";


        $startTime = (string) $this->convertToMysqlTime($startTime);
        $endTime = (string) $this->convertToMysqlTime($endTime);




        $startPoint = (string) $this->mergeDateTime($startDate, $startTime);
        $endPoint = (string) $this->mergeDateTime($endDate, $endTime);

        echo "start Datetime" . $startPoint . "<br>";

        $dateTimeObj = new Datetime();

        $currentDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $currentDateTime);
        $startDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $startPoint);
        $endDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $endPoint);


        /*
        $interval = $currentDateTimeObj->diff($startDateTimeObj);
        $bookingDuration = (double) $interval->format('%h.%i');
        */


            if($currentDateTimeObj > $startDateTimeObj)
            {
                    
                       echo 'Booking datetime of history are not allowed';
                     
            }

            else {
                echo "all good";
            }
        

        die();

        $interval = $currentDateTimeObj->diff($startDateTimeObj);

        $diff = $interval->m;

        echo "<pre>";
        var_dump($diff);
        echo "</pre>";
        
        die();
        $result  = $bookingModule->validateDuration($startPoint, $endPoint);

        

    }






}