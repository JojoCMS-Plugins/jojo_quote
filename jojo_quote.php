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

/*
* Core
*/

    /* Get quotes  */
    static function getItems($num=false, $start = 0, $categoryid='all', $sortby='qt_date desc', $exclude=false, $include=false) {
        global $page;
        if ($categoryid == 'all' && $include != 'alllanguages') {
            $categoryid = array();
            $sectionpages = self::getPluginPages('', $page->page['root']);
            foreach ($sectionpages as $s) {
                $categoryid[] = $s['quotecategoryid'];
            }
        }
        if (is_array($categoryid)) {
             $categoryquery = " AND qt_category IN ('" . implode("','", $categoryid) . "')";
        } else {
            $categoryquery = is_numeric($categoryid) ? " AND qt_category = '$categoryid'" : '';
        }
        /* if calling page is an quote, Get current quote, exclude from the list and up the limit by one */
        $exclude = ($exclude && Jojo::getOption('quote_sidebar_exclude_current', 'no')=='yes' && $page->page['pg_link']=='jojo_plugin_jojo_quote' && (Jojo::getFormData('id') || Jojo::getFormData('url'))) ? (Jojo::getFormData('url') ? Jojo::getFormData('url') : Jojo::getFormData('id')) : '';
        if ($num && $exclude) $num++;
        $shownumcomments = (boolean)(class_exists('Jojo_Plugin_Jojo_comment') && Jojo::getOption('comment_show_num', 'no') == 'yes');
        $query  = "SELECT q.*, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_livedate, pg_expirydate";
        $query .= $shownumcomments ? ", COUNT(com.itemid) AS numcomments" : '';
        $query .= " FROM {quote} q";
        $query .= " LEFT JOIN {quotecategory} c ON (q.qt_category=c.quotecategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .= $shownumcomments ? " LEFT JOIN {comment} com ON (com.itemid = q.quoteid AND com.plugin = 'jojo_quote')" : '';
        $query .= " WHERE 1" . $categoryquery;
        $query .= $shownumcomments ? " GROUP BY quoteid" : '';
        $query .= $num ? " ORDER BY $sortby LIMIT $start,$num" : '';
        $quotes = Jojo::selectQuery($query);
        $quotes = self::cleanItems($quotes, $exclude, $include);
        if (!$num)  $quotes = self::sortItems($quotes, $sortby);
        return $quotes;
    }

     /* get items by id - accepts either an array of ids returning a results array, or a single id returning a single result  */
    static function getItemsById($ids = false, $sortby='qt_date desc', $include=false) {
        $query  = "SELECT q.*, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_livedate, pg_expirydate";
        $query .= " FROM {quote} q";
        $query .= " LEFT JOIN {quotecategory} c ON (q.qt_category=c.quotecategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .=  is_array($ids) ? " WHERE quoteid IN ('". implode("',' ", $ids) . "')" : " WHERE quoteid=$ids";
        $items = Jojo::selectQuery($query);
        $items = self::cleanItems($items, '', $include);
        if ($items) {
            $items = is_array($ids) ? self::sortItems($items, $sortby) : $items[0];
            return $items;
        } else {
            return false;
        }
    }

   /* clean items for output */
    static function cleanItems($items, $exclude=false, $include=false) {
        $now    = time();
        foreach ($items as $k=>&$i){
            $pagedata = Jojo_Plugin_Core::cleanItems(array($i), $include);
            if (!$pagedata || $i['qt_livedate']>$now || (!empty($i['qt_expirydate']) && $i['qt_expirydate']<$now) || (!empty($i['quoteid']) && $i['quoteid']==$exclude)  || (!empty($i['qt_url']) && $i['qt_url']==$exclude)) {
                unset($items[$k]);
                continue;
            }
            $i['pagetitle'] = $pagedata[0]['title'];
            $i['pageurl']   = $pagedata[0]['url'];
            $i['id']           = $i['quoteid'];
            $i['title']        = htmlspecialchars($i['qt_title'], ENT_COMPAT, 'UTF-8', false);
            $i['seotitle']        = htmlspecialchars($i['qt_seotitle'], ENT_COMPAT, 'UTF-8', false);
            $i['author']        = htmlspecialchars($i['qt_author'], ENT_COMPAT, 'UTF-8', false);
            $i['designation'] = htmlspecialchars($i['qt_designation'], ENT_COMPAT, 'UTF-8', false);
            $i['company'] = htmlspecialchars($i['qt_company'], ENT_COMPAT, 'UTF-8', false);
            $i['weblink'] = $i['qt_weblink'];
            // Snip for the index description
            $i['bodysnip'] = array_shift(Jojo::iExplode('[[snip]]', $i['qt_body']));
            /* Strip all tags and template include code ie [[ ]] */
            $i['bodysnip'] = strpos($i['bodysnip'], '[[')!==false ? preg_replace('/\[\[.*?\]\]/', '',  $i['bodysnip']) : $i['bodysnip'];
            $i['bodyplain'] = trim(strip_tags($i['bodysnip']));
            $i['desc'] = strlen($i['bodyplain']) >400 ?  substr($mbody=wordwrap($i['bodyplain'], 400, '$$'), 0, strpos($mbody,'$$')) : $i['bodyplain'];
            $i['descriptionplain'] = strpos($i['qt_description'], '[[')!==false ? preg_replace('/\[\[.*?\]\]/', '',  $i['qt_description']) : $i['qt_description'];
            $i['descriptionplain'] = trim(strip_tags($i['descriptionplain']));
            $i['snippet']       = isset($i['snippet']) ? $i['snippet'] : '400';
            $i['thumbnail']       = isset($i['thumbnail']) ? $i['thumbnail'] : 's150';
            $i['mainimage']       = isset($i['mainimage']) ? $i['mainimage'] : 'v60000';
            $i['readmore'] = isset($i['readmore']) ? str_replace(' ', '&nbsp;', htmlspecialchars($i['readmore'], ENT_COMPAT, 'UTF-8', false)) : '&gt;&nbsp;read&nbsp;more';
            $i['date']       = $i['qt_date'];
            $i['datefriendly'] = isset($i['dateformat']) && !empty($i['dateformat']) ? strftime($i['dateformat'], $i['qt_date']) :  Jojo::formatTimestamp($i['qt_date'], "medium");
            $i['image'] = !empty($i['qt_image']) ? 'quotes/' . urlencode($i['qt_image']) : '';
            $i['url']          = self::getUrl($i['quoteid'], $i['qt_url'], $i['qt_title'], $i['pageid'], $i['qt_category']);
            $i['plugin']     = 'jojo_quote';
            unset($items[$k]['qt_body_code']);
            unset($items[$k]['qt_description_code']);
        }
        $items = array_values($items);
        return $items;
    }

    /* sort items for output */
    static function sortItems($items, $sortby=false) {
        if ($sortby) {
            $order = "date";
            $reverse = false;
            switch ($sortby) {
              case "qt_date":
                $order="date";
                $reverse = true;
                break;
              case "qt_title":
                $order="name";
                break;
              case "qt_author":
                $order="author";
                break;
              case "qt_displayorder":
                $order="order";
                break;
            }
            usort($items, array('Jojo_Plugin_Jojo_Quote', $order . 'sort'));
            $items = $reverse ? array_reverse($items) : $items;
        }
        return $items;
    }

    private static function namesort($a, $b)
    {
         if ($a['qt_title']) {
            return strcmp($a['qt_title'],$b['qt_title']);
        }
    }

    private static function authorsort($a, $b)
    {
         if ($a['qt_author']) {
            return strcmp($a['qt_author'],$b['qt_author']);
        }
    }

    private static function datesort($a, $b)
    {
         if ($a['qt_date']) {
            return strnatcasecmp($a['qt_date'],$b['qt_date']);
         }
    }

    private static function ordersort($a, $b)
    {
         if ($a['qt_displayorder']) {
            return strnatcasecmp($b['qt_displayorder'],$a['qt_displayorder']);
         }
    }

    /*
     * calculates the URL for the quote - requires the quote ID, but works without a query if given the URL or title from a previous query
     *
     */
    static function getUrl($id=false, $url=false, $title=false, $pageid=false, $category=false )
    {
        $pageprefix = Jojo::getPageUrlPrefix($pageid);
        /* URL specified */
        if (!empty($url)) {
            return $pageprefix . self::_getPrefix($category) . '/' . $url . '/';
         }
        /* ID + title specified */
        if ($id && !empty($title)) {
            return $pageprefix . self::_getPrefix($category) . '/' . $id . '/' .  Jojo::cleanURL($title) . '/';
        }
        /* use the ID to find either the URL or title */
        if ($id) {
            $quote = Jojo::selectRow("SELECT qt_url, qt_title, qt_category, p.pageid FROM {quote} q LEFT JOIN {quotecategory} c ON (q.qt_category=c.quotecategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid) WHERE quoteid = ?", array($id));
             if ($quote) {
                return self::getUrl($id, $quote['qt_url'], $quote['qt_title'], $quote['pageid'], $quote['qt_category']);
            }
         }
        /* No quote matching the ID supplied or no ID supplied */
        return false;
    }

    function _getContent()
    {
        global $smarty;
        $content = array();
        $pageid = $this->page['pageid'];
        $pageprefix = Jojo::getPageUrlPrefix($pageid);
        $smarty->assign('multilangstring', $pageprefix);

        if (class_exists('Jojo_Plugin_Jojo_comment') && Jojo::getOption('comment_subscriptions', 'no') == 'yes') {
            Jojo_Plugin_Jojo_comment::processSubscriptionEmails();
        }

        /* Are we looking at an quote or the index? */
        $id = Jojo::getFormData('id',        0);
        $url       = Jojo::getFormData('url',      '');
        $action    = Jojo::getFormData('action',   '');
        $categorydata =  Jojo::selectRow("SELECT * FROM {quotecategory} WHERE pageid = ?", $pageid);
        $categorydata['type'] = isset($categorydata['type']) ? $categorydata['type'] : 'normal';
        if ($categorydata['type']=='index') {
            $categoryid = 'all';
        } elseif ($categorydata['type']=='parent') {
            $childcategories = Jojo::selectQuery("SELECT quotecategoryid FROM {page} p  LEFT JOIN {quotecategory} c ON (c.pageid=p.pageid) WHERE pg_parent = ? AND pg_link = 'jojo_plugin_jojo_quote'", $pageid);
            foreach ($childcategories as $c) {
                $categoryid[] = $c['quotecategoryid'];
            }
            $categoryid[] = $categorydata['quotecategoryid'];
        } else {
            $categoryid = $categorydata['quotecategoryid'];
        }
        $sortby = $categorydata ? $categorydata['sortby'] : '';

        /* handle unsubscribes */
        if ($action == 'unsubscribe') {
            $code      = Jojo::getFormData('code',      '');
            $id = Jojo::getFormData('quoteid', '');
            if (Jojo_Plugin_Jojo_comment::removeSubscriptionByCode($code, $id, 'jojo_quote')) {
                $content['content'] = 'Subscription removed.<br />';
            } else {
                $content['content'] = 'This unsubscribe link is inactive, or you have already been unsubscribed.<br />';
            }
            $content['content'] .= 'Return to <a href="' . self::getUrl($id) . '">quote</a>.';
            return $content;
        }


        $quotes = self::getItems('', '', $categoryid, $sortby, $exclude=false, $include='showhidden');
        if ($id || !empty($url)) {
            /* find the current, next and previous items */
            $quote = array();
            $prevquote = array();
            $nextquote = array();
            $next = false;
            foreach ($quotes as $a) {
                if (!empty($url) && $url==$a['qt_url']) {
                    $quote = $a;
                    $next = true;
               } elseif ($id==$a['quoteid']) {
                    $quote = $a;
                    $next = true;
                } elseif ($next==true) {
                    $nextquote = $a;
                     break;
                } else {
                    $prevquote = $a;
                }
            }

            /* If the item can't be found, return a 404 */
            if (!$quote) {
                include(_BASEPLUGINDIR . '/jojo_core/404.php');
                exit;
            }

            if ($modquote = Jojo::runHook('modify_quote', array($quote))) {
                $quote = $modquote;
            }
            /* Get the specific quote */
            $id = $quote['quoteid'];

            /* calculate the next and previous quotes */
            if (Jojo::getOption('quote_next_prev') == 'yes') {
                if (!empty($nextquote)) {
                    $smarty->assign('nextquote', $nextquote);
                }
                if (!empty($prevquote)) {
                    $smarty->assign('prevquote', $prevquote);
                }
            }

            /* Get tags if used */
            if (class_exists('Jojo_Plugin_Jojo_Tags')) {
                /* Split up tags for display */
                $tags = Jojo_Plugin_Jojo_Tags::getTags('jojo_quote', $id);
                $smarty->assign('tags', $tags);

                /* generate tag cloud of tags belonging to this quote */
                $quote_tag_cloud_minimum = Jojo::getOption('quote_tag_cloud_minimum');
                if (!empty($quote_tag_cloud_minimum) && ($quote_tag_cloud_minimum < count($tags))) {
                    $itemcloud = Jojo_Plugin_Jojo_Tags::getTagCloud('', $tags);
                    $smarty->assign('itemcloud', $itemcloud);
                }
               /* get related quotes if tags plugin installed and option enabled */
                $numrelated = Jojo::getOption('quote_num_related');
                if ($numrelated) {
                    $related = Jojo_Plugin_Jojo_Tags::getRelated('jojo_quote', $id, $numrelated, 'jojo_quote'); //set the last argument to 'jojo_quote' to restrict results to only quotes
                    $smarty->assign('related', $related);
                }
            }

            /* Get Comments if used */
            if (class_exists('Jojo_Plugin_Jojo_comment') && (!isset($quote['comments']) || $quote['comments']) ) {
                /* Was a comment submitted? */
                if (Jojo::getFormData('comment', false)) {
                    Jojo_Plugin_Jojo_comment::postComment($quote);
                }
               $quotecommentsenabled = (boolean)(isset($quote['qt_comments']) && $quote['qt_comments']);
               $commenthtml = Jojo_Plugin_Jojo_comment::getComments($quote['id'], $quote['plugin'], $quote['pageid'], $quotecommentsenabled);
               $smarty->assign('commenthtml', $commenthtml);
            }

            /* Add breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = $quote['title'];
            $breadcrumb['rollover']           = $quote['desc'];
            $breadcrumb['url']                = $quote['url'];
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Assign quote content to Smarty */
            $smarty->assign('jojo_quote', $quote);

            /* Prepare fields for display */
            if (isset($quote['qt_htmllang'])) {
                // Override the language setting on this page if necessary.
                $content['pg_htmllang'] = $quote['qt_htmllang'];
                $smarty->assign('pg_htmllang', $quote['qt_htmllang']);
            }
            $content['title']            = $quote['title'];
            $content['seotitle']         = Jojo::either($quote['seotitle'], $quote['title']);
            $content['breadcrumbs']      = $breadcrumbs;

            if (!empty($quote['qt_metadesc'])) {
                $content['meta_description'] = $quote['qt_metadesc'];
            } else {
                $meta_description_template = Jojo::getOption('quote_meta_description', '[title] - [body]... ');
                $metafilters = array(
                        '[title]',
                        '[site]',
                        '[body]',
                        '[author]'
                        );
                $metafilterreplace = array(
                        $quote['title'],
                        _SITETITLE,
                        $quote['desc'],
                        $quote['author']
                        );
                        $content['meta_description'] = str_replace($metafilters, $metafilterreplace, $meta_description_template);
            }
            $content['metadescription']  = $content['meta_description'];
            if ((boolean)(Jojo::getOption('ogdata', 'no')=='yes')) {
                $content['ogtags']['description'] = $quote['desc'];
                $content['ogtags']['image'] = $quote['image'] ? _SITEURL .  '/images/' . ($quote['thumbnail'] ? $quote['thumbnail'] : 's150') . '/' . $quote['image'] : '';
                $content['ogtags']['title'] = $quote['title'];
            }

            $smarty->assign('jojo_quote', $quote);

        } else {

            /* index section */
            $pagenum = Jojo::getFormData('pagenum', 1);
            if ($pagenum[0] == 'p') {
                $pagenum = substr($pagenum, 1);
            }

            /* get number of quotes for pagination */
            $quotesperpage = Jojo::getOption('quotesperpage', 40);
            $start = ($quotesperpage * ($pagenum-1));
            $numquotes = count($quotes);
            $numpages = ceil($numquotes / $quotesperpage);
            /* calculate pagination */
            if ($numpages == 1) {
                $pagination = '';
            } elseif ($numpages == 2 && $pagenum == 2) {
                $pagination = sprintf('<a href="%s/p1/">previous...</a>', $pageprefix . self::_getPrefix($categorydata['quotecategoryid']) );
            } elseif ($numpages == 2 && $pagenum == 1) {
                $pagination = sprintf('<a href="%s/p2/">more...</a>', $pageprefix . self::_getPrefix($categorydata['quotecategoryid']) );
            } else {
                $pagination = '<ul>';
                for ($p=1;$p<=$numpages;$p++) {
                    $url = $pageprefix . self::_getPrefix($categorydata['quotecategoryid']) . '/';
                    if ($p > 1) {
                        $url .= 'p' . $p . '/';
                    }
                    if ($p == $pagenum) {
                        $pagination .= '<li>&gt; Page '.$p.'</li>'. "\n";
                    } else {
                        $pagination .= '<li>&gt; <a href="'.$url.'">Page '.$p.'</a></li>'. "\n";
                    }
                }
                $pagination .= '</ul>';
            }
            $smarty->assign('pagination', $pagination);
            $smarty->assign('pagenum', $pagenum);

            /* clear the meta description to avoid duplicate content issues */
            $content['metadescription'] = '';

            /* get quote content and assign to Smarty */
            $quotes = array_slice($quotes, $start, $quotesperpage);
            $smarty->assign('jojo_quotes', $quotes);

       }
        $content['content'] = $smarty->fetch('jojo_quote.tpl');
        return $content;
    }

    static function getPluginPages($for='', $section=0)
    {
        global $sectiondata;
        $items =  Jojo::selectQuery("SELECT c.*, p.*  FROM {quotecategory} c LEFT JOIN {page} p ON (c.pageid=p.pageid) ORDER BY pg_parent, pg_order");
        // use core function to clean out any pages based on permission, status, expiry etc
        $items =  Jojo_Plugin_Core::cleanItems($items, $for);
        foreach ($items as $k=>$i){
            if ($section && $section != $i['root']) {
                unset($items[$k]);
                continue;
            }
        }
        return $items;
    }

    public static function sitemap($sitemap)
    {
        global $page;
        /* See if we have any quote sections to display and find all of them */
        $indexes =  self::getPluginPages('sitemap');
        if (!count($indexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('quote_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to the quotes section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = Jojo_Plugin_Jojo_sitemap::_sitemapRemoveSelf($section['tree'], $indexes);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $limit = 15;
        $itemsperpage = Jojo::getOption('itemsperpage', 40);
         /* Make sitemap trees for each quotes instance found */
        foreach($indexes as $k => $i){
            $categoryid = $i['quotecategoryid'];
            $sortby = $i['sortby'];

            /* Create tree and add index and feed links at the top */
            $tree = new hktree();
            $indexurl = $i['url'];
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $tree->addNode('index', 0, $i['title'], $indexurl);
               $parent = 'index';
            }

            $items = self::getItems('', '', $categoryid, $sortby);
            $n = count($items);

            /* Trim items down to first page and add to tree*/
            $items = array_slice($items, 0, $itemsperpage);
            foreach ($items as $a) {
                $tree->addNode($a['id'], $parent, $a['title'], $a['url']);
            }

            /* Get number of pages for pagination */
            $numpages = ceil($n / $itemsperpage);
            /* calculate pagination */
            if ($numpages > 1) {
                for ($p=2; $p <= $numpages; $p++) {
                    $url = $indexurl .'p' . $p .'/';
                    $nodetitle = $i['title'] . ' (p.' . $p . ')';
                    $tree->addNode('p' . $p, $parent, $nodetitle, $url);
                }
            }
            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = $i['url'];
                $sitemap['pages']['tree'] = Jojo_Plugin_Jojo_sitemap::_sitemapAddInplace($sitemap['pages']['tree'], $tree->asArray(), $url);
            } else {
                $mldata = Jojo::getMultiLanguageData();
                /* Add to the end */
                $sitemap["quotes$k"] = array(
                    'title' => $i['title'] . (count($mldata['sectiondata'])>1 ? ' (' . ucfirst($mldata['sectiondata'][$i['root']]['name']) . ')' : ''),
                    'tree' => $tree->asArray(),
                    'order' => 3 + $k,
                    'header' => '',
                    'footer' => '',
                    );
            }
        }
        return $sitemap;
    }

    /**
    /**
     * XML Sitemap filter
     *
     * Receives existing sitemap and adds quote pages
     */
    static function xmlsitemap($sitemap)
    {
        /* Get quotes from database */
        $items = self::getItems('', '', 'all', '', '', 'alllanguages');
        $now = time();
        $indexes =  self::getPluginPages('xmlsitemap');
        $ids=array();
        foreach ($indexes as $i) {
            $ids[$i['quotecategoryid']] = true;
        }
        /* Add quotes to sitemap */
        foreach($items as $k => $a) {
            // strip out items from expired pages
            if (!isset($ids[$a['qt_category']])) {
                unset($items[$k]);
                continue;
            }
            $url = _SITEURL . '/'. $a['url'];
            $lastmod = $a['date'];
            $priority = 0.6;
            $changefreq = '';
            $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
        }
        /* Return sitemap */
        return $sitemap;
    }

    /**
     * Removes any [[snip]] tags leftover in the content before outputting
     */
    static function removesnip($data)
    {
        $data = str_ireplace('[[snip]]','',$data);
        return $data;
    }

    /**
     * Get the url prefix for a particular part of this plugin
     */
    static function _getPrefix($categoryid=false) {
        $cacheKey = 'quote';
        $cacheKey .= ($categoryid) ? $categoryid : 'false';

        /* Have we got a cached result? */
        static $_cache;
        if (isset($_cache[$cacheKey])) {
            return $_cache[$cacheKey];
        }

        /* Cache some stuff */
        $res = Jojo::selectRow("SELECT p.pageid, pg_title, pg_url FROM {page} p LEFT JOIN {quotecategory} c ON (c.pageid=p.pageid) WHERE `quotecategoryid` = '$categoryid'");
        if ($res) {
            $_cache[$cacheKey] = !empty($res['pg_url']) ? $res['pg_url'] : $res['pageid'] . '/' . $res['pg_title'];
        } else {
            $_cache[$cacheKey] = '';
        }
        return $_cache[$cacheKey];
    }

    static function getPrefixById($id=false) {
        if ($id) {
            $data = Jojo::selectRow("SELECT quotecategoryid, pageid FROM {quote} LEFT JOIN {quotecategory} ON (qt_category=quotecategoryid) WHERE quoteid = ?", array($id));
            if ($data) {
                $fullprefix = Jojo::getPageUrlPrefix($data['pageid']) . self::_getPrefix($data['quotecategoryid']);
                return $fullprefix;
            }
        }
        return false;
    }

    function getCorrectUrl()
    {
        global $page;
        $pageid  = $page->page['pageid'];
        $id = Jojo::getFormData('id',     0);
        $url       = Jojo::getFormData('url',    '');
        $action    = Jojo::getFormData('action', '');
        $pagenum   = Jojo::getFormData('pagenum', 1);

        $data = Jojo::selectRow("SELECT quotecategoryid FROM {quotecategory} WHERE pageid=?", $pageid);
        $categoryid = !empty($data['quotecategoryid']) ? $data['quotecategoryid'] : '';

        if ($pagenum[0] == 'p') {
            $pagenum = substr($pagenum, 1);
        }

        /* unsubscribing */
        if ($action == 'unsubscribe') {
            return _PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $correcturl = self::getUrl($id, $url, null, $pageid, $categoryid);

        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        /* index - default */
        return parent::getCorrectUrl();
    }

    static public function isUrl($uri)
    {
        $prefix = false;
        $getvars = array();
        /* Check the suffix matches and extract the prefix */
        if (preg_match('#^(.+)/unsubscribe/([0-9]+)/([a-zA-Z0-9]{16})$#', $uri, $matches)) {
            /* "$prefix/[action:unsubscribe]/[quoteid:integer]/[code:[a-zA-Z0-9]{16}]" eg "quotes/unsubscribe/34/7MztlFyWDEKiSoB1/" */
            $prefix = $matches[1];
            $getvars = array(
                        'action' => 'unsubscribe',
                        'quoteid' => $matches[2],
                        'code' => $matches[3]
                        );
        /* Check for standard plugin url format matches */
        } elseif ($uribits = parent::isPluginUrl($uri)) {
            $prefix = $uribits['prefix'];
            $getvars = $uribits['getvars'];
        } else {
            return false;
        }
        /* Check the prefix matches */
        if ($res = self::checkPrefix($prefix)) {
            /* If full uri matches a prefix it's an index page so ignore it and let the page plugin handle it */
            if (self::checkPrefix(trim($uri, '/'))) {
                return false;
            }

            /* The prefix is good, pass through uri parts */
            foreach($getvars as $k => $v) {
                $_GET[$k] = $v;
            }
            return true;
        }
        return false;
    }

    /**
     * Check if a prefix is a prefix for this plugin
     */
    static public function checkPrefix($prefix)
    {
        static $_prefixes, $categories;
        if (!isset($categories)) {
            /* Initialise cache */
            $categories = array(false);
            $categories = array_merge($categories, Jojo::selectAssoc("SELECT quotecategoryid, quotecategoryid as quotecategoryid2 FROM {quotecategory}"));
            $_prefixes = array();
        }
        /* Check if it's in the cache */
        if (isset($_prefixes[$prefix])) {
            return $_prefixes[$prefix];
        }
        /* Check everything */
        foreach($categories as $category) {
            $testPrefix = self::_getPrefix($category);
            $_prefixes[$testPrefix] = true;
            if ($testPrefix == $prefix) {
                /* The prefix is good */
                return true;
            }
        }
        /* Didn't match */
        $_prefixes[$testPrefix] = false;
        return false;
    }

    // Sync the category data over *to* the page table
    static function admin_action_after_save_quotecategory($id) {
        if (!Jojo::getFormData('fm_pageid', 0)) {
            // no pageid set for this category (either it's a new category or maybe the original page was deleted)
            self::sync_category_to_page($id);
       }
    }

    // Sync the category data over *from* the page table
    static function admin_action_after_save_page($id) {
        if (strtolower(Jojo::getFormData('fm_pg_link',    ''))=='jojo_plugin_jojo_quote') {
           self::sync_page_to_category($id);
       }
    }

    static function sync_category_to_page($catid) {
        // add a new hidden page for this category and make up a title
            $newpageid = Jojo::insertQuery(
            "INSERT INTO {page} SET pg_title = ?, pg_link = ?, pg_url = ?, pg_parent = ?, pg_status = ?",
            array(
                'Orphaned Quotes',  // Title
                'jojo_plugin_jojo_quote',  // Link
                'orphaned-quotes',  // URL
                0,  // Parent - don't do anything smart, just put it at the top level for now
                'hidden' // hide new page so it doesn't show up on the live site until it's been given a proper title and url
            )
        );
        // If we successfully added the page, update the category with the new pageid
        if ($newpageid) {
            jojo::updateQuery(
                "UPDATE {quotecategory} SET pageid = ? WHERE quotecategoryid = ?",
                array(
                    $newpageid,
                    $catid
                )
            );
       }
       return true;
    }

    static function sync_page_to_category($pageid) {
        // Get the list of categories by page id
        $categories = jojo::selectAssoc("SELECT pageid AS id, pageid FROM {quotecategory}");
        // no category for this page id
        if (!count($categories) || !isset($categories[$pageid])) {
            jojo::insertQuery("INSERT INTO {quotecategory} (pageid) VALUES ('$pageid')");
        }
        return true;
    }

    /**
     * Site Search
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        $searchfields = array(
            'plugin' => 'jojo_quote',
            'table' => 'quote',
            'idfield' => 'quoteid',
            'languagefield' => 'qt_htmllang',
            'primaryfields' => 'qt_title',
            'secondaryfields' => 'qt_title,qt_body,qt_description,qt_author,qt_company,qt_designation',
        );
        $rawresults =  Jojo_Plugin_Jojo_search::searchPlugin($searchfields, $keywords, $language, $booleankeyword_str);
        $data = $rawresults ? self::getItemsById(array_keys($rawresults)) : '';
        if ($data) {
            foreach ($data as $result) {
                $result['relevance'] = $rawresults[$result['id']]['relevance'];
                $result['type'] = $result['pagetitle'];
                $result['tags'] = isset($rawresults[$result['id']]['tags']) ? $rawresults[$result['id']]['tags'] : '';
                $results[] = $result;
            }
        }
        /* Return results */
        return $results;
    }

    /**
     * Newsletter content
     */
    static function newslettercontent($contentarray, $newletterid=false)
    {
        /* Get all the articles for this newsletter */
        if ($newletterid) {
            $quoteids = Jojo::selectAssoc('SELECT n.order, q.quoteid FROM {quote} q, {newsletter_quote} n WHERE q.quoteid = n.quoteid AND n.newsletterid = ? ORDER BY n.order', $newletterid);
            if ($quoteids) {
                $items = self::getItemsById($quoteids, '', 'showhidden');
                foreach($items as &$a) {
                    $a['title'] = mb_convert_encoding($a['qt_title'], 'HTML-ENTITIES', 'UTF-8');
                    $a['bodyplain'] = mb_convert_encoding($a['bodyplain'], 'HTML-ENTITIES', 'UTF-8');
                    $a['body'] = mb_convert_encoding($a['qt_body'], 'HTML-ENTITIES', 'UTF-8');
                    $a['imageurl'] = rawurlencode($a['image']);
                    foreach ($quoteids as $k => $i) {
                        if ($i==$a['quoteid']) {
                            $contentarray['quotes'][$k] = $a;
                        }
                    }
                }
            }
        }
        /* Return results */
        return $contentarray;
    }

/*
* Tags
*/
    static function getTagSnippets($ids)
    {
        $snippets = self::getItemsById($ids);
        return $snippets;
    }
}