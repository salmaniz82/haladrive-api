<?php

class View
{
    public static function render($page, $data=null)
    {
        
    	if(strpos($page, '.') === false)
    	{
    		$page = $page.'.php';
    		
    	}
    	else {
    		$page = $page;
    	}

        return require_once 'pages/'.$page;
        
    }


    public static function responseJson($data, $statusCode = 200)
    {
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        //header('x-powered-by: Helium MVC Framework');
       // echo json_encode($data, JSON_UNESCAPED_UNICODE); 
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function loadJsonFile($page, $statusCode = 200)
    {
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');


        if(strpos($page, '.') === false)
        {
            $page = $page.'.json';
            
        }
        else {
            $page = $page;
        }

        return require_once 'json/'.$page;


    }

    public static function composeTemplatePartial($templateRoute)
    {

        $templateRoute = siteURL().$templateRoute;
        echo file_get_contents($templateRoute);

    }

    public static function composeTemplateCurl($templateRoute)
    {

        $url = siteURL().$templateRoute;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);
        curl_close($curl);
        echo $data;
    }


    public static function fetchRoute($routeAddr)
    {

        $url = siteURL().$routeAddr;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            
            "Content-Type: application/json; charset=utf-8",
        ];

        if($token = jwtAuth::hasToken())
            {

                $string = "token: {$token}";

                array_push($headers, $string);
            }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


        $data = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($data, true);

        $serverhttp = $_SERVER;

        return $data;
        
    }

    

}
