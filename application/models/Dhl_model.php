<?php

require_once(APPPATH . '/libraries/dhl-php-sdk/includes/_nonComposerLoader.php');
use Petschko\DHL\Credentials as Credentials;
class Dhl_model extends CI_Model
{
	const DDAU = "shopinna";
	const DDAP = "kn@02@07NGWU";

	private $credentials;
	private $mode;
	private $api_user;
	private $api_password;

	//test
	private $dhl_dev_acc_username; 		//USERNAME (not E-Mail!) of your DHL-Dev-Account
	private $dhl_dev_acc_password; 		//Password of your DHL-Dev-Account

	//live
	private $application_id;        	// Your Applications-ID (You can find it in your DHL-Dev-Account)
	private $application_token;        // Your Applications-Token (You can find it also where you found the App-Id)

	private $dhl_account; 				// DHL-Account (Same as if you Login with then to create Manual-Labels)
	private $dhl_account_password;		// DHL-Account-Password
	private $dhl_account_number;		// Number of your Account (Provide at least the first 10 digits)

	public function __construct()
	{
		parent::__construct();
		$this->init();
	}

	//test-ables <starts>
	public function dump_this()
	{
		var_dump($this);
	}

	public function print_this($as_array = false,$with_pre = false, $exit = false)
	{
		$outcome = $this;
		if($as_array){
			$outcome = (array) $this;
		}

		if ($with_pre) {
			echo "<pre>";
		}
		print_r($outcome);
		if ($with_pre) {
			echo "</pre>";
		}

		if ($exit) {
			exit;
		}
	}

	public function get_this()
	{
		return $this;
	}
	//test-ables <ends>


	public function init()
	{
		$this->set_mode()->set_api_user_and_password()->set_other_live_credentials()->set_credentials();
		return $this;
	}

	public function get_mode()
	{
		return $this->mode;
	}

	public function set_mode()
	{
		$this->mode = "test"; //test or live //get from db //hardcoded now
		return $this;
	}

	public function set_api_user_and_password()
	{
		if ($this->mode == "test") {
			$this->dhl_dev_acc_username = self::DDAU; 			//get from db //hardcoded now
			$this->dhl_dev_acc_password = self::DDAP; 			//get from db //hardcoded now
		} else {
			$this->application_id = ""; 						//get from db //hardcoded now
			$this->application_token = ""; 						//get from db //hardcoded now
		}

		return $this;
	}

	public function set_other_live_credentials()
	{
		if ($this->mode == "live") {
			$this->dhl_account = ""; 					//get from db //hardcoded now
			$this->dhl_account_password = ""; 			//get from db //hardcoded now
			$this->dhl_account_number = ""; 			//get from db //hardcoded now
		}
		return $this;
	}


	public function get_credentials()
	{
		return $this->credentials;
	}

	public function set_credentials()
	{
		$credentials = null;
		if ($this->mode == "test") {
			// You can initial the Credentials-Object with one of the pre-set Test-Accounts
			$credentials = new \Petschko\DHL\Credentials(/* Optional: Test-Modus */
				Credentials::TEST_NORMAL); // Normal-Testuser
			$credentials = new \Petschko\DHL\Credentials(/* Optional: Test-Modus */
				Credentials::TEST_THERMO_PRINTER); // Thermo-Printer-Testuser
			// Now you just need to set your DHL-Developer-Data to it
			$credentials->setApiUser($this->api_user); // Set the USERNAME (not E-Mail!) of your DHL-Dev-Account
			$credentials->setApiPassword($this->api_password); // Set the Password of your DHL-Dev-Account
		} else {
			// Just create the Credentials-Object
			$credentials = new \Petschko\DHL\Credentials();
			// Setup these Infos: (ALL Infos are Case-Sensitive!)
			$credentials->setUser($this->dhl_account); // DHL-Account (Same as if you Login with then to create Manual-Labels)
			$credentials->setSignature($this->dhl_account_password); // DHL-Account-Password
			$credentials->setEkp($this->dhl_account_number); // Number of your Account (Provide at least the first 10 digits)
			$credentials->setApiUser($this->api_user); // Your Applications-ID (You can find it in your DHL-Dev-Account)
			$credentials->setApiPassword($this->api_password); // Your Applications-Token (You can find it also where you found the App-Id)
		}

		$this->credentials = $credentials;
		return $this;
	}


	public function get_api_user()
	{
		return $this->api_user;
	}

	public function set_api_user()
	{
		if ($this->mode == "test") {
			$this->api_user = $this->dhl_dev_acc_username;
		} else {
			$this->api_user = $this->application_id;
		}

		return $this;
	}

	public function get_api_password()
	{
		return $this->get_api_password;
	}

	public function set_api_password()
	{
		if ($this->mode == "test") {
			$this->api_password = $this->dhl_dev_acc_password;
		} else {
			$this->api_password = $this->application_id;
		}

		return $this;
	}

	//------------------------------------------------------------------------------------------------------------------

	//$params has mandatory keys like name,street_name,street_number,zip,city,country,country_iso_code
	//$additional_params has optional keys
	public function set_sender(array $params,array $additional_params = array())
	{
		$sender = new \Petschko\DHL\Sender();

		$sender->setName((string) $params['name']); // Can be a Person-Name or Company Name
		// You need to seperate the StreetName from the Number and set each one to its own setter
		// Example Full Address: "Oberer Landweg 12a"
		$sender->setStreetName((string) $params['street_name']);
		$sender->setStreetNumber((string) $params['street_number']); // A Number is ALWAYS needed
		$sender->setZip((string) $params['zip']);
		$sender->setCity((string) $params['city']);
		$sender->setCountry((string) $params['country']);
		$sender->setCountryISOCode((string) $params['country_iso_code']); // 2 Chars ONLY

		//// You can specify the delivery location
		if(!empty($additional_params) && isset($additional_params['address_addition'])){
			$sender->setAddressAddition((string) $additional_params['address_addition']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['dispatching_info'])){
			$sender->setDispatchingInfo((string) $additional_params['dispatching_info']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['state'])){
			$sender->setState((string) $additional_params['state']); // Default: null -> Disabled
		}

		// You can add more Personal-Info
		if(!empty($additional_params) && isset($additional_params['name2'])){
			$sender->setName2((string) $additional_params['name2']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['name3'])){
			$sender->setName3((string) $additional_params['name3']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['phone'])){
			$sender->setPhone((string) $additional_params['phone']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['email'])){
			$sender->setEmail((string) $additional_params['email']); // Default: null -> Disabled
		}

		// Mostly used in bigger Companies (Contact-Person)
		if(!empty($additional_params) && isset($additional_params['contact_person'])){
			$sender->setContactPerson((string) $additional_params['contact_person']); // Default: null -> Disabled
		}

		return $sender;

	}

	//$params has mandatory keys like name,street_name,street_number,zip,city,country,country_iso_code
	//$additional_params has optional keys
	public function set_receiver(array $params,array $additional_params = array())
	{
		$receiver = new \Petschko\DHL\Receiver();

		$receiver->setName((string) $params['name']); // Can be a Person-Name or Company Name
		// You need to seperate the StreetName from the Number and set each one to its own setter
		// Example Full Address: "Oberer Landweg 12a"
		$receiver->setStreetName((string) $params['street_name']);
		$receiver->setStreetNumber((string) $params['street_number']); // A Number is ALWAYS needed
		$receiver->setZip((string) $params['zip']);
		$receiver->setCity((string) $params['city']);
		$receiver->setCountry((string) $params['country']);
		$receiver->setCountryISOCode((string) $params['country_iso_code']); // 2 Chars ONLY

		//// You can specify the delivery location
		if(!empty($additional_params) && isset($additional_params['address_addition'])){
			$receiver->setAddressAddition((string) $additional_params['address_addition']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['dispatching_info'])){
			$receiver->setDispatchingInfo((string) $additional_params['dispatching_info']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['state'])){
			$receiver->setState((string) $additional_params['state']); // Default: null -> Disabled
		}

		// You can add more Personal-Info
		if(!empty($additional_params) && isset($additional_params['name2'])){
			$receiver->setName2((string) $additional_params['name2']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['name3'])){
			$receiver->setName3((string) $additional_params['name3']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['phone'])){
			$receiver->setPhone((string) $additional_params['phone']); // Default: null -> Disabled
		}
		if(!empty($additional_params) && isset($additional_params['email'])){
			$receiver->setEmail((string) $additional_params['email']); // Default: null -> Disabled
		}

		// Mostly used in bigger Companies (Contact-Person)
		if(!empty($additional_params) && isset($additional_params['contact_person'])){
			$receiver->setContactPerson((string) $additional_params['contact_person']); // Default: null -> Disabled
		}

		return $receiver;

	}

	//$params : shipment_date,
	public function set_shipment_details(array $params =  array())
	{
		$credentials = $this->get_credentials();
		// Create the Object with the first 10 Digits of your Account-Number (EKP).
		// You can use the \Petschko\DHL\Credentials function "->getEkp((int) amount)" to get just the first 10 digits if longer
		$shipmentDetails = new \Petschko\DHL\ShipmentDetails((string) $credentials->getEkp(10) . '0101'); // Ensure the 0101 at the end (or the number you need for your Product)

		// You can set a Shipment-Date you have to provide it in this Format: YYYY-MM-DD
		if(!empty($params) && isset($params['shipment_date'])){
			$shipmentDetails->setShipmentDate((string) $params['shipment_date']); // Default: Today or 1 day higher if Today is a Sunday
		}
		return $shipmentDetails;
	}

	public function set_shipment_order($shipmentDetails,$sender,$receiver,array $optional_params = array())
	{
		$shipmentOrder = new \Petschko\DHL\ShipmentOrder();
		// Add all the required informations from previous Objects
		$shipmentOrder->setShipmentDetails($shipmentDetails); // \Petschko\DHL\ShipmentDetails Object
		$shipmentOrder->setSender($sender); // \Petschko\DHL\Sender Object
		$shipmentOrder->setReceiver($receiver); // \Petschko\DHL\Receiver, \Petschko\DHL\PackStation or the \Petschko\DHL\Filial Object

		return $shipmentOrder;
	}

	public function create_business_shipment()
	{
		$credentials = $this->get_credentials();
		$dhl = new \Petschko\DHL\BusinessShipment($credentials);
		return $dhl;
	}

	public function create_shipment(array $shipmentOrders)
	{
		$dhl = $this->create_business_shipment();

		foreach ($shipmentOrders as $shipmentOrder) {
			$dhl->addShipmentOrder($shipmentOrder);
		}

		$response = $dhl->createShipment();

		$shipment_response = array();
		$shipment_response['response'] = $response;
		$shipment_response['success'] = false;
		$shipment_response['errors'] = array();


		if($response === false) {
			$shipment_response['errors'] = $dhl->getErrors(); // Get the Error-Array
		} else {
			$shipment_response['success'] = true;
		}

		return $shipment_response;
	}

	public function delete_shipment(array $shipment_numbers)
	{
		$dhl = $this->create_business_shipment();
		$response = $dhl->deleteShipment((array) $shipment_numbers);

		$delete_response = array();
		$delete_response['response'] = $response;
		$delete_response['success'] = false;
		$delete_response['errors'] = array();

		if($response === false) {
			$delete_response['errors'] = $dhl->getErrors(); // Get the Error-Array
		} else {
			$delete_response['success'] = true;
		}

		return $delete_response;
	}

	public function get_elaborated_response($response)
	{
		$elaborated_response = array();
		if(!empty($response)){
			for($i = 0; $i < $response->countLabelData(); $i++) {
				// For example get the Shipment-Number of every item
				$elaborated_response[$i]['shipment_number'] = $response->getLabelData($i)->getShipmentNumber();
				// Status Values of each request (Every Request-Item has their own status)
				$elaborated_response[$i]['shipment_code'] = (int) $response->getLabelData($i)->getStatusCode(); // Returns the Status-Code of the 1st Request (Difference to DHL - Weak-Validation is 1 not 0) - See below
				$elaborated_response[$i]['status_text'] = (string) $response->getLabelData($i)->getStatusText(); // Returns the Status-Text of the 1st Request or null
				$elaborated_response[$i]['status_message'] = (string) $response->getLabelData($i)->getStatusMessage(); // Returns the Status-Message (More details) of the 1st Request or null
				// Info-Values
				$elaborated_response[$i]['shipment_number'] =  (string) $response->getLabelData($i)->getShipmentNumber(); // Returns the Shipment-Number of the 1st Request or null
				$elaborated_response[$i]['label'] = (string) $response->getLabelData($i)->getLabel(); // Returns the Label URL or Base64-Label-String of the 1st Request or null
				$elaborated_response[$i]['return_label'] = (string) $response->getLabelData($i)->getReturnLabel(); // Returns the ReturnLabel (URL/B64) of the 1st Request or null
				$elaborated_response[$i]['export_doc'] = (string) $response->getLabelData($i)->getExportDoc(); // Returns the Export-Document (URL/B64) of the 1st Request or null (Can only be obtained if the Export-Doc Object was added to the Shipment request)
				$elaborated_response[$i]['sequence_number'] = (string) $response->getLabelData($i)->getSequenceNumber(); // Returns your provided sequence number of the 1st Request or null
				$elaborated_response[$i]['code_label'] =(string) $response->getLabelData($i)->getCodLabel(); // Returns the Cod-Label of the 1st Request or null
			}
		}


		return $elaborated_response;
	}
}
