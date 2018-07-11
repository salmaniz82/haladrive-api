<?php 
class gsectionCtrl extends appCtrl {

	private $DB;


	public function __construct()
	{
		
        

        if(JwtAuth::validateToken() && JwtAuth::$user['role_id'] == 1)
        {

            $this->DB = new Database();
            $this->DB->table = 'gsection';
        }

        else {

            $data['status'] = false;
            $data['message'] = 'Access Denied no API keys was provided';
            $statusCode = 401;           
            view::responseJson($data, $statusCode);
            die();

        }

 
	}

	public function index()
	{


        $data = $this->DB->listAll()->returnData();
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

            
            $keys = array('titleEN', 'titleAR', 'g_slug');
            $keys = $this->DB->sanitize($keys);
            $keys['status'] = 1;
            $g_slug = $keys['g_slug'];
            $keys['g_id'] = $this->pslugbyId($g_slug);


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
                $data['message'] = "Record Not found with id " . $id;
                $data['type'] = "error";
                $data['status'] = false;
                $statusCode = 500;
            }

        return view::responseJson($data, $statusCode);
	}

	public function delete()
	{
		$id = $this->getID();
		if( $this->DB->getbyId($id)->returnData() )
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
		    $statusCode = 404;
		    $data['message'] = 'cannot find record with this id';
            $data['type'] = 'error';
            $data['status'] = false;
        }

        view::responseJson($data, $statusCode);

	}


    public function bySlug()
    {

        $data = [];
        $slug = Route::$params['slug'];

        if($data = $this->DB->build('S')->Colums()->Where("g_slug = '".$slug."'")->go()->returnData())
        {

            $statusCode = 200;
                        
        }

        else 
        {
            $statusCode = 500;
            $data['message'] = 'cannot find record with this id';
            $data['type'] = 'failed';
            $data['status'] = false;
        }


        return view::responseJson($data, $statusCode);

    }


    public function pslugbyId($slug)
    {


        $DB = new Database();
        $DB->table = 'global';

        if($pID = $DB->build('S')->Colums('id')->Where("slug = '".$slug."'")->go()->returnData())
        {
            return $pID[0]['id'];
        }
        else 
        {
            return 0;
        }

    }

}
?>