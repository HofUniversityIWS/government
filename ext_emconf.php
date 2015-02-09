<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'The official Government Package',
	'description' => 'This package delivers a new website (page tree) and shows all out-of-the-box features of TYPO3 in terms of web-accessibility.',
	'category' => 'distribution',
	'version' => '2.0.0',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 1,
	'author' => 'Introduction Package Team',
	'author_email' => '',
	'author_company' => '',
	'constraints' => 
	array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
			'realurl' => '1.12.8-1.12.99',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);
