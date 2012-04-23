CREATE TABLE fe_users (
	tx_cicregister_sfdc_contact_id varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_sfdc_lead_id varchar(255) DEFAULT '' NOT NULL,
	tx_cicregister_sfdc_sync_timestamp varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE fe_groups (
	tx_cicregister_enrollment_code varchar(255) DEFAULT '' NOT NULL
);
