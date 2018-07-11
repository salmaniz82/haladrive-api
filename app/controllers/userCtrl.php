<?php 

class userCtrl {

	public function showLogin() 
	{

		if(Auth::loginStatus())
		{
			return header('location: /');
		}

		$data['title'] = 'Login';
    	View::render('login', $data);

	}

	public function doLogin() 
	{

		if( (isset($_POST['email']) && $_POST['email'] != NULL) && (isset($_POST['password']) && $_POST['password'] != NULL) ) {
        

        $creds['email'] = $_POST['email'];
        $creds['password'] = $_POST['password'];

	        if (filter_var($creds['email'], FILTER_VALIDATE_EMAIL)) 
	        {          
	            // do the login stuff

	            if( $user = Auth::attemptLogin($creds) ) 
	            {

	            		Auth::check()->id = Auth::User()['id'];
		                header('location: /todos');
	                
	            } 
	            else 
		        {
		                $_SESSION['flashMsg'] = 'Invalid Credentials';
		                $_SESSION['fClass'] = 'error';
		                header("location: /login");

		        }


	        } 
	        else 
		    {
		        $data['message'] = 'Invalid Email';
		    }
	     
    	} 
    	else 
	    {
	    	 $data['message'] = 'creds not found';    
	    }

		   
	    View::render('page', $data);

	

	}

	public function logout() 
	{
		
		Auth::logout();
		header('location: /login');
		
    	
	}

	public function showProfile()
	{
		

	if( !Auth::loginStatus() ) 
     {
        
    	$data['message'] = 'This is protected you are not allowed to see details unless your are logged IN';
    	return View::render('page', $data);
     }

  
     	$data['title'] = 'Profile';
		$data['message'] = 'User Profile Details can be seen here ...';
		$data['profile'] =  Auth::User();
    	View::render('page', $data);
	}


	public function showRegister()
	{
		$data['title'] = 'Register';
		$data['message'] = 'Please enter your details for signing up';
		View::render('register', $data);
	}

	public function doRegister()
	{
		$db = new Database();
		$db->table = 'users';

		$user['name'] = $_POST['name'];
		$user['email'] = $_POST['email'];
		$user['role_id'] = 3;
		$password = $_POST['password'];
		$password = sha1($password);
		$user['password'] = $password;

		if( $db->insert($user) ) 
		{

			$data['title'] = 'Congratulations';
			$data['message'] = 'You are now registered you can use your credentials to login';
			View::render('page', $data);
		} else 
			{

			$data['message'] = 'Some thing went wrong during registration';
			View::render('page', $data);

			}
	}

	public function checkReturnAuthenticatedUser()
	{

		if(Auth::loginStatus())
		{
			
			$response['status'] = true;
			$response['userCount'] = 1;
			$response['user'] = Auth::User();
			
		}
		else
		{

			$response['status'] = false;
			$response['userCount'] = 0;
			

		}

	View::responseJson($response);

	}

}