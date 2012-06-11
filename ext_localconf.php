<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
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

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Login',
	array(
		'Login' => 'dispatch, login, forgotPassword, handleForgotPassword, resetPassword, handleResetPassword',
	),
	// non-cacheable actions
	array(
		'Login' => 'login, logout, forgotPassword, handleForgotPassword, resetPassword, handleResetPassword',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Enroll',
	array(
		'FrontendUser' => 'enroll,saveEnrollment',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => 'enroll,saveEnrollment',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Button',
	array(
		'FrontendUser' => 'button,create',
	),
	// non-cacheable actions
	array(
		'FrontendUser' => '',
	)
);



require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/Authentication.php');

t3lib_extMgm::addService($_EXTKEY, 'auth' /* sv type */, 'Tx_Cicregister_Service_Authentication' /* sv key */,
	array(
		'title' => 'Cicregister Authentication',
		'description' => 'Frontend authentication service',
		'subtype' => 'getUserFE,authUserFE,getGroupsFE',
		'available' => TRUE,
		'priority' => 100,
		'quality' => 100,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/Authentication.php',
		'className' => 'Tx_Cicregister_Service_Authentication',
	)
);

?>