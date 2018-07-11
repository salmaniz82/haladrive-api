<?php 

class templateCtrl
{


	public $DB;

	public function __construct()
	{
		$this->DB = new Database();
	}

	public function buildShopCategories()
	{
		$this->DB->table = 'categories';
		$data['categories'] = $this->DB->rawSql('select categories.id, categories.name from categories inner join products on categories.id = products.category_id')->returnData();
		View::render('templates/shop-categories', $data);
	}


}