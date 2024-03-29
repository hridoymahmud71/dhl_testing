<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once (APPPATH.'/models/Dreipunktnull_shipment_request_service.php');
require_once (APPPATH.'/libraries/dhl-express-master/vendor/autoload.php');
use DHL\Express\Webservice\ContactInfoType;
use DHL\Express\Webservice\ContactType;
use DHL\Express\Webservice\AddressType;
class Dhl_controller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("Petschko_model");
		$this->load->model("Petschko_given_test_model");
		$this->load->model("Alfallouji_given_test_model");
		//$this->load->model("Dreipunktnull_shipment_request_service");
	}

	public function test_petschko_credentials()
	{
		$this->Petschko_model->get_credentials();
		$this->Petschko_model->print_this(false,true);
	}

	public function test_petschko_shipment()
	{
		$sender_params = array();
		$sender_params['name'] = "Chinaza Specialist Hospital";
		$sender_params['street_name'] = "Hakeem Onitiri Street, Offago Palace Way , Okota";
		$sender_params['street_number'] = "2";
		$sender_params['zip'] = "1111";
		$sender_params['city'] = "Lagos";
		$sender_params['country'] = "Nigeria";
		$sender_params['country_iso_code'] = "NG";

		$receiver_params = array();
		$receiver_params['name'] = "Diagnostic & Therapeutic Endoscopy Centre (D&TEC)";
		$receiver_params['street_name'] = "koyi Club Road , Ikoyi";
		$receiver_params['street_number'] = "8";
		$receiver_params['zip'] = "2222";
		$receiver_params['city'] = "Lagos";
		$receiver_params['country'] = "Nigeria";
		$receiver_params['country_iso_code'] = "NG";

		$shipment_details_params = array();
		$shipment_details_params['shipment_date'] = "2019-09-18";

		$shipment_details_object = $this->Petschko_model->set_shipment_details($shipment_details_params);
		$sender_obj =  $this->Petschko_model->set_sender($sender_params);
		$receiver_obj =  $this->Petschko_model->set_receiver($receiver_params);

		$shipment_order_object = $this->Petschko_model->set_shipment_order($shipment_details_object,$sender_obj,$receiver_obj);

		$shipment_response = $this->Petschko_model->create_shipment(array($shipment_order_object));
		$shipment_response_elaborated = $this->Petschko_model->get_elaborated_response($shipment_response['response']);
		echo "<pre>";
		print_r($shipment_response);
		//print_r($shipment_response_elaborated);
		echo "</pre>";
	}

	public function run_petscko_given_test_code()
	{
		$this->Petschko_given_test_model->run_test();
	}
	
	//------------------------------------------------------------------------------------------------------------------

	public function run_alfallaouji_given_shipment_request()
	{
		$this->Alfallouji_given_test_model->shipment_request();
	}

	public function run_reipunktnull_shipment_request()
	{
		$user =  'v62_RQzB9wtMF5';
		$password = 'vf3fmnZ8TM' ;
		$accountNumber = '365162861' ;
		 $shipping_request_service = new  Dreipunktnull_shipment_request_service($user,$password,$accountNumber);

		$date = new DateTime('2019-09-25 11:00:00', new DateTimeZone('Europe/Rome'));

		$sct = new ContactType("Ike  Izon-ebi","Patterson-Fletcher","07087499733");
		$sat = new AddressType("Omo Olugbon House, Obafalabi Street, Ojodu Berger","Ikeja","","NG");
		$sender = new ContactInfoType($sct,$sat);

		$rct = new ContactType("Blessing  Izon-ebi","Olson Electronics","08034161826");
		$rat = new AddressType("2B, Ogalade Close","Lagos Island","","NG");
		$receiver = new ContactInfoType($rct,$rat);


		$shipment_request = new stdClass();
		$shipment_detail_type =  $shipping_request_service->createShipping($date,$sender,$receiver,$shipment_request);

		echo "<pre>";
		print_r($shipment_detail_type);
		echo "<pre>";

	}


}
