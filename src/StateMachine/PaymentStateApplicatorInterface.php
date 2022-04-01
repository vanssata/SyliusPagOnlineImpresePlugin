<?php

namespace Vanssata\SyliusPagOnlineImpresePlugin\StateMachine;

use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentStateApplicatorInterface
{
    public function __construct(FactoryInterface $factory);
    public function applyOrderCompleted(PaymentInterface $payment): void;
    public function applyPaymentFailed(PaymentInterface $payment): void;
    public function applyPaymentCanceled(PaymentInterface $payment): void;
}
