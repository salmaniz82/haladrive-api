<?php
class bookingModule extends appCtrl {

	public $DB;

	public function __construct()
	{
		$this->DB = new Database();
		$this->table = 'bookings';
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




	public function is_reserved($vehicle_id, $startdatetime, $enddatetime)
    {
      
      $vehicle_id = (int)$vehicle_id;   

      $startdatetime = $this->dtDelayPull($startdatetime);
      $enddatetime = $this->dtDelayPush($enddatetime);

      $query = "SELECT * FROM {$this->DB->table} where vehicle_id = $vehicle_id AND (startdatetime <= '{$enddatetime}' AND enddatetime >= '{$startdatetime}')";

      if($this->DB->rawSql($query)->returnData())
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

	
	public function cancleBooking()
	{



	}

	public function approveBooking()
	{


	}

	public function removeBooking()
	{



	}


	public function initiateBooking()
	{



	}

	public function completeBooking()
	{


	}


	public function updateMiles()
	{




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


}