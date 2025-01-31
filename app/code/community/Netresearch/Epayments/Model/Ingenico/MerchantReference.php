<?php
/**
 * Class Netresearch_Epayments_Model_Ingenico_MerchantReference
 */
class Netresearch_Epayments_Model_Ingenico_MerchantReference
{
    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $epaymentsConfig;

    /**
     * MerchantReference constructor.
     */
    public function __construct()
    {
        $this->epaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function generateMerchantReference(Mage_Sales_Model_Order $order)
    {
        return $this->epaymentsConfig->getReferencePrefix() . $order->getIncrementId();
    }

    /**
     * @param $merchantReference
     * @throws \InvalidArgumentException
     * @return string
     */
    public function extractOrderReference($merchantReference)
    {
        if ($this->epaymentsConfig->getReferencePrefix() !== ''
            && strpos($merchantReference, $this->epaymentsConfig->getReferencePrefix()) !== 0) {
            throw new \InvalidArgumentException('This reference is most likely not originating from this system.');
        }

        return str_replace($this->epaymentsConfig->getReferencePrefix(), '', $merchantReference);
    }
}
