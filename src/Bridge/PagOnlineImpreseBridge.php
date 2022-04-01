<?php

declare(strict_types=1);


namespace Vanssata\SyliusPagOnlineImpresePlugin\Bridge;


use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Payum\PagOnlineImpreseApi;
use VanssataPagOnlineSDK\Init\IgfsCgInit;
use VanssataPagOnlineSDK\Init\IgfsCgVerify;

class PagOnlineImpreseBridge implements PagOnlineImpreseBridgeInterface
{
    /**
     * @var IgfsCgInit
     */
    private $IgfsCgInit;

    /**
     * @var IgfsCgVerify
     */
    private $IgfsCgVerify;

    public function __construct()
    {
        $this->IgfsCgInit = new IgfsCgInit();
    }

    final public function execute(TokenInterface $token, PagOnlineImpreseApi $pagOnlineImpreseApi, PaymentInterface $payment): string
    {
        $this
            ->setAuthSettings($pagOnlineImpreseApi)
            ->setCurrentTransactionSettings($payment)
            ->setUrls($token)
        ;

        if (!$this->getIgfsCgInit()->execute()) {
            throw new \LogicException("Cannot execute call. Error: {$this->IgfsCgInit->errorDesc} with code {$this->IgfsCgInit->error}");
        }

        return $this->getIgfsCgInit()->redirectURL;
    }

    final public function getIgfsCgInit(): IgfsCgInit
    {
        return $this->IgfsCgInit;
    }
    final public function getIgfsCgVerify(): IgfsCgVerify
    {
        if(!$this->IgfsCgVerify instanceof IgfsCgVerify){
            $this->IgfsCgVerify = new IgfsCgVerify();
        }

        return $this->IgfsCgVerify;
    }



    final public function setUrls(TokenInterface $token): PagOnlineImpreseBridge
    {
        $this->getIgfsCgInit()->notifyURL = $token->getTargetUrl()."?".http_build_query(['status' => PaymentInterface::STATE_PROCESSING]);
        $this->getIgfsCgInit()->errorURL  = $token->getTargetUrl().'?'.http_build_query(['status' => PaymentInterface::STATE_CANCELLED]);

        return $this;
    }

    final public function setAuthSettings(PagOnlineImpreseApi $pagOnlineImpreseApi): PagOnlineImpreseBridge
    {
        //Auth settings
        $this->getIgfsCgInit()->tid = $pagOnlineImpreseApi->getTid() ?? "Config is broke";
        $this->getIgfsCgInit()->kSig = $pagOnlineImpreseApi->getKSing() ?? "Config is broke";
        $this->getIgfsCgInit()->serverURL = $pagOnlineImpreseApi->getServerURL() ?? "Config is broke";
        $this->getIgfsCgInit()->timeout = $pagOnlineImpreseApi->getTimeOut() ?? "Config is broke";
        $this->getIgfsCgInit()->trType = $pagOnlineImpreseApi->getTrType() ?? "Config is broke";
        return $this;
    }

    final public function setCurrentTransactionSettings(PaymentInterface $payment): PagOnlineImpreseBridge
    {
        $this->getIgfsCgInit()->setHttpVerifySsl(false);
        //Current transaction settings
        $this->getIgfsCgInit()->shopID = time();
        $this->getIgfsCgInit()->shopUserRef = $payment->getOrder()->getUser()->getId();
        $this->getIgfsCgInit()->currencyCode = 'EUR';
        $this->getIgfsCgInit()->amount = $payment->getAmount(); //Amount without comma, 1,00EUR will be 100
        $this->getIgfsCgInit()->shopUserName = $payment->getOrder()->getCustomer()->getFirstName();
        $this->getIgfsCgInit()->shopUserAccount = $payment->getOrder()->getCustomer()->getEmailCanonical();
        $this->getIgfsCgInit()->paymentID = $payment->getOrder()->getNumber();

        $this->getIgfsCgInit()->addInfo1 = sprintf("Paid for transaction whit id %s", $payment->getId());
        $this->getIgfsCgInit()->addInfo2 = sprintf("Paid for order whit number %s", $payment->getOrder()->getNumber());
        return $this;
    }

    final public function verifyResponse(PaymentInterface $payment,PagOnlineImpreseApi $pagOnlineImpreseApi): PagOnlineImpreseBridgeInterface
    {
        $this->getIgfsCgVerify();
        $this->getIgfsCgVerify()->paymentID = $payment->getDetails()['paymentID'];
        $this->getIgfsCgVerify()->timeout = 36000;
        $this->getIgfsCgVerify()->kSig = $pagOnlineImpreseApi->getKSing();
        $this->getIgfsCgVerify()->tid = $pagOnlineImpreseApi->getTid();
        $this->getIgfsCgVerify()->serverURL = $pagOnlineImpreseApi->getServerURL();
        $this->getIgfsCgVerify()->shopID = $payment->getDetails()['shopID'];
        $this->getIgfsCgVerify()->execute();
        return  $this;
    }

    public function getVerifyState(): string
    {
        if($this->getIgfsCgVerify()->rc !== null) {
            $code = $this->getIgfsCgVerify()->rc;
            if(in_array($code,self::STATUS_CODES['positive'])){
                return  PaymentInterface::STATE_COMPLETED;
            }

            if(in_array($code,self::STATUS_CODES['negative'])){
                return  PaymentInterface::STATE_FAILED;
            }

            if(in_array($code,self::STATUS_CODES['canceled'])){
                return  PaymentInterface::STATE_CANCELLED;
            }
        }
        return PaymentInterface::STATE_UNKNOWN;
    }
}
