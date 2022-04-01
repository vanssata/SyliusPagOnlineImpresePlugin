<?php

declare(strict_types=1);

namespace Vanssata\SyliusPagOnlineImpresePlugin\Payum\Action;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Bridge\PagOnlineImpreseBridge;
use Vanssata\SyliusPagOnlineImpresePlugin\Bridge\PagOnlineImpreseBridgeInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Payum\PagOnlineImpreseApi;


/**
 * @property PagOnlineImpreseApi $api
 */
final class StatusAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var PagOnlineImpreseBridgeInterface
     */
    protected $pagOnlineImpreseBridge;

    /**
     * @param  string  $apiClass
     */
    public function __construct(string $apiClass = PagOnlineImpreseApi::class) {
        $this->apiClass = $apiClass;
        $this->pagOnlineImpreseBridge = new PagOnlineImpreseBridge();
    }

    /**
     * @param  GetStatus  $request
     * @return void
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /**
         * @var $payment Payment
         */
        $payment = $request->getFirstModel();

        $paymentDetails = $payment->getDetails();
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (false === isset($paymentDetails['status'])) {
            $request->markNew();
            return;
        }

        $verify = $this->pagOnlineImpreseBridge->verifyResponse($payment, $this->api);
        $paymentDetails = array_merge($paymentDetails, $verify->getIgfsCgVerify()->toArray());

        if(isset($paymentDetails['rc'])) {

          if(in_array($paymentDetails['rc'], PagOnlineImpreseBridgeInterface::STATUS_CODES['positive']))
          {
            $request->markCaptured();
            return;
          }

          if(in_array($paymentDetails['rc'], PagOnlineImpreseBridgeInterface::STATUS_CODES['negative']))
          {
            $request->markFailed();
              return;

          }

          if(in_array($paymentDetails['rc'], PagOnlineImpreseBridgeInterface::STATUS_CODES['canceled']))
          {
            $request->markCanceled();
            return;
          }
        }

        $request->markUnknown();

    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface && $request->getFirstModel() instanceof SyliusPaymentInterface;
    }
}
