<?php
class invoiceModule extends appCtrl {

    public $DB;

    function __construct()
    {
        $this->DB = new Database();
        $this->DB->table = 'invoices';
    }


	public function listVendorInvoice()
	{

	}


	public function listClientInvoice()
	{

	}



	public function removeInvoice($bookingID)
	{

	}

    public function generateInvoice($bookingId)
    {


        $bookingModule = $this->load('module', 'booking');

        if(JwtAuth::validateToken())
        {
            $user_id = (int) JwtAuth::$user['id'];
        }

        if($bookingData = $bookingModule->getSingle($bookingId))
        {

            $perDay = $bookingData[0][0]['perDay'];
            $keys['booking_id'] = $bookingId;
            $keys['user_id'] = $user_id;

            $keys['perDay'] = $perDay;
            $keys['status'] = "Unpaid";

            if($this->DB->insert($keys))
            {
                return true;
            }
            else {
                return false;
            }


        }





    }

	
}