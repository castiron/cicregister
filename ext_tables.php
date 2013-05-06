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


Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Button',
	'CICRegister: Create Account Button'
);

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'ValidateEmail',
	'CICRegister: Email Validation'
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
	'tx_cicregister_state' => array(
		'exclude' => 0,
		'label' => 'State',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		),
	),
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users", $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes("fe_users", "--div--;CIC Register, tx_cicregister_state, tx_cicregister_sfdc_contact_id, tx_cicregister_sfdc_lead_id, tx_cicregister_sfdc_sync_timestamp");


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


t3lib_extMgm::addLLrefForTCAdescr('tx_cicregister_domain_model_invitation', 'EXT:cicregister/Resources/Private/Language/locallang_csh_tx_sjcert_domain_model_invitation.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_cicregister_domain_model_invitation');
$TCA['tx_cicregister_domain_model_invitation'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:cicregister/Resources/Private/Language/locallang_db.xml:tx_cicregister_domain_model_invitation',
		'label' => 'email',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'searchFields' => 'email',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Invitation.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_cicregister_domain_model_invitation.gif'
	),
);


?>