<?php 
class vInsuranceCtrl extends appCtrl {

	public $DB;


	public function __construct()
	{
		
        if(!JwtAuth::validateToken())
        {

            $data['status'] = false;
            $data['message'] = 'Access Denied no API keys was provided';
            $statusCode = 401;
            view::responseJson($data, $statusCode);
            die();
        }


        $this->DB = new Database();
        $this->DB->table = 'vinsurance';

	}

	public function index()
	{
		
        $user_id = (int) $this->jwtUserId();
        $vehicle_id = (int) $this->getID();


        $cd = $this->getDbCurrentDate();


                //DATE_FORMAT(b.startdatetime, '%d-%m-%Y);

        $query = "SELECT vi.id, vi.ins_id, vi.vehicle_id, vi.insuredName, vi.nationality, vi.policyno, vi.licensepurpose, 
        DATE_FORMAT(vi.registration, '%d-%m-%Y') as 'registration', DATE_FORMAT(vi.expiration, '%d-%m-%Y') as 'expiration', 
        TIMESTAMPDIFF(DAY, DATE(NOW()), vi.expiration) AS 'DaysToEx', IF((SELECT DaysToEx) > 0, 'Active', 'Expired') AS 'status', 
        i.nameEN as 'insuredVia'
        from vinsurance as vi 
        INNER JOIN insurance i on vi.ins_id = i.id 
        WHERE vi.user_id = {$user_id} AND vi.vehicle_id = {$vehicle_id} ORDER BY vi.id DESC";

        if($data['vi'] = $this->DB->rawSql($query)->returnData())
        {
            $data['message'] = "Success";
            $data['status'] = true;
            $statusCode = 200;        
        }
        else {

            if($data['vi'] == null)
            {
                $data['message'] = "No Insurance associated with Vehicle and User";
                $data['status'] = false;
                $statusCode = 404;
            }
            else {
                $data['message'] = "Error";
                $data['status'] = false;
                $statusCode = 500;
            }

        }
        $data['v'] = Route::crossFire("api/vehicles/{$vehicle_id}")['v'][0];       
        return view::responseJson($data, $statusCode);

	}

	public function single()
	{
	

	}

	public function save()
	{

        $data = [];

        $this->load('external', 'gump.class');
        $gump = new GUMP();
            $_POST = $gump->sanitize($_POST);
            $gump->validation_rules(array(
            
            'ins_id'            => 'required|integer',
            'vehicle_id'        =>  'required|integer',
            'insuredName'       =>  'required',
            'policyno'          => 'required',
            'licensepurpose'    => 'required',
            'registration'      => 'required',
            'expiration'        => 'required',
            'nationality'       => 'required'
        ));

            $pdata = $gump->run($_POST);


            if($pdata === false) 
            {
                $data['status'] = false;
                $data['message'] = 'Required fields were missing or supplied with invalid format';
                $data['errorlist'] = $gump->get_errors_array();
                $statusCode = 422;
            }
            else {

                $keys = array('ins_id', 'vehicle_id','insuredName', 'policyno','licensepurpose', 'registration', 'expiration', 'nationality');
                $keys = $this->DB->sanitize($keys);
                $keys['user_id'] = (int) $this->jwtUserId();
                $keys['photo'] = 'tempstring';

                if($lastID = $this->DB->insert($keys))
                {

                    $statusCode = 200;    
                    
                    $data['lastID'] = $lastID;
                    $data['message'] = 'Record Added with Success';
                    $data['status'] = true;
                }
                else {
                    $statusCode = 503;    
                    $data['message'] = 'Record cannot be added at this point please try again';
                    $data['debug'] = $this->DB;
                    $data['status'] = false;
                }

            }
                 
            view::responseJson($data, $statusCode);
	}

	public function update()
	{
        $id = $this->getID();
        $user_id = $this->jwtUserId();
        $role_id = $this->jwtRoleId();
        $_POST = Route::$_PUT;

        

        if( $record = $this->DB->getbyId($id)->returnData() )
        {
            if($record[0]['user_id'] == $user_id || $role_id == 1)
            {
        
                    $keys = array_keys($_POST);
                    $keys = $this->DB->sanitize($keys);

                    $keys['registration'] = $this->dateInputCheck($keys['registration']);
                    $keys['expiration'] = $this->dateInputCheck($keys['expiration']);


                   if( $this->DB->update($keys, $id) )
                   {
                        $statusCode = 200;
                        $data['message'] = 'Record Updated successfully';
                        $data['type'] = 'success';
                        $data['status'] = true;
                   }
                   else {
                    $data['debug'] = $this->DB;
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

	public function delete()
	{
	
        return $this->removeOwnerOrAdmin();
	}


}
?>