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
 * SEPA file "Payment Information" block.
 */
class DebitPaymentInfoING extends DebitPaymentInfo
{
	/**
	 * Check and clean the array of paymentinfo values.
	 * For any non-recoverable errors an exception will be thrown. 
	 * Others will be fixed (e.g. truncated).
	 * 
	 * @param array $paymentInfo as used in setInfo
	 * @throws Exception for any non-recoverable violations like wrong ID's
	 */
	private function cleanInfoArray($paymentInfo){
		if(!isset($paymentInfo['id']) || strlen($paymentInfo['id']) > 35){
			throw new Exception('Payment Information Identification should be set and 35 characters or less');
		}		
		if(isset($paymentInfo['creditorName'])){
			$paymentInfo['creditorName'] = substr($paymentInfo['creditorName'], 0,70);
		}
		
		if(isset($paymentInfo['creditorAccountIBAN'])){
			$this->validateIBAN($paymentInfo['creditorAccountIBAN']);
		}
		
		if(isset($paymentInfo['creditorAgentBIC']) ){
			$this->validateBIC($paymentInfo['creditorAgentBIC']);
		}
		
		if(isset($paymentInfo['creditorAccountCurrency']) && $paymentInfo['creditorAccountCurrency'] != 'EUR'){
			throw new Exception('ING only support EUR as creditorAccountCurrency');
		}
		
		if (isset($paymentInfo['localInstrumentCode']) && $paymentInfo['localInstrumentCode'] == 'COR1'){
			throw new Exception('ING does not (yet) support COR1');
		}
				
	}
	
	/**
	 * Set the information for this "Payment Information" block.
	 * Cleans information
	 * @param array $paymentInfo
	 */
	public function setInfo(array $paymentInfo){
		$this->cleanInfoArray(&$paymentInfo);
		
		$this->id = $paymentInfo['id'];
		if(isset($paymentInfo['creditorName'])){
			$this->creditorName = substr($paymentInfo['creditorName'], 0,70);
		}	
		if(isset($paymentInfo['creditorAccountIBAN'])){
			$this->creditorAccountIBAN  = $paymentInfo['creditorAccountIBAN']; 
		}		
		if(isset($paymentInfo['creditorAgentBIC']) ){
			$this->creditorAgentBIC = $paymentInfo['creditorAgentBIC'];
		}
		
		if (isset($paymentInfo['localInstrumentCode'])){
				$this->setLocalInstrumentCode($paymentInfo['localInstrumentCode']);
		}
		if (isset($paymentInfo['paymentMethod'])){
			$this->setPaymentMethod($paymentInfo['paymentMethod']);
		}
	
	}
	
	
	/**
	 * Add a credit transfer transaction.
	 * @param array $transferInfo
	 */
	public function addDebitTransfer(array $transferInfo){
		$transfer = new DebitTransferING();
		$transfer->setInfo($transferInfo);
		$transfer->endToEndId = $this->transferFile->messageIdentification . '/' . $this->getNumberOfTransactions();
		
		$this->debitTransfers[] = $transfer;
		$this->numberOfTransactions++;
		$this->controlSumCents += $transfer->getAmountCents();
	}
	
    /**
     * (non-PHPdoc)
     * AS ING does not accept currency, that field needs to be removed.
     * @see \Digitick\Sepa\DebitPaymentInfo::generateXml()
     */
    public function generateXml(\SimpleXMLElement $xml){
        $datetime = new \DateTime();
        $requestedCollectionDate = $datetime->format('Y-m-d');

        // -- Payment Information --\\

        $PmtInf = $xml->CstmrDrctDbtInitn->addChild('PmtInf');
        $PmtInf->addChild('PmtInfId', $this->id); 	          

        $PmtInf->addChild('PmtMtd', $this->paymentMethod);
        $PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
        $PmtInf->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));
        
        $PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', 'SEPA');
        if ($this->localInstrumentCode){
            $PmtInf->PmtTpInf->addChild('LclInstrm')->addChild('Cd', $this->localInstrumentCode);
        }
        
        $PmtInf->addChild('ReqdColltnDt', $requestedCollectionDate);
        
        $PmtInf->addChild('Cdtr')->addChild('Nm', htmlentities($this->creditorName));

        
        $CdtrAcct = $PmtInf->addChild('CdtrAcct');
        $CdtrAcct->addChild('Id')->addChild('IBAN', $this->creditorAccountIBAN);

        $PmtInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->creditorAgentBIC);
        
        $PmtInf->addChild('ChrgBr', 'SLEV');
        
        $other = $PmtInf->addChild('CdtrSchmeId')->addChild('Id')->addChild('PrvtId')->addChild('Othr');
        $other->addChild('Id','NL'); //according to Netherlands Pain008 Direct Debit MINGZ Validation: ISO 3166 code needed
        $other->addChild('SchmeNm')->addChild('Prtry','SEPA');
        
        // -- Credit Transfer Transaction Information --\\

        foreach ($this->debitTransfers as $transfer) {
            $PmtInf = $transfer->generateXml($PmtInf);
        }
        return $xml;
    }
}
