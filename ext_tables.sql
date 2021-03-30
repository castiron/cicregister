CREATE TABLE fe_users (
	tx_cicregister_sfdc_contact_id varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_sfdc_lead_id varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_sfdc_sync_timestamp varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_state varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_used_honeypot tinyint(4) unsigned DEFAULT '0' NOT NULL,
);

CREATE TABLE fe_groups (
	tx_cicregister_enrollment_code varchar(255) DEFAULT '' NOT NULL
);

#
# Ta6ble structure for table 'tx_cicregister_domain_model_invitation'
#
CREATE TABLE tx_cicregister_domain_model_invitation (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	email tinytext NOT NULL,
	invited_by int(11) unsigned DEFAULT '0',
	expires_on int(11) unsigned DEFAULT '0' NOT NULL,
	accepted int(4) DEFAULT '0' NOT NULL,
	on_acceptance text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);