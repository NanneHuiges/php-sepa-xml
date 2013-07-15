<?php

namespace Digitick\Sepa;

/**
 * SEPA file generator.
 *
 * ALPHA QUALITY SOFTWARE
 * Do NOT use in production environments!!!
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
 * @license GNU Lesser General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Lesser Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Jérémy Cambon
 * @author Ianaré Sévi
 * @author Vincent MOMIN
 * @author Nanne Huiges
 */

/**
 * SEPA payments file object.
 */
class CreditMessage extends Message
{
	/**
	 * @var \Digitick\Sepa\CreditPaymentInfo[]
	 */
	protected $payments;

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"></Document>';

	public function __construct()
	{
		$this->xml = simplexml_load_string(self::INITIAL_STRING);
		$this->xml->addChild('CstmrCdtTrfInitn');
	}
	

	/**
	 * Set the information for the "Payment Information" block.
	 * @param array $paymentInfo
	 * @return \Digitick\Sepa\PaymentInfo
	 */
	public function addPaymentInfo(array $paymentInfo)
	{
		$payment = new CreditPaymentInfo($this);
		$payment->setInfo($paymentInfo);
	
		$this->payments[] = $payment;
	
		return $payment;
	}	
	
	/**
	 * Generate the XML structure.
	 */
	protected function generateXml()
	{
		$this->updatePaymentCounters();
		
		$datetime = new \DateTime();
		$creationDateTime = $datetime->format('Y-m-d\TH:i:s');

		// -- Group Header -- \\

		$GrpHdr = $this->xml->CstmrCdtTrfInitn->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification);
		$GrpHdr->addChild('CreDtTm', $creationDateTime);
		if ($this->isTest)
			$GrpHdr->addChild('Authstn')->addChild('Prtry', 'TEST');

		$GrpHdr->addChild('NbOfTxs', $this->numberOfTransactions);
		$GrpHdr->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));
		$GrpHdr->addChild('InitgPty')->addChild('Nm', $this->initiatingPartyName);
		if (isset($this->initiatingPartyId))
			$GrpHdr->addChild('InitgPty')->addChild('Id', $this->initiatingPartyId);

		// -- Payment Information --\\
		foreach ($this->payments as $payment) {
			$this->xml = $payment->generateXml($this->xml);
		}
	}
}

