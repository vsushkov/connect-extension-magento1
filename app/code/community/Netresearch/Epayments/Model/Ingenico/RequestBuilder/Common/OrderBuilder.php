<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_OrderBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_OrderBuilder
{
    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $ePaymentsHelper;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_CustomerBuilder
     */
    protected $customerBuilder;


    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_ShoppingCartBuilder
     */
    protected $shoppingCartBuilder;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_MerchantReference
     */
    protected $merchantReference;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_OrderBuilder constructor.
     */
    public function __construct()
    {
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->ePaymentsHelper = Mage::helper('netresearch_epayments');
        $this->customerBuilder = Mage::getModel('netresearch_epayments/ingenico_requestBuilder_common_customerBuilder');
        $this->shoppingCartBuilder = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_common_shoppingCartBuilder'
        );
        $this->merchantReference = Mage::getSingleton('netresearch_epayments/ingenico_merchantReference');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Order
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoOrder = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Order();

        $ingenicoOrder->amountOfMoney = $this->getAmountOfMoney($order);
        $ingenicoOrder->customer = $this->customerBuilder->create($order);

        $ingenicoOrder->shoppingCart = $this->shoppingCartBuilder->create($order);
        $ingenicoOrder->references = $this->getReferences($order);
        $ingenicoOrder->additionalInput = $this->getAdditionalInput($order);

        return $ingenicoOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney
     */
    protected function getAmountOfMoney(Mage_Sales_Model_Order $order)
    {
        $amountOfMoney = new \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney();
        $amountOfMoney->amount = $this->_formatAmount($order->getBaseGrandTotal());
        $amountOfMoney->currencyCode = $order->getBaseCurrencyCode();

        return $amountOfMoney;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferences
     */
    protected function getReferences(Mage_Sales_Model_Order $order)
    {
        $references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferences();
        $references->merchantReference = $this->merchantReference->generateMerchantReference($order);
        $references->descriptor = $this->ePaymentsConfig->getDescriptor();

        return $references;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AdditionalOrderInput
     */
    protected function getAdditionalInput(Mage_Sales_Model_Order $order)
    {
        $additionalInput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AdditionalOrderInput();

        /** @var Mage_Core_Model_Date $date */
        $date = Mage::getSingleton('core/date');
        $additionalInput->orderDate = $date->date(
            'YmdHis',
            strtotime($order->getCreatedAt())
        );

        $typeInformation = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderTypeInformation();
        $typeInformation->purchaseType = 'good';
        $typeInformation->usageType = 'commercial';
        $additionalInput->typeInformation = $typeInformation;

        return $additionalInput;
    }

    /**
     * @param float $amount
     * @return mixed
     */
    protected function _formatAmount($amount)
    {
        return $this->ePaymentsHelper->formatIngenicoAmount($amount);
    }
}
