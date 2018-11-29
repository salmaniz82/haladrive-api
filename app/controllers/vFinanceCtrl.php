<?php 
class vFinanceCtrl extends appCtrl {

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
        $this->DB->table = 'vfinance';

	}

	public function index()
	{
		
        $user_id = (int) $this->jwtUserId();
        $vehicle_id = (int) $this->getID();

        $query = "SELECT vf.id, f.id AS 'finance_id', f.nameEN, f.nameAR, 
                    IF((SELECT vf.id) > 0, '1', '0') AS 'status' from vfinance as vf 
                    RIGHT JOIN finance f ON vf.finance_id = f.id AND vf.vehicle_id = {$vehicle_id} 
                    WHERE f.user_id = {$user_id}";

        if($data['vf'] = $this->DB->rawSql($query)->returnData())
        {
            $data['message'] = "Success";
            $data['status'] = true;
            $statusCode = 200;        
        }
        else {

            if($data['vf'] == null)
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


	public function save()
	{

        $data = [];

        $this->load('external', 'gump.class');
        $gump = new GUMP();
            $_POST = $gump->sanitize($_POST);
            $gump->validation_rules(array(
            
            
            'vehicle_id'        =>  'required|integer',
            'finance_id'       =>  'required|integer'
            
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

                $keys = array('vehicle_id', 'finance_id');
                $keys = $this->DB->sanitize($keys);
                $keys['user_id'] = (int) $this->jwtUserId();
                $keys['status'] = 1;

                if($lastID = $this->DB->insert($keys))
                {

                    $statusCode = 200;    
                    
                    $data['lastID'] = $lastID;
                    $data['message'] = 'Finance linked with Vehicle';
                    $data['status'] = true;
                }
                else {
                    $statusCode = 503;    
                    $data['message'] = 'Cannot linked Finance with Vehicle';
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