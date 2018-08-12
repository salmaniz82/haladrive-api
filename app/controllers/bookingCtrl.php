<?php 
class bookingCtrl extends appCtrl {

	protected $DB;

	public function __construct()
	{
      
        $this->DB = new Database();
        $this->DB->table = 'bookings';
    }

    public function index()
    {
    	
    	$allowedRoles = [1,3,4];
    	$data = [];

    	if( JwtAuth::validateToken() && in_array((int) JwtAuth::$user['role_id'], $allowedRoles) )
    	{

    		$user_id = (int) JwtAuth::$user['id'];
    		$role_id = (int) JwtAuth::$user['role_id'];

    		$bookingModule = $this->load('module', 'booking');

    		$result = $bookingModule->listBookings($user_id, $role_id);

    		$data = $result[0];
    		$statusCode = $result[1];

    		return view::responseJson($data, $statusCode);

    	}

    	else {

    		return $this->uaReponse();
    	}

    		
    		
    }



    public function single()
    {

    	$id = $this->getID();
    	
    	$data = [];


		$cDT = $cDT = $this->Dt_24();

    	$query = "SELECT b.id, b.user_id, b.vehicle_id, b.client_id, DATE_FORMAT(b.sDate, '%d-%m-%Y') as 'sDate', b.eDate, b.sTime, b.eTime, 
    	DATE_FORMAT(b.startdatetime, '%d-%m-%Y %h:%i %p') AS 'startdatetime', DATE_FORMAT(b.enddatetime, '%d-%m-%Y %h:%i %p') AS 'enddatetime', b.expired, b.status, 
    	c.nameEN as 'clentNameEN', c.nameAR as 'clentNameAR',
    	v.photo as 'vphoto', v.mileage as 'mileage', v.vin as 'plateno', v.perDay as 'perDay',  
    	brands.nameEN as 'modelEN', brands.nameAR as 'modelAR', 
    	gMaker.titleEN as 'makerEN',
		gMaker.titleAR as 'makerAR' , 
		

		TIMESTAMPDIFF(DAY, startdatetime, enddatetime) AS 'forDays',  
		TRUNCATE(TIMESTAMPDIFF(MINUTE, startdatetime, enddatetime)/60, 2) AS 'forHours', 

		DATEDIFF(b.enddatetime, '". $cDT ."') AS 'exInDays'	

    	FROM bookings as b
    	INNER JOIN clients c on b.client_id = c.user_id 
    	INNER JOIN vehicles v on b.vehicle_id = v.id 
    	INNER JOIN gsection gMaker on v.maker = gMaker.id 
    	INNER JOIN brands on v.model_id = brands.id WHERE b.id = $id";

    	if($data = $this->DB->rawSql($query)->returnData())
    	{

    		


    	}

    	else {

    		$data = $this->DB;

    	}


    	view::responseJson($data, 200);

    }


    public function crossFire()
    {
    	
    	$data = [];
    	$query = "SELECT b.id, b.user_id, b.vehicle_id, b.client_id, b.sDate, b.eDate, 
    	b.sTime, b.eTime, b.startdatetime, b.enddatetime, b.expired, 
    	c.nameEN as 'clentNameEN', c.nameAR as 'clentNameAR'
    	FROM bookings as b
    	INNER JOIN clients c on b.client_id = c.id";
    	if($data = $this->DB->rawSql($query)->returnData())
    	{
		

    		foreach ($data as $key => $value) {
    			$vehicle_id = $value['vehicle_id'];


    			$data[$key]['v'] = view::fetchRoute('api/vehicles/'.$vehicle_id)['v'];
    		}

    	}

    	else {
    		$data['debug'] = $this->DB;
    	}

    	view::responseJson($data, 200);

    }



    
    public function commonBookingGateway()
    {

    	$allowedRoles = [1,3,4];
    	$data = [];

    	if( JwtAuth::validateToken() && in_array((int) JwtAuth::$user['role_id'], $allowedRoles) )
    	{
    		

    		$role_id = (int) JwtAuth::$user['role_id'];	

    		$gump = new GUMP();
			$_POST = $gump->sanitize($_POST);

			$validationRules = array(
				'civilno' => 'required',
				'sDate'    =>  'required|date',
				'eDate'    =>  'required|date',
				'sTime'    =>  'required',
				'eTime'    =>  'required',		
				'vehicle_id' => 'required|integer'
			);

			if($role_id == 4) {unset($validationRules['civilno']);}

			$gump->validation_rules($validationRules);

			if($gump->run($_POST) === false)
			{
				
				// validation failed
				$data['message'] = "Validation Error";
				$statusCode = 500;
				return view::responseJson($data, $statusCode);
			}

			else 
			{
				// validaton passes load required modules
				$vehicleModule = $this->load('module', 'vehicle');
				$bookingModule = $this->load('module', 'booking');
				$clientModule = $this->load('module', 'client');

				$vehicle_id = $_POST['vehicle_id'];

				if($role_id == 3)
				{ // vendor specific
					$civilno =  $_POST['civilno'];

		 			if(!$client_id = $clientModule->pluckIdByCivilId($civilno))
		 			{
		 				// client not found
		 				$data['message'] = "Client Not Found";
						$statusCode = 500;
						return view::responseJson($data, $statusCode);

		 			}
		 			$user_id = (int) JwtAuth::$user['id'];

		 			$vendor_id = $user_id;
				}
				if($role_id == 4)
				{ // client specific
					unset($validationRules['civilno']);	
		 			
		 			if(!$vendor_id = (int) $vehicleModule->pluckVendor_id($vehicle_id))
		 			{
		 				$data['message'] = "Vehicle Not Found";
						$statusCode = 500;
						return view::responseJson($data, $statusCode);
		 			}

		 			$user_id = $vendor_id;
		 			$client_id = (int) JwtAuth::$user['id'];	
				}


				$keys = array('vehicle_id', 'sDate', 'eDate', 'sTime', 'eTime');
				$keys = $this->DB->sanitize($keys);
				$keys['client_id'] = $client_id;
				$keys['user_id'] = $user_id;
				$keys['expired'] = 0;
				// format start and end time
				$this->prepareDateTime($keys);
				$keys['status'] = 'pending';

				if( $vehicleModule->is_available($vehicle_id) )
				{

					if(!$bookingModule->is_reserved($vehicle_id, $keys['startdatetime'], $keys['enddatetime']))
					{

						if($lastID = $bookingModule->addBooking($keys))
						{

							
							$data['message'] = 'New Booking created with ';

							if(!$clientModule->isClient($client_id, $vendor_id))
							{							
								
								$data['message'] .= ($role_id == 3) ? "New Client" : "New Vendor ";
								
								$clientModule->addClient($client_id, $vendor_id);

							}

							$data['message'] .= ($role_id == 3) ? "Existing Client" : "Existing Vendor ";
							$statusCode = 200;
							return view::responseJson($data, $statusCode);

							
						}
						else {

							$data['message'] = "Error While Adding Booking";
							$statusCode = 500;
							return view::responseJson($data, $statusCode);

						}

					}
					else {
						$data['message'] = "Not available with provided date and time";
						$statusCode = 500;
						return view::responseJson($data, $statusCode);
					}
					
				}

				else {
						$data['message'] = "Vehicle is not avaible";
						$statusCode = 500;
						return view::responseJson($data, $statusCode);
				}

			}


    	}
    	else {

    		return $this->uaReponse();

    	}


    }


    public function delete()
    {
    	$id = $this->getID();
    	$user_id = $this->jwtUserId();
        $role_id = $this->jwtRoleId();

		if($record = $this->DB->getbyId($id)->returnData() )
		{
            
			if($record[0]['user_id'] == $user_id || $role_id == 1)
			{
				if($record[0]['status'] == 'cancelled')
				{

					if($this->DB->delete($id))
		            {
		                $statusCode = 200;
		                $data['message'] = 'Record Successfully Removed From Database';
		                $data['type'] = 'success';
		                $data['status'] = true;           
		            }
		            else{
		                $statusCode = 503;
		                $data['message'] = 'Service is unavailable at the moment please try later';
		                $data['type'] = 'failed';
		                $data['status'] = false;
		            }			

					
				}
				else {	
					$statusCode = 403;
		            $data['message'] = 'Not cancelled hence cannot be removed';
		            $data['type'] = 'failed';
		            $data['status'] = false;			
				}          

	        }
	        else {
			    $statusCode = 404;
			    $data['message'] = 'cannot find record with this id';
	            $data['type'] = 'error';
	            $data['status'] = false;
	        	}
			}
			else {
					$statusCode = 401;
	                $data['message'] = 'Un Authorized permission denied';
	                $data['type'] = 'error';
	                $data['status'] = false;
			}		

	        view::responseJson($data, $statusCode);
    }

    public function update()
    {
      $id = $this->getID();
      $_POST = Route::$_PUT;

      $doUpdate = true;

    if($bookingData = $this->DB->getbyId($id)->returnData())
    {
        // valid record found with this id
            $keys = array_keys($_POST);

            $vehicle_id = $bookingData[0]['vehicle_id'];
            $bookingId = $bookingData[0]['id'];



            $datetimeStamp = date('Y-m-d H:i:s');

            $keys = $this->DB->sanitize($keys);

            if(isset($keys['status']) && $keys['status'] == 'initiated')
            {		
            	// check simultanous initiation of same vehicles
				if($this->checkInitiated($vehicle_id) == true)
				{
					$doUpdate = false;
				}


			}
			elseif (isset($keys['status']) && $keys['status'] == 'completed')
			{
				$keys['completed_at'] = $datetimeStamp;
			}

			elseif (isset($keys['status']) && $keys['status'] == 'cancelled')
			{

				$nulldateTime = '1000-01-01 00:00:00';
				$keys['completed_at'] = $nulldateTime;
				$keys['initiated_at'] = $nulldateTime;

				$keys['startMileage'] = "0";
				$keys['endMileage'] = "0";
			}

			if($doUpdate)
			{
				// on legal intiated status 
					

				if($keys['status'] == 'cancelled')
				{

					$keys['completed_at'] = $nulldateTime;
					$keys['initiated_at'] = $nulldateTime;

				}	
				else {
					$keys['initiated_at'] = $datetimeStamp;
				}

				$startMileage = view::fetchRoute('api/vehicles/available/'.$vehicle_id)[0]['mileage'];

				if($keys['status'] !== 'cancelled')
				{
					$keys['startMileage'] = $startMileage;	
				}

				

				if($this->DB->update($keys, $id))
            	{
	                // found and updated
	                $data['message'] = "Record Updated";
	                $data['type'] = "success";
	                $data['status'] = true;
	                $data['keys'] = $keys;
	                $statusCode = 200;
                if($keys['status'] == 'completed')
                {
                	$vehicle_id = $bookingData[0]['vehicle_id'];
                	
					$pushData = array('mileage' => $keys['endMileage']);

					$res = Route::crossFire("api/vehicles/{$vehicle_id}", 'PUT', $pushData);


					$this->generateInvoice($bookingId);
					$this->DB->table = 'bookings';
                }

            }

            else
                {
                    // found but not updated
                    $data['message'] = "Record cannot be updated";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $statusCode = 500;
                }	
			}

			else {

				    $data['message'] = "Simultanious Initiation on vehicle not allowed";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $statusCode = 403;
			}

            
        }
        else
            {
                // record not found
                $data['message'] = "Record Not found with id " . $id;
                $data['type'] = "error";
                $data['status'] = false;
                $statusCode = 500;
            }

        return view::responseJson($data, $statusCode);
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


	public function generateInvoice($bookingId)
	{

		$this->DB->table = 'invoices';


		/*
			$dataFields;
			$vehicle_id, $bookingId;
		*/

		if(JwtAuth::validateToken())
    	{
    		$user_id = (int) JwtAuth::$user['id'];		
    	}


		if($bData = view::fetchRoute('api/booking/'.$bookingId))
		{
			/* preparing to insert booking data some data will be copied to invoice

			id
			booking_id
			
			perHour
			perDay
			forHours
			forDays
			created_at
			status

			*/

			$perDay = $bData[0]['perDay'];

			$keys['booking_id'] = $bookingId;
			$keys['user_id'] = $user_id;

			$keys['perDay'] = $perDay;
			$keys['status'] = "Unpaid";

			if($this->DB->insert($keys))
			{
				return true;
			}

			else {
				return $this->DB;
			}

		}

		else {
			return false;
		}
		

	}


	private function isClient($vendor_id, $client_id)
	{


			$this->DB->table = 'vendor_clients';
			if(JwtAuth::validateToken() && JwtAuth::$user['role_id'] == 4)
			{
				$vClientID = (int) JwtAuth::$user['id'];
			}
			else {
				$vClientID = (int) $client_id[0]['user_id'];	
			}

			
			if(!$vClient = $this->DB->build('S')->Colums('id')->Where("vendor_id = ". $vendor_id )->Where( "client_id = ". $vClientID )->go()->returnData())
			{

					$keys2['vendor_id'] = $vendor_id;
                    $keys2['client_id'] = $vClientID;
                    $keys2['status'] = 1;
                    if($r = Route::crossFire("api/vclients", 'POST', $keys2))
                    {
                    	return $r;
                    }
                    else {
                    	return 'Failed to Assigned A Client';
                    }
				
			}
			else {
				return null;
			}
			

	}



	public function prepareDateTime(&$keys)
	{
	
		$keys['sTime'] = $this->convertToMysqlTime($keys['sTime']);	
		$keys['eTime'] = $this->convertToMysqlTime($keys['eTime']);
		$keys['startdatetime'] = $this->mergeDateTime($keys['sDate'], $keys['sTime']);
		$keys['enddatetime'] = $this->mergeDateTime($keys['eDate'], $keys['eTime']);

	}


}