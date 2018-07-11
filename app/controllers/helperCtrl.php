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

	

}