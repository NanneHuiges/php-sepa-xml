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
abstract class DebitMessage extends Message
{
	/**
	 * @var \Digitick\Sepa\DebitPaymentInfo[]
	 */
	protected $payments;

	const INITIAL_STRING = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02"></Document>';

	public function __construct()
	{
		$this->xml = simplexml_load_string(self::INITIAL_STRING);
		$this->xml->addChild('CstmrDrctDbtInitn');
	}
	
	protected function checkXML(){
		$xsd = __DIR__.'/../../../assets/pain.008.001.02.xsd';
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->loadXML($this->xml->asXML());
		$dom->schemaValidate($xsd);
	}
	

	/**
	 * Set the information for the "Payment Information" block.
	 * @param array $paymentInfo
	 * @return \Digitick\Sepa\PaymentInfo
	 */
	abstract public function addPaymentInfo(array $paymentInfo);	
	
	/**
	 * Generate the XML structure.
	 */
	abstract protected function generateXml();
}

