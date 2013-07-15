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
	 * Add a credit transfer transaction.
	 * @param array $transferInfo
	 */
	public function addDebitTransfer(array $transferInfo)
	{
		$transfer = new DebitTransferING();
		$values = array(
				'id', 'debtorBIC', 'debtorName',
				'debtorAccountIBAN', 'remittanceInformation','mandateId'
		);
		foreach ($values as $name) {
			if (isset($transferInfo[$name]))
				$transfer->$name = $transferInfo[$name];
		}
		if (isset($transferInfo['amount']))
			$transfer->setAmount($transferInfo['amount']);
	
		if (isset($transferInfo['currency']))
			$transfer->setCurrency($transferInfo['currency']);
	
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
    public function generateXml(\SimpleXMLElement $xml)
    {
        $datetime = new \DateTime();
        $requestedCollectionDate = $datetime->format('Y-m-d');

        // -- Payment Information --\\

        $PmtInf = $xml->CstmrDrctDbtInitn->addChild('PmtInf');
        $PmtInf->addChild('PmtInfId', $this->id); 				//unique, max35 (from addpaymentinfo array)
        if (isset($this->categoryPurposeCode)){
        	throw new Exception('Categorypurpose is not part of the IG SDD netherlands addendum');
        }
            

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
