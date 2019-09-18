<?php

class ShipmentRequestService extends CI_Model
{
	/**
	 * @var string
	 */
	private $user;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $accountNumber;

	/**
	 * @param string $user
	 * @param string $password
	 * @param string $accountNumber
	 */
	public function __construct(string $user, string $password, string $accountNumber)
	{
		$this->user = $user;
		$this->password = $password;
		$this->accountNumber = $accountNumber;
	}

	/**
	 * @param \DateTimeInterface $shipTimestamp
	 * @param ContactInfoType $sender
	 * @param ContactInfoType $recipient
	 * @param ShipmentRequest $shipmentRequest
	 * @param string|null $restrictToServiceType
	 *
	 * @return \DHL\Express\Webservice\ShipmentDetailType
	 */
	public function createShipping(\DateTimeInterface $shipTimestamp, ContactInfoType $sender, ContactInfoType $recipient, ShipmentRequest $shipmentRequest, $restrictToServiceType = null)
	{
		$webservice = $this->prepareWebservice();

		$shipmentInfo = new ShipmentInfoType(
			DropOffType::REQUEST_COURIER,
			$this->calculateShippingType($shipmentRequest),
			ShipmentInfoType::CURRENCY_EUR,
			UnitOfMeasurement::SI
		);

		$shipmentInfo->setAccount($this->accountNumber);

		$internationalDetail = new InternationDetailType(new CommoditiesType('Keine Angabe'));
		$internationalDetail->setContent(Content::DOCUMENTS);

		$requestedShipment = new RequestedShipmentType(
			$shipmentInfo,
			sprintf('%sT%s GMT+01:00', $shipTimestamp->format('Y-m-d'), $shipTimestamp->format('H:i:s')),
			PaymentInfo::DDP,
			$internationalDetail,
			new ShipType($sender, $recipient),
			new docTypeRef_PackagesType(
				new docTypeRef_RequestedPackagesType(
					self::PACKAGE_WEIGHT,
					new docTypeRef_DimensionsType(self::PACKAGE_LENGTH, self::PACKAGE_WIDTH, self::PACKAGE_HEIGHT),
					'1',
					1,
					'No Description'
				)
			)
		);

		if (null !== $shipmentRequest->getSpecialPickupInstruction()) {
			$requestedShipment->setSpecialPickupInstruction($shipmentRequest->getSpecialPickupInstruction());
		}

		if (null !== $shipmentRequest->getPickupLocation()) {
			$requestedShipment->setPickupLocation($shipmentRequest->getPickupLocation());
		}

		if ($shipTimestamp instanceof \DateTimeImmutable) {
			$pickupLocationCloseTime = $shipTimestamp->modify('+91 minutes');
		} else {
			$pickupLocationCloseTime = \DateTimeImmutable::createFromMutable($shipTimestamp)->modify('+91 minutes');
		}

		$requestedShipment->setPickupLocationCloseTime($pickupLocationCloseTime->format('H:i'));

		$request = new ProcessShipmentRequestType($requestedShipment);

		$shipmentDetailType = $webservice->createShipmentRequest($request);

		return $shipmentDetailType;
	}

	/**
	 * This should only be done if the pickup type is something other than PickupType::Regular.
	 *
	 * @param ShipmentRequest $shipmentRequest
	 * @param string $requester This parameter is required but won't be evaluated.
	 * @param string $reason This parameter is optional but may be useful.
	 * @return DeleteResponseType
	 */
	public function cancel(ShipmentRequest $shipmentRequest, $requester = 'ERNIE_OR_BERT', $reason = DeleteRequestType::REASON_REASON_NOT_GIVEN): DeleteResponseType
	{
		$webservice = $this->prepareWebservice();

		$deleteRequest = new DeleteRequestType(
			$shipmentRequest->getShipmentTime()->format('Y-m-d'),
			$shipmentRequest->getPickupCountry(),
			$shipmentRequest->getDispatchConfirmationNumber(),
			$requester
		);

		$deleteRequest->setReason($reason);

		return $webservice->deleteShipmentRequest($deleteRequest);
	}

	/**
	 * @return GblDHLExpressTrack
	 */
	private function prepareWebservice(): GblDHLExpressTrack
	{
		$track = new GblDHLExpressTrack(['trace' => 1]);

		$wsse_header = WssWsuAuthHeader::soapClientWSSecurityHeader($this->user, $this->password, $this->accountNumber);
		$track->__setSoapHeaders([$wsse_header]);

		return $track;
	}
}
