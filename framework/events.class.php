<?php 

class Event 
{
	private static $events = array();

	
	public static function on($type, $callback)
	{

		if(!array_key_exists($type, self::$events) )
		{
			
			self::$events[$type] = array();

		}
		
		array_push(self::$events[$type], $callback);
		
	}


	public static function fire($type)
	{


		if(array_key_exists($type, self::$events) )
		{
			
			foreach (self::$events[$type] as $callback) 
			{

				$callback();

			}	

		}

		else {
			echo "<span style=\"color: red\"> Unrecognized Event : " . $type . "</span> <br >";
		}

	}


	public static function checkmessage()
	{
		echo self::$message;
	}

}


