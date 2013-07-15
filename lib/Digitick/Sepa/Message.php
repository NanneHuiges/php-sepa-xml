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
abstract class Message extends FileBlock
{
	/**
	 * @var boolean If true, the transaction will never be executed.
	 */
	public $isTest = false;
	/**
	 * @var string Unambiguously identify the message.
	 */
	public $messageIdentification;
	/**
	 * @var string Payment sender's name.
	 */
	public $initiatingPartyName;
	/**
	 * @var string Payment sender's ID (for example: the tax ID).
	 */
	public $initiatingPartyId;
	/**
	 * @var string Purpose of the transaction(s).
	 */
	public $categoryPurposeCode;
	/**
	 * @var string NOT USED - reserve for future.
	 */
	public $grouping;

	/**
	 * @var integer Sum of all transactions in all payments regardless of currency.
	 */
	protected $controlSumCents = 0;
	/**
	 * @var integer Number of payment transactions.
	 */
	protected $numberOfTransactions = 0;
	/**
	 * @var \SimpleXMLElement
	 */
	protected $xml;
	
	/**
	 * Return the XML string.
	 * @return string
	 */
	public function asXML()
	{
		$this->generateXml();
		return $this->xml->asXML();
	}
	
	/**
	 * Get the header control sum in cents.
	 * @return integer
	 */
	public function getHeaderControlSumCents()
	{
		return $this->controlSumCents;
	}

	/**
	 * Get the payment control sum in cents.
	 * @return integer
	 */
	public function getPaymentControlSumCents()
	{
		return $this->controlSumCents;
	}


	/**
	 * Update counters related to "Payment Information" blocks.
	 */
	protected function updatePaymentCounters()
	{
		$this->numberOfTransactions = 0;
		$this->controlSumCents = 0;
		
		foreach ($this->payments as $payment) {
			$this->numberOfTransactions += $payment->getNumberOfTransactions();
			$this->controlSumCents += $payment->getControlSumCents();
		}
	}

}

