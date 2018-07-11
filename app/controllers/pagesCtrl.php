<?php
class pagesCtrl{

    public $DB;

	public function __construct()
    {
        $this->DB = new Database();
        $this->DB->table = 'pages';
    }

    
    public function getPageDataHandler($slug)
    {
        
        $data = $this->DB->build('S')->Colums()->Where("slug = '".$slug."'")->Limit(1)->go()->returnData();
        return $data;

    }


    public function homePage()
	{
        
        $data['page'] = $this->getPageDataHandler('/');
        $data['title'] = $data['page']['0']['title'];
        View::render('page', $data);

	}


	public function productsPage()
	{
		
        $data['page'] = $this->getPageDataHandler('/products');
        $data['title'] = $data['page']['0']['title'];
        View::render('page', $data);

	}


	public function servicesPage()
	{

        $data['page'] = $this->getPageDataHandler('/services');
        $data['title'] = $data['page']['0']['title'];
        View::render('page', $data);

	}


	public function contactPage()
	{
    	
        $data['page'] = $this->getPageDataHandler('/contact');
        $data['title'] = $data['page']['0']['title'];
        View::render('page', $data);

	}
	
}