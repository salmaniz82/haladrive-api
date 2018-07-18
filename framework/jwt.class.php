<?php

class JwtAuth {

    public static $isLoggedIn;
    public static $user;


    public static function hasToken()
    {
        if( isset($_SERVER['HTTP_TOKEN']) && $_SERVER['HTTP_TOKEN'] != null)
        {

            return $_SERVER['HTTP_TOKEN'];
        }
        else
            {
                return false;
            }
    }

    public static function findUserWithCreds($creds)
    {
        $db = new Database();
        $db->table = 'users';
        $email = mysqli_real_escape_string($db->connection, $creds['email']);
        $password = mysqli_real_escape_string($db->connection, $creds['password']);

        if($result = $db->returnSet($email, $password))
        {

            self::$isLoggedIn = true;
            self::$user = $result[0];
            return $user = $result[0];

        }
        else
        {
            self::$isLoggedIn = false;
            return false;
        }
    }

    public static function generateToken($payload)
    {

        $user_id = $payload['id'];

        $header = ["alg" => "HS256", "typ" => "JWT"];

        $header = base64_encode( json_encode($header));

        $payload = base64_encode( json_encode($payload));

        $key = self::getKey();

        $signature = hash_hmac('sha256', "$header.$payload", $key, true);

        $signature = base64_encode($signature);

        $token = "$header.$payload.$signature";

        return self::tokenDbProcedure($token, $user_id);


    }

    public static function tokenDbProcedure($token, $user_id = null)
    {
            $db = new Database();
            $db->table = 'user_token';

            $data = array('user_id' => $user_id, 'token'=> $token);

            if($storedToken = self::findExistingToken(null, $user_id))
            {
                return $storedToken;


            }
            else if ($newToken = $db->insert($data))
            {
                return $token;
            }

            else
                {
                    return false;
                }


    }

    public static function findExistingToken($token = null, $user_id = null)
    {
        $db = new Database();
        $db->table = 'user_token';

        if($user_id != null)
        {
            $searchCondition = "user_id = ".$user_id;
        }
        else {
            $searchCondition = "token = '".$token."'";
        }


        if($storedToken = $db->build('S')->Colums()->Where($searchCondition)->go()->returnData())
        {

            $db->table = 'users';

            $user_id = $storedToken[0]['user_id'];

            $user = $db->getbyId($user_id, ['id', 'name', 'email', 'role_id'])->returnData();

            self::$user = $user[0];
           
            return $storedToken[0]['token'];


        }
        else {
            return false;
        }

    }


    public static function validateToken()
    {
        if($userToken = self::hasToken() )
        {

            if(self::findExistingToken($userToken))
            {
                return true;
            }

        }
    }




    private static function getKey()
    {
        return JWT_SECRET;
    }


}