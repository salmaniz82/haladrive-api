<?php
class helperCtrl extends appCtrl {


	public function index()
	{

		$db = new Database();
		$db->table = 'global';

		$data1 = $db->listAll()->returnData();

		$db->table = 'gsection';

		foreach ($data1 as $key => $row) {

			$id = $row['id'];
			$slug = $row['slug'];


			$data[$slug] = array(
				'en'=> $row['titleEN'],
				'ar'=> $row['titleAR'],
				'list' => $db->build('S')->Colums()->Where("g_id = '".$id."'")->Where("status = '".'1'."'")->go()->returnData()
			);

	
		}


		$db->table = 'brands';

		$data['brandlist'] = $db->build('S')->Colums()->Where("status = '".'1'."'")->go()->returnData();

		 view::responseJson($data, 200);
	}


	public function loadGlobalJson()
	{


		return view::loadJsonFile('global.json');


	}


	public function filters()
	{


		$db = new Database();
		$db->table = 'global';

		// $data1 = $db->listAll()->returnData();

		$filterSlugs = "('bodystyle', 'fuel', 'engine', 'brand', 'transmission', 'drivetrain', 'options')";

		$data1 = $db->build('S')->Colums()->Where("slug IN " . $filterSlugs)->Where("status = '".'1'."'")->go()->returnData();



		

			


		$db->table = 'gsection';

		foreach ($data1 as $key => $row) {

			$id = $row['id'];
			$slug = $row['slug'];

			$data[$slug] = array(
				'en'=> $row['titleEN'],
				'ar'=> $row['titleAR'],
				'list' => $db->build('S')->Colums()->Where("g_id = '".$id."'")->Where("status = '".'1'."'")->go()->returnData()
			);
			
		}


		$db->table = 'brands';
		//$modelData = $db->build('S')->Colums()->Where("status = '".'1'."'")->go()->returnData();

		$sql = "SELECT id, brand_id, nameEN as titleEN, nameAR as titleAR, logo, status from brands WHERE status = 1";
		$modelData = $db->rawSql($sql)->returnData();


		$data['models'] = array(
			'en' => 'Models',
			'ar' => 'ارضات ازياء',
			'list'=> $modelData
		);

		view::responseJson($data, 200);


	}

	

}