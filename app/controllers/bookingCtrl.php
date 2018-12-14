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

        $bookingModule = $this->load('module', 'booking');

        if($result = $bookingModule->getSingle($id))
        {
            $data = $result[0];
            $statusCode = $result[1];
        }
        else {
            $data['message'] = "Record Not found";
            $statusCode = 404;
        }

        view::responseJson($data, $statusCode);

    }

    public function commonBookingGateway()
    {

    	$allowedRoles = [1,3,4];
    	$data = [];

        if(!isset($_POST)) {
            return   $this->emptyRequestResponse();
            exit();
        }

    	if( JwtAuth::validateToken() && in_array((int) JwtAuth::$user['role_id'], $allowedRoles) )
    	{
    		$role_id = (int) JwtAuth::$user['role_id'];
            $this->load('external', 'gump.class');
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
				$statusCode = 406;
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
		 			{// client not found
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

				$tolerance = $bookingModule->validateDuration($keys['startdatetime'], $keys['enddatetime'], (double)2);

				if(is_array($tolerance))
				{
					$data['message'] = $tolerance['message'];
					$statusCode = 406;
				    return view::responseJson($data, $statusCode);
				}

				if( $vehicleModule->is_available($vehicle_id) )
				{
					if(!$bookingModule->is_reserved($vehicle_id, $keys['startdatetime'], $keys['enddatetime']))
					{
						if($lastID = $bookingModule->addBooking($keys))
						{
							$data['message'] = 'New Booking created ';
							if(!$clientModule->isClient($client_id, $vendor_id))
							{
								
                                

								if(!$clientModule->addClient($client_id, $vendor_id))
                                {
                                    $data['message'] .= ($role_id == 3) ? " Client assignment failed " : " Vendor Assignment Failed ";
                                    
                                }

                                else {
                                    $data['message'] .= ($role_id == 3) ? "New Client " : " New Vendor ";
                                }

                                $statusCode = 200;
							}
                            else {

                                $data['message'] .= ($role_id == 3) ? "Existing Client" : "Existing Vendor ";
                                $statusCode = 200;

                            }
							
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
		            $data['message'] = 'Status other than cancelled cannot be removed';
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
        // this has got over bloaded this should only be used to just manage the updates in the status
        $id = $this->getID();
        $_POST = Route::$_PUT;
        $bookingModule = $this->load('module', 'booking');
        if($bookingData = $bookingModule->getSingle($id))
        {// valid record found with this id
            $vehicle_id = $bookingData[0][0]['vehicle_id'];
            $bookingId = $bookingData[0][0]['id'];
            $vehicleMileage = $bookingData[0][0]['mileage'];

            $datetimeStamp = date('Y-m-d H:i:s');
            $nulldateTime = '1000-01-01 00:00:00';

            $keys = array_keys($_POST);
            $keys = $this->DB->sanitize($keys);

            if($keys['status'] == "initiated"){

                $keys['initiated_at'] = $datetimeStamp;
                $keys['completed_at'] = $nulldateTime;
                $keys['startMileage'] = $vehicleMileage;
                $keys['endMileage'] = "0";

                if($bookingModule->updateStatus($keys, $bookingId))
                {
                    $data['message'] = "Booking Initialized";
                    $data['type'] = "success";
                    $data['status'] = true;
                    $data['keys'] = $keys;
                    $statusCode = 200;
                }
                else {
                    $data['message'] = "Failed to Initialise booking";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $data['keys'] = $keys;
                    $statusCode = 500;
                }

            }
            elseif($keys['status'] == "cancelled"){

                $keys['completed_at'] = $nulldateTime;
                $keys['initiated_at'] = $nulldateTime;
                $keys['startMileage'] = "0";
                $keys['endMileage'] = "0";

                if($bookingModule->updateStatus($keys, $bookingId))
                {
                    $data['message'] = "Cancellation Done";
                    $data['type'] = "success";
                    $data['status'] = true;
                    $data['keys'] = $keys;
                    $statusCode = 200;
                }
                else {
                    $data['message'] = "Failed during Cancellation";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $data['keys'] = $keys;
                    $statusCode = 500;
                }

            }
            elseif($keys['status'] == "completed"){

                $keys['completed_at'] = $datetimeStamp;
                if(isset($keys['initiated_at'])) {unset($keys['initiated_at']);}

                $keys['startMileage'] = $vehicleMileage;
                $keys['completed_at'] = $datetimeStamp;
                // update data on booking table then update vehicle and generate invoice

                if($vehicleMileage < (int) $keys['endMileage'] )
                { // only if end mileage is greater then start mileage

                    if($bookingModule->updateStatus($keys, $bookingId))
                    {
                        $pushData = array('mileage' => $keys['endMileage']);
                        $vehicleModule = $this->load('module', 'vehicle');
                        $vehicleModule->updateMileage($pushData, $vehicle_id);
                        $invoiceModule = $this->load('module', 'invoice');
                        $invoiceModule->generateInvoice($bookingId);

                        $data['message'] = "Invoice Generated and Vehicle Updated";
                        $data['type'] = "success";
                        $data['status'] = true;
                        $data['keys'] = $keys;
                        $statusCode = 200;

                    }
                    else {
                        $data['message'] = "Cannot updated complete completion";
                        $data['type'] = "error";
                        $data['status'] = false;
                        $data['keys'] = $keys;
                        $statusCode = 500;

                    }
                }
                else {

                    $data['message'] = "Failed complete Invoice : Mileage cannot be less than : $vehicleMileage";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $data['keys'] = $keys;
                    $statusCode = 500;

                }

            }
            elseif($keys['status'] == "confirmed"){

                $confirmKeys = array('status'=> "confirmed");

                if($bookingModule->updateStatus($confirmKeys,  $bookingId))
                {
                    $data['message'] = "Confirmation Completed";
                    $data['type'] = "success";
                    $data['status'] = true;
                    $data['keys'] = $keys;
                    $statusCode = 200;
                }
                else {
                    $data['message'] = "Confirmation Failed";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $data['keys'] = $keys;
                    $statusCode = 500;
                }

            }
            else {

                $data['message'] = "Un known status Type";
                $data['type'] = "error";
                $data['status'] = false;
                $data['keys'] = $keys;
                $statusCode = 500;
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

	public function prepareDateTime(&$keys)
	{
		$keys['sTime'] = $this->convertToMysqlTime($keys['sTime']);	
		$keys['eTime'] = $this->convertToMysqlTime($keys['eTime']);
		$keys['startdatetime'] = $this->mergeDateTime($keys['sDate'], $keys['sTime']);
		$keys['enddatetime'] = $this->mergeDateTime($keys['eDate'], $keys['eTime']);
	}

}