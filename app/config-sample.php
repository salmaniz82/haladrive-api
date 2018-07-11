<?php

function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'].'/';
    return $protocol.$domainName;
}

define( 'SITE_URL', siteURL() );



if(SITE_URL == 'http://api.haladrive.local/')
	{
		define('ENV', 'Dev');
		define('SERVER', 'localhost');
		define('USER', 'root');
		define('DATABASE', 'haladrive-api');
		define('PASSWORD', '');
		define( 'TIMEZONE', 'Asia/Karachi');

	}

	else {

		define('ENV', 'Live');
		define('SERVER', 'localhost');
		define('USER', 'serverUser');
		define('DATABASE', 'userDB');
		define('PASSWORD', 'userMysqlPassword');
		define( 'TIMEZONE', 'Asia/Kuwait');

}

date_default_timezone_set(TIMEZONE);