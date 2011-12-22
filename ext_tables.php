<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Create',
	'Account Creation Interface'
);

//$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . create;
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_' .create. '.xml');



Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Edit',
	'Account Edit Interface'
);

//$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . edit;
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_' .edit. '.xml');



Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Login',
	'Login Interface'
);

//$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . login;
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_' .login. '.xml');



Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Recoverpass',
	'Recover Password Interface'
);

//$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . recoverpass;
//$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_' .recoverpass. '.xml');


t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'CIC User Registration');

// Add the type to fe_users!
t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['tx_extbase_type']['config']['items'][] = array('CIC Register User', 'Tx_Cicregister_Domain_Model_FrontendUser');
$TCA['fe_users']['types']['Tx_Cicregister_Domain_Model_FrontendUser'] = $TCA['fe_users']['types']['0'];



?>