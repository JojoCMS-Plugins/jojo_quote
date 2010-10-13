<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007 Harvey Kane <code@ragepank.com>
 * Copyright 2007 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

if (!defined('_MULTILANGUAGE')) {
    define('_MULTILANGUAGE', Jojo::getOption('multilanguage', 'no') == 'yes');
}

$table = 'quote';
$o = 1;


$tabledisplay = (_MULTILANGUAGE) ? ", ' (', qt_language, ')'" : '';

$default_td[$table]['td_displayfield'] = "CONCAT(qt_author, ': ', qt_title $tabledisplay)";
$default_td[$table]['td_parentfield'] = '';
$default_td[$table]['td_orderbyfields'] = 'qt_title';
$default_td[$table]['td_topsubmit'] = 'yes';
$default_td[$table]['td_filter'] = 'yes';
$default_td[$table]['td_deleteoption'] = 'yes';
$default_td[$table]['td_menutype'] = 'list';
$default_td[$table]['td_categoryfield'] = '';
$default_td[$table]['td_categorytable'] = '';
$default_td[$table]['td_group1'] = '';
$default_td[$table]['td_help'] = 'Quotes are managed from here.  The system will comfortably take many hundreds of quotes, but you may want to manually delete anything that is no longer relevant, or correct.';

//Quote ID
$default_fd[$table]['quoteid'] = array(
        'fd_order' => $o++,
        'fd_name' => "ID",
        'fd_type' => "readonly",
        'fd_default' => "0",
        'fd_help' => 'A unique ID, automatically assigned by the system',
        'fd_mode' => 'advanced',
        'fd_tabname' => 'Content',
    );

//Title
$field = 'qt_title';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'text';
$default_fd[$table][$field]['fd_required'] = 'yes';
$default_fd[$table][$field]['fd_size'] = '60';
$default_fd[$table][$field]['fd_help'] = 'Title of the quote.';
$default_fd[$table][$field]['fd_mode'] = 'basic';
$default_fd[$table][$field]['fd_tabname'] = 'Content';


//Date
$field = 'qt_date';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_type']      = 'date';
$default_fd[$table][$field]['fd_default']   = 'NOW()';
$default_fd[$table][$field]['fd_help']      = 'Date the quote was published (defaults to Today)';
$default_fd[$table][$field]['fd_mode']      = 'standard';
$default_fd[$table][$field]['fd_tabname']   = 'Content';

//URL
$field = 'qt_url';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_type']      = 'internalurl';
$default_fd[$table][$field]['fd_required']  = 'no';
$default_fd[$table][$field]['fd_size']      = '20';
$default_fd[$table][$field]['fd_help']      = 'A customized URL - leave blank to create a URL from the title of the quote';
/* the actual URL may vary here, so query the database to be sure */
$default_fd[$table][$field]['fd_options']   = 'quotes';
foreach (Jojo::listPlugins('jojo_quote.php') as $pluginfile) {
    require_once($pluginfile);
    $default_fd[$table][$field]['fd_options'] = JOJO_Plugin_Jojo_quote::_getPrefix();
    break;
}
$default_fd[$table][$field]['fd_mode']      = 'standard';
$default_fd[$table][$field]['fd_tabname']   = 'Content';


//Body Code
$field = 'qt_body_code';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_name'] = 'Quote Content';
$default_fd[$table][$field]['fd_type'] = 'texteditor';
$default_fd[$table][$field]['fd_options'] = 'qt_body';
$default_fd[$table][$field]['fd_rows'] = '10';
$default_fd[$table][$field]['fd_cols'] = '50';
$default_fd[$table][$field]['fd_help'] = 'The editor code for the body text.';
$default_fd[$table][$field]['fd_mode'] = 'basic';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Body
$field = 'qt_body';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'hidden';
$default_fd[$table][$field]['fd_rows'] = '10';
$default_fd[$table][$field]['fd_cols'] = '50';
$default_fd[$table][$field]['fd_help'] = 'The body of the quote.';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Author
$field = 'qt_author';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'text';
$default_fd[$table][$field]['fd_options'] = '';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The author of the quote';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Designation
$field = 'qt_designation';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'text';
$default_fd[$table][$field]['fd_options'] = '';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The designation of the author';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Company
$field = 'qt_company';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'text';
$default_fd[$table][$field]['fd_options'] = '';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The company of the author';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Website Link
$field = 'qt_weblink';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'url';
$default_fd[$table][$field]['fd_options'] = '';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'Author website URL';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Company URL
$field = 'qt_companylink';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'text';
$default_fd[$table][$field]['fd_options'] = '';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The company of the author';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Image
$field = 'qt_image';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'fileupload';
$default_fd[$table][$field]['fd_help'] = 'An image for the quote (eg of the author), if  available';
$default_fd[$table][$field]['fd_mode'] = 'standard';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Body Code
$field = 'qt_description_code';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_name'] = 'Description Content';
$default_fd[$table][$field]['fd_type'] = 'texteditor';
$default_fd[$table][$field]['fd_options'] = 'qt_description';
$default_fd[$table][$field]['fd_rows'] = '10';
$default_fd[$table][$field]['fd_cols'] = '50';
$default_fd[$table][$field]['fd_help'] = 'The editor code for the description text.';
$default_fd[$table][$field]['fd_mode'] = 'basic';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Body
$field = 'qt_description';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type'] = 'hidden';
$default_fd[$table][$field]['fd_rows'] = '10';
$default_fd[$table][$field]['fd_cols'] = '50';
$default_fd[$table][$field]['fd_help'] = 'The body of the quote description.';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//Language
$field = 'qt_language';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_name'] = 'Language/country';
$default_fd[$table][$field]['fd_type'] = 'dblist';
$default_fd[$table][$field]['fd_options'] = 'lang_country';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The language/country of the quote';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';

//HTML-language
$field = 'qt_htmllang';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_name'] = 'HTML language';
$default_fd[$table][$field]['fd_type'] = 'dblist';
$default_fd[$table][$field]['fd_options'] = 'language';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size'] = '20';
$default_fd[$table][$field]['fd_help'] = 'The language of the quote (if different from the default language for the language/country above)';
$default_fd[$table][$field]['fd_mode'] = 'advanced';
$default_fd[$table][$field]['fd_tabname'] = 'Content';


/* SEO TAB */


// SEO Title Field
$default_fd[$table]['qt_seotitle'] = array(
        'fd_name' => "SEO Title",
        'fd_type' => "text",
        'fd_options' => "seotitle",
        'fd_size' => "60",
        'fd_help' => "Title of the quote - it may be worth including your search phrase at the beginning of the title to improve rankings for that phrase.",
        'fd_order' => "1",
        'fd_tabname' => "SEO",
        'fd_mode' => "standard",
    );

// META Description Field
$default_fd[$table]['qt_metadesc'] = array(
        'fd_name' => "META Description",
        'fd_type' => "textarea",
        'fd_options' => "metadescription",
        'fd_rows' => "4",
        'fd_cols' => "60",
        'fd_help' => "A META Description for the quote. By default, a meta description is auto-generated, but hand-written descriptions are always better. This is a recommended field.",
        'fd_order' => "2",
        'fd_tabname' => "SEO",
        'fd_mode' => "advanced",
    );

/* TAGS TAB */
$o = 1;

//Tags
$field = 'qt_tags';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_name']      = 'Tags';
$default_fd[$table][$field]['fd_type']      = 'tag';
$default_fd[$table][$field]['fd_required']  = 'no';
$default_fd[$table][$field]['fd_options']   = 'jojo_quote';
$default_fd[$table][$field]['fd_showlabel'] = 'no';
$default_fd[$table][$field]['fd_tabname']   = 'Tags';
$default_fd[$table][$field]['fd_help']      = 'A list of words describing the quote';
$default_fd[$table][$field]['fd_mode']      = 'standard';

/* SCHEDULING TAB */
$o = 1;
//Go Live Date
$field = 'qt_livedate';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_name']      = 'Go Live Date';
$default_fd[$table][$field]['fd_type']      = 'unixdate';
$default_fd[$table][$field]['fd_default']   = 'NOW()';
$default_fd[$table][$field]['fd_help']      = 'The quote will not appear on the site until this date';
$default_fd[$table][$field]['fd_mode']      = 'standard';
$default_fd[$table][$field]['fd_tabname']   = 'Scheduling';

//Expiry Date
$field = 'qt_expirydate';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_name']      = 'Expiry Date';
$default_fd[$table][$field]['fd_type']      = 'unixdate';
$default_fd[$table][$field]['fd_default']   = 'NOW()';
$default_fd[$table][$field]['fd_help']      = 'The page will be removed from the site after this date';
$default_fd[$table][$field]['fd_mode']      = 'standard';
$default_fd[$table][$field]['fd_tabname']   = 'Scheduling';
