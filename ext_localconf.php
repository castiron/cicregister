<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}




$TYPO3_CONF_VARS['FE']['eID_include']['cicregister-getUsername'] = 'EXT:cicregister/Scripts/GetUsername.php';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CIC.Cicregister',
	'Create',
	array(
		'FrontendUser' => 'new,create,edit,update,createConfirmation,createConfirmationMustValidate,validateUser',
		'FrontendUserJSON' => 'create,edit,update,createConfirmation,createConfirmationMustValidate,validateUser'
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'new,create,edit,update,createConfirmation,createConfirmationMustValidate,validateUser',
		'FrontendUserJSON' => 'create,edit,update,createConfirmation,createConfirmationMustValidate,validateUser'
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CIC.Cicregister',
	'Login',
	array(
		'Login' => 'dispatch, login, forgotPassword, handleForgotPassword, resetPassword, handleResetPassword',
	),
	// non-cacheable actions
	array(
		'Login' => 'dispatch, login, logout, forgotPassword, handleForgotPassword, resetPassword, handleResetPassword',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CIC.Cicregister',
	'Enroll',
	array(
		'FrontendUser' => 'enroll,saveEnrollment',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'enroll,saveEnrollment',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CIC.Cicregister',
	'Button',
	array(
		'FrontendUser' => 'button,create',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => '',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CIC.Cicregister',
	'ValidateEmail',
	array(
		'FrontendUser' => 'sendValidationEmail,validateUser'
	),
	array(
		'FrontendUser' => 'sendValidationEmail,validateUser'
	)
);

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Service/Authentication.php');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY, 'auth' /* sv type */, 'CIC\\Cicregister\\Service\\Authentication' /* sv key */,
	array(
		'title' => 'Cicregister Authentication',
		'description' => 'Frontend authentication service',
		'subtype' => 'getUserFE,authUserFE,getGroupsFE',
		'available' => TRUE,
		'priority' => 100,
		'quality' => 100,
		'os' => '',
		'exec' => '',
		'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Service/Authentication.php',
		'className' => 'CIC\\Cicregister\\Service\\Authentication',
	)
);
