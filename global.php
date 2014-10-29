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

/* Create Quotes array for sidebar based on the page language, shuffle them randomly and display as many as are set in options (default is 1)*/
if (Jojo::getOption('quote_num_sidebar_articles') >= 1) {

    $exclude = (boolean)(Jojo::getOption('quote_sidebar_exclude_current', 'no')=='yes' );
    $quotes = Jojo_Plugin_Jojo_Quote::getItems('', '', 'all', '', $exclude);
    shuffle($quotes);
    $quotes = array_slice($quotes, 0, Jojo::getOption('quote_num_sidebar_articles', 1)); 
    $smarty->assign('quotes', $quotes);
}
