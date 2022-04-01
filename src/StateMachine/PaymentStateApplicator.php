<?php

declare(strict_types=1);


namespace Vanssata\SyliusPagOnlineImpresePlugin\StateMachine;

use SM\Factory\Factory;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PaymentStateApplicator implements PaymentStateApplicatorInterface
{

    /**
     * @var FactoryInterface
     */
    protected $smFactory;

    public function __construct(FactoryInterface $smFactory)
    {
        $this->smFactory = $smFactory;
    }

    public function applyOrderCompleted(PaymentInterface $payment): void
    {
        $this->applyPaymentState($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);
        $this->applyPaymentState($payment->getOrder(), OrderPaymentTransitions::GRAPH, OrderPaymentTransitions::TRANSITION_PAY);
    }

    private function applyPaymentState(object $entity, string $graph, string $transitions): bool
    {
        $paymentStateMachine = $this->smFactory->get($entity, $graph);
        if (!$paymentStateMachine->can($transitions)) {
            return false;
        }
        $paymentStateMachine->apply($transitions);
        return true;
    }


    public function applyPaymentFailed(PaymentInterface $payment): void
    {
        $this->applyPaymentState($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_FAIL);
        $this->applyPaymentState($payment->getOrder(), OrderPaymentTransitions::GRAPH, OrderPaymentTransitions::TRANSITION_CANCEL);
    }

    public function applyPaymentCanceled(PaymentInterface $payment): void
    {
        $this->applyPaymentState($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);
        $this->applyPaymentState($payment->getOrder(), OrderPaymentTransitions::GRAPH, OrderPaymentTransitions::TRANSITION_CANCEL);
    }

}
