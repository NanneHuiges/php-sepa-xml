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
class DebitPaymentInfo extends PaymentInfo
{
    /**
     * @var string Payment method.
     */
    protected $paymentMethod = 'DD';

    /**
     * @var \Digitick\Sepa\debitTransfer[]
     */
    protected $debitTransfers = array();
    
    /**
     * @var string creditor's name.
     */
    public $creditorName;
    /**
     * @var string creditor's account IBAN.
     */
    public $creditorAccountIBAN;
    /**
     * @var string creditor's account bank BIC code.
     */
    public $creditorAgentBIC;
    
    /**
     * @var string creditor's account ISO currency code.
     */
    protected $creditorAccountCurrency = 'EUR';
    

    /**
     * Set the creditor's account currency code.
     * @param string $code currency ISO code
     * @throws \Digitick\Sepa\Exception
     */
    public function setCreditorAccountCurrency($code)
    {
    	$this->creditorAccountCurrency = $this->validateCurrency($code);
    }
    
    /**
     * Set the payment method.
     * @param string $method
     * @throws \Digitick\Sepa\Exception
     */
    public function setPaymentMethod($method)
    {
    	$method = strtoupper($method);
    	if (!in_array($method, array('DD'))) {
    		throw new Exception("Invalid Payment Method: $method");
    	}
    	$this->paymentMethod = $method;
    }
    
    /**
     * Set the information for this "Payment Information" block.
     * @param array $paymentInfo
     */
    public function setInfo(array $paymentInfo)
    {
        $values = array(
            'id', 'categoryPurposeCode', 'creditorName', 'creditorAccountIBAN',
            'creditorAgentBIC', 'creditorAccountCurrency'
        );
        foreach ($values as $name) {
            if (isset($paymentInfo[$name]))
                $this->$name = $paymentInfo[$name];
        }
        if (isset($paymentInfo['localInstrumentCode']))
            $this->setLocalInstrumentCode($paymentInfo['localInstrumentCode']);

        if (isset($paymentInfo['paymentMethod']))
            $this->setPaymentMethod($paymentInfo['paymentMethod']);

        if (isset($paymentInfo['creditorAccountCurrency']))
            $this->setCreditorAccountCurrency($paymentInfo['creditorAccountCurrency']);
    }


    /**
     * Add a credit transfer transaction.
     * @param array $transferInfo
     */
    public function addDebitTransfer(array $transferInfo)
    {
        $transfer = new DebitTransfer();
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
     * DO NOT CALL THIS FUNCTION DIRECTLY!
     *
     * Generate the XML structure for this "Payment Info" block.
     *
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     */
    public function generateXml(\SimpleXMLElement $xml)
    {
        $datetime = new \DateTime();
        $requestedCollectionDate = $datetime->format('Y-m-d');

        // -- Payment Information --\\

        $PmtInf = $xml->CstmrDrctDbtInitn->addChild('PmtInf');
        $PmtInf->addChild('PmtInfId', $this->id);
        if (isset($this->categoryPurposeCode))
            $PmtInf->addChild('CtgyPurp')->addChild('Cd', $this->categoryPurposeCode);

        $PmtInf->addChild('PmtMtd', $this->paymentMethod);
        $PmtInf->addChild('NbOfTxs', $this->numberOfTransactions);
        $PmtInf->addChild('CtrlSum', $this->intToCurrency($this->controlSumCents));
        $PmtInf->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', 'SEPA');
        if ($this->localInstrumentCode)
            $PmtInf->PmtTpInf->addChild('LclInstrm')->addChild('Cd', $this->localInstrumentCode);

        $PmtInf->addChild('ReqdColltnDt', $requestedCollectionDate);
        $PmtInf->addChild('Cdtr')->addChild('Nm', htmlentities($this->creditorName));

        $CdtrAcct = $PmtInf->addChild('CdtrAcct');
        $CdtrAcct->addChild('Id')->addChild('IBAN', $this->creditorAccountIBAN);
        $CdtrAcct->addChild('Ccy', $this->creditorAccountCurrency);

        $PmtInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->creditorAgentBIC);
        $PmtInf->addChild('ChrgBr', 'SLEV');

        // -- Credit Transfer Transaction Information --\\

        foreach ($this->debitTransfers as $transfer) {
            $PmtInf = $transfer->generateXml($PmtInf);
        }
        return $xml;
    }
}
