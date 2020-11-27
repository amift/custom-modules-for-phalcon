<?php

namespace Communication\Tool;

class TemplateCode 
{

    /**
     * Code constants
     */
    const CODE_MEMBER_REGISTRATION = 'email.member_registration';
    const CODE_MEMBER_PASSWORD_RECOVERY = 'email.member_password_recovery';
    const CODE_ARTICLE_CREATED = 'email.article_created';
    const CODE_ARTICLE_CONFIRMED = 'email.article_confirmed';
    const CODE_ARTICLE_NOT_CONFIRMED = 'email.article_not_confirmed';

    /**
     * Get all codes keys.
     * 
     * @access public
     * @return array
     */
    public static function getCodes()
    {
        return [
            self::CODE_MEMBER_REGISTRATION,
            self::CODE_MEMBER_PASSWORD_RECOVERY,
            self::CODE_ARTICLE_CREATED,
            self::CODE_ARTICLE_CONFIRMED,
            self::CODE_ARTICLE_NOT_CONFIRMED,
        ];
    }

    /**
     * Get all codes labels.
     * 
     * @access public
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::CODE_MEMBER_REGISTRATION => 'Member registration',
            self::CODE_MEMBER_PASSWORD_RECOVERY => 'Member password recovery',
            self::CODE_ARTICLE_CREATED => 'Article created',
            self::CODE_ARTICLE_CONFIRMED => 'Article confirmed',
            self::CODE_ARTICLE_NOT_CONFIRMED => 'Article not confirmed',
        ];
    }

    /**
     * Get code label
     * 
     * @access public
     * @param string $code
     * @return string
     */
    public static function getLabel($code)
    {
        $labels = self::getLabels();

        if (isset($labels[$code])) {
            return $labels[$code];
        }

        return '-';
    }

}
