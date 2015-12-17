<?php

namespace TIG\Buckaroo\Model;

/**
 * Class RefundFieldsFactory
 *
 * @package TIG\Bukcaroo\Model
 */
class RefundFieldsFactory
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $refundFields;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $refundFields
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $refundFields = []
    ) {
        $this->objectManager = $objectManager;
        $this->refundFields = $refundFields;
    }

    /**
     * Retrieve proper transaction builder for the specified transaction type.
     *
     * @param string $paymentMethod
     *
     * @throws \LogicException|\TIG\Buckaroo\Exception
     */
    public function get($paymentMethod)
    {
        if (!isset($this->refundFields)) {
            throw new \LogicException('No refund fields are set.');
        }

        return $this->refundFields[$paymentMethod];
    }

}