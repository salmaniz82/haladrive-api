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

		/*
			http://api.haladrive.local/api/vehicles/q/?name=me&price=300&mileage=300&options[]=apple&options[]=banana&brand=toyota&model=aqua%20ES
			print_r($_GET);		
		*/

		// get all the vehicles

			if(isset($_GET['brand']))
			{
				$qBrand = $_GET['brand'];
			}

			if(isset($_GET['model']))
			{
				$qModel = $_GET['model'];
			}

			if(isset($_GET['body']))
			{
				$qBody = $_GET['body'];
			}

			if(isset($_GET['minmileage']))
			{
				$minMileage = $_GET['minmileage'];
			}

			if(isset($_GET['maxmileage']))
			{
				$maxMileage = $_GET['maxmileage'];
			}

			if(isset($_GET['minprice']))
			{
				$minPrice = $_GET['minprice'];
			}

			if(isset($_GET['maxprice']))
			{
				$maxPrice = $_GET['maxprice'];
			}

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
	

		if(isset($role_id) && $role_id !== 1)
		{
		
			$string = " v.user_id = {$user_id} ";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($qBrand))
		{

			$string = " v.maker = {$qBrand}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($qBody))
		{

			$string = " v.bodyStyle = {$qBody}";
			$query .= $this->appendQuery($query, $string);
		}


		if(isset($qModel))
		{

			$string = " v.model_id = {$qModel}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($minMileage))
		{

			$string = " v.mileage >= {$minMileage}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($maxMileage))
		{

			$string = " v.mileage <= {$maxMileage}";
			$query .= $this->appendQuery($query, $string);
		}


		if(isset($minPrice))
		{

			$string = " v.price >= {$minPrice}";
			$query .= $this->appendQuery($query, $string);
		}

		if(isset($maxPrice))
		{

			$string = " v.price <= {$maxPrice}";
			$query .= $this->appendQuery($query, $string);
		}

		
		//	echo $query;

		if($data['v'] = $this->DB->rawSql($query)->returnData())
		{


		$this->DB->table = 'v_options';

		foreach ($data['v'] as $key => $value) {	

			$id = $value['id'];
			$queryOpt = "select vo.id, opt.titleEN, opt.titleAR from v_options as vo 
			INNER JOIN gsection opt on vo.options_id = opt.id where vo.vehicle_id = {$id}";
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

		return $queryString;

	}

	
}