<?php 
class jwtauthCtrl extends appCtrl {


	public function check()
	{
		

		if(JwtAuth::hasToken())
		  {
		      $data['message'] = "token was present in header";
		      $data['status'] = true;
		      View::responseJson($data, 200);
		  }
		  else
		      {
		          $data['message'] = "Un Authenticated token was not provided";
		          $data['status'] = false;
		          View::responseJson($data, 403);
		      }
	}

	public function login()
	{


    $creds = array(
    	'email'=> $_POST['email'],
    	'password' => $_POST['password']
    );



	    if( $payload = JwtAuth::findUserWithCreds($creds) )
	    {

	        $token = JwtAuth::generateToken($payload);
	        $data['status'] = true;
	        $data['message'] = 'user found';
	        $data['token'] = $token;
	        $data['user'] = $payload;
	        return View::responseJson($data, 200);
	    }
	    else
	        {
	            $data['status'] = false;
	            $data['message'] = 'user not found';
	            return View::responseJson($data, 401);
	        }

	}

	public function validateToken()
	{

		if( JwtAuth::validateToken() )
	    {

	        $data['status'] = true;
	        $data['message'] = 'user found';
	        $data['user'] = JwtAuth::$user;
	        return View::responseJson($data, 200);
	    }
	    else
	    {
	        $data['status'] = false;
	        $data['message'] = 'not a valid token';
	        return View::responseJson($data, 401);
	    }

	}

	public function adminOnlyProtected()
	{
		if( JwtAuth::validateToken() && JwtAuth::$user['role_id'] == 1)
    	{
        	$data['message'] = "you are admin you can access this route";
        	return View::responseJson($data, 200);
    	}

    	else {
        	$data['message'] = "Un Authorize attempt you don not have permission to access this route";
        	return View::responseJson($data, 401);
    	}
	}


	public function register()
	{

	}

	public function consumerRegister()
	{

		if( (JwtAuth::validateToken() ) && ( JwtAuth::$user['role_id'] == 3 || JwtAuth::$user['role_id'] == 1) )
		{
			// ONLY ADMIN AND VENDOR CAN CREATE CONSUMER USERS
			if(!isset($_POST['email']) && !isset($_POST['password']))
			{
				$data['message'] = 'No Post values provided';
				$statusCode = 204;
				return view::reponseJson($data, $statusCode);
				die();

			}

			else {
                $this->load('external', 'gump.class');
				$gump = new GUMP();
		        $_POST = $gump->sanitize($_POST); 
		        $gump->validation_rules(array(
		            'name'  => 'required',
		            'email'   => 'required|valid_email'
		        ));
			}

		if($gump->run($_POST) === false)
        {
            // validation failed
                $data['status'] = false;
                $data['message'] = 'Required fields were missing or supplied with invalid format';
                $data['errorlist'] = $gump->get_errors_array();
                $statusCode = 422;
        }

        else {

        		$db = new Database();
				$db->table = 'users';
		        $keys = array('name', 'email');
		        $keys = $db->sanitize($keys);
		        $keys['role_id'] = 4;
		        $keys['password'] = sha1('123456');

		        if($cosumerId = $db->insert($keys) ) 
				{
					$data['title'] = 'Success';
					$data['message'] = 'Done registration';
					$data['consumer_Id'] = $cosumerId;
					$statusCode = 200;
				} else {
					$statusCode = 500;
					$data['consumer_Id'] = 0;
					$data['debug'] = $db;
					$data['message'] = 'Consumer Registration Failed';					
				}

        	}

        }

        else {
        		$data['title'] = 'Error';
        		$statusCode = 401;
        		$data['message'] = 'Un Authorize Access is Denied';
        	}
        	
        	return View::responseJson($data, $statusCode);
        
		
	}

}