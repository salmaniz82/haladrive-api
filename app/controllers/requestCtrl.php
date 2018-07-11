<?php class requestCtrl 
{
	
	public function getRequest()
	{
		
		$data['message'] = "Resonse for get";
		return View::responseJson($data, 200);

	}

	public function postRequest()
	{
		

		var_dump($_POST);
	}

	public function putRequest()
	{

        $data['input'] = Route::$_PUT;
		View::responseJson($data, 200);

	}

	public function deleteRequest()
	{

        
        $data['message'] = "Resonse for DELETE";
		$data['incoming'] = $_POST;
		return View::responseJson($data, 200);

	}

}