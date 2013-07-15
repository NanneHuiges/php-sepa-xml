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
class DebitTransferING extends DebitTransfer
{
	/**
	 * (non-PHPdoc)
	 * @see \Digitick\Sepa\DebitTransfer::generateXml()
	 */
	public function generateXml(\SimpleXMLElement $xml)
	{
		// -- Credit Transfer Transaction Information --\\
		
		$amount = $this->intToCurrency($this->getAmountCents());

		$DrctDbtTxInf = $xml->addChild('DrctDbtTxInf');
		$PmtId = $DrctDbtTxInf->addChild('PmtId');
		$PmtId->addChild('InstrId', $this->id);
		$PmtId->addChild('EndToEndId', $this->endToEndId);
		$DrctDbtTxInf->addChild('InstdAmt', $amount)->addAttribute('Ccy', $this->currency);
		
		$MndtRltdInf = $DrctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
		$MndtRltdInf->addChild('MndtId',$this->mandateId);
		$MndtRltdInf->addChild('DtOfSgntr', '2012-01-01'); //TODO
		
		$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorBIC);
		$DrctDbtTxInf->addChild('Dbtr')->addChild('Nm', htmlentities($this->debtorName));
		$DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->remittanceInformation);
		
		return $xml;
	}
}
