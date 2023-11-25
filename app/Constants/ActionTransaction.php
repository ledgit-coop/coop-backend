<?php

namespace App\Constants;

class ActionTransaction
{
    const DepositShareCapital = "deposit-share-capital";
    const WithdrawShareCapital = "withdraw-share-capital";
    const DepositSavings = "deposit-savings";
    const WithdrawSavings = "withdraw-savings";
    const PayAmortization = "pay-amortization";
    const PayMembership = "pay-membership";
    const PayOrientation = "pay-orientation";
    const PayMortuary = "pay-mortuary";
    const PayLoanPreTerminationFee = "pay-loan-pre-termination-fee";

    const LIST = [
        self::DepositShareCapital,
        self::WithdrawShareCapital,
        self::DepositSavings,
        self::WithdrawSavings,
        self::PayAmortization,
        self::PayMembership,
        self::PayLoanPreTerminationFee,
        self::PayOrientation,
        self::PayMortuary,
    ];
}