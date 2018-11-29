<?php 
class maintenanceCtrl extends appCtrl {

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
        $this->DB->table = 'maintenance';

	}

	public function index()
	{
		
        $user_id = (int) $this->jwtUserId();
        $vehicle_id = (int) $this->getID();

        $query = "SELECT 
        m.id, m.cost, m.miles, m.dated, m.description
        from maintenance as m 
        WHERE m.user_id = {$user_id} AND m.vehicle_id = {$vehicle_id} ORDER BY m.id DESC";

        if($data['m'] = $this->DB->rawSql($query)->returnData())
        {
            $data['message'] = "Success";
            $data['status'] = true;
            $statusCode = 200;        
        }
        else {

            if($data['m'] == null)
            {
                $data['message'] = "No data found from maitain ctrl for index function";
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

        $keys = array('vehicle_id', 'description', 'miles', 'cost');

            $this->load('external', 'gump.class');
            $gump = new GUMP();
            $_POST = $gump->sanitize($_POST);
            $gump->validation_rules(array(

            'vehicle_id'    => 'required|integer',
            'description'   =>  'required',
            'miles'         =>  'required|integer',
            'cost'          =>  'required|integer'
            
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

                $keys = array('vehicle_id','description', 'cost','miles');
                $keys = $this->DB->sanitize($keys);
                $keys['user_id'] = (int) $this->jwtUserId();
                $keys['miles'] = "4000";
                

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