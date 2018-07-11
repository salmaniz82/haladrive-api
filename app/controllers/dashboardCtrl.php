<?php 
class dashboardCtrl{

    public $DB;

    public function __construct()
    {
        $this->DB = new Database();
    }


    public function isAdminFilter()
    {
        if(Auth::User()['role_id'] != 1 ) { 

            return header("Location: /");

        }
    }

	public function dasboardLanding()
	{

		if( !Auth::loginStatus() ) 
    	{
        	return header("Location: /login");
    	}

    	$data['title'] = 'Dashboard';
    	$data['message'] = 'You can perform administrative actions in dashoard section';
    	return View::render('dashboard/index', $data);
	}

    public function pagesList()
    {
        
        $this->isAdminFilter();

        $data['title'] = 'Dashboard';
        $data['message'] = 'Edit Pages';
        $this->DB->table = 'pages';
        $data['pages'] = $this->DB->listall()->returnData();
        return View::render('dashboard/pages-list', $data);
    }

    public function showPageEdit()
    {
        
        $this->isAdminFilter();

        $id = Route::$params['id'];
        $data['title'] = 'Dashboard';
        $data['message'] = 'Edit Pages';
        $this->DB->table = 'pages';
        $data['page'] = $this->DB->getbyId($id)->returnData();
        return View::render('dashboard/page-edit', $data);
        
    }

    public function updatePage()
    {
        
        $this->isAdminFilter();

        $id = $_POST['id'];
        $this->DB->table = 'pages';
        $key = array('title', 'slug', 'contents');
        $updatedPageData = $this->DB->sanitize($key);

        if($this->DB->update($updatedPageData, $id))
        {
            return header("Location: {$_POST['slug']}");
        }
        else
        {
            echo 'Failed While Updating Values';
        }

    }

    public function showAddProducts()
    {
        
        $this->isAdminFilter();

        $this->DB->table = 'categories';
        $data['categories'] = $this->DB->listall()->returnData();

        View::render('dashboard/addproducts', $data);
    }

    public function saveProducts()
    {

        $this->isAdminFilter();
        
        $key = array('category_id', 'name', 'detail');
        $data = $this->DB->sanitize($key);
        $this->DB->table = 'products';

        if($this->DB->insert($data))
        {
            echo 'done';
        }
        else {
            echo 'failed';
        }

    }

}