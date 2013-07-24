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
class DebitMessageING extends DebitMessage{
	
	//possible override needed for special ING xsd?
	
	/**
	 * (non-PHPdoc)
	 * ING needs a specia PaymentInfo type
	 * @see \Digitick\Sepa\DebitMessage::addPaymentInfo()
	 */
	public function addPaymentInfo(array $paymentInfo)
	{
		$payment = new DebitPaymentInfoING($this);
		$payment->setInfo($paymentInfo);
	
		$this->payments[] = $payment;
	
		return $payment;
	}	
	
	/**
	 * Check variables that have not been checked in any function before
	 * 
	 */
	public function clean(){
		//The id must be unique to be usefull, so abort if too long
		if(strlen($this->messageIdentification) > 35){
			throw new Exception('messageIdentification should be less then 35 characters');
		}
		//partyname is less important: remove too long strings
		$this->initiatingPartyName = substr($this->initiatingPartyName, 0,70);  
	}
	
	/**
	 * Generate the XML structure.
	 */
	protected function generateXml()
	{
		$this->updatePaymentCounters();
		$this->clean();
		
		$datetime = new \DateTime();
		$creationDateTime = $datetime->format('Y-m-d\TH:i:s');
	
		// -- Group Header -- \\
	
		$GrpHdr = $this->xml->CstmrDrctDbtInitn->addChild('GrpHdr');
		$GrpHdr->addChild('MsgId', $this->messageIdentification); //unique, max35text
		$GrpHdr->addChild('CreDtTm', $creationDateTime);		  //isoDateTime
		if ($this->isTest){
			throw new Exception('test authorisation not supported by ING. (See addendum)');
			//$GrpHdr->addChild('Authstn')->addChild('Prtry', 'TEST');
		}
			
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

