<?php 
class fakerCtrl extends appCtrl
{


	public function random_color_part() {
		return str_pad( dechex( mt_rand( 600, 1000 ) ), 3, '0', STR_PAD_LEFT);
	}



	public function randomHex() {
    	return strtoupper(random_color_part() . random_color_part() . random_color_part());
	}

	public function randomPrefix()
	{
		return strtoupper(substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 3)), 0, 3));
	}

	public function randomizeIt($arr)
	{
		$idx = array_rand($arr);
		return $arr[$idx];
	}


	public function randUniqueRange($min, $max, $quantity) 
	{
 		   $numbers = range($min, $max);
    		shuffle($numbers);
    		return array_slice($numbers, 0, $quantity);
	}



	public function vehicleMassInsert()
	{

		$records = Route::$params['records'];
		$db = new Database();
		$s_ID  = $db->rawSql("SELECT max(id) as 'mxID' from vehicles")->returnData();
		echo "StartFromID". $s_ID[0]['mxID'] . "<br>";
		echo 'designed to mass insert on vehicles <br>';
		$bodyStyleAr = range(40, 64);
		$brandAr = range(66,97);
		$yearAr  = range(2010, 2018);

		$driveTrainAr = range(100,102);
		$engineAr = range(100,102);
		$fuelAr = range(106,110);
		$transmissonAr  = range(98,99);
		$priceAr = range(100,10000);
		$mileageAr = range(2500,25000);
		$modelAr = range(20,37);
		$photoAr = range(0, 664);
		$userAr = array(4,5,6,7,8,12,11,14);


		function e($colStr)
		{
			return "'".$colStr ."'";
		}

		

		for($t = 0; $t <= 10; $t++)
		{
			
			// echo mt_rand(10, 100) . "<br>";
		}


		$db = new Database();	

		$queryString = "INSERT INTO vehicles(photo, user_id, bodyStyle, maker, model_id, year, series, vin,
		mileage, price, trans, dtrain, engine, fuel, owner, nokeys, acdamage, perDay, status, is_available) VALUES  ";

		$limiter = $records;

		$ValuesAr = [];
	
		for($i=0; $i <= $limiter; $i++)
		{
			
			$photo = "'faker/faker_thumb_ (".randomizeIt($photoAr).").jpg'";
			$user_id = e(randomizeIt($userAr));
			$bodyStyle = e(randomizeIt($bodyStyleAr));
			$maker = e(randomizeIt($brandAr));
			$model_id = e(randomizeIt($modelAr));
			$year = e(randomizeIt($yearAr));
			$series = e(randomPrefix().'-'.randomHex());
			$vin = e(randomPrefix().'-'.randomHex());
			$mileage = e(randomizeIt($mileageAr));
			$price = e(randomizeIt($priceAr));
			$trans = e(randomizeIt($transmissonAr));
			$dtrain = e(randomizeIt($driveTrainAr));
			$engine = e(randomizeIt($engineAr));
			$fuel = e(randomizeIt($fuelAr));
			$owner = e(1);
			$nokeys = e(1);
			$acdamage = e(1);
			$perDay = e(1);
			$status = e(1);
			$is_available = e(1);

			// $queryString .= " ( ";

			$ValuesAr[$i] = "(". "$photo, $user_id, $bodyStyle, $maker, $model_id, $year, $series, $vin, $mileage, $price, $trans, $dtrain, 
			$engine, $fuel, $owner, $nokeys, $acdamage, $perDay, $status, $is_available" ."),";


		}	


			foreach($ValuesAr as $key => $values )
			{				
					
					$queryString .= $values;
			}

			$queryString = rtrim($queryString, ',');
			//echo $queryString;
		
			$db->table = 'vehicles';
			$db->sqlSyntax = $queryString;
			
			if($db->runQuery())
			{
				echo 'done <br>';
			}else {
				var_dump($db->queryError);
			}
			


			$s_ID  = $db->rawSql("SELECT max(id) as 'mxID' from vehicles")->returnData();
			echo "End". $s_ID[0]['mxID'] . "<br>";


	}


	public function optionsMassAttachment()
	{


			$start_id = (int) Route::$params['start_id'];
			$end_id = (int)  Route::$params['end_id'];
			$mainLength = $end_id - $start_id;

			$db = new Database();
			$db->table = 'v_options';

			$s_ID  = $db->rawSql("SELECT max(id) as 'mxID' from v_options")->returnData();
			echo "StartFromID". $s_ID[0]['mxID'] . "<br>";

			$vehicle_id = $start_id;


			$optQuery = "INSERT INTO v_options (vehicle_id, options_id) VALUES ";


			for($i=0; $i <= $mainLength; $i++)
			{

					
				$optLength =  rand(1, 10);
				$optIDs = $this->randUniqueRange(111,120, $optLength);				
				$idx = 0;

				

				for($r=0; $r <= $optLength-1; $r++)
				{
					
					$vehicle_id;
					$options_id = $optIDs[$r];
					$optQuery .= "(";
					$optQuery .=  "$vehicle_id, $options_id ";
					$optQuery .= ")";

					if($r < $optLength)
					{
						$optQuery .= ",";
					}

				}
				
				$vehicle_id++;

			}


		$optQuery = rtrim($optQuery,",");
		
		
		$db->sqlSyntax = $optQuery;
		if($db->runQuery())
		{
			
			echo 'done'. "<br>";

		} else {
			var_dump($db->queryError);
		}

		$e_ID  = $db->rawSql("SELECT max(id) as 'mxID' from v_options")->returnData();
			echo "EndedAtID". $e_ID[0]['mxID'] . "<br>";

	}


}