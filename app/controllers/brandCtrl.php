<?php 
class brandCtrl extends appCtrl {

	private $DB;


	public function __construct()
	{


        $this->DB = new Database();
        $this->DB->table = 'brands';
    }


    public function index()
    {

        $data = $this->DB->listAll()->returnData();
        view::responseJson($data, 200);
    }


    public function getBrand()
    {
        $id = $this->getID();
        $this->DB->table = 'gsection';
        $brand = $this->DB->getbyId($id)->returnData();
        $this->DB->table = 'brands';
        return $brand;
    }

    
	public function collection()
	{
	
		$id = $this->getID();

        $brand = $this->getBrand();
        $data = [];

        $data['brand'] = $brand;

        

		
		if($data['list'] = $this->DB->build('S')->Colums()->Where("brand_id = '".$id."'")->go()->returnData() )
		{          
            
            $statusCode = 200;
        }
        else {
            $data['brand'] = $brand;
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

            
            $keys = array('brand_id', 'nameEN', 'nameAR');
            $keys = $this->DB->sanitize($keys);
            $keys['status'] = 1;
            $keys['logo'] = 'tempstring';

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
        if($data['data'] = $this->DB->build('S')->Colums()->Where("user_id = '".$userID."'")->go()->returnData())
        {

            $statusCode = 200;
            $data['message'] = 'Record found';
            $data['type'] = 'success';
            $data['status'] = true;
            
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



}
?>