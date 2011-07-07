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

/** Example usage in theme template:
            {if $quotes}
            <div id="quotebox" class="sidebarbox">
                <h2>Testimonials</h2>
                {foreach from=$quotes key=key item=quote}
                    <h3>{$quote.title}</h3>
                    <div class="quote">
                        {if $quote.imageurl}<img class="quoteimage" src="{$quote.imageurl}" alt="{$quote.author}" />{/if}
                        <div class="quotebody">{$quote.body}</div>
                        <div class = "quotecredit">{$quote.author}</div>
                        <p>{$quote.description|truncate:150:"..."}<br />
                        <a class="links" href="{$quote.url}">&gt; Read more</a></p>
                     </div>
                {/foreach}
                <p class="links"><a href='{$SITEURL}/{if _MULTILANGUAGE}{$lclanguage}/{/if}{$quoteshome}/'>&gt;  See all testimonials</a></p>
            </div>
            {/if}
*/