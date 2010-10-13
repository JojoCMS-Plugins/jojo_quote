<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007-2008 Harvey Kane <code@ragepank.com>
 * Copyright 2007-2008 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

class Jojo_Plugin_Jojo_Quote extends JOJO_Plugin
{
    private static $tablename = 'quote';
    private static $pluginname = 'Jojo_Plugin_Jojo_Quote'; // full plugin name
    private static $pglinkname = 'jojo_plugin_jojo_quote'; // full plugin name
    private static $plugintype = 'jojo_quote'; // short plugin name used in tags, search etc

 /* Standard Fields */
    private static $idfield = 'quoteid';
    private static $urlfield = 'qt_url';
    private static $titlefield = 'qt_title';
    private static $datefield = 'qt_date';
    private static $bodyfield = 'qt_body';
    private static $imgfield = 'qt_image';
    private static $langfield = 'qt_language';
    private static $livefield = 'qt_livedate'; // sheduling go live date field name
    private static $expfield = 'qt_expirydate'; // sheduling expiry date field name

    static function saveTags($record, $tags = array())
    {
        /* Delete existing tags for this item */
        JOJO_Plugin_Jojo_Tags::deleteTags(self::$plugintype, $record[self::$idfield]);

        /* Save all the new tags */
        foreach($tags as $tag) {
            JOJO_Plugin_Jojo_Tags::saveTag($tag, self::$plugintype, $record[self::$idfield]);
        }
    }

    static function getTagSnippets($ids)
    {
        /* Convert array of ids to a string */
        $ids = "'" . implode($ids, "', '") . "'";

        /* Get the items */
        $items = Jojo::selectQuery("SELECT *
                                       FROM {". self::$tablename ."}
                                       WHERE "
                                            . self::$idfield ." IN ($ids)
                                         AND
                                           ". self::$livefield ." < ?
                                         AND
                                           ". self::$expfield ."<=0 OR ". self::$expfield ." > ?
                                       ORDER BY "
                                         . self::$datefield ." DESC",
                                      array(time(), time()));

        /* Create the snippets */
        $snippets = array();
        foreach ($items as $i) {
            $image = !empty($i[self::$imgfield]) ? self::$tablename . 's/' . $i[self::$imgfield] : '';
            $snippets[] = array(
                    'id'    => $i[self::$idfield],
                    'image' => $image,
                    'title' => htmlspecialchars($i[self::$titlefield], ENT_COMPAT, 'UTF-8', false),
                    'text'  => strip_tags($i[self::$bodyfield]),
                    'url'   => Jojo::urlPrefix(false) . self::getUrl($i[self::$idfield], $i[self::$urlfield], $i[self::$titlefield], $i[self::$langfield])
                );
        }

        /* Return the snippets */
        return $snippets;
    }

    public static function getItems($num = false, $start = 0, $exclude=false) {
        global $page;
        if (_MULTILANGUAGE) $language = !empty($page->page['pg_language']) ? $page->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');

        /* exclude the current page from the returned list of items */
        $excludethisid = ($exclude && $page->page['pg_link']== self::$pglinkname && Jojo::getFormData('id')) ? Jojo::getFormData('id') : '';
        $excludethisurl = ($exclude && $page->page['pg_link']== self::$pglinkname && Jojo::getFormData('url')) ? Jojo::getFormData('url') : '';

        $now = time();
        $query = "SELECT * FROM {". self::$tablename ."}";
        $query .= " WHERE ". self::$livefield ."<$now AND (". self::$expfield ."<=0 OR ". self::$expfield .">$now) ";
        $query .= (_MULTILANGUAGE) ? " AND (" . self::$langfield ." = '$language')" : '';
        $query .= ($excludethisid) ? " AND (" . self::$idfield . " != '$excludethisid')" : '';
        $query .= ($excludethisurl) ? " AND (" . self::$urlfield . " != '$excludethisurl')" : '';
        $query .= " ORDER BY " . self::$datefield ." DESC";
        $query .= ($num) ? " LIMIT $start, $num" : '';
        $items = Jojo::selectQuery($query);
        foreach ($items as &$i){
            $i['id'] = $i[self::$idfield];
            $i['title'] = htmlspecialchars($i[self::$titlefield], ENT_COMPAT, 'UTF-8', false);
            $i['body'] = strip_tags($i[self::$bodyfield]);
            $i['date'] = Jojo::strToTimeUK($i[self::$datefield]);
            $i['datefriendly'] = Jojo::mysql2date($i[self::$datefield], "medium");
            $i['url'] = self::getUrl($i[self::$idfield], $i[self::$urlfield], $i[self::$titlefield], $i[self::$langfield]);
            $i['imageurl'] = ($i[self::$imgfield]) ? 'images/v12000/' .  self::$tablename . 's/' . $i[self::$imgfield] : '';
            $i['author'] = htmlspecialchars($i['qt_author'], ENT_COMPAT, 'UTF-8', false);
			$i['designation'] = htmlspecialchars($i['qt_designation'], ENT_COMPAT, 'UTF-8', false);
			$i['company'] = htmlspecialchars($i['qt_company'], ENT_COMPAT, 'UTF-8', false);
			$i['weblink'] = $i['qt_weblink'];
            $i['description'] = ($i['qt_description']) ? strip_tags($i['qt_description']) : '';
            $i['test'] = $excludethisid;
        }
        return $items;
    }

    /*
     * calculates the URL for the item - requires the ID, but works without a query if given the URL or title from a previous query
     */
    static function getUrl($id=false, $url=false, $title=false, $language=false )
    {
        if (_MULTILANGUAGE) {
            $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
            $mldata = Jojo::getMultiLanguageData();
            $lclanguage = $mldata['longcodes'][$language];
        }
        $languagePrefix = ( _MULTILANGUAGE ) ? Jojo::getMultiLanguageString ( $language ) : '';
        /* URL specified */
        if (!empty($url)) {
            $fullurl = $languagePrefix;
            $fullurl .= self::_getPrefix('', (_MULTILANGUAGE ? $language : '')) . '/' . $url . '/';
            return $fullurl;
         }
        /* ID + title specified */
        if ($id && !empty($title)) {
            $fullurl = $languagePrefix;
            $fullurl .= (_MULTILANGUAGE && $language != 'en') ? self::_getPrefix('', $language) . '/' . $id . '/' . urlencode($title) : Jojo::rewrite(self::_getPrefix('', ((_MULTILANGUAGE) ? $language : '')), $id, $title, '');
            return $fullurl;
        }
        /* use the ID to find either the URL or title */
        if ($id) {
            $item = Jojo::selectRow("SELECT `" . self::$urlfield . "`, " . self::$titlefield . ", " . self::$langfield . " FROM {". self::$tablename . "} WHERE " . self::$idfield . " = ?", array($id));
            if (count($item)) {
                return self::getUrl($id, $item[self::$urlfield], $item[self::$titlefield], $item[self::$langfield]);
            }
         }
        /* No item matching the ID supplied or no ID supplied */
        return false;
    }

    function _getContent()
    {
        global $smarty, $_USERGROUPS;
        $content = array();
        $language = !empty($this->page['pg_language']) ? $this->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
        $mldata = Jojo::getMultiLanguageData();
        $languagePrefix = ( _MULTILANGUAGE ) ? Jojo::getMultiLanguageString ( $language ) : '';
        if (_MULTILANGUAGE) {
            $smarty->assign('multilangstring', $languagePrefix);
        }
        $items = self::getItems();
        $numitems = count($items);

        /* Are we looking at a quote or the index? */
        $id = Util::getFormData('id', 0);
        $url = Util::getFormData('url', '');

        if ($id || !empty($url) || $numitems == 1) {
            $item = array();

            if ($numitems == 1) {
                /* if there's only one, show that */
                $id = $items[0][self::$idfield];
                $item = $items[0];
                $smarty->assign('onequote', true);
            } else {
                /* find the current, next and previous items */
                $prev = array();
                $next = array();
                $isnext = false;
                foreach ($items as $i) {
                    if (!_MULTILANGUAGE && !empty($url) && $url==$i[self::$urlfield]) {
                        $item = $i;
                        $isnext = true;
                   } elseif (_MULTILANGUAGE && !empty($url) && $url==$i[self::$urlfield] && $language==$i[self::$langfield]) {
                        $item = $i;
                        $isnext = true;
                   } elseif ($id==$i[self::$idfield]) {
                        $item = $i;
                        $isnext = true;
                    } elseif ($isnext==true) {
                        $next = $i;
                         break;
                    } else {
                        $prev = $i;
                    }
                }
            }

            /* If the item can't be found, return a 404 */
            if (!$item) {
                include(_BASEPLUGINDIR . '/jojo_core/404.php');
                exit;
            }

            /* Get the specific item */
            $id = $item[self::$idfield];
            $item['qt_datefriendly'] = Jojo::mysql2date($item[self::$datefield], "long");

            /* Get the next and previous items */
            if (Jojo::getOption('quote_next_prev') == 'yes' && $numitems >1) {
                if (!empty($next)) {
                    $smarty->assign('nextquote', $next);
                }
                if (!empty($prev)) {
                    $smarty->assign('prevquote', $prev);
                }
            }

            /* Ensure the tags class is available */
            if (class_exists('Jojo_Plugin_Jojo_Tags')) {
                /* Split up tags for display */
                $tags = Jojo_Plugin_Jojo_Tags::getTags(self::$plugintype, $id);
                $smarty->assign('tags', $tags);

                /* generate tag cloud of tags belonging to this quote */
                $tag_cloud_minimum = Jojo::getOption('quote_tag_cloud_minimum');
                if (!empty($tag_cloud_minimum) && ($tag_cloud_minimum < count($tags))) {
                    $itemcloud = JOJO_Plugin_Jojo_Tags::getTagCloud('', $tags);
                    $smarty->assign('itemcloud', $itemcloud);
                }
            }

            /* Calculate whether the item has expired or not */
            $now = Jojo::strToTimeUK('now');
            if (($now < $item[self::$livefield]) || (($now > $item[self::$expfield]) && ($item[self::$expfield] > 0)) ) {
                $this->expired = true;
            }

            /* Add quote breadcrumb */
            $breadcrumbs = $this->_getBreadCrumbs();
            $breadcrumb = array();
            $breadcrumb['name'] = $item['title'];
            $breadcrumb['name'] .= $item['author'] ?  ' - ' .   $item['author'] : '';
            $breadcrumb['rollover'] = $item['title'];
            $breadcrumb['url'] = self::getUrl($id, $item[self::$urlfield], $item[self::$titlefield], $item[self::$langfield]);
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;


            /* Assign quote content to Smarty */
            $smarty->assign(self::$plugintype, $item);

            /* Prepare fields for display */
            if ($item['qt_htmllang']) {
                // Override the language setting on this page if necessary.
                $content['pg_htmllang'] = $item['qt_htmllang'];
                $smarty->assign['pg_htmllang'] = $item['ar_htmllang'];
            }
            $content['title']            = $item['title'];
            $content['seotitle']         = Jojo::either($item['qt_seotitle'], $item['title'] . ' - ' . $item['author']);
            $content['breadcrumbs']      = $breadcrumbs;
            $content['meta_description'] = Jojo::either($item['qt_metadesc'], $item['title'].', a quote from ' . $item['author'] . ' on ' . _SITETITLE . ' - Read all about '. $item['title'] . ' and other subjects on ' . _SITETITLE . '. ' . Jojo::getOption('linkbody'));
            $content['metadescription']  = $content['meta_description'];

        } else {
            /*  index section */

            $pagenum = Util::getFormData('pagenum', 1);
            $smarty->assign(self::$plugintype,'');
            $itemsperpage = Jojo::getOption('quotesperpage', 40);
            $start = ($itemsperpage * ($pagenum-1));

            /* get number of items for pagination */
            $numpages = ceil($numitems / $itemsperpage);

            /* calculate pagination */
            if ($numpages == 1) {
                $pagination = '';
            } elseif ($numpages == 2 && $pagenum == 2) {
                $pagination = sprintf('<a href="%s/p1/">Previous page...</a>', (_MULTILANGUAGE ? $languagePrefix . self::_getPrefix('', $language) : self::_getPrefix()) );
            } elseif ($numpages == 2 && $pagenum == 1) {
                $pagination = sprintf('<a href="%s/p2/">Next page...</a>', (_MULTILANGUAGE ? $languagePrefix . self::_getPrefix('', $language) : self::_getPrefix()) );
            } else {
                $pagination = '<ul>';
                for ($p=1; $p<=$numpages; $p++) {
                    $url = (_MULTILANGUAGE) ? $languagePrefix . self::_getPrefix('', $language) . '/' : self::_getPrefix() . '/';
                    if ($p > 1) {
                        $url .= 'p' . $p . '/';
                    }
                    if ($p == $pagenum) {
                        $pagination .= '<li>&gt; Page '.$p.'</li>'. "\n";
                    } else {
                        $pagination .= '<li>&gt; <a href="' . $url . '">Page ' . $p . '</a></li>' . "\n";
                    }
                }
                $pagination .= '</ul>';
            }
            $smarty->assign('pagination', $pagination);
            $smarty->assign('pagenum', $pagenum);

            /* clear the meta description to avoid duplicate content issues */
            $content['metadescription'] = '';
            $content['meta_description'] = '';

            /* get content and assign to Smarty */
            $smarty->assign('jojo_quotes', self::getItems($itemsperpage, $start));

        }
        /* get related items if tags plugin installed and option enabled */
        $numrelated = Jojo::getOption('quote_num_related');
        if ($numrelated) {
            $related = JOJO_Plugin_Jojo_Tags::getRelated(self::$plugintype, $id, $numrelated, self::$plugintype); //set the last argument to self::$plugintype to restrict results to only quotes
            $smarty->assign('related', $related);
        }

        $indexurl = (_MULTILANGUAGE) ? $languagePrefix . self::_getPrefix('', $language) . '/' : self::_getPrefix() . '/';
        $smarty->assign ( 'indexurl', $indexurl );

        $content['content'] = $smarty->fetch('jojo_quote.tpl');
        return $content;
    }

    static function admin_action_after_save()
    {
        Jojo::updateQuery("UPDATE {option} SET `op_value`='" . time() . "' WHERE `op_name`='quote_last_updated'");
        return true;
    }

    /**
     * Sitemap filter
     *
     * Receives existing sitemap and adds quotes section
     */
    public static function sitemap($sitemap)
    {
        /* See if we have any sections to display */
        $indexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = '" . self::$pluginname . "' AND pg_sitemapnav = 'yes'");
        if (!count($indexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('quote_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to this section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = self::_sitemapRemoveSelf($section['tree']);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $now = strtotime('now');
        $itemsperpage = Jojo::getOption('quotesperpage', 40);
        $limit = ($itemsperpage >= 15) ? 15 : $itemsperpage ;
        foreach($indexes as $k => $i){
            if (_MULTILANGUAGE) {
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
            }
            $languagePrefix = ( _MULTILANGUAGE ) ? Jojo::getMultiLanguageString ( $language ) : '';
            /* Create tree and add index and feed links at the top */
            $tree = new hktree();
            $indexurl = (_MULTILANGUAGE) ? $languagePrefix . self::_getPrefix('', $language) . '/' : self::_getPrefix() . '/' ;
            $i['title'] = htmlspecialchars($i['pg_title'], ENT_COMPAT, 'UTF-8', false);
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $tree->addNode('index', 0, $i['title'], $indexurl);
               $parent = 'index';
            }

            /* Get the item content from the database */
            $query =  "SELECT * FROM {". self::$tablename ."} WHERE ". self::$livefield ."<$now AND (". self::$expfield ."<=0 OR ". self::$expfield .">$now)";
            $query .= (_MULTILANGUAGE) ? " AND (". self::$langfield ." = '$language')" : '';
            $query .= " ORDER BY ". self::$datefield ." DESC LIMIT $limit";

            $items = Jojo::selectQuery($query);
            $n = count($items);
            foreach ($items as $a) {
               $a['title'] = htmlspecialchars($a[self::$titlefield], ENT_COMPAT, 'UTF-8', false);
                $tree->addNode($a[self::$idfield], $parent, $a['title'], self::getUrl($a[self::$idfield], $a[self::$urlfield], $a[self::$titlefield], $a[self::$langfield]));
            }

            /* Get number of items for pagination */
            $countquery =  "SELECT COUNT(*) AS num FROM {". self::$tablename ."} WHERE ". self::$livefield ."<$now AND (". self::$expfield ."<=0 OR ". self::$expfield .">$now)";
            $countquery .= (_MULTILANGUAGE) ? " AND (". self::$langfield ." = '$language')" : '';
            $count = Jojo::selectQuery($countquery);
            $numitems = $count[0]['num'];
            $numpages = ceil($numitems / $itemsperpage);

            /* calculate pagination */
            if ($numpages == 1) {
                if ($limit < $numitems) {
                    $tree->addNode('p1', $parent, 'More ' . $i['title'] , $indexurl );
                }
            } else {
                for ($p=1; $p <= $numpages; $p++) {
                    if (($limit < $itemsperpage) && ($p == 1)) {
                        $tree->addNode('p1', $parent, '...More' , $indexurl );
                    } elseif ($p != 1) {
                        $url = $indexurl .'p' . $p .'/';
                        $nodetitle = $i['title'] . ' Page '. $p;
                        $tree->addNode('p' . $p, $parent, $nodetitle, $url);
                    }
                }
            }

            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = $languagePrefix . self::_getPrefix('', (_MULTILANGUAGE ? $language : '')) . '/';
                $sitemap['pages']['tree'] = self::_sitemapAddInplace($sitemap['pages']['tree'], $tree->asArray(), $url);
            } else {
                /* Add to the end */
                $sitemap["quotes$k"] = array(
                    'title' => _MULTILANGUAGE ? $i['title'] . ' (' . ucfirst($lclanguage) . ')' : $i['title'],
                    'tree' => $tree->asArray(),
                    'order' => 3 + $k,
                    'header' => '',
                    'footer' => '',
                    );
            }
        }
        return $sitemap;
    }

    private static function _sitemapAddInplace($sitemap, $toadd, $url)
    {
        foreach ($sitemap as $k => $t) {
            if ($t['url'] == $url) {
                $sitemap[$k]['children'] = $toadd;
            } elseif (isset($sitemap[$k]['children'])) {
                $sitemap[$k]['children'] = self::_sitemapAddInplace($t['children'], $toadd, $url);
            }
        }
        return $sitemap;
    }

    private static function _sitemapRemoveSelf($tree)
    {
        static $urls;

        if (!is_array($urls)) {
            $urls = array();
            $indexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = '" . self::$pluginname . "' AND pg_sitemapnav = 'yes'");
            if (count($indexes)==0) {
               return $tree;
            }

            foreach($indexes as $key => $i){
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $languagePrefix = ( _MULTILANGUAGE ) ? Jojo::getMultiLanguageString ( $language ) : '';
                $urls[] = $languagePrefix . self::_getPrefix('', (_MULTILANGUAGE ? $language : '')) . '/';
            }
        }

        foreach ($tree as $k =>$t) {
            if (in_array($t['url'], $urls)) {
                unset($tree[$k]);
            } else {
                $tree[$k]['children'] = self::_sitemapRemoveSelf($t['children']);
            }
        }
        return $tree;
    }


    /**
     * XML Sitemap filter
     *
     * Receives existing sitemap and adds quotes pages
     */
    static function xmlsitemap($sitemap)
    {
        /* Get items from database */
        $items = Jojo::selectQuery("SELECT * FROM {". self::$tablename ."} WHERE ". self::$livefield ."<".time()." AND (". self::$expfield ."<=0 OR ". self::$expfield .">" . time() . ")");

        /* Add items to sitemap */
        foreach($items as $a) {
            $url = _SITEURL . '/'. self::getUrl($a[self::$idfield], $a[self::$urlfield], $a[self::$titlefield], $a[self::$langfield]);
            $lastmod = strtotime($a[self::$datefield]);
            $priority = 0.6;
            $changefreq = '';
            $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
        }

        /* Return sitemap */
        return $sitemap;
    }

    /**
     * Site Search
     *
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        global $_USERGROUPS;
        $pagePermissions = new JOJO_Permissions();
        $boolean = ($booleankeyword_str) ? true : false;
        $keywords_str = ($boolean) ? $booleankeyword_str :  implode(' ', $keywords);
        if ($boolean && stripos($booleankeyword_str, '+') === 0  ) {
            $like = '1';
            foreach ($keywords as $keyword) {
                $like .= sprintf(" AND (qt_body LIKE '%%%s%%' OR qt_title LIKE '%%%s%%' OR qt_description LIKE '%%%s%%')", JOJO::clean($keyword), JOJO::clean($keyword), JOJO::clean($keyword));
            }
        } elseif ($boolean && stripos($booleankeyword_str, '"') === 0) {
            $like = "(qt_body LIKE '%%%". implode(' ', $keywords). "%%' OR qt_title LIKE '%%%". implode(' ', $keywords) . "%%' OR qt_description LIKE '%%%". implode(' ', $keywords) . "%%')";
        } else {
            $like = '(0';
            foreach ($keywords as $keyword) {
                $like .= sprintf(" OR qt_body LIKE '%%%s%%' OR qt_title LIKE '%%%s%%' OR qt_description LIKE '%%%s%%'", JOJO::clean($keyword), JOJO::clean($keyword), JOJO::clean($keyword));
            }
            $like .= ')';
        }

        $query = "SELECT quoteid, qt_url, qt_title, qt_author, qt_designation, qt_company, qt_description, qt_body, qt_language, ". self::$expfield .", ". self::$livefield .", qt_image, ((MATCH(qt_title) AGAINST (?) * 0.2) + MATCH(qt_title, qt_description, qt_body, qt_author, qt_designation, qt_company) AGAINST (?)) AS relevance ";
        $query .= ", p.pg_url, p.pg_title";
        $query .= " FROM {". self::$tablename ."} AS ". self::$tablename;
        $query .= " LEFT JOIN {page} p ON (p.pg_link='Jojo_Plugin_Jojo_Quote' AND p.pg_language=". self::$langfield .")";
        $query .= "LEFT JOIN {language} AS language ON (". self::$langfield ." = languageid) ";
        $query .= "WHERE $like";
        $query .= ($language) ? " AND ". self::$langfield ." = '$language' " : ' ';
        $query .= "AND language.active = '1' ";
        $query .= "AND ". self::$livefield ."<" . time() . " AND (". self::$expfield ."<=0 OR ". self::$expfield .">" . time() . ") ";
        $query .= " ORDER BY relevance DESC LIMIT 100";

        $data = Jojo::selectQuery($query, array($keywords_str, $keywords_str));

        if (_MULTILANGUAGE) {
            global $page;
            $mldata = Jojo::getMultiLanguageData();
            $homes = $mldata['homes'];
        } else {
            $homes = array(1);
        }

        foreach ($data as $d) {
            $pagePermissions->getPermissions('quote', $d[self::$idfield]);
            if (!$pagePermissions->hasPerm($_USERGROUPS, 'view')) {
                continue;
            }
            $result = array();
            $result['relevance'] = $d['relevance'];
            $result['title'] = $d[self::$titlefield];
            $result['image'] = !empty($d[self::$imgfield]) ? self::$tablename . 's/' . $d[self::$imgfield] : '';
            $result['body'] = $d[self::$bodyfield] . ' - ' . $d['qt_author'] . ($d['qt_designation'] ? ', ' . $d['qt_designation'] : '') . ($d['qt_company'] ? ', ' . $d['qt_company'] : '') . '<br />' . $d['qt_description'];
            $result['url'] = self::getUrl($d[self::$idfield], $d[self::$urlfield], $d[self::$titlefield], $d[self::$langfield]);
            $result['absoluteurl'] = _SITEURL. '/' . $result['url'];
            $result['id'] = $d[self::$idfield];
            $result['plugin'] = self::$plugintype;
            $result['type'] = $d['pg_title'] ? $d['pg_title'] : 'Testimonials';
            $results[] = $result;
        }

        /* Return results */
        return $results;
    }


    /**
     * Remove Snip
     *
     * Removes any [[snip]] tags leftover in the content before outputting
     */
    static function removesnip($data) {
        $data = str_ireplace('[[snip]]','',$data);
        return $data;
    }



    /**
     * Get the url prefix for a particular part of this plugin
     */
    static function _getPrefix($for='testimonials', $language=false) {
        $cacheKey = $for;
        $cacheKey .= ($language) ? $language : 'false';

        /* Have we got a cached result? */
        static $_cache;
        if (isset($_cache[$cacheKey])) {
            return $_cache[$cacheKey];
        }

        $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
        $query = "SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_link = '" . self::$pluginname . "'";
        $query .= (_MULTILANGUAGE) ? " AND pg_language = '$language'" : '';
        $res = Jojo::selectRow($query);

        if ($res) {
            $_cache[$cacheKey] = !empty($res['pg_url']) ? $res['pg_url'] : $res['pageid'] . '/' . Jojo::cleanURL($res['pg_title']);
        } else {
            $_cache[$cacheKey] = '';
        }
        return $_cache[$cacheKey];
    }


    function getCorrectUrl()
    {
        $id = Util::getFormData('id', 0);
        $url       = Util::getFormData('url', '');
        $action    = Util::getFormData('action', '');
        $pagenum   = Util::getFormData('pagenum', 1);

        global $page;
        $language = $page->page['pg_language'];


        /* the special URL for the latest item */
        if ($action == 'latest') {
            $latest = Jojo::selectRow("SELECT * FROM {". self::$tablename ."} WHERE 1 ORDER BY ". self::$datefield ." DESC" );
            return _SITEURL . '/' . self::getUrl($latest[self::$idfield], $latest[self::$urlfield], $latest[self::$titlefield], $latest[self::$langfield]);
        }
        $correcturl = self::getUrl($id, $url, null, $language);
        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* quote index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        /* quote index - default */
        return parent::getCorrectUrl();
    }
}