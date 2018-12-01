<?php 
class voptionsModule 
{

	public $DB;

	public function __construct()
	{
		$this->DB = new Database();
		$this->DB->table = 'v_options';
	}


	public function prepareReturnDatasetArray($vehicle_id, $options)
	{

		$options = explode(',', $options);

		$dataset['cols'] = array('vehicle_id', 'options_id');

		for($i=0; $i<=sizeof($options)-1; $i++) { 

			$dataset['vals'][$i] = array(
				'vehicle_id'=> $vehicle_id,
				'options_id'=> (int)$options[$i]
			);

		}

		return $dataset;

	}


	public function saveOptionsMuliple(array $dataset)
	{


		if($this->DB->multiInsert($dataset))
		{
			return true;
		}
		else {
			return false;
		}

	}



}



