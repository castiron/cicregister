<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Create',
	'CICRegister: Create/Edit Account'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Enroll',
	'CICRegister: Group Enrollment'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Login',
	'CICRegister: Login'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Button',
	'CICRegister: Create Account Button'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'ValidateEmail',
	'CICRegister: Email Validation'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . 'create';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/CreateFlexform.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CIC User Registration');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Frontend', 'CIC User Registration Frontend Styles / JS');

// Add the type to fe_users!
\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
$TCA['fe_users']['columns']['tx_extbase_type']['config']['items'][] = array('CIC Register User', 'CIC\\Cicregister\\Domain\\Model\\FrontendUser');
$TCA['fe_users']['types']['CIC\\Cicregister\\Domain\\Model\\FrontendUser'] = $TCA['fe_users']['types']['0'];

// Add the type to fe_groups!
\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_groups');
$TCA['fe_groups']['columns']['tx_extbase_type']['config']['items'] = array();
$TCA['fe_groups']['columns']['tx_extbase_type']['config']['items'][] = array('CIC Register Usergroup', 'CIC\\Cicregister\\Domain\\Model\\FrontendUserGroup');
$TCA['fe_groups']['types']['CIC\\Cicregister\\Domain\\Model\\FrontendUserGroup'] = $TCA['fe_groups']['types']['0'];

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

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("fe_users");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("fe_users", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("fe_users", "--div--;CIC Register, tx_cicregister_state, tx_cicregister_sfdc_contact_id, tx_cicregister_sfdc_lead_id, tx_cicregister_sfdc_sync_timestamp");


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
\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA("fe_groups");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("fe_groups", $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("fe_groups", "--div--;Enrollment, tx_cicregister_enrollment_code");


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_cicregister_domain_model_invitation', 'EXT:cicregister/Resources/Private/Language/locallang_csh_tx_sjcert_domain_model_invitation.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_cicregister_domain_model_invitation');
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
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Invitation.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_cicregister_domain_model_invitation.gif'
	),
);


?>