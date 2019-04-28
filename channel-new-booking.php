<?php

if($_SERVER['HTTP_HOST'] == 'api.haladrive.local')
{
	header('Access-Control-Allow-Origin: http://localhost');
}
else {

	header('Access-Control-Allow-Origin: https://app.haladrive.com');

}

 
	header('Access-Control-Expose-Headers: *');
	header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    header("Content-Type: text/event-stream");
	header("Cache-Control: no-cache");
	header("Connection: keep-alive");

		$lastId = $_SERVER["HTTP_LAST_EVENT_ID"];
		if (isset($lastId) && !empty($lastId) && is_numeric($lastId)) {
		    $lastId = intval($lastId);
		    $lastId++;
		}

		if ( !defined('ABSPATH') )
		define('ABSPATH', dirname(__FILE__) . '/');

		require_once ABSPATH .'app/config.php';
		require_once ABSPATH .'framework/database.class.php';

		$db =  new Database();
		$db->table = 'bookings';

		if(isset($_GET['user_id']))
		{

			$user_id = $_GET['user_id'];
			$sql = "SELECT COUNT(id) as 'totalBookings', max(id) AS 'lastBookingId' from bookings WHERE user_id = {$user_id} LIMIT 1";
		}
		else {
			$sql = "SELECT COUNT(id) as 'totalBookings', max(id) AS 'lastBookingId' from bookings LIMIT 1";
		}

		while (true) {

			$data = $db->rawSql($sql)->returnData()[0];

		    if ($data) {
		        sendMessage($lastId, $data);
		        $lastId++;
		    }
		    sleep(5);
		}

		function sendMessage($id, $data) {
		    echo "id: $id\n";
		    echo 'data: ' . json_encode($data) . "\n\n";
		    ob_flush();
		    flush();
		}
