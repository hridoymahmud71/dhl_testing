<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dhl_controller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("Dhl_model");
		$this->load->model("Petschko_given_test_model");
	}

	public function test_credentials()
	{
		$this->Dhl_model->get_credentials();
		$this->Dhl_model->print_this(false,true);
	}

	public function test_shipment()
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

		$shipment_details_object = $this->Dhl_model->set_shipment_details($shipment_details_params);
		$sender_obj =  $this->Dhl_model->set_sender($sender_params);
		$receiver_obj =  $this->Dhl_model->set_receiver($receiver_params);

		$shipment_order_object = $this->Dhl_model->set_shipment_order($shipment_details_object,$sender_obj,$receiver_obj);

		$shipment_response = $this->Dhl_model->create_shipment(array($shipment_order_object));
		$shipment_response_elaborated = $this->Dhl_model->get_elaborated_response($shipment_response['response']);
		echo "<pre>";
		print_r($shipment_response);
		//print_r($shipment_response_elaborated);
		echo "</pre>";
	}

	public function run_petscko_given_test()
	{
		$this->Petschko_given_test_model->run_test();
	}


}
