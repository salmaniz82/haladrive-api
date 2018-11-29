<?php 
class vclientsCtrl extends appCtrl {

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
        $this->DB->table = 'vendor_clients';

	}

	public function index()
	{
		
        
	}


	public function single()
	{
	

	}

	public function save()
	{

        $user_id = (int) $this->jwtUserId();

        $data = [];
        // server side validation is yet pending for this processing
        $this->load('external', 'gump.class');
        $gump = new GUMP();
        $_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.
        $gump->validation_rules(array(
            'vendor_id'  => 'required|numeric',
            'client_id'  => 'required|numeric'
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
            $keys = array('vendor_id', 'client_id');
            $keys = $this->DB->sanitize($keys);
            $keys['status'] = "1";
            
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
                $data['debug'] = $this->DB;
            }

        }
           

        view::responseJson($data, $statusCode);

	}

	public function update()
	{

	}

	public function delete()
	{

        return $this->removeOwnerOrAdmin();
	}


}
?>