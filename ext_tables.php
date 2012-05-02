<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Create',
	'CICRegister: Create/Edit Account'
);

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Enroll',
	'CICRegister: Group Enrollment'
);

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Login',
	'CICRegister: Login'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . 'create';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/CreateFlexform.xml');

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CIC User Registration');

// Add the type to fe_users!
t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['tx_extbase_type']['config']['items'][] = array('CIC Register User', 'Tx_Cicregister_Domain_Model_FrontendUser');
$TCA['fe_users']['types']['Tx_Cicregister_Domain_Model_FrontendUser'] = $TCA['fe_users']['types']['0'];

// Add the type to fe_groups!
t3lib_div::loadTCA('fe_groups');
$TCA['fe_groups']['columns']['tx_extbase_type']['config']['items'] = array();
$TCA['fe_groups']['columns']['tx_extbase_type']['config']['items'][] = array('CIC Register Usergroup', 'Tx_Cicregister_Domain_Model_FrontendUserGroup');
$TCA['fe_groups']['types']['Tx_Cicregister_Domain_Model_FrontendUserGroup'] = $TCA['fe_groups']['types']['0'];

$tempColumns = Array(
	'tx_cicregister_sfdc_contact_id' => array(
		'exclude' => 0,
		'label' => 'Salesforce Contact ID',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
	'tx_cicregister_sfdc_lead_id' => array(
		'exclude' => 0,
		'label' => 'Salesforce Lead ID',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
	'tx_cicregister_sfdc_sync_timestamp' => array(
		'exclude' => 0,
		'label' => 'Salesforce Sync Timestamp',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("fe_users", "--div--;Salesforce, tx_cicregister_sfdc_contact_id, tx_cicregister_sfdc_lead_id, tx_cicregister_sfdc_sync_timestamp");


// Add enrollment code to FE Groups
$tempColumns = array(
	'tx_cicregister_enrollment_code' => array(
		'exclude' => 0,
		'label' => 'Enrollment Code',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim,unique'
		),
	),
);
t3lib_div::loadTCA("fe_groups");
t3lib_extMgm::addTCAcolumns("fe_groups", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("fe_groups", "--div--;Enrollment, tx_cicregister_enrollment_code");

?>