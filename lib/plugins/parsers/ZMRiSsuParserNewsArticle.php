<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: pages.php 249 2009-09-10 04:27:39Z yellow1912 $
*/

class ZMRiSsuParserNewsArticle extends ZMRiSsuParser{
    protected $table        = TABLE_NEWS_ARTICLES_TEXT;
    protected $name_field   = "news_article_name";
    protected $id_field     = "article_id";
    protected $identifier   = "news_articles";
    protected $query_key    = "article_id";
    protected $languages_field = 'languages_id';
}