<?php

$table = 'quote';
$query = "
    CREATE TABLE {quote} (
      `quoteid` int(11) NOT NULL auto_increment,
      `qt_title` varchar(255) NOT NULL default '',
      `qt_date` date default NULL,
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
      `qt_tags` text NULL,
      `qt_livedate` int(11) NOT NULL default '0',
      `qt_expirydate` int(11) NOT NULL default '0',
      `qt_seotitle` varchar(255) NOT NULL default '',
      `qt_metadesc` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`quoteid`),
      FULLTEXT KEY `title` (`qt_title`),
      FULLTEXT KEY `body` (`qt_title`,`qt_body`, `qt_author`, `qt_description`, `qt_designation`, `qt_company`)
    ) TYPE=MyISAM ;";


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
