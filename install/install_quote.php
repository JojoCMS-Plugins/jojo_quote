<?php

$table = 'quote';
$query = "
    CREATE TABLE {quote} (
      `qt_category` int(11) NOT NULL default '0',
      `quoteid` int(11) NOT NULL auto_increment,
      `qt_title` varchar(255) NOT NULL default '',
      `qt_date` int(11) default '0',
      `qt_url` varchar(255) NOT NULL default '',
      `qt_body` text NULL,
      `qt_body_code` text NULL,
      `qt_image` varchar(255) NOT NULL default '',
      `qt_author` varchar(255) NOT NULL default '',
      `qt_designation` varchar(255) NOT NULL default '',
	  `qt_company` varchar(255) NOT NULL default '',
	  `qt_weblink` varchar(255) NOT NULL default '',
      `qt_description` text NULL,
      `qt_description_code` text NULL,
      `qt_language` varchar(100) NOT NULL default 'en',
      `qt_htmllang` varchar(100) NOT NULL default '',
      `qt_displayorder` int(11) NOT NULL default '0',
      `qt_livedate` int(11) NOT NULL default '0',
      `qt_expirydate` int(11) NOT NULL default '0',
      `qt_seotitle` varchar(255) NOT NULL default '',
      `qt_metadesc` varchar(255) NOT NULL default '',";
if (class_exists('Jojo_Plugin_Jojo_Tags')) {
$query .= "
      `qt_tags` text NULL,";
}
if (class_exists('Jojo_Plugin_Jojo_comment')) {
    $query .= "
     `qt_comments` tinyint(1) NOT NULL default '1',";
}
$query .= "
      PRIMARY KEY  (`quoteid`),
      FULLTEXT KEY `title` (`qt_title`),
      FULLTEXT KEY `body` (`qt_title`,`qt_body`, `qt_author`, `qt_description`, `qt_designation`, `qt_company`)
    ) TYPE=MyISAM ;";

/* Convert mysql date format to unix timestamps */
if (Jojo::tableExists($table) && Jojo::getMySQLType($table, 'qt_date') == 'date') {
    date_default_timezone_set(Jojo::getOption('sitetimezone', 'Pacific/Auckland'));
    $items = Jojo::selectQuery("SELECT quoteid, qt_date FROM {quote}");
    Jojo::structureQuery("ALTER TABLE  {quote} CHANGE  `qt_date`  `qt_date` INT(11) NOT NULL DEFAULT '0'");
    foreach ($items as $k => $a) {
        if ($a['qt_date']!='0000-00-00') {
            $timestamp = strtotime($a['qt_date']);
        } else {
            $timestamp = 0;
        }
       Jojo::updateQuery("UPDATE {quote} SET qt_date=? WHERE quoteid=?", array($timestamp, $a['quoteid']));
    }
}

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_quote: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_quote: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);

$table = 'quotecategory';
$query = "
    CREATE TABLE {quotecategory} (
      `quotecategoryid` int(11) NOT NULL auto_increment,
      `sortby` ENUM('qt_title','qt_date','qt_displayorder','qt_author') NOT NULL default 'qt_date',
      `addtonav` tinyint(1) NOT NULL default '0',
      `pageid` int(11) NOT NULL default '0',
      `type` enum('normal','parent','index') NOT NULL default 'normal',
      `showdate` tinyint(1) NOT NULL default '0',
      `dateformat` varchar(255) NOT NULL default '%e %b %Y',
      `snippet` varchar(255) NOT NULL default '400',
      `readmore` varchar(255) NOT NULL default '> Read more',
      `thumbnail` varchar(255) NOT NULL default 's150',
      `mainimage` varchar(255) NOT NULL default 'v60000',";
if (class_exists('Jojo_Plugin_Jojo_comment')) {
    $query .= "
     `comments` tinyint(1) NOT NULL default '0',";
}
$query .= "
      PRIMARY KEY  (`quotecategoryid`),
      KEY `id` (`pageid`)
    ) TYPE=MyISAM ;";

/* Prepare for adding default category to pre-category installs  */
$quotepage = array();
if (!Jojo::tableExists($table)) {
    $quotepage = Jojo::selectQuery("SELECT pageid FROM {page} WHERE pg_link='Jojo_Plugin_Jojo_Quote' OR pg_link = 'jojo_plugin_jojo_quote'");
}

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_quote: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_quote: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table, $result['different']);

/* add default category to pre-category installs  */
if ($quotepage) {
    $quoteid = $quotepage[0]['pageid'];
    $catid = Jojo::insertQuery("INSERT INTO {quotecategory} (pageid) VALUES ('$quoteid')");
    Jojo::updateQuery("UPDATE {quote} SET qt_category=?", array($catid));
}

/* add relational table for use by newsletter plugin if present */
if (class_exists('Jojo_Plugin_Jojo_Newsletter')) {
    $table = 'newsletter_quote';
    $query = "CREATE TABLE {newsletter_quote} (
      `newsletterid` int(11) NOT NULL,
      `quoteid` int(11) NOT NULL,
      `order` int(11) NOT NULL
    );";
    
    /* Check table structure */
    $result = Jojo::checkTable($table, $query);
    
    /* Output result */
    if (isset($result['created'])) {
        echo sprintf("jojo_newsletter_phplist: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
    }
    
    if (isset($result['added'])) {
        foreach ($result['added'] as $col => $v) {
            echo sprintf("jojo_newsletter_phplist: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
        }
    }
    
    if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);

    /* add the new articles field to the newsletter table if it does not exist */
    if (Jojo::tableExists('newsletter') && !Jojo::fieldExists('newsletter', 'quotes')) {
        Jojo::structureQuery("ALTER TABLE `newsletter` ADD `quotes` TEXT NOT NULL;");
    }

}
