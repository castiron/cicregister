<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Create',
	array(
		
	),
	// non-cacheable actions
	array(
		
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Edit',
	array(
		
	),
	// non-cacheable actions
	array(
		
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Login',
	array(
		
	),
	// non-cacheable actions
	array(
		
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Recoverpass',
	array(
		
	),
	// non-cacheable actions
	array(
		
	)
);

?>