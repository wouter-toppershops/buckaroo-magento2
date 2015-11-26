<?php


namespace TIG\Buckaroo\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Model\Method\Logger;

class Soap extends \Magento\Payment\Gateway\Http\Client\Soap
{
    /**
     * @param Logger $logger
     * @param \TIG\Buckaroo\Soap\ClientFactory $clientFactory
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        Logger $logger,
        \TIG\Buckaroo\Soap\ClientFactory $clientFactory,
        ConverterInterface $converter = null
    ) {
        parent::__construct($logger, $clientFactory, $converter);
    }
}