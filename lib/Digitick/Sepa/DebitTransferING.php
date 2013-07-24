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
	
	private function cleanTransferArray($transferInfo){
		if(!isset($transferInfo['id']) || strlen($transferInfo['id']) > 35){
			throw new Exception('InstructionIdentification should be set and 35 characters or less');
		}
		
		if(isset($transferInfo['debtorBIC'])){
			$this->validateBIC($transferInfo['debtorBIC']);
		}else{
			throw new Exception('Only BIC/IBAN identification supported, so debtorBIC is mandatory');
		}
		
		if(isset($transferInfo['debtorAccountIBAN'])){
			$this->validateIBAN($transferInfo['debtorAccountIBAN']);
		}else{
			throw new Exception('Only BIC/IBAN identification supported, so debtorAccountIBAN is mandatory');
		}
				
		if(isset($transferInfo['debtorName'])){
			$transferInfo['debtorName'] = substr($transferInfo['debtorName'], 0,140);
		}else{
			throw new Exception('Please provide a debtorName');
		}
				
		
		if(isset($transferInfo['remittanceInformation'])){
			$transferInfo['remittanceInformation'] = substr($transferInfo['remittanceInformation'], 0,140);
		}else{
			throw new Exception('Please provide the remittanceInformation');
		}

		if(isset($transferInfo['mandateId'])){
			if(strlen($transferInfo['mandateId']) > 35){
				throw new Exception('When including a mandateId, it should be 35 characters or less');
			}
			if(!isset($transferInfo['mandateSigDate'])){
				throw new Exception('If mandateId is provided, the singature date is mandatory.');
			}
		}
	}
	
	/**
	 * Set the information for this transfer transaction block.
	 * @param array $transferInfo
	 */
	public function setInfo($transferInfo){
		$this->cleanTransferArray(&$transferInfo);

		$this->id 						= $transferInfo['id'];
		$this->debtorBIC 				= $transferInfo['debtorBIC'];
		$this->debtorName 				= $transferInfo['debtorName'];
		$this->debtorAccountIBAN 		= $transferInfo['debtorAccountIBAN'];
		$this->remittanceInformation	= $transferInfo['remittanceInformation'];
		if(isset($transferInfo['mandateId'])){
			$this->mandateId			= $transferInfo['mandateId'];
			$this->mandateSigDate 		= $transferInfo['mandateSigDate'];
		}	
		
		if (isset($transferInfo['amount']))
			$this->setAmount($transferInfo['amount']);
		
		if (isset($transferInfo['currency']))
			$this->setCurrency($transferInfo['currency']);
		
		
	}
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
		
		if(isset($this->mandateId) && isset($this->mandateSigDate)){
			$MndtRltdInf = $DrctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
			$MndtRltdInf->addChild('MndtId',$this->mandateId);
			$MndtRltdInf->addChild('DtOfSgntr', $this->mandateSigDate); 
		}
		
		$DrctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->debtorBIC);
		$DrctDbtTxInf->addChild('Dbtr')->addChild('Nm', htmlentities($this->debtorName));
		$DrctDbtTxInf->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->debtorAccountIBAN);
		$DrctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $this->remittanceInformation);
		
		return $xml;
	}
}
