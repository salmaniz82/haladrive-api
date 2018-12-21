<?php 
class clientsCtrl extends appCtrl {

	protected $DB;


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
        $this->DB->table = 'clients';

	}

	public function index()
	{
		
         $userID = (int) $this->jwtUserId();



        if(JwtAuth::$user['role_id'] == 1)
        {

            $query = "SELECT c.id, vc.vendor_id, vc.client_id, c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status from vendor_clients as vc INNER JOIN clients c on c.user_id = vc.client_id";
        }
        else {

            $query = "SELECT c.id, vc.vendor_id, vc.client_id, 
            c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status 
            from vendor_clients as vc 
            INNER JOIN clients c on c.user_id = vc.client_id WHERE vc.vendor_id = $userID";

        }

        if($data = $this->DB->rawSql($query)->returnData())
        {
            
            $statusCode = 200;
        }

        
        
        
		return view::responseJson($data, 200);

        die();
	}

    public function byCivilNo()
    {

        $userID = (int) $this->jwtUserId();

        $query = "SELECT c.civilno, c.photo from clients c
        inner join vendor_clients v
        on v.client_id = c.user_id
        where v.vendor_id = $userID AND v.status = 1";

        if($row = $this->DB->rawSql($query)->returnData())
        {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$value['civilno']] = $value['photo'];
            }
        }

        else {
            $data['civilno'] = null;
            $statusCode = 406;
        }

        return  view::responseJson($data, 200);

        die();

    }

	public function single()
	{
	

        $id = $this->getID();
        $userID = (int) $this->jwtUserId();

        $query = "SELECT c.id, vc.vendor_id, vc.client_id, c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status 
        from vendor_clients as vc 
        INNER JOIN clients c on c.user_id = vc.client_id WHERE vc.vendor_id = $userID AND c.id = $id";

        if(is_numeric($id))
        {
            if($data = $this->DB->rawSql($query)->returnData())
            {
                $statusCode = 200;
            }
            else {
                $data['message'] = 'Record not found with id under clients ' . $id;
                $data['status'] = false;
                $data['type'] = 'error';
                $statusCode = 500;
            }

            return view::responseJson($data, $statusCode);    
        }
        else {
            return false;
        }

	}

	public function save()
	{     
        /*
            1. validation
            2. duplicate entry check
            3. register consumer in users table
            4. add information in client table
            5. link client and vendor in vendor_clients table
        */
         $data = [];
         $this->load('external', 'gump.class');
         $gump = new GUMP();
         
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.

        $gump->validation_rules(array(
            'nameEN'  => 'required',
            'nameAR'  => 'required',
            'email'   => 'required|valid_email',
            'mobile'  => 'required|integer',
            'mobile2' => 'numeric',
            'civilno' => 'required|numeric|exact_len,12',
        ));

        

        if($gump->run($_POST) === false)
        {
            // validation failed

                $data['status'] = false;
                $data['message'] = 'Required fields were missing or supplied with invalid format';
                $data['errorlist'] = $gump->get_errors_array();
                $statusCode = 422;

        }
        else {
            // validation passes

            /*
            *   add registration data first get the last_id then
            *   add the last_id as user_id in the clients table
            *   
            */ 

            // adding registration data

                $clientModule = $this->load('module', 'client');
                $userModule = $this->load('module', 'user');

                $newUserEmail = $_POST['email'];

                

                $keys = array('nameEN', 'nameAR', 'email', 'civilno', 'mobile', 'mobile2');
                $keys = $this->DB->sanitize($keys);

            
                // registering user as a consumer
                $consumer['name'] = $keys['nameEN'];
                $consumer['email'] = $keys['email'];

                if($userModule->emailExists($newUserEmail))
                {

                    $statusCode = 422;
                    $data['status'] = false;
                    $data['message'] = 'User Already Exist with Email';
                }

                else if ($clientModule->existbyCivilId($_POST['civilno']))
                {
                    $statusCode = 422;
                    $data['status'] = false;
                    $data['message'] = 'User With Civil ID alreadty Exists';
                }

                else {

                    if($insertId = $userModule->registerNewConsumer($consumer))
                    {
                        $consumer_Id = $insertId;
                        // assigning a consumer to a vendor
                        $vendorID = JwtAuth::$user['id'];
                        $clientID = $consumer_Id;                      
                        // adding in clients table    
                        $keys['user_id'] = $consumer_Id;
                        $keys['status'] = 1;

                        if($clientModule->saveClientwithDetails($keys))
                        {
                            $clientModule->addClient($clientID, $vendorID);
                            $statusCode = 200;    
                            $data['message'] = 'Record Added with Success';
                            $data['status'] = true;
                        }
                        else {
                            $statusCode = 503;    
                            $data['message'] = 'Record cannot be added at this point please try again';
                            $data['status'] = false;
                        }
                    }

                }

        }
           

        view::responseJson($data, $statusCode);

	}

	public function update()
	{

		$id = $this->getID();
		$_POST = Route::$_PUT;

		if($this->DB->getbyId($id)->returnData())
		{
		    // valid record found with this id
            $keys = array_keys($_POST);

            $keys = $this->DB->sanitize($keys);


            if(isset($keys['vendor_id'])) {unset($keys['vendor_id']);}

            if(isset($keys['client_id'])) {unset($keys['client_id']);}



            if($this->DB->update($keys, $id))
            {
                // found and updated
                $data['message'] = "Cleints Updated";
                $data['type'] = "success";
                $data['status'] = true;
                $data['keys'] = $keys;
                $statusCode = 200;
            }

            else
                {
                    // found but not updated
                    $data['message'] = "Client cannot be updated";
                    $data['type'] = "error";
                    $data['status'] = false;

                    $data['debug'] = $this->DB;


                    $statusCode = 500;
                }
        }
        else
            {
                // record not found
                $data['message'] = "Cleint Not found with id " . $id;
                $data['type'] = "error";
                $data['status'] = false;
                $statusCode = 500;
            }

        return view::responseJson($data, $statusCode);
	}

	public function delete()
	{
	
           $data['message'] = 'Not Available at this point';
           $statusCode = 503;

           return view::responseJson($data, $statusCode);

      //  return $this->removeOwnerOrAdmin();

	}


}
?>