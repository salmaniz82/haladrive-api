<?php 
class langCtrl extends appCtrl {

	public $DB;

	public function __construct()
	{
		$this->DB = new Database();
		$this->DB->table = 'lang';
	}


	public function listall()
	{
		
		$db = new Database();
		$db->table = 'lang';

		$data['list'] = $db->listAll()->returnData();

		View::responseJson($data);
	}


	public function addInterface()
	{
		$data['title'] = 'Add Names';

		View::render('lang-add', $data);

	}

	public function save()
	{
		
		$db = new Database();
		$db->table = 'lang';

		$inputFields = array('name_en', 'name_ar');

		$langdata = $db->sanitize($inputFields);
		

		if( $db->insert($langdata) )
		{
			
			$data['message'] = 'new row added';
			
		}
		else{
			
			$data['message'] = 'failed';

		}
	

		View::responseJson($data);

	}


	public function debugpost()
	{
		
		print_r($_FILES['thumb']);

	}

	public function update()
	{
		$id = Route::$params['id'];
        $_POST = Route::$_PUT;

		if($this->DB->getbyId($id)->returnData())
		{
		    // valid record found with this id
            $keys = array_keys($_POST);

            $keys = $this->DB->sanitize($keys);
            if($this->DB->update($keys, $id))
            {
                // found and updated
                $data['message'] = "Lang updated";
                $data['type'] = "success";
                $data['status'] = true;
                $data['keys'] = $keys;
                $statusCode = 200;
            }

            else
                {
                    // found but not updated
                    $data['message'] = "Lang cannot be updated";
                    $data['type'] = "error";
                    $data['status'] = false;
                    $statusCode = 500;
                }
        }
        else
            {
                // record not found
                $data['message'] = "lang Not found with id " . $id;
                $data['type'] = "error";
                $data['status'] = false;
                $data['debug'] = $this->DB;
                $statusCode = 500;
            }

        return view::responseJson($data, $statusCode);

	}


	public function test()
	{
		$id = Route::$params['id'];

        $_POST = Route::$_PUT;


        $data = array('one'=> 'this will be returned');


        return view::responseJson($data, $statusCode);

	}

}