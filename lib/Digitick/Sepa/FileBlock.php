<?php

namespace Digitick\Sepa;

/**
 * SEPA file generator.
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
 */

/**
 * Base class for SEPA file blocks.
 */
abstract class FileBlock
{

	//abstract public function generateXml(SimpleXMLElement $xml);

	/**
	 * Format an integer as a monetary value.
	 */
	protected function intToCurrency($amount)
	{
		return sprintf("%01.2f", ($amount / 100));
	}

	/**
	 * @param string $code
	 * @return string currency ISO code
	 * @throws Exception
	 */
	protected function validateCurrency($code)
	{
		if (strlen($code) !== 3)
			throw new Exception("Invalid ISO currency code: $code");

		return $code;
	}
	
	/**
	 * Validates BIC according to the xsd expression
	 * @param string $bic
	 */
	protected function validateBIC($bic){
		$pattern = '/^[A-Z]{6,6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3,3}){0,1}$/';
		if( ! preg_match($pattern, $bic)){
			throw new Exception("'$bic' is not a valid BIC");
		}
	}
	/**
	 * Validates IBAN according to the xsd expression
	 * @param string $iban
	 */
	protected function validateIBAN($iban){
		$pattern = '/^[A-Z]{2,2}[0-9]{2,2}[a-zA-Z0-9]{1,30}$/';
		if( ! preg_match($pattern, $iban)){
			throw new Exception("'$iban' is not a valid IBAN");
		}
	}

	

}
