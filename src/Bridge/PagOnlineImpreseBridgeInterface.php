<?php

namespace Vanssata\SyliusPagOnlineImpresePlugin\Bridge;

use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Vanssata\SyliusPagOnlineImpresePlugin\Payum\PagOnlineImpreseApi;
use VanssataPagOnlineSDK\Init\IgfsCgInit;
use VanssataPagOnlineSDK\Init\IgfsCgVerify;

interface PagOnlineImpreseBridgeInterface
{

    public const STATUS_CODES = [
        'positive' => ['IGFS_000'],
        'pending' =>  ['IGFS_814'],
        'canceled' => ['IGFS_20090'],
        'negative' => ["IGFS_001", "IGFS_00155", "IGFS_00156", "IGFS_00157", "IGFS_00158", "IGFS_00159", "IGFS_002", "IGFS_00260", "IGFS_00261", "IGFS_003", "IGFS_004", "IGFS_00452", "IGFS_00456", "IGFS_005", "IGFS_006", "IGFS_007", "IGFS_00701", "IGFS_00704", "IGFS_00705", "IGFS_008", "IGFS_009", "IGFS_00950", "IGFS_00951", "IGFS_00952", "IGFS_010", "IGFS_01000", "IGFS_011", "IGFS_014", "IGFS_015", "IGFS_016", "IGFS_018", "IGFS_020", "IGFS_021", "IGFS_029", "IGFS_030", "IGFS_032", "IGFS_033", "IGFS_083", "IGFS_085", "IGFS_086", "IGFS_087", "IGFS_088", "IGFS_091", "IGFS_092", "IGFS_093", "IGFS_095", "IGFS_096", "IGFS_097", "IGFS_098", "IGFS_10000", "IGFS_101", "IGFS_102", "IGFS_104", "IGFS_107", "IGFS_108", "IGFS_112", "IGFS_115", "IGFS_117", "IGFS_118", "IGFS_119", "IGFS_121", "IGFS_122", "IGFS_123", "IGFS_125", "IGFS_129", "IGFS_160", "IGFS_164", "IGFS_180", "IGFS_181", "IGFS_1921", "IGFS_1922", "IGFS_1923", "IGFS_20000", "IGFS_20001", "IGFS_20007", "IGFS_20010", "IGFS_20011", "IGFS_20012", "IGFS_20013", "IGFS_20014", "IGFS_20018", "IGFS_20019", "IGFS_20020", "IGFS_20021", "IGFS_20022", "IGFS_20023", "IGFS_20024", "IGFS_20025", "IGFS_20026", "IGFS_20027", "IGFS_20028", "IGFS_20029", "IGFS_20030", "IGFS_20031", "IGFS_20032", "IGFS_20033", "IGFS_20034", "IGFS_20035", "IGFS_20036", "IGFS_20037", "IGFS_20038", "IGFS_20044", "IGFS_20090", "IGFS_20100", "IGFS_400", "IGFS_800", "IGFS_801", "IGFS_802", "IGFS_803", "IGFS_804", "IGFS_805", "IGFS_807", "IGFS_808", "IGFS_809", "IGFS_810", "IGFS_811", "IGFS_812", "IGFS_813", "IGFS_814", "IGFS_815", "IGFS_90000", "IGFS_90005", "IGFS_902", "IGFS_903", "IGFS_907", "IGFS_908", "IGFS_909", "IGFS_910", "IGFS_911", "IGFS_912", "IGFS_913", "IGFS_990",],
    ];

    public function execute(TokenInterface $token, PagOnlineImpreseApi $pagOnlineImpreseApi, PaymentInterface $payment): string;

    public function getIgfsCgInit(): IgfsCgInit;

    public function setAuthSettings(PagOnlineImpreseApi $pagOnlineImpreseApi): PagOnlineImpreseBridge;

    public function setCurrentTransactionSettings(PaymentInterface $payment): PagOnlineImpreseBridge;

    public function setUrls(TokenInterface $token): PagOnlineImpreseBridge;

    public function verifyResponse(PaymentInterface $payment,PagOnlineImpreseApi $pagOnlineImpreseApi): PagOnlineImpreseBridgeInterface;

    public function getIgfsCgVerify(): IgfsCgVerify;

    public function getVerifyState(): string;
}
