<?php

declare(strict_types=1);

namespace Vanssata\SyliusPagOnlineImpresePlugin\Payum;

final class PagOnlineImpreseApi
{
    CONST AVALIABLE_TRTYPES = ["AUTH","PURCHASE","VERIFY"];

    /** @var string */
    protected $kSing;
    /**
     * @var bool
     */
    protected $testMode;
    /**
     * @var int
     */
    protected $timeOut;
    /**
     * @var string
     */
    protected $tId;
    /**
     * @var string
     */
    protected $serverUrl;
    /**
     * @var string
     */
    protected $trType;


    public function __construct(
        string $tId,
        string $kSing,
        bool $testMode,
        int $timeOut,
        string $serverUrl,
        string $trType
    )
    {
        $this->tId = $tId;
        $this->kSing = $kSing;
        $this->testMode = $testMode;
        $this->timeOut = $timeOut ?? 36000;
        $this->serverUrl = $serverUrl;
        $this->trType = $trType;
    }

    public function getTid(): string
    {
        return $this->hasTestMode() ? 'UNI_ECOM' : $this->tId ?? 'Broke';
    }

    public function hasTestMode(): bool
    {
        return (bool)$this->testMode;
    }

    public function getKSing(): string
    {
        return $this->hasTestMode() ? 'UNI_TESTKEY' : $this->kSing;
    }

    public function getServerURL(): string
    {
        return $this->hasTestMode() ? "https://testeps.netswgroup.it/UNI_CG_SERVICES/services"
            : $this->serverUrl
            ;
    }

    public function getTimeOut(): int
    {
        return (int)$this->timeOut ?? 3600;
    }

    public function getTrType(): string
    {
        $trType = $this->trType ?? "AUTH";
        if(!in_array($trType, PagOnlineImpreseApi::AVALIABLE_TRTYPES)){
            throw new \LogicException(
                sprintf("Configuration Error!!! TrType %s is not avaliable, plese set one of possible choices: %s",$trType,join(",", PagOnlineImpreseApi::AVALIABLE_TRTYPES))
            );
        }
        return  $trType;
    }
}
