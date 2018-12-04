<?php
class vehiclesCtrl extends appCtrl {

	public $DB;


	public function __construct()
	{

		$this->DB = new Database();
        $this->DB->table = 'vehicles';
	}


	public function index()
	{

		// get all the vehicles


		if(JwtAuth::validateToken()){

		$role_id = (int) JwtAuth::$user['role_id'];
		$user_id = (int) JwtAuth::$user['id'];

		$query = "SELECT v.id as 'id', v.photo as 'photo', v.nameEN as 'carnameEN', v.nameAR as 'carnameAR', v.year as 'year', v.series as 'series', v.vin as 'vin',
		v.mileage as 'mileage', v.price as 'price', v.owner as 'owner', v.nokeys as 'nokeys', v.acdamage as 'accident_damage', v.status as 'status', 
		v.is_available as 'is_available', 

		gBody.titleEN as 'bodystyleEN',
		gBody.titleAR as 'bodystyleAR',

		gMaker.titleEN as 'makerEN',
		gMaker.titleAR as 'makerAR',

		gTrans.titleEN as 'transmissionEN',
		gTrans.titleAR as 'transmissionAR',


		gDtrain.titleEN as 'driveTrainEN',
		gDtrain.titleAR as 'driveTrainAR',

		gEngine.titleEN as 'engineEN',
		gEngine.titleAR as 'engineAR',

		gFuel.titleEN as 'fuelEN',
		gFuel.titleAR as 'fuelAR',

		brands.nameEN as 'modelEN',
		brands.nameAR as 'modelAR'



		FROM vehicles as v
		INNER JOIN gsection gBody on v.bodystyle = gBody.id 
		INNER JOIN gsection gMaker on v.maker = gMaker.id 
		INNER JOIN gsection gTrans on v.trans = gTrans.id 
		INNER JOIN gsection gDtrain on v.dtrain = gDtrain.id 
		INNER JOIN gsection gEngine on v.engine = gEngine.id 
		INNER JOIN gsection gFuel on v.fuel = gFuel.id 
		INNER JOIN brands on v.model_id = brands.id ";

		if($role_id !== 1)
		{
			$query .= " WHERE v.user_id = {$user_id}";
		}

		

	
		if($data['v'] = $this->DB->rawSql($query)->returnData())
		{

		$this->DB->table = 'v_options';


		foreach ($data['v'] as $key => $value) {	

			$id = $value['id'];
			$queryOpt = "select vo.id, vo.options_id, opt.titleEN, opt.titleAR, opt.options_id from v_options as vo 
			INNER JOIN gsection opt on  = opt.id where vo.vehicle_id = {$id}";
			$data['v'][$key]['options'] = $this->DB->rawSql($queryOpt)->returnData();
			
		}

		$statusCode = 200;


		}// @endif $data primary

			else {
				$data['message'] = 'No Records Yet';
				$data['debug'] = $this->DB;

				$data['status'] = false;
				$statusCode = 500;
			}
			
			

		} // @endif JwtAuth::validateToken()

		else {

			$data['status'] = false;
			$data['message'] = 'Access Denied Unauthorized Request';
			$data['userid'] = JwtAuth::$user['id'];
			$statusCode = 401;

		}

		$data['pagination'] = array(
			'total' => 300,
			'pages' => 30,
			'perPage' => 10,
			'nextLink'=> 'http://somedomain.com/5/100',
			'previouslink'=> 'http://somedomain.com/5/100'
		);

		view::responseJson($data, $statusCode);

	}


	public function single()
	{

		$ID = (int) $this->getID();
		$isLoggedIn = false;

		if(JwtAuth::validateToken()){

			
			$role_id = (int) JwtAuth::$user['role_id'];
			$user_id = (int) JwtAuth::$user['id'];
			$isLoggedIn = true;
		}

		$query = "SELECT v.id as 'id', v.photo as 'photo', v.nameEN as 'carnameEN', v.nameAR as 'carnameAR', v.year as 'year', v.series as 'series', v.vin as 'vin',
		v.mileage as 'mileage', v.price as 'price', v.owner as 'owner', v.nokeys as 'nokeys', v.seats ,v.acdamage as 'accident_damage', v.status as 'status',
		gBody.titleEN as 'bodystyleEN',
		gBody.titleAR as 'bodystyleAR',
		gMaker.titleEN as 'makerEN',
		gMaker.titleAR as 'makerAR',
		gTrans.titleEN as 'transmissionEN',
		gTrans.titleAR as 'transmissionAR',
		gDtrain.titleEN as 'driveTrainEN',
		gDtrain.titleAR as 'driveTrainAR',
		gEngine.titleEN as 'engineEN',
		gEngine.titleAR as 'engineAR',
		gFuel.titleEN as 'fuelEN',
		gFuel.titleAR as 'fuelAR', 
		
		gInterior.titleEN as 'interiorEN',
		gInterior.titleAR as 'interiorAR',

		gExterior.titleEN as 'exteriorEN',
		gExterior.titleAR as 'exteriorAR',
		
		brands.nameEN as 'modelEN',
		brands.nameAR as 'modelAR'
		FROM vehicles as v
		INNER JOIN gsection gBody on v.bodystyle = gBody.id 
		INNER JOIN gsection gMaker on v.maker = gMaker.id 
		INNER JOIN gsection gTrans on v.trans = gTrans.id 
		INNER JOIN gsection gDtrain on v.dtrain = gDtrain.id 
		INNER JOIN gsection gEngine on v.engine = gEngine.id 
		INNER JOIN gsection gFuel on v.fuel = gFuel.id 
		INNER JOIN gsection gInterior on v.interior = gInterior.id 
		INNER JOIN gsection gExterior on v.exterior = gExterior.id 
		INNER JOIN brands on v.model_id = brands.id ";
		if(!$isLoggedIn)
		{
			// non-admin users match id and user id
			$query .= " WHERE v.id = {$ID} AND v.status = 1 LIMIT 1";
		}
		else {
			// for admin match only id so he can view other vendor data
			$query .= " WHERE v.id = {$ID} LIMIT 1";
		}
		
		if($data['v'] = $this->DB->rawSql($query)->returnData())
		{
			// inject options data into table
			$this->DB->table = 'v_options';
			
			$queryOpt = "select vo.id, vo.options_id, opt.titleEN, opt.titleAR from v_options as vo 
			INNER JOIN gsection opt on vo.options_id = opt.id where vo.vehicle_id = {$ID}";
			 
			if($options = $this->DB->rawSql($queryOpt)->returnData())
			{
				$data['v'][0]['options'] = $options;
			}
			else {
				$data['v'][0]['options'] = null;	
			}
			// inject slides into vehicle array
			$this->DB->table = 'slides';
			if ($slides = $this->DB->build('S')->Colums('slide_large')->Where("vehicle_id = '".$ID."'")->go()->returnData()) 
			{
				$data['v'][0]['slides']	 = $slides;
			}
			else {
				$data['v'][0]['slides'] = null;	
			}
		
		$statusCode = 200;
		}// @endif $data primary
		else {
			$data['message'] = 'Cannot any active Vehicle';
			$data['status'] = false;
			$statusCode = 404;
		}
		
		view::responseJson($data, $statusCode);


	}

	public function save()
	{

		
		if(JwtAuth::validateToken())
		{

			$this->load('external', 'gump.class');
			$gump = new GUMP();
			$_POST = $gump->sanitize($_POST);
			$gump->validation_rules(array(
			
			'bodystyle' => 'required|integer',
			'maker'    =>  'required|integer',
			'year'    =>  'required|integer',
			
			
			'series' 	=> 'required',
			'model_id' 	=> 'required',

			'vin' 		=> 'required',
			'mileage' 	=> 'required',
			'price'		=> 'required',


			'trans'		=> 'required|integer',
			'dtrain'	=> 'required|integer',
			'engine'	=> 'required|integer',
			'fuel'		=> 'required|integer',

			'interior'		=> 'required|integer',
			'exterior'		=> 'required|integer',

			'owner'		=> 'required|integer',
			'nokeys'	=> 'required|integer',
			'acdamage'	=> 'required|integer',
			'options'	=> 'required',
			'seats'		=> 	'required|integer'

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

		else
		{
			// ready to add record to database

			

			$keys = array(
				'bodystyle', 'maker', 'model_id', 'year', 'nameEN', 'nameAR', 'series', 'vin', 'mileage', 'price', 'trans', 'dtrain', 'engine', 'fuel', 
				'interior', 'exterior', 'owner', 'nokeys', 'seats', 'acdamage');

			$postData = $this->DB->sanitize($keys);

			$postData['status'] = 1;
			$postData['user_id'] = JwtAuth::$user['id'];


			if($lastID = $this->DB->insert($postData) )
			{

				$target_dir = "uploads/";
				$filename = $lastID. basename($_FILES["file"]["name"]);
				$target_file = $target_dir.$filename;



				if(!file_exists($target_file))
				{
					if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) 
					{
		     			
						
						$photoKey = array('photo'=> $filename);
						$this->DB->update($photoKey, $lastID);
		     			$data['message'] = 'uploaded to server';
		     			if( !mkdir('uploads/'.$lastID, 0777, true) )
		     			{
		     				$data['message'] .= " but failed to create a directory with vehicle id";
		     			}
		     			else {
		     				$data['message'] .= " and slides directory created ";
		     			}
		     			$statusCode = 200;

		    		} else {
		        		$data['message'] = 'image was not uploaded to server';
		        		$statusCode = 500;
		    		}	
				}
				else {

					$data['message'] = 'File already exists with the same name';
		        	$statusCode = 500;

				}

				// prepare add options to options tables with last id;			
				$optionModule = $this->load('module', 'voptions');
				$dataset = $optionModule->prepareReturnDatasetArray($lastID, $_POST['options']);

				if($optionModule->saveOptionsMuliple($dataset))
				{
						$data['status'] = true;
						$data['message'] = 'New Record added to database with options';
						$data['last_id'] = $lastID;
						$data['debug'] = $this->DB;
						$statusCode = 200;
				}
				else {

					$data['status'] = false;
					$data['message'] = 'Vehicle added with failed to save related options data';
					$data['debug'] = $this->DB;
					$statusCode = 500;

				}

			}

			else {

				$data['status'] = false;
				$data['message'] = 'Cannot add record data please retry';
				$data['debug'] = $this->DB;
				$statusCode = 500;

			}


		}

		} // @endif JwtAuth::validateToken
		else
		{
			$data['status'] = false;
			$data['message'] = 'You do have authorized permission to perform this action';
			$statusCode = 401;
		}

		return view::responseJson($data, $statusCode);

	}


	public function saved()
	{

		$data['options'] = gettype($_POST['options']);
		$statusCode = 200;

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
		if($row = $this->DB->getbyId($id, ['photo', 'status', 'is_available'])->returnData() )
		{

			$photo = $row[0]['photo'];
			if($row[0]['status'] == 0 && $row[0]['is_available'] == 0) 
			{
				// when not active and disabled
				if($photo !== null || $photo !== "")
				{
					// prepare remove a photo
					$fullpath = 'uploads/'.$photo;
					if(file_exists($fullpath))
					{
						unlink($fullpath);
					}
					else {
						$data['photo'] = 'cannot locate file in '. $fullpath;
					}
				}

				if($this->DB->delete($id))
	            {
	                
	            	// prepare delete relation data
	            	$this->DB->table = 'v_options';
	            	if($this->DB->delete(['vehicle_id', $id], false))
	            	{
	            		$statusCode = 200;
	                	$data['message'] = 'Record Successfully Removed From Database';
	                	$data['type'] = 'success';
	                	$data['status'] = true;	
	            	}

	            	else {

	            		$statusCode = 422;
	                	$data['message'] = 'Orphan record were not removed';
	                	$data['type'] = 'fail';
	                	$data['status'] = false;
	            	}

	              
	            }
	            else{
	                $statusCode = 503;
	                $data['message'] = 'Service is unavailable at the moment please try later';
	                $data['type'] = 'failed';
	                $data['status'] = false;
	            }

			} 

			else {
			// record is hence cannot be removed
					$statusCode = 403;
                	$data['message'] = "Vehicle : Available or Active Hence Cannot Be Removed";
                	$data['type'] = 'fail';
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



	public function is_available()
	{
		$id = (int) $this->getID();	
		$enabled = '1';


		if($data = $this->DB->build('S')->Colums('id, mileage, user_id')->Where("id = '".$id."'")->Where("is_available = '".$enabled."'")->go()->returnData())
		{
			
			if($data[0]['id'] != null)
			{
				return view::responseJson($data, 200);
			}	
		}

		else {	
			return false;
		}

	}

	public function is_active()
	{
		$id = (int) $this->getID();	

	}

	public function manageSlides()
	{
		if( isset($_FILES['file']['name'] ) && isset(Route::$params['vehicle_id']) ) 
			{

				$last_id = Route::$params['vehicle_id'];
				$target_dir = "uploads/{$last_id}/";
				$filename = $last_id.'_'.basename($_FILES['file']["name"]);
				$target_file = $target_dir.$filename;

				if ( is_dir($target_dir) || mkdir($target_dir, 0777, true) ) 
				{

					if(!file_exists($target_file))
					{
						if (move_uploaded_file($_FILES['file']["tmp_name"], $target_file)) 
						{
							

							// record entry in database
							$this->DB->table = 'slides';
							$keys = array(
								'vehicle_id' => $last_id,
								'slide_large' => mysqli_real_escape_string($this->DB->connection, $filename),
								'status'=> 1,
								'label'=> 'default',
								'slide_order'=> 1
							);


							if( $lastSlide = $this->DB->insert($keys) )
							{
								$data['message'] = 'record added and uploaded to server';
			     				$statusCode = 200;	
							}
							else {
								$data['message'] = 'Image uploaded with cannot add dababase entry please try again';
								$data['debug'] = $this->DB->connection;
			     				$statusCode = 500;
							}
			     			

			    		} else {
				        	$data['message'] = 'image was not uploaded to server';
				        	$statusCode = 500;
			    		}	
					} else {

						$data['message'] = 'File already exist';
				        $statusCode = 403;
					}
				} else {
					$data['message'] = 'Directory not found error while creating new';
				    $statusCode = 500;
				}

			} else {
				$data['message'] = 'Not File provided for upload OR Vehicle Id is missing in url';
				$statusCode = 500;
			}

			return view::responseJson($data, $statusCode);
	}

	


}