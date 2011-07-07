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

$_provides['pluginClasses'] = array(
        'Jojo_Plugin_Jojo_Quote' => 'Quotes - Quote Listing and View'
        );

/* Register URI handlers */
Jojo::registerURI(null, 'jojo_plugin_jojo_quote', 'isUrl');

/* Sitemap filter */
Jojo::addFilter('jojo_sitemap', 'sitemap', 'jojo_quote');

/* XML Sitemap filter */
Jojo::addFilter('jojo_xml_sitemap', 'xmlsitemap', 'jojo_quote');

/* Search Filter */
if (class_exists('Jojo_Plugin_Jojo_search')) {
    Jojo::addFilter('jojo_search', 'search', 'jojo_quote');
}
/* Newsletter content Filter */
if (class_exists('Jojo_Plugin_Jojo_Newsletter') && Jojo::tableExists('newsletter_quote')) {
    Jojo::addFilter('jojo_newslettercontent', 'newslettercontent', 'jojo_quote');
}

/* Content Filter */
Jojo::addFilter('content', 'removesnip', 'jojo_quote');

/* capture the button press in the admin section */
Jojo::addHook('admin_action_after_save_quote', 'admin_action_after_save_quote', 'jojo_quote');
Jojo::addHook('admin_action_after_save_page', 'admin_action_after_save_page', 'jojo_quote');
Jojo::addHook('admin_action_after_save_quotecategory', 'admin_action_after_save_quotecategory', 'jojo_quote');


$_options[] = array(
    'id' => 'quote_tag_cloud_minimum',
    'category' => 'Quotes',
    'label' => 'Minimum tags to form cloud',
    'description' => 'On the article pages, a tag cloud will be formed from tags if this number of tags is met (otherwise a plain text list of tags is shown). Set to zero to always use the plain text list.',
    'type' => 'integer',
    'default' => '0',
    'options' => '',
    'plugin' => 'jojo_quote'
);


$_options[] = array(
    'id' => 'quotesperpage',
    'category' => 'Quotes',
    'label' => 'Quotes per page on index',
    'description' => 'The number of quotes to show on the Quotes index page before paginating',
    'type' => 'integer',
    'default' => '20',
    'options' => '',
    'plugin' => 'jojo_quote'
);

$_options[] = array(
    'id' => 'quote_next_prev',
    'category' => 'Quotes',
    'label' => 'Show Next / Previous links',
    'description' => 'Show a link to the next and previous quote at the top of each quote page',
    'type' => 'radio',
    'default' => 'yes',
    'options' => 'yes,no',
    'plugin' => 'jojo_quote'
);

$_options[] = array(
    'id' => 'quote_num_related',
    'category' => 'Quotes',
    'label' => 'Show Related Quotes',
    'description' => 'The number of related quotes to show at the bottom of each article (0 means do not show)',
    'type' => 'integer',
    'default' => '0',
    'options' => '',
    'plugin' => 'jojo_quote'
);

$_options[] = array(
    'id'          => 'quote_num_sidebar_articles',
    'category'    => 'Quotes',
    'label'       => 'Number of quote teasers to show in the sidebar',
    'description' => 'The number of quotes to be displayed as snippets in a teaser box on other pages)',
    'type'        => 'integer',
    'default'     => '1',
    'options'     => '',
    'plugin'      => 'jojo_quote'
);

$_options[] = array(
    'id'          => 'quote_inplacesitemap',
    'category'    => 'Quotes',
    'label'       => 'Quotes sitemap location',
    'description' => 'Show quotes as a separate list on the site map, or in-place on the page list',
    'type'        => 'radio',
    'default'     => 'inplace',
    'options'     => 'separate,inplace',
    'plugin'      => 'jojo_quote'
);

$_options[] = array(
    'id'          => 'quote_sidebar_exclude_current',
    'category'    => 'Quotes',
    'label'       => 'Exclude current Quote from list',
    'description' => 'Exclude the Quote from the sidebar list when on that Quotes page',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_quote'
);
