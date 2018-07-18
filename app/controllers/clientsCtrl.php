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

            $query = "SELECT c.id, vc.vendor_id, vc.client_id, c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status from vendor_clients as vc INNER JOIN clients c on c.id = vc.client_id";
        }
        else {

            $query = "SELECT c.id, vc.vendor_id, vc.client_id, c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status from vendor_clients as vc INNER JOIN clients c on c.id = vc.client_id WHERE vc.vendor_id = $userID";

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
        $query = "SELECT c.civilno, c.photo from vendor_clients as vc 
        INNER JOIN clients c on c.id = vc.client_id 
        WHERE vc.vendor_id = $userID AND c.status = 1";

        if($row = $this->DB->rawSql($query)->returnData())
        {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$value['civilno']] = $value['photo'];
            }
        }

        return  view::responseJson($data, 200);

        die();

    }

	public function single()
	{
	

        $id = $this->getID();
        $userID = (int) $this->jwtUserId();

        $query = "SELECT c.id, vc.vendor_id, vc.client_id, c.nameEN, c.nameAR, c.mobile, c.email, c.civilno, c.mobile, c.mobile2, c.status from vendor_clients as vc INNER JOIN clients c on c.id = vc.client_id WHERE vc.vendor_id = $userID AND c.id = $id";

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
        $data = [];

          // server side validation is yet pending for this processing

         $gump = new GUMP();

        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.

        $gump->validation_rules(array(
            'nameEN'  => 'required',
            'nameAR'  => 'required',
            'email'   => 'required|valid_email',
            'mobile'  => 'required|phone_number',
            'mobile2' => 'phone_number',
            'civilno' => 'required|numeric|exact_len,12',
        ));

        $pdata = $gump->run($_POST);

        if($pdata === false)
        {
            // validation failed

                $data['status'] = false;
                $data['message'] = 'Required fields were missing or supplied with invalid format';
                $data['errorlist'] = $gump->get_errors_array();
                $statusCode = 422;

        }
        else {
            // validation passes

            if($this->jwtUserId())
            {
                $user_id = $this->jwtUserId();
            }
            else {
             $user_id = 0;   
            }

            $roleID = $this->jwtRoleId();
            $keys = array('nameEN', 'nameAR', 'mobile', 'email', 'mobile2', 'civilno');
            $keys = $this->DB->sanitize($keys);
            $keys['user_id'] = $user_id;
            $keys['status'] = 1;

            if($lastID = $this->DB->insert($keys))
            {
                if($roleID == 3)
                {
                    
                    $keys2['vendor_id'] = $user_id;
                    $keys2['client_id'] = $lastID;
                    $keys2['status'] = 0;
                    $resCurl = Route::crossFire("api/vclients", 'POST', $keys2);
                    $data['resCulr'] = $resCurl;

                }

                $statusCode = 200;    
                $data['userid'] = $user_id;
                $data['lastID'] = $lastID;

                $data['message'] = 'Record Added with Success';
                $data['status'] = true;
            }
            else {
                $statusCode = 503;    
                $data['message'] = 'Record cannot be added at this point please try again';
                $data['status'] = false;
                $data['debug'] = $this->DB;
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
	
        return $this->removeOwnerOrAdmin();

	}


}
?>