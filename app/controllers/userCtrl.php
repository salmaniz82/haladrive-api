<?php 

class userCtrl extends appCtrl {

    public $DB;

    public function __construct()
    {

        $this->DB = new Database();
        $this->DB->table = 'users';

    }

    public function clientRegister()
    {
        
        if(isset($_POST) && !empty($_POST))
        {


            $this->load('external', 'gump.class');

            $clientModule = $this->load('module', 'client');

            $gump = new GUMP();
            $_POST = $gump->sanitize($_POST);

            $gump->validation_rules(array(
                'name' => 'required',
                'email'    =>  'required|valid_email',
                'password'    =>  'required',
                'civilno'   => 'required|numeric|exact_len,12',
                'cpassword' => 'required'
            ));

            $pdata = $gump->run($_POST);

            if($pdata === false)
            {
                
                $statusCode = 406;
                $data['message'] = "Required fields were missing or provided with incomplete information";


            }
            else if ($_POST['password'] != $_POST['cpassword']) {
                $statusCode = 406;
                $data['message'] = "Make sure password and confirm are the same";
            }

            else if ($clientModule->existbyCivilId($_POST['civilno']))
            {

                $statusCode = 406;
                $data['message'] = "Cleint Already Exist with Civil Id";

            }

            else {

                $keys = array('name', 'email', 'password');
                $dataKeys = $this->DB->sanitize($keys);
                $dataKeys['password'] = sha1($dataKeys['password']);
                $dataKeys['role_id'] = 4;

                if($lastUserId = $this->DB->insert($dataKeys))
                {
                                       
                    $Consumer['user_id'] = $lastUserId;
                    $Consumer['nameEN'] = $dataKeys['name'];
                    $Consumer['nameAR'] = $dataKeys['name'];
                    $Consumer['civilno'] = $_POST['civilno'];
                    $Consumer['email'] = $dataKeys['email'];
                    $Consumer['status'] = 1;


                    

                    if($clientModule->saveClientwithDetails($Consumer))
                    {

                        $data['message'] = "Registration Successfull";
                        $statusCode = 200;
                    }
                    else {

                        $data['message'] = "Partially Done";
                        $statusCode = 409;   
                    }

                } 

                else {

                    $data['message'] = "User Cannnot be created";
                    $statusCode = 500;

               }

            }
        }
        else {

            $statusCode = 406;
            $data['message'] = "Cannot process empty request";
        }

        view::responseJson($data, $statusCode);

    }


    public function changePassword() 
    {

        $_POST = Route::$_PUT;

        $oldPassword = (isset($_POST['oldPass'])) ? $_POST['oldPass'] : null;
        $newPassword = (isset($_POST['newPass'])) ? $_POST['newPass'] : null;
        $email = (isset($_POST['email'])) ? $_POST['email'] : null;

        if(!empty($_POST['oldPass']) && !empty($_POST['newPass']) && !empty($_POST['email']))
        {
                if( JwtAuth::validateToken() && (JwtAuth::$user['email'] == $email || JwtAuth::$user['role_id'] == 1) )
                {

                    if($oldPassword != $newPassword)
                    {


                        $userModule = $this->load('module', 'user');
                        $creds = array(
                            'email' => $email,
                            'password' => $oldPassword,
                            );

                           if($userData = $userModule->userByCreds($creds))
                            {               
                                $user_id = $userData[0]['id'];
                                if($userModule->changePassword($user_id, $newPassword))
                                {
                                    $statusCode = 200;
                                    $data['message'] = "Password updated Success";
                                }
                                else {
                                    $statusCode = 501;
                                    $data['message'] = "Password updated Failed";
                                }

                            }
                            else {
                                $statusCode = 500;
                                $data['message'] = "User Not Found";        
                            }
                    }
                    else {
                        $data['message'] = "Current and New Password cannot be same";
                        $statusCode = 400;
                    }

            }
            else {
                $statusCode = 401;
                $data['message'] = "Access Denied";
            }
        }
        else {

            $data['message'] = "Request body is incomplete or not fomratted";
            $statusCode = 400;
        }

        View::responseJson($data, $statusCode);

    }

}