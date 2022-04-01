<?php

declare(strict_types=1);

namespace Vanssata\SyliusPagOnlineImpresePlugin\Payum\Action;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Bridge\PagOnlineImpreseBridgeInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Payum\PagOnlineImpreseApi;
use Payum\Core\GatewayAwareInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;
    use ApiAwareTrait;

    /**
     * @var PagOnlineImpreseBridgeInterface
     */
    protected $pagOnlineImpreseBridge;
    /**
     * @var EntityManagerInterface
     */
    protected $em;



    public function __construct(PagOnlineImpreseBridgeInterface $pagOnlineImpreseBridge, EntityManagerInterface $em, ?string $apiClass = PagOnlineImpreseApi::class)
    {
        $this->pagOnlineImpreseBridge = $pagOnlineImpreseBridge;
        $this->em = $em;
        $this->apiClass = $apiClass;
    }


    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if(!isset($httpRequest->query['status']))
        {
            /** @var TokenInterface $token */
            $token = $request->getToken();
            if (!$this->tokenFactory instanceof TokenFactoryInterface) {
                throw new \LogicException("This gateway cannot generate token.");
            }

            $redirectUrl = $this->pagOnlineImpreseBridge->execute($token, $this->api, $payment);
            $payment->setDetails([
                'status' => PaymentInterface::STATE_PROCESSING,
                'shopID' => $this->pagOnlineImpreseBridge->getIgfsCgInit()->shopID,
                'paymentID' => $this->pagOnlineImpreseBridge->getIgfsCgInit()->paymentID,
            ]);
            $this->em->persist($payment);
            $this->em->flush();

            throw new HttpRedirect($redirectUrl);
        }

    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof SyliusPaymentInterface;
    }


}
