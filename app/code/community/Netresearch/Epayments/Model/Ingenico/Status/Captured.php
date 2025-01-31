<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;

class Netresearch_Epayments_Model_Ingenico_Status_Captured implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Captured constructor.
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
        $payment = $order->getPayment();
        $currentStatus = '';
        $captureTransaction = $payment->getTransaction($ingenicoStatus->id);
        if ($captureTransaction) {
            $currentCaptureStatus = new \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse();
            $currentCaptureStatus = $currentCaptureStatus->fromJson(
                $captureTransaction->getAdditionalInformation(
                    Netresearch_Epayments_Model_Method_HostedCheckout::TRANSACTION_INFO_KEY
                )
            );

            $currentStatus = $currentCaptureStatus->status;
        }

        if ($currentStatus !== StatusInterface::CAPTURE_REQUESTED) {
            /** @var Netresearch_Epayments_Model_Ingenico_Status_CaptureRequested $captureRequestedStatus */
            $captureRequestedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_captureRequested'
            );
            $captureRequestedStatus->resolveStatus($order, $ingenicoStatus);
        }

        $payment->setNotificationResult(true);
        $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_ACCEPT, false);
        $payment->setIsTransactionPending(false);

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }
}
