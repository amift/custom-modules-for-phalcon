<?php

namespace Communication\Service;

use Articles\Entity\Article;
use Core\Library\AbstractLibrary;
use Core\Tool\ImageEmbed;

class NewsletterService extends AbstractLibrary
{
    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articleRepo;

    /**
     * @param string $idsString
     * @return string
     */
    public function bodyArticles($subject = '', $idsString = '')
    {
        $ids = explode(',', $idsString);
        if (count($ids) < 1) {
            return '';
        }

        $articles = [];
        foreach ($ids as $id) {
            $article = $this->getArticleRepo()->findObjectById($id);
            if ($article !== null) {
                $articles[] = $article;
            }
        }

        $domain = $this->di->get('config')->web_url;
        $num = count($articles);
        $parts = explode('|', $subject, 2);
        $titleLine1 = isset($parts[0]) ? strtr($parts[0], ['{num}' => $num]) : '';
        $titleLine2 = isset($parts[1]) ? strtr($parts[1], ['{num}' => $num]) : '';

        $html = '
                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed">
                        <tbody>
                            <tr>
                                <td width="440" colspan="2" bgcolor="#FFFFFF" style="padding: 20px 20px 20px 20px; color: #4a4a4a; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 17px; font-weight: normal; background-color:#ffffff;">
                                    <h1 style="margin: 0; padding: 0 0 0 0; font-size: 24px; line-height: 24px; color: #45434e; font-weight: 400; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif">'.$titleLine1.'</h1>
                                    <h2 style="margin: 0; padding: 0 0 0 0; font-size: 36px; line-height: 36px; font-weight: 700; color: #ff003c; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif">'.$titleLine2.'</h2>
                                </td>
                            </tr>';
        foreach ($articles as $article) {
            $html.= '
                            <tr>';
            if ($article->hasImage()) {
                $imgPath = method_exists($article, 'getImageServerPath') ? $article->getImageServerPath() : $article->getImagePath();
                $html.= '
                                <td width="200" bgcolor="#FFFFFF" style="padding: 20px 0 20px 20px; border-top: 1px solid #dedee2; color: #4a4a4a; vertical-align: top; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 17px; font-weight: normal; background-color:#ffffff;">
                                   <a href="'.$domain.$article->getFullUrl().'" target="_blank" title="Allsports" style="display: block; width: 200px"><img src="'.ImageEmbed::dataUri($imgPath).'" style="display: block" width="200" alt="AllSports" border="0" /></a>
                                </td>
                                <td width="220" bgcolor="#FFFFFF" style="padding: 20px 20px 20px 20px; border-top: 1px solid #dedee2; color: #4a4a4a; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 17px; font-weight: normal; background-color:#ffffff;">';
            } else {
                $html.= '
                                <td width="440" colspan="2" bgcolor="#FFFFFF" style="padding: 20px 20px 20px 20px; border-top: 1px solid #dedee2; color: #4a4a4a; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 17px; font-weight: normal; background-color:#ffffff;">';
            }
            $html.= '
                                    <p style="margin: 0; padding: 0 0 10px 0; line-height: 16px; font-weight: 700; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif">'.$article->getTitle().'</p>
                                    <p style="margin: 0; padding: 0 0 10px 0; font-size: 13px; font-weight: 400; color: #9997a6; line-height: 18px; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif">'.$article->getSummary().'</p>
                                    <a href="'.$domain.$article->getFullUrl().'" target="_blank" title="Lasīt vairāk" style="display: block; text-align: center; width: 100px; height: 24px; font-size: 11px; text-transform: uppercase; color: #FFFFFF; font-weight: 700; line-height: 24px; text-decoration: none; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; background: #00aa32"><span style="color: #FFFFFF">Lasīt vairāk</span></a>
                                </td>';
            $html.= '
                            </tr>';
        }
        $html.= '
                        </tbody>
                    </table>';

        return $html;
    }

    /**
     * @param string $body
     * @return string
     */
    public function bodyCustom($body)
    {
        $html = '
                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; table-layout: fixed">
                        <tbody>
                            <tr>
                                <td width="440" colspan="2" bgcolor="#FFFFFF" style="padding: 20px 20px 20px 20px; color: #4a4a4a; font-family: Circular,&#39;Helvetica Neue&#39;,Helvetica,Arial,sans-serif; font-size: 17px; font-weight: normal; background-color:#ffffff;">
                                    ' . $body . '
                                </td>
                            </tr>
                        </tbody>
                    </table>';

        return $html;
    }

    /**
     * Get Article entity reposotory
     *
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articleRepo === null || !$this->_articleRepo) {
            $this->_articleRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articleRepo;
    }
}