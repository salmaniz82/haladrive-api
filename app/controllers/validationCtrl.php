<?php 
class validationCtrl extends appCtrl 
{
	public function show()
	{
		$data['title'] = 'validation test';

		View::render('validation-test.php', $data);
	}

	public function processForm()
	{

		$gump = new GUMP();

		$_POST = $gump->sanitize($_POST); // You don't have to sanitize, but it's safest to do so.

		$gump->validation_rules(array(
			'email'       => 'required|valid_email',
			'password'    => 'required|max_len,10|min_len,6',
			
		));

		
		$pdata = $gump->run($_POST);

		if($pdata === false) 
		{
		
			echo "Please Correct errors in your input";
			var_dump($gump->get_errors_array());
			
		}

		else
		{
				echo "Validation Success";
		}

		
	}

}