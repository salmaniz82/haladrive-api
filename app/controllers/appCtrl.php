<?php 
class appCtrl {



	public function load($loadType, $Loadentity)
	{

		if($loadType == 'module')
		{
			
			require_once ABSPATH.'modules/'.$Loadentity.'Module.php';
			$ModuleClass =  $Loadentity.'Module';
			return new $ModuleClass();
		}

		elseif($loadType == 'external')
		{
			
			$path = ABSPATH.'external/'.$Loadentity.'.php';
			require_once($path);
			
		}

	}
	
	

	public function uaReponse() 
	{

		$data['status'] = false;
    	$data['message'] = "Access Denied";
	    $statusCode = 401;
    	return view::responseJson($data, $statusCode);
	}


	public function emptyRequestResponse()
	{

		$data['status'] = false;
    	$data['message'] = "The Request is Empty Process Terminated";
	    $statusCode = 403;
    	return view::responseJson($data, $statusCode);

	}



	public function getID()
	{
		
		if( isset(Route::$params['id']) )
		{
			return Route::$params['id'];
		}
		else 
		{
			return null;
		}

	}

	public function appMethod()
	{
		return 'this is from the appcontroller';
	}

	
	public function canManageCourse()
	{
		if( Auth::loginStatus() && (Auth::User()['role_id'] == 1 ))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	public function convertToMysqlTime($rawTime)
    {
  	
		if($this->istime24hrs($rawTime))
		{
			return $rawTime;
		}
		else {
			$tempTime = DateTime::createFromFormat( 'H:i A', $rawTime);
			$output = $tempTime->format('H:i:s');
			return $output;		
		}

		
    }

    public function mergeDateTime($inputDate, $inputTime)
    {

    	$date = new DateTime($inputDate);
		$time = new DateTime($inputTime);

		// Solution 1, merge objects to new object:
		$merge = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));

		return $merge->format('Y-m-d H:i:s'); // Outputs '2017-03-14 13:37:42'


		/* Solution 2, update date object with time object:
		$date->setTime($time->format('H'), $time->format('i'), $time->format('s'));
		echo $date->format('Y-m-d H:i:s'); // Outputs '2017-03-14 13:37:42'

		*/


    }

    public function jwtUserId()
    {

    	if(JwtAuth::validateToken())
		{
			return (int) JwtAuth::$user['id'];
		}
		else {
			return false;
		}

    }

    public function jwtRoleId()
    {

    	if(JwtAuth::validateToken())
		{
			return (int) JwtAuth::$user['role_id'];
		}
		else {
			return false;
		}

    }


    public function removeOwnerOrAdmin()
    {
    	$id = $this->getID();
        $user_id = $this->jwtUserId();
        $role_id = $this->jwtRoleId();


		if( $record = $this->DB->getbyId($id)->returnData() )
		{
            if($record[0]['user_id'] == $user_id || $role_id == 1)
            {
                   if( $this->DB->delete($id) )
                   {
                        $statusCode = 200;
                        $data['message'] = 'Record removed successfully';
                        $data['type'] = 'success';
                        $data['status'] = true;
                   }
            }
            else {
                $statusCode = 401;
                $data['message'] = 'Un Authorized permission denied';
                $data['type'] = 'error';
                $data['status'] = false;
            }

        }
        else {
		    $statusCode = 404;
		    $data['message'] = 'cannot find record with this id';
            $data['type'] = 'error';
            $data['status'] = false;
        }


	    return  view::responseJson($data, $statusCode);

    }


    public function getDbCurrentDateTime()
    {
    	

    	$cDtDB = $this->DB->rawSql("SELECT NOW() as 'cd' ")->returnData();
		return $cDtDB[0]['cd'];
		
    }

    public function getDbCurrentDate()
    {

    	$db = new Database();
   	   	$cDtDB = $this->DB->rawSql("SELECT DATE(NOW()) AS 'currentDate'")->returnData();
		return $cDtDB[0]['currentDate'];
    }

    public function dateInputCheck($inputDate)
    {

		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$inputDate)) 
		{
	    		return $inputDate;
		} 
		else {
		   	return date('Y-m-d', strtotime($inputDate));
		}	
		

    }


    public function Dt_24()
    {
    	return Date('Y-m-d H:i:s');
    }


    public function istime24hrs($timeInput)
	{	
		return preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $timeInput);
	}

    
}
