<?php

namespace Communication\Tool;

use Core\Tool\ImageEmbed;

class NewsletterContent
{
    const TPL_HEADER = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed" width="100%">
        <tbody>
            <tr>
                <td width="600" align="center" bgcolor="#0c0c11" height="50" style="border-collapse: collapse;background-color:#0c0c11;" valign="top">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed">
                        <tbody>
                            <tr>
                                <td width="600" height="50" bgcolor="#0c0c11" align="center" style="border-collapse: collapse;background-color:#0c0c11;">
                                    <a href="{{ url_logo }}" target="_blank" title="AllSports" style="display: block; width: 143px; height: 30px; padding-top: 10px;"><img src="{{ img_logo }}" style="display: block" width="143" height="30" alt="AllSports" border="0" /></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>';

    const TPL_FOOTER = '<table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed" width="100%">
        <tbody>
            <tr>
                <td width="600" align="center" bgcolor="#dedee2" height="120" style="border-collapse: collapse;background-color:#dedee2;" valign="top">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed">
                        <tbody>
                            <tr>
                                <td width="440" height="88" align="center" style="border-collapse: collapse; padding: 32px 80px 0 80px; font-weight: 400; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 13px; color: #9997a6;">
                                    <p style="font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 13px; font-weight: 400; color: #9997a6; line-height: 18px;">
                                        {{ txt_footer }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>';

    public static function preview($body = '')
    {
        $html = '
<div bgcolor="#dedee2" style="-ms-text-size-adjust: 100% !important; -webkit-font-smoothing: antialiased !important; -webkit-text-size-adjust: 100% !important; background: #dedee2; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; margin: 0; min-width: 100%; padding: 0">

    {{ tpl_header }}

    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed" width="100%">
        <tbody>
            <tr>
                <td align="center" bgcolor="#dedee2" style="border-collapse: collapse; padding: 40px 0 0 0; min-height: 300px;" valign="top">
                    {{ tpl_body }}
                </td>
            </tr>
        </tbody>
    </table>

    {{ tpl_footer }}

</div>';

        $placeholders = [
            '{{ tpl_header }}'  => self::getHeader(),
            '{{ tpl_body }}'    => $body,
            '{{ tpl_footer }}'  => self::getFooter(),
        ];

        return strtr($html, $placeholders);
    }

    public static function getHeader()
    {
        $file = ROOT_PATH . str_replace('/', DS, '/public/assets/img/mail_allsports.gif');

        return strtr(self::TPL_HEADER, [
            '{{ url_logo }}' => 'http://www.allsports.lv',
            '{{ img_logo }}' => ImageEmbed::dataUri($file)
        ]);
    }

    public static function getFooter($unsubscribeUrl = '')
    {
        $txt = '© 2016 AllSports.lv. All Rights Reserved.';

        if ($unsubscribeUrl !== '') {
            $txt.= '<br/>Ja nevēlies turpmāk saņemt automātiskos e-pastus, spied <a href="'.$unsubscribeUrl.'" target="_blank" style="font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 13px; color: #d15844; text-decoration: underline"><span class="color: d15844">šeit</span></a>.';
        }

        return strtr(self::TPL_FOOTER, [
            '{{ txt_footer }}' => $txt
        ]);
    }

    public static function full($body = '')
    {
        $html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <!--[if gte mso 9]>
        <xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
        <style type="text/css">.body {background: #dedee2;}.container {background: none;}</style>
    <![endif]-->
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>AllSports.lv</title>
</head>
<body bgcolor="#dedee2" style="-ms-text-size-adjust: 100% !important; -webkit-font-smoothing: antialiased !important; -webkit-text-size-adjust: 100% !important; background: #dedee2; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; margin: 0; min-width: 100%; padding: 0">

    {{ tpl_header }}

    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed" width="100%">
        <tbody>
            <tr>
                <td align="center" bgcolor="#dedee2" style="border-collapse: collapse; padding: 40px 0 0 0; min-height: 300px;" valign="top">
                    {{ tpl_body }}
                </td>
            </tr>
        </tbody>
    </table>

    {{ tpl_footer }}

</body>
</html>';

        $placeholders = [
            '{{ tpl_header }}'  => self::getHeader(),
            '{{ tpl_body }}'    => $body,
        ];

        return strtr($html, $placeholders);
    }
}