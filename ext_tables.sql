#
# Table structure for table 'tx_tcafe_record'
#
CREATE TABLE tx_tcafe_record (
	title tinytext,
	bodytext mediumtext,
	datetime bigint(20) DEFAULT '0' NOT NULL,
	url text,
	file_url text,
	relation_categories int(11) DEFAULT '0' NOT NULL,
	relation_csv tinytext,
	relation_many int(11) DEFAULT '0' NOT NULL,
	relation_fal int(11) unsigned DEFAULT '0',
	relation_inline int(11) DEFAULT '0' NOT NULL,
);


#
# Extend table structure of table 'relation_csv'
#
CREATE TABLE tx_tcafe_relation_csv (
	title tinytext,
);


#
# Table structure for table 'relation_many'
#
#
CREATE TABLE tx_tcafe_record_relation_many_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

# relation_inline
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_tcafe_relation_inline int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	select_single_3 varchar(255) DEFAULT '' NOT NULL,
);
