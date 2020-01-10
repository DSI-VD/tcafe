#
# Table structure for table 'tx_tcafe_record'
#
CREATE TABLE tx_tcafe_record (
	title tinytext,
	bodytext mediumtext,
	datetime bigint(20) DEFAULT '0' NOT NULL,
	url text,

	select_single_static int(11) DEFAULT '0' NOT NULL,
	select_singlebox_static tinytext,
	select_checkbox_static tinytext,
	select_multiplesidebyside_static tinytext,
	radio_static int(11) DEFAULT '0' NOT NULL,
	checkbox_static_bool int(11) DEFAULT '0' NOT NULL,
	checkbox_static int(11) DEFAULT '0' NOT NULL,

	relation_categories int(11) DEFAULT '0' NOT NULL,
	relation_csv tinytext,
	relation_many int(11) DEFAULT '0' NOT NULL,
	relation_fal int(11) unsigned DEFAULT '0',
	relation_inline int(11) DEFAULT '0' NOT NULL,
	relation_from int(11) DEFAULT '0' NOT NULL,
	relation_to int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_tcafe_many'
#
CREATE TABLE tx_tcafe_many (
	title tinytext,
	bodytext mediumtext,
	datetime bigint(20) DEFAULT '0' NOT NULL,
	url text,
	relation_from int(11) DEFAULT '0' NOT NULL,
);

#
# Table structure for table 'tx_tcafe_record_many_mm'
#
#
CREATE TABLE tx_tcafe_record_many_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	relation_from int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign),
);

#
# Table structure for table 'tx_tcafe_record_relation_mm'
#
CREATE TABLE tx_tcafe_record_relation_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign),
);

#
# Table structure for table 'tx_tcafe_record_relation_mm'
#
CREATE TABLE tx_news_domain_model_news (
	select_single_3 tinytext,
);
