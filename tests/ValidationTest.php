<?php

namespace Tests;

/**
 * Various schema validation tests.
 */
class ValidationTest extends \PHPUnit_Framework_TestCase
{
	protected $schema;
	protected $schema_DD;

    /**
     * @var \DOMDocument
     */
    protected $dom;
    
    /**
     * @var \DOMDocument
     */
    protected $dom_DD;
	
	protected function setUp()
	{
		$this->schema = __DIR__ . "/pain.001.001.03.xsd";
		$this->dom = new \DOMDocument('1.0', 'UTF-8');
		
		$this->schema_DD = __DIR__ . "/pain.008.001.02.xsd";
		$this->dom_DD = new \DOMDocument('1.0', 'UTF-8');
	} 

	/**
	 * Sanity check: test reference file with XSD.
	 */
	public function testSanity()
	{
		$this->dom->load(__DIR__ . '/pain.001.001.03.xml');
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
		
		$this->dom_DD->load(__DIR__ . '/pain.008.001.02.xml');
		$validated = $this->dom_DD->schemaValidate($this->schema_DD);
		$this->assertTrue($validated);
		
	}
	

	/**
	 * Test a transfer file with one debit and one transaction, direct debit
	 */
	public function testSingleDDPaymentSingleTrans()
	{
		$sepaFile = new \Digitick\Sepa\DebitMessageING();
		$sepaFile->messageIdentification = time().'unique.TODO';
		$sepaFile->initiatingPartyName = 'Belsimpel.nl';
	
		$payment = $sepaFile->addPaymentInfo(array(
				'id'                      => 'paymentinformationgroupid_unique',
				'creditorName'            => 'My Corp',
				'creditorAccountIBAN'     => 'NL82ABNA0593035550',
				'creditorAgentBIC'        => 'ABNANL2A',
				'localInstrumentCode'     => 'CORE',
		));
		$payment->addDebitTransfer(array(
				'id'                    => 'Id shown in bank statement',
				'currency'              => 'EUR',
				'amount'                => '0.02',
				'debtorName'            => 'Their Corp',
				'debtorAccountIBAN'     => 'NL07NUYL9882399339',
				'debtorBIC'             => 'RABONL2U',
				'remittanceInformation' => 'Transaction description',
				'mandateId'				=> 'myMandateId',
				
		));
	
		
		$xml =  $sepaFile->asXML();
		
		file_put_contents("test_output.xml", $xml);
		
		$this->dom_DD->loadXML($xml);
		
		$validated = $this->dom_DD->schemaValidate($this->schema_DD);
		$this->assertTrue($validated);
	}	
	
	

	/**
	 * Test a transfer file with one payment and one transaction.
	 */
	public function testSinglePaymentSingleTrans()
	{
		$sepaFile = new \Digitick\Sepa\CreditMessage();
		$sepaFile->messageIdentification = 'transferID';
		$sepaFile->initiatingPartyName = 'Me';
		
		$payment = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '0.02',
			'creditorName'          => 'Their Corp',
			'creditorAccountIBAN'   => 'FI1350001540000056',
			'creditorBIC'           => 'OKOYFIHH',
			'remittanceInformation' => 'Transaction description',
		));
		
		$this->dom->loadXML($sepaFile->asXML());
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}

	/**
	 * Test a transfer file with one payment and several transactions.
	 */
	public function testSinglePaymentMultiTrans()
	{
		$sepaFile = new \Digitick\Sepa\CreditMessage();
		$sepaFile->messageIdentification = 'transferID';
		$sepaFile->initiatingPartyName = 'Me';
		
		$payment = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '0.02',
			'creditorName'          => 'Their Corp',
			'creditorAccountIBAN'   => 'FI1350001540000056',
			'creditorBIC'           => 'OKOYFIHH',
			'remittanceInformation' => 'Transaction description',
		));
		$payment->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '5000.00',
			'creditorName'          => 'GHI Semiconductors',
			'creditorAccountIBAN'   => 'BE30001216371411',
			'creditorBIC'           => 'DDDDBEBB',
			'remittanceInformation' => 'Transaction description',
		));
		
		$this->dom->loadXML($sepaFile->asXML());
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}
	
	/**
	 * Test a transfer file with several payments, one transaction each.
	 */
	public function testMultiPaymentSingleTrans()
	{
		$sepaFile = new \Digitick\Sepa\CreditMessage();
		$sepaFile->messageIdentification = 'transferID';
		$sepaFile->initiatingPartyName = 'Me';
		
		$payment1 = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment1->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '0.02',
			'creditorName'          => 'Their Corp',
			'creditorAccountIBAN'   => 'FI1350001540000056',
			'creditorBIC'           => 'OKOYFIHH',
			'remittanceInformation' => 'Transaction description',
		));
		$payment2 = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment2->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '5000.00',
			'creditorName'          => 'GHI Semiconductors',
			'creditorAccountIBAN'   => 'BE30001216371411',
			'creditorBIC'           => 'DDDDBEBB',
			'remittanceInformation' => 'Transaction description',
		));
		
		$this->dom->loadXML($sepaFile->asXML());
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}
	
	/**
	 * Test a transfer file with several payments, several transactions each.
	 */
	public function testMultiPaymentMultiTrans()
	{
		$sepaFile = new \Digitick\Sepa\CreditMessage();
		$sepaFile->messageIdentification = 'transferID';
		$sepaFile->initiatingPartyName = 'Me';
		
		$payment1 = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment1->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '0.02',
			'creditorName'          => 'Their Corp',
			'creditorAccountIBAN'   => 'FI1350001540000056',
			'creditorBIC'           => 'OKOYFIHH',
			'remittanceInformation' => 'Transaction description',
		));
		$payment1->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '5000.00',
			'creditorName'          => 'GHI Semiconductors',
			'creditorAccountIBAN'   => 'BE30001216371411',
			'creditorBIC'           => 'DDDDBEBB',
			'remittanceInformation' => 'Transaction description',
		));
		
		$payment2 = $sepaFile->addPaymentInfo(array(
			'id'                    => 'Payment Info ID',
			'debtorName'            => 'My Corp',
			'debtorAccountIBAN'     => 'FR1420041010050500013M02606',
			'debtorAgentBIC'        => 'PSSTFRPPMON'
		));
		$payment2->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '0.02',
			'creditorName'          => 'Their Corp',
			'creditorAccountIBAN'   => 'FI1350001540000056',
			'creditorBIC'           => 'OKOYFIHH',
			'remittanceInformation' => 'Transaction description',
		));
		$payment2->addCreditTransfer(array(
			'id'                    => 'Id shown in bank statement',
			'currency'              => 'EUR',
			'amount'                => '5000.00',
			'creditorName'          => 'GHI Semiconductors',
			'creditorAccountIBAN'   => 'BE30001216371411',
			'creditorBIC'           => 'DDDDBEBB',
			'remittanceInformation' => 'Transaction description',
		));
		
		$this->dom->loadXML($sepaFile->asXML());
		
		$validated = $this->dom->schemaValidate($this->schema);
		$this->assertTrue($validated);
	}
}
