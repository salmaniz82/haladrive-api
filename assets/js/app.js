angular.module('todoSPA', ['ui.router']);


angular.module('todoSPA').controller('appController', function($scope, $http, authFactory) {


    $scope.userName = null;

    authFactory.async().then(function(d) {
        $scope.authData = d;

        $scope.userName = d.user.name;

    });


});



angular.module('todoSPA').config(function($stateProvider, $urlRouterProvider, $locationProvider){

    
    $locationProvider.hashPrefix('');
    
    /*
    $locationProvider.html5Mode(true);
    */
    
    


    $urlRouterProvider.otherwise('/');

    

    $stateProvider

    .state('todos', {

        url: '/',
        templateUrl: '/pages/partials/todos.html',
        controller : 'todoController'

    })


    .state('books', {
        url: '/books',
        templateUrl: '/pages/partials/books.html',
        controller : 'booksController'
    })

    .state('cars', {
        url: '/cars',
        templateUrl: '/pages/partials/cars.html',
        controller : 'carsController'
    })

    .state('phones', {
        url: '/phones',
        templateUrl: '/pages/partials/phones.html',
        controller : 'phonesController'
    });


});





angular.module('todoSPA').controller('todoController', function($scope, $http){




	$scope.message = 'TODO SPA';

	$scope.todos;


    

	
	$http.get("/todospa/listapi")
    .then(function(response) {
	     $scope.todos =  response.data;   
    });

    

    $scope.changeStatus = function(todobj)
    {
    	
                
        var id = todobj.id;
        var url = '/todospa/update/'+todobj.id;
        todobj.date_complited = (new Date()).toISOString().substring(0, 10);


        


        $http({

        method: 'POST',
        url:  url,
        data: todobj

        }).then(function(response){

            console.log(response.data);

        });


    }





    $scope.addTodo = function(formdata)
    {
        

     
        formdata.user_id = $scope.authData.user.id;
        formdata.date_created = (new Date()).toISOString().substring(0, 10);
        formdata.is_complited = '0';

        

        $http({
            method: 'POST',
            url: '/todospa/add',
            data: formdata

        }).then(function(response){

            


            if(response.data.status == 'Succeess')
             {
                
                
                var lastid = response.data.lastId;

                $http.get("/todospa/single/"+lastid)
                .then(function(response) {
                    
                    $scope.todos.todos.splice(0, 0, response.data.todos[0]);
                    formdata.todo = ''; 
                });


             }
             

        });
    
        
    };


    $scope.clearTodo = function(id, $index) {
        
        url = '/todospa/clear/'+id+'/'+$scope.authData.user.id;

        $http.post(url).then(function(response) {

            if(response.data.status == 'Success')
            {
               $scope.todos.todos.splice($index, 1);
               console.log($index);
            }            

        });


    };



});


angular.module('todoSPA').controller('booksController', function($scope, $http){

    $scope.books;


    $http.get("/bookapi")
        
        .then(function(response) {
        $scope.books =  response.data;

    });




});


angular.module('todoSPA').service('getAuthData', function($http) {

        var self = this;
        
        this.userAuthLog = 44;
        this.message = 'Say hello from get auth data';


        this.doSomething = function(){
            return 'this is some string return via service';
        };


        this.checkReturnAuthStatus = function(){
            $http.get('/getauthenticateduser').then(function(response){

                return response.data;


            });


        
            
        };

});



angular.module('todoSPA').factory('authFactory', function($http) {

/*
    - Inside a factory you create an object 
    - you assign properties and method that object
    - at the end you return this object to be used inside a controller
*/


  var authFactory = {
    async: function() {
      // $http returns a promise, which has a then function, which also returns a promise
      var promise = $http.get('/getauthenticateduser').then(function (response) {
        // The then function here is an opportunity to modify the response
        //console.log(response);
        // The return value gets picked up by the then in the controller.
        return response.data;
      });
      // Return the promise to the controller
      return promise;
    }
  };
  return authFactory;
});


angular.module('todoSPA').controller('carsController', function($scope, $http, authFactory){

    
    authFactory.async().then(function(d) {
        $scope.data = d;
    });

    $scope.data;
    $scope.message = $scope.data;
    



});


angular.module('todoSPA').controller('phonesController', function($scope, $http){

    $scope.message = 'Message from Controller for Phone Page';   
  

        $http.get('getauthenticateduser').then(function(response){

            $scope.userdata = response.data;  

         });

});










