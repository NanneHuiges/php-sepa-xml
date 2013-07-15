<?php

namespace Digitick\Sepa;

/**
 * SEPA file generator for direct debit
 * 
 * @author Nanne Huiges, based on CreditTransfer by @ digitick
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
 */

/**
 * SEPA file "Debit Transfer Transaction Information" block.
 */
abstract class DebitTransfer extends FileBlock
{
	/**
	 * @var string Payment ID.
	 */
	public $id;
	/**
	 * @var string
	 */
	public $endToEndId;
	/**
	 * @var string Account bank's BIC
	 */
	public $debtorBIC;
	/**
	 * @var string Name
	 */
	public $debtorName;
	/**
	 * @var string account IBAN
	 */
	public $debtorAccountIBAN;
	/**
	 * @var string Remittance information.
	 */
	public $remittanceInformation;

	/**
	 * @var string Mandate Identification
	 */
	public $mandateId;
	
	/**
	 * @var string ISO currency code
	 */
	protected $currency;
	/**
	 * @var integer Transfer amount in cents.
	 */
	protected $amountCents = 0;

	
	
	
	/**
	 * Set the transfer amount.
	 * @param mixed $amount
	 */
	public function setAmount($amount)
	{
		$amount += 0;
		if (is_float($amount))
			$amount = (integer) ($amount * 100);

		$this->amountCents = $amount;
	}

	/**
	 * Get the transfer amount in cents.
	 * @return integer
	 */
	public function getAmountCents()
	{
		return $this->amountCents;
	}
	
	/**
	 * Set the debtor's account currency code.
	 * @param string $code currency ISO code
	 * @throws Exception
	 */
	public function setCurrency($code)
	{
		$this->currency = $this->validateCurrency($code);
	}
	
	/**
	 * DO NOT CALL THIS FUNCTION DIRECTLY!
	 * 
	 * @param \SimpleXMLElement $xml
	 * @return \SimpleXMLElement
	 */
	abstract public function generateXml(\SimpleXMLElement $xml);
}
