<?php 

class testCtrl extends appCtrl
{
	
	public $DB;

	public function __construct()
	{	
		$this->DB = new Database();
	}

	public function checkInherit()
	{
		echo $this->appMethod();
	}

	public function createCourse()
	{
		if( $this->canManageCourse() )
		{
			echo 'creating course';
		}

		else 
		{
			echo 'you cannot do this';
		}
	}

	public function udpateCourse()
	{
		if( $this->canManageCourse() )
		{
			echo 'update course';
		}

		else 
		{
			echo 'you cannot do update this';
		}	
	}


	public function testPaginate()
	{
		$perPage = Route::$params['perPage'];
	    $currentPage = Route::$params['currentPage'];

	    $db = new Database();
	    $db->table = 'todos';

	   // $data = $db->rawSql('SELECT * FROM todos LIMIT 10 OFFSET 10')->returnData();
	    $data = $db->build('S')->Colums('id, todo')->Paginate($perPage, $currentPage)->go()->returnData();
	    var_dump($data);
	}


	public function treeCheck()
	{

    $db = new Database();
    $db->table = 'categories';

    $data['categories'] = $db->listall()->returnData();

    function has_children($rows,$id)
	    {
	      foreach ($rows as $row)
	      {
	        if ($row['parent_id'] == $id)
	        return true;
	      }
	      return false;
		}

	}


	public function buildMenu()
	{
		
		echo 'Building menu';
	}


	public function checkUserInfo()
	{
		$data['userId'] = $this->jwtUserId();
		$data['roleId'] = $this->jwtRoleId();

		view::responseJson($data, 200);

	}

	public function vehicleQuery()
	{



		if(JwtAuth::validateToken())
		{
			$role_id = (int) JwtAuth::$user['role_id'];
			$user_id = (int) JwtAuth::$user['id'];
		}

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
	


		if(isset($_GET['brand']))
		{
		
			$qBrand = $_GET['brand'];
			$string = " gMaker.titleEN = '{$qBrand}'";	
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['body']))
		{

			$qBody = $_GET['body'];
			$string = " gBody.titleEN = '{$qBody}'";
			$query .= $this->appendQuery($query, $string);
		}


		if(isset($_GET['model']))
		{

			$qModel = $_GET['model'];
			$string = " brands.nameEN = '{$qModel}' ";
			$query .= $this->appendQuery($query, $string);
		}


		if(isset($_GET['minmileage']))
		{

			$minMileage = $_GET['minmileage'];
			$string = " v.mileage >= {$minMileage}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['maxmileage']))
		{
			$maxMileage = $_GET['maxmileage'];
			$string = " v.mileage <= {$maxMileage}";
			$query .= $this->appendQuery($query, $string);
		}


		if( isset($_GET['minprice']) )
		{
			$minPrice = $_GET['minprice'];
			$string = " v.price >= {$minPrice}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['maxprice']))
		{

			$maxPrice = $_GET['maxprice'];
			$string = " v.price <= {$maxPrice}";
			$query .= $this->appendQuery($query, $string);
		}

		if( isset($_GET['fuel']) )
		{
			$fuel = $_GET['fuel'];
			$string = " gFuel.titleEN = '{$fuel}' ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['engine']) )
		{
			$engine = $_GET['engine'];
			$string = " gEngine.titleEN = '{$engine}' ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['transmission']) )
		{
			$transmission = $_GET['transmission'];
			$string = " gTrans.titleEN = '{$transmission}' ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['drive']) )
		{
			$drive = $_GET['drive'];
			$string = " gDtrain.titleEN = '{$drive}' ";
			$query .= $this->appendQuery($query, $string);	
		}




		if($data['v'] = $this->DB->rawSql($query)->returnData())
		{


			if(isset($_GET['options']) && isset($_GET['options']) == 'true')
			{
				$this->DB->table = 'v_options';
				foreach ($data['v'] as $key => $value) 
				{	
					$id = $value['id'];
					$queryOpt = "select vo.id, opt.titleEN, opt.titleAR from v_options as vo 
					INNER JOIN gsection opt on vo.options_id = opt.id where vo.vehicle_id = {$id}";
					$data['v'][$key]['options'] = $this->DB->rawSql($queryOpt)->returnData();	
				}
			
			}

			$statusCode = 200;
			$data['records'] = $this->DB->noRows;

		}// @endif $data primary

			else {
				$data['message'] = 'No Match were found';
				$data['status'] = false;
				$statusCode = 500;
			}


		view::responseJson($data, $statusCode);

	}


	public function appendQuery($query, $string)
	{
		if (strpos($query, 'WHERE') !== false) {
    		$queryString = ' AND '. $string;	
		}
		else {	
			$queryString = ' WHERE '. $string;
		}
		return urldecode($queryString);

	}

	public function checknPrepare($getKey)
	{

			if(isset($getKey))
			{
				if( gettype($getKey) == 'array')
				{
					
					return $string = 'IN ("'  . implode('", "', $getKey) . '")';
				}	

				if( gettype($getKey) == 'string')
				{
					
					return $string = '= "'. $getKey . '"';
				}
			}

	}



	public function vehicleQueryArray()
	{

		

		if(isset($_GET['limit']))
		{
			$limit = (int) $_GET['limit'];
		}
		else {
			$limit = 10;
		}

		if(isset($_GET['page']))
		{
			$page = $_GET['page'];
		}
		else {
			$page = 1;	
		}

		
		$query = "SELECT SQL_CALC_FOUND_ROWS v.id as 'id', v.photo as 'photo', v.nameEN as 'carnameEN', v.nameAR as 'carnameAR', v.year as 'year', v.series as 'series', v.vin as 'vin',
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


		FROM vehicles v 
		INNER JOIN gsection gBody on v.bodystyle = gBody.id 
		INNER JOIN gsection gMaker on v.maker = gMaker.id 
		INNER JOIN gsection gTrans on v.trans = gTrans.id 
		INNER JOIN gsection gDtrain on v.dtrain = gDtrain.id 
		INNER JOIN gsection gEngine on v.engine = gEngine.id 
		INNER JOIN gsection gFuel on v.fuel = gFuel.id 
		INNER JOIN brands on v.model_id = brands.id 
		INNER JOIN v_options vo on v.id = vo.vehicle_id 
		INNER JOIN gsection gOptions on vo.options_id = gOptions.id ";
	


		if(isset($_GET['brand']))
		{
		
			$qBrand = $_GET['brand'];
			$matchType = $this->checknPrepare($qBrand);
			$string = " gMaker.titleEN {$matchType}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['bodystyle']))
		{

			$qBody = $_GET['bodystyle'];
			$matchType = $this->checknPrepare($qBody);
			$string = " gBody.titleEN  {$matchType}";
			$query .= $this->appendQuery($query, $string);
		}

		if( isset($_GET['fuel']) )
		{
			$fuel = $_GET['fuel'];
			$matchType = $this->checknPrepare($fuel);
			$string = " gFuel.titleEN {$matchType} ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['engine']) )
		{
			$engine = $_GET['engine'];
			$matchType = $this->checknPrepare($engine);
			$string = " gEngine.titleEN  {$matchType} ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['transmission']) )
		{
			
			$transmission = $_GET['transmission'];
			$matchType = $this->checknPrepare($transmission);
			$string = " gTrans.titleEN {$matchType} ";
			$query .= $this->appendQuery($query, $string);

		}

		if( isset($_GET['drivetrain']) )
		{
			$drive = $_GET['drivetrain'];
			$matchType = $this->checknPrepare($drive);
			$string = " gDtrain.titleEN {$matchType} ";
			$query .= $this->appendQuery($query, $string);	
		}

		if( isset($_GET['options']) )
		{
			$options = $_GET['options'];
			$matchType = $this->checknPrepare($options);
			$string = " gOptions.titleEN {$matchType} ";
			$query .= $this->appendQuery($query, $string);	
		}


		if(isset($_GET['model']))
		{

			$qModel = $_GET['model'];
			$matchType = $this->checknPrepare($qModel);
			$string = " brands.nameEN  {$matchType} ";
			$query .= $this->appendQuery($query, $string);
		}


		if(isset($_GET['minmileage']))
		{

			$minMileage = $_GET['minmileage'];
			$string = " v.mileage >= {$minMileage}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['maxmileage']))
		{
			$maxMileage = $_GET['maxmileage'];
			$string = " v.mileage <= {$maxMileage}";
			$query .= $this->appendQuery($query, $string);
		}


		if( isset($_GET['minprice']) )
		{
			$minPrice = $_GET['minprice'];
			$string = " v.price >= {$minPrice}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($_GET['maxprice']))
		{

			$maxPrice = $_GET['maxprice'];
			$string = " v.price <= {$maxPrice}";
			$query .= $this->appendQuery($query, $string);
		}


		$filterRolesAr = [1,3];
    	if( JwtAuth::validateToken() && in_array((int) JwtAuth::$user['role_id'], $filterRolesAr) )
    	{
    			$user_id = (int) JwtAuth::$user['id'];
    			$role_id = (int) JwtAuth::$user['role_id'];

    			if($role_id == 3)
    			{
    				$filterRoles = " v.user_id = $user_id ";
					$query .= $this->appendQuery($query, $filterRoles);			
    			}

    	}
    	else {

    		$isActive = " v.status = 1 ";
			$query .= $this->appendQuery($query, $isActive);

    	}



		$pageCursor = ($page - 1) * $limit;

		$query .= ' GROUP BY v.id ';
		$query .= ' ORDER BY v.id '; 
		$query .= " LIMIT {$pageCursor},  {$limit}";




			/*		
			echo '<pre>';
			echo $query;
			echo '</pre>';
			die();

			*/
			
			
		
		

		if($data['v'] = $this->DB->rawSql($query)->returnData())
		{


			if(isset($_GET['extras']) && isset($_GET['extras']) == 'true')
			{
				$this->DB->table = 'v_options';
				foreach ($data['v'] as $key => $value) 
				{	
					$id = $value['id'];
					$queryOpt = "select vo.id, opt.titleEN, opt.titleAR from v_options as vo 
					INNER JOIN gsection opt on vo.options_id = opt.id where vo.vehicle_id = {$id}";
					$data['v'][$key]['options'] = $this->DB->rawSql($queryOpt)->returnData();	
				}
			
			}

			$totalMatched = $this->getTotalMatched();
			$statusCode = 200;
			$data['records'] = $totalMatched;
			$data['limit'] = $limit;
			$data['noPages'] = ceil($totalMatched / $limit);
			$data['currentPage'] = (int) $page;
			



		}// @endif $data primary

			else {
				$data['message'] = 'No Match were found';
				$data['status'] = false;
				$data['records'] = 0;
				$data['v'] = null;
				$statusCode = 500;
			}

		view::responseJson($data, $statusCode);

	}



	public function getTotalMatched()
	{
		$sqlTotal = "SELECT FOUND_ROWS() as 'totalMatched'";
		$this->DB->table = 'vehicles';

		$totalRecords = $this->DB->rawSql($sqlTotal)->returnData();
		return $totalRecords[0]['totalMatched'];
	}

	
}