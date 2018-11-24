<?php 
class invoiceCtrl extends appCtrl {

	private $DB;


	public function __construct()
	{
		
        if(!JwtAuth::validateToken())
        {

            $data['status'] = false;
            $data['message'] = 'Un Authorized Access is Denied no API keys provided';
            $statusCode = 401;
            view::responseJson($data, $statusCode);

            die();

        }

        $this->DB = new Database();
        $this->DB->table = 'invoices';    

	}

	public function index()
	{
		
        
        $user_id = (int) $this->jwtUserId();
        
     
        $query = "SELECT i.id, DATE_FORMAT(i.created_at, '%d-%m-%y %h:%i %p') as 'created_at', i.booking_id, i.perDay, 
        b.vehicle_id, b.client_id, (b.endMileage  - b.startMileage) as 'mileage',  
        c.nameEN, c.nameAR, 
        TRUNCATE(TIMESTAMPDIFF(MINUTE, b.initiated_at, b.completed_at)/60, 2) AS 'HRS', 
        v.vin, ROUND((SELECT HRS * i.perDay), 2) as 'Amount', v.year, 
        gMaker.titleEN as 'makerEN', gMaker.titleAR as 'makerAR', 
        gBody.titleEN as 'bodyEN', gBody.titleAR as 'bodyAR',   
        brands.nameEN as 'modelEN', brands.nameAR as 'modelAR'   
        from invoices as i 
        INNER JOIN bookings as b on i.booking_id = b.id 
        INNER JOIN vehicles as v on b.vehicle_id = v.id 
        INNER JOIN gsection gMaker ON v.maker = gMaker.id 
        INNER JOIN gsection gBody ON v.bodystyle = gBody.id 
        INNER JOIN brands ON v.model_id = brands.id 
        INNER JOIN clients as c on c.user_id = b.client_id WHERE i.user_id = {$user_id} ORDER BY i.id DESC";

        if($data['i'] = $this->DB->rawSql($query)->returnData())
        {
            $statusCode = 200;    
        }        
        else {
            $statusCode = 204;
            $data['message'] = 'No Record found';
            $data['status'] = 'false';
        }

    return  view::responseJson($data, $statusCode);

}

   

	public function single()
	{
	
		$id = $this->getID();


        if(is_numeric($id))
        {
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
        else {
            return false;
        }

	}



	public function update()
	{

		$id = $this->getID();
        
		$_POST = Route::$_PUT;

		if( $this->DB->getbyId($id)->returnData() )
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



        $user_id = $this->jwtUserId();
        $role_id = $this->jwtRoleId();


		if( $record = $this->DB->getbyId($id)->returnData() )
		{
            if($record[0]['user_id'] == $user_id || $role_id == 1)
            {
                   if( $this->DB->delete($id) )
                   {
                        $statusCode = 200;
                        $data['message'] = 'Record removed successfully';
                        $data['type'] = 'success';
                        $data['status'] = true;
                   }
            }
            else {
                $statusCode = 401;
                $data['message'] = 'Un Authorized permission denied';
                $data['type'] = 'error';
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

}
?>