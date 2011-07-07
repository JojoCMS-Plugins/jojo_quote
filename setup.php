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


/* Quotes */
$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link='Jojo_Plugin_Jojo_Quote'");
if (!count($data)) {
    echo "Jojo_Plugin_Jojo_Quote: Adding <b>Quotes</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Quotes', pg_link='Jojo_Plugin_Jojo_Quote', pg_url='quotes'");
}

/* Edit Quotes */
$data = JOJO::selectQuery("SELECT * FROM {page} WHERE pg_url='admin/edit/quote'");
if (count($data) == 0) {
    echo "jojo_quote: Adding <b>Edit Quotes</b> Page to menu<br />";
    JOJO::insertQuery("INSERT INTO {page} SET pg_title='Quotes', pg_link='Jojo_Plugin_Admin_Edit', pg_url='admin/edit/quote', pg_parent=". JOJO::clean($_ADMIN_CONTENT_ID).", pg_order=3");
}

/* Edit Categories */
$data = Jojo::selectQuery("SELECT * FROM {page}  WHERE pg_url='admin/edit/quotecategory'");
if (!count($data)) {
    echo "jojo_quote: Adding <b>Quote Page Options</b> Page to Content menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Quote Page Options', pg_link='Jojo_Plugin_Admin_Edit', pg_url='admin/edit/quotecategory', pg_parent=?, pg_order=3", array($_ADMIN_CONTENT_ID));
}

/* Ensure there is a folder for uploading quote images */
$res = JOJO::RecursiveMkdir(_DOWNLOADDIR . '/quotes');
if ($res === true) {
    echo "jojo_quote: Created folder: " . _DOWNLOADDIR . '/quotes';
} elseif($res === false) {
    echo 'jojo_quote: Could not automatically create ' .  _DOWNLOADDIR . '/quotes' . 'folder on the server. Please create this folder and assign 777 permissions.';
}


/* Regenerating HTML cache for Quotes */
$quotes = JOJO::selectQuery("SELECT * FROM {quote} WHERE qt_body_code != ''");
if (count($quotes)) {
    echo 'jojo_quote: Regenerating HTML cache for quotes<br />';
    $n = count($quotes);
    for ($i=0; $i<$n; $i++) {
        $bbcode = $quotes[$i]['qt_body_code'];
        $cache = '';
        if (strpos($bbcode, '[editor:bb]') !== false) {
            /* BB Code field */
            $bbcode = preg_replace('/\\[editor:bb\\][\\r\\n]{0,2}(.*)/si', '$1', $bbcode);
            $bb = new bbconverter;
            $bb->truncateurl = 30;
            $bb->imagedropshadow = true; // JOJO::yes2true(JOJO::getOption('imagedropshadow'));
            $bb->setBBCode($bbcode);
            $cache = $bb->convert('bbcode2html');
        } elseif (strpos($bbcode, '[editor:html]') !== false) {
            $cache = str_replace('[editor:html]', '', $bbcode);
        }
        if ($cache){
            /* Update DB with the cached HTML data */
            JOJO::updateQuery("UPDATE {quote} SET qt_body='". JOJO::clean($cache) . "' WHERE quoteid=". $quotes[$i]['quoteid'] . " LIMIT 1");
        }
    }
}