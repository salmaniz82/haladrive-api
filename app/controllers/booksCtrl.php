<?php

class booksCtrl extends appCtrl
{
    
    public $DB;

    public function __construct()
    {
        $this->DB = new Database();
        $this->DB->table = 'books';
    }

    public function bookListHandler()
    {
        $data['books'] = $this->DB->listall( ['id', 'name', 'author', 'published_date', 'details'] )->returnData();
        $data['count'] = count($data['books']);
        $data['title'] = 'List of All Books 2nd Example';
        return $data;
    }

    public function listbooks()
    {
        
        $data = $this->bookListHandler();
        View::render('books', $data);
    }



    public function single()
    {
       
       $id = Route::$params['id'];
       $data['book'] = $this->DB->getbyId($id)->returnData();
       $data['title'] = $data['book'][0]['name'];
       View::render('book-single', $data);
    }

    public function showAdd()
    {
      if( !Auth::loginStatus() ) 
     {
         return header("Location: /login");
     }

        $data['title'] = 'Adding Books to Database';
        View::render('dashboard/book-add', $data);
        exit();
    }

    public function saveBook()
    {

      if( !Auth::loginStatus() ) 
     {
         return header("Location: /login");
     }


        $keys = array('name', 'details', 'published_date');
        $book = $this->DB->sanitize($keys);
        $author = Auth::User();
        $book['author'] = $author['name'];
       
        
        if($this->DB->insert($book))
        {

            $data['message'] = 'Data Added To Database';
            $data['class'] = 'success';
            

        } else
            {
                $data['message'] = 'Cannot Add data to databased';
                $data['class'] = 'error';
            }

            View::render('dashboard/book-add', $data);

           

    }

    public function showUpdateForm()
    {
      $id = (int) Route::$params['id'];
      $data['title'] = 'Update Book Details';
      $data['book'] = $this->DB->getbyId($id)->returnData();    
      View::render('dashboard/book-edit', $data);
    }

    public function updateBook()
    {
      
      $id = (int) Route::$params['id'];

      $keys = array('name', 'published_date', 'details');

      $dataSanitized = $this->DB->sanitize($keys);

      /*

     $data = array(
        'name'=> $_POST['name'],
        'published_date' => $_POST['published_date'],
        'details' => $_POST['details']
      );

      */

      if( $this->DB->update($dataSanitized, $id) ) 
      {
          return header("Location: /book/{$id}");
      } 
        else
        {
         echo 'Updated Failed some thing went wrong!'; 
        }      

    }

    public function removeBook()
    {
        $id = Route::$params['id'];
         if( $this->DB->delete($id) ) 
         {
            header('location: /books');
         }
          else  
         {
         echo 'failed';
        }
    }

    public function bookApi()
    {

      $data = $this->bookListHandler();
      View::responseJson($data, 200);

    } 

    



    

}