<?php

class bookingModule extends appCtrl {

	public $DB;

	public function __construct()
	{
		$this->DB = new Database();
		$this->DB->table = 'bookings';
	}



	public function listBookings($user_id, $role_id)
    {
    	
    	$allowedRoles = [1,3,4];
    	$data = [];

    	$user_id = (int) $user_id;
    	$role_id = (int) $role_id;

    	if( in_array((int) $role_id, $allowedRoles) )
    	{

    		
    		$query = $this->buildBookingListQueryString($user_id, $role_id);
    		

	    	if($data['b'] = $this->DB->rawSql($query)->returnData())
	    	{
	    		$data['message'] = "Success";
	    		$statusCode = 200;
	    			
	    	}
	    	else {
	    		$data['message'] = "Record not found";
	    		$statusCode = 204;
	    	}

    	}

    	else {
    		$data['message'] = "Access Denied";
    		$statusCode = 401;
    	}

    	return	array($data, $statusCode);
    }


    public function getSingle($id, $user_id = null, $role_id = null)
    {

        $query = "SELECT b.id, b.user_id, b.vehicle_id, b.client_id, DATE_FORMAT(b.sDate, '%d-%m-%Y') as 'sDate', b.eDate, b.sTime, b.eTime, 
    	DATE_FORMAT(b.startdatetime, '%d-%m-%Y %h:%i %p') AS 'startdatetime', DATE_FORMAT(b.enddatetime, '%d-%m-%Y %h:%i %p') AS 'enddatetime', b.expired, b.status, 
    	c.nameEN as 'clentNameEN', c.nameAR as 'clentNameAR',
    	v.photo as 'vphoto', v.mileage as 'mileage', v.vin as 'plateno', v.perDay as 'perDay',  
    	brands.nameEN as 'modelEN', brands.nameAR as 'modelAR', 
    	gMaker.titleEN as 'makerEN',
		gMaker.titleAR as 'makerAR' , 
		

		TIMESTAMPDIFF(DAY, startdatetime, enddatetime) AS 'forDays',  
		TRUNCATE(TIMESTAMPDIFF(MINUTE, startdatetime, enddatetime)/60, 2) AS 'forHours', 

		DATEDIFF(b.enddatetime, NOW()) AS 'exInDays'	

    	FROM bookings as b
    	INNER JOIN clients c on b.client_id = c.user_id 
    	INNER JOIN vehicles v on b.vehicle_id = v.id 
    	INNER JOIN gsection gMaker on v.maker = gMaker.id 
    	INNER JOIN brands on v.model_id = brands.id WHERE b.id = $id";

        if($data = $this->DB->rawSql($query)->returnData())
        {
            $statusCode = 200;

        }

        else {

            return false;

    	}

        return array($data, $statusCode);

    }




	public function is_reserved($vehicle_id, $startdatetime, $enddatetime)
    {
      
      $vehicle_id = (int)$vehicle_id;   

      $startdatetime = $this->dtDelayPull($startdatetime);
      $enddatetime = $this->dtDelayPush($enddatetime);

      $query = "SELECT * FROM {$this->DB->table} where vehicle_id = $vehicle_id AND (startdatetime <= '{$enddatetime}' AND enddatetime >= '{$startdatetime}')";

      if($data = $this->DB->rawSql($query)->returnData())
      {
	      	return true;	
      }
      else {
    	  	return false;	
      }
     
    }


	public function addBooking($dateArray)
	{


		if($lastID = $this->DB->insert($dateArray))
		{
			return $lastID;
		}

		else {

			return false;
		}

	}

	


	


	public function buildBookingListQueryString($user_id, $role_id)
	{
		$cDT = $this->Dt_24();

	    	$query = "SELECT b.id, b.vehicle_id, ";


	    	if($role_id == 3 || $role_id == 1) 
	    	{
	    		$query .= "b.client_id, b.user_id,";	
	    	}

	    	if($role_id == 4)
	    	{
	    		$query .= "b.user_id as vendor_id,";
	    	}

	    	$query .= " DATE_FORMAT(b.sDate, '%d-%m-%Y') as 'sDate', b.eDate, b.sTime, b.eTime, 
	    	DATE_FORMAT(b.startdatetime, '%d-%m-%Y %h:%i %p') AS 'startdatetime', 
	    	DATE_FORMAT(b.enddatetime, '%d-%m-%Y %h:%i %p') AS 'enddatetime', b.expired, b.status, ";

	    	if($role_id == 3 || $role_id == 1)
	    	{
	    		$query .= " c.name as 'clentNameEN', ";	
	    	}
	    	

	    	$query .= " v.photo as 'vphoto', v.mileage as 'mileage', v.vin as 'plateno',  
	    	brands.nameEN as 'modelEN', brands.nameAR as 'modelAR', 
	    	gMaker.titleEN as 'makerEN',
			gMaker.titleAR as 'makerAR', 
			

			TRUNCATE(TIMESTAMPDIFF(MINUTE, startdatetime, enddatetime)/1440, 2) AS 'forDays',  
			TRUNCATE(TIMESTAMPDIFF(MINUTE, startdatetime, enddatetime)/60, 2) AS 'forHours',  

			DATEDIFF(b.enddatetime, '". $cDT ."') AS 'exInDays', 	
			TRUNCATE(TIMESTAMPDIFF(MINUTE,  '". $cDT ."', startdatetime)/60, 2) AS 'initHours'  

	    	FROM bookings as b ";

	    	$query .= " INNER JOIN users c on b.client_id = c.id ";
	    	$query .= " INNER JOIN vehicles v on b.vehicle_id = v.id 
	    				INNER JOIN gsection gMaker on v.maker = gMaker.id 
	    				INNER JOIN brands on v.model_id = brands.id ";

		    if($role_id == 3)
		    {
		    	$query .= "	WHERE b.user_id = {$user_id} ";	
		    }

		    if($role_id == 4)
		    {
		    	$query .= "	WHERE b.client_id = {$user_id} ";	
		    }


	    	$query .= "	ORDER BY b.id DESC ";


	    	return $query;
	}



	public function dtDelayPush($dtStringInput)
    {
    	
	    $dtString = new DateTime($dtStringInput);
		$dtString->add(new DateInterval('PT2H'));
		return $dtString->format('Y-m-d H:i:s');
    }

    public function dtDelayPull($dtStringInput)
    {
    	
	    $dtString = new DateTime($dtStringInput);
		$dtString->sub(new DateInterval('PT2H'));
		return $dtString->format('Y-m-d H:i:s');

    }


    public function validateDuration($startPoint, $endPoint, $hoursTreshold = null)
	{



		if($hoursTreshold ==  null)
		{
			// there is no timeThreshold provided 

			$startPoint = new DateTime($startPoint);
			$endPoint = new DateTime($endPoint);

			if($startPoint == $endPoint)
			{
				

				return array (

					'status' => true,
					'message' => 'Both Time are Equal'

				);
			}
			else if ($startPoint < $endPoint) 
			{

				return array (

					'status' => true,
					'message' => 'Start Time is Less'

				);
			}

			else if ($startPoint > $endPoint)
			{


				return array (

					'status' => false,
					'message' => 'Start Date cannot be lesser than End Date'
				);
			}
			else {

				return array (

					'status' => false,
					'message' => 'Expected Dates format is YYYY-MM-DD'
				);
				
			}


		}

		else {

			// compare time and treshold
			if($this->validateDateTimeFormat($startPoint) && $this->validateDateTimeFormat($endPoint))
			{

				$currentDateTime = $this->Dt_24();
				
				$dateTimeObj = new Datetime();

				$currentDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $currentDateTime);
				$startDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $startPoint);
				$endDateTimeObj = $dateTimeObj->createFromFormat('Y-m-d H:i:s', $endPoint);

				$interval = $startDateTimeObj->diff($endDateTimeObj);
				$bookingDuration = (double) $interval->format('%h.%i');

				$hoursTreshold = (double) $hoursTreshold;

				if($currentDateTimeObj > $startDateTimeObj)
				{
					return array (
						'status' => false,
						'message' => 'Booking datetime of history are not allowed'
					);	
				}

				else if($startDateTimeObj > $endDateTimeObj)
				{
					return array (

						'status' => false,
						'message' => 'Start Datetime cannot be lesser than Ending Datetime'
					);
				}

				else if($hoursTreshold <= $bookingDuration)
				{
					
					return true;
				}

				else {

					return array (
						'status' => false,
						'message' => "Duration less than ". $hoursTreshold . ' Hours not allowed '
					);
					
				}

			}
			else {

				return array (
					'status' => false,
					'message' => 'Datetime format is invalid'
				);

			}

		}

	}


	public function validateDateTimeFormat($inputDateTime)
	{

		$dt = new Datetime();

		if($dt->createFromFormat('Y-m-d H:i:s', $inputDateTime))
		{
			
			return true;
		}
		else {
			
			return false;
		}

	}

	public function updateStatus($data, $id)
    {
        if($this->DB->update($data, $id))
        {
            return true;
        }
        else {
            return false;
        }
    }

    public function checkInitiated($vehicleId)
    {

        $id = (int) $vehicleId;
        $status = 'initiated';

        if( $data = $this->DB->build('S')->Colums('id')->Where("vehicle_id = '".$id."'")->Where("status = '".$status."'")->go()->returnData() );
        {

            if($data[0]['id'] != null)
            {
                return true;
            }
            else {
                return false;
            }
        }

    }



}