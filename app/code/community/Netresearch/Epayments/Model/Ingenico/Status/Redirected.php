<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Redirected
 */
class Netresearch_Epayments_Model_Ingenico_Status_Redirected implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Redirected constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $this->orderEMailManager->process($order, $ingenicoStatus->status);

        /**
         * For inline payments with redirect actions a transaction is created. If the transaction is not kept open,
         * a later online capture is impossible
         */
        $order->getPayment()->setIsTransactionClosed(false);
    }
}
