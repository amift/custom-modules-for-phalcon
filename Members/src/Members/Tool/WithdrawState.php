<?php

namespace Members\Tool;

class WithdrawState
{

    /**
     * State constants
     */
    const STATE_PENDING         = 1;
    const STATE_TRANSFER_MONEY  = 2;
    const STATE_COMPLETED       = 3;
    const STATE_REJECTED        = 4;

    /**
     * Get all states keys.
     * 
     * @access public
     * @return array
     */
    public static function getStates()
    {
        return [
            self::STATE_PENDING,
            self::STATE_TRANSFER_MONEY,
            self::STATE_COMPLETED,
            self::STATE_REJECTED,
        ];
    }

    /**
     * Get all pending states keys.
     * 
     * @access public
     * @return array
     */
    public static function getPendingStates()
    {
        return [
            self::STATE_PENDING,
            self::STATE_TRANSFER_MONEY
        ];
    }

    /**
     * @return array
     */
    public static function getStyles()
    {
        return [
            self::STATE_PENDING => 'label-default',
            self::STATE_TRANSFER_MONEY => 'label-warning',
            self::STATE_COMPLETED => 'label-success',
            self::STATE_REJECTED => 'label-danger',
        ];
    }

    /**
     * Get all states labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::STATE_PENDING => 'Pending',
            self::STATE_TRANSFER_MONEY => 'Transfer money',
            self::STATE_COMPLETED => 'Completed',
            self::STATE_REJECTED => 'Rejected',
        ];
    }

}
