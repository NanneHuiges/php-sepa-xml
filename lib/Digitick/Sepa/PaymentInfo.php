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
Abstract class PaymentInfo extends FileBlock
{
    /**
     * @var string Unambiguously identify the payment.
     */
    public $id;
    /**
     * @var string Purpose of the transaction(s).
     */
    public $categoryPurposeCode;

    /**
     * @var string Local service instrument code.
     */
    protected $localInstrumentCode;
    /**
     * @var integer
     */
    protected $controlSumCents = 0;
    /**
     * @var integer Number of payment transactions.
     */
    protected $numberOfTransactions = 0;

    /**
     * @var \Digitick\Sepa\Message
     */
    protected $transferFile;

    /**
     * Constructor.
     * @param \Digitick\Sepa\Message $transferFile
     */
    public function __construct(Message $transferFile)
    {
        $this->setTransferFile($transferFile);
    }


    /**
     * Set the local service instrument code.
     * @param string $code
     * @throws \Digitick\Sepa\Exception
     */
    public function setLocalInstrumentCode($code)
    {
        $code = strtoupper($code);
        if (!in_array($code, array('CORE', 'B2B','COR1'))) {
            throw new Exception("Invalid Local Instrument Code: $code");
        }
        $this->localInstrumentCode = $code;
    }


    /**
     * @return integer
     */
    public function getNumberOfTransactions()
    {
        return $this->numberOfTransactions;
    }

    /**
     * @return integer
     */
    public function getControlSumCents()
    {
        return $this->controlSumCents;
    }

    /**
     * Set the transfer file.
     * @param \Digitick\Sepa\Message $transferFile
     */
    public function setTransferFile(Message $transferFile)
    {
        $this->transferFile = $transferFile;
    }


}
