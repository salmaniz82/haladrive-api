<?php 
class insuranceCtrl extends appCtrl {

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
        $this->DB->table = 'insurance';    
	}

	public function index()
	{

        
		
        if(JwtAuth::$user['role_id'] == 1)
        {
          $data = $this->DB->listAll()->returnData();
        }
        else {

            $userID = JwtAuth::$user['id'];
            $data = $this->DB->build('S')->Colums()->Where("user_id = '".$userID."'")->go()->returnData();  

        }

        
		view::responseJson($data, 200);
	}

	public function single()
	{
	
		$id = $this->getID();
		
		if($data = $this->DB->getbyId($id)->returnData() )
		{
		    $statusCode = 200;
        }
        else {
		    $data['message'] = 'Cannot find data associated with Id ' . $id;
            $data['status'] = false;
            $data['type'] = 'error';
            $statusCode = 500;
        }

        return view::responseJson($data, $statusCode);

	}

	public function save()
	{
        $data = [];

        
          // server side validation is yet pending for this processing

            $user_id = JwtAuth::$user['id'];          
            $keys = array('nameEN', 'nameAR', 'mobile', 'email');
            $keys = $this->DB->sanitize($keys);
            $keys['user_id'] = $user_id;
            $keys['status'] = 1;

            if($lastID = $this->DB->insert($keys))
            {

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
                $data['message'] = "Record Updated";
                $data['type'] = "success";
                $data['status'] = true;
                $data['keys'] = $keys;
                $statusCode = 200;
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