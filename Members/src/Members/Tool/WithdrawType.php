<?php

namespace Members\Tool;

class WithdrawType
{

    /**
     * Type constants
     */
    const TYPE_PAYPAL = 1;
    const TYPE_BANK_TRANSFER = 2;

    /**
     * Bank constants when TYPE_BANK_TRANSFER choosed
     */
    const BANK_SWEDBANK = 'swedbank';

    /**
     * Get all types keys.
     * 
     * @access public
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_PAYPAL,
            self::TYPE_BANK_TRANSFER,
        ];
    }

    /**
     * Get all types labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::TYPE_PAYPAL => 'PayPal',
            self::TYPE_BANK_TRANSFER => 'Bank transfer',
        ];
    }

    /**
     * Get banks labels.
     * 
     * @access public
     * @return array
     */
    public static function getBanksLabels($type = null)
    {
        if ((int)$type === self::TYPE_BANK_TRANSFER) {
            return [
                self::BANK_SWEDBANK => 'Swedbank',
            ];
        }

        return [];
    }
    public static function getFrontendLabels()
    {
        return [
            self::TYPE_PAYPAL => 'PayPal',
            self::TYPE_BANK_TRANSFER => 'Bankas pārskaitījums',
        ];
    }

}
