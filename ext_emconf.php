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
	'author' => 'Government Package Team',
	'author_email' => '',
	'author_company' => '',
	'constraints' => 
	array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
			'realurl' => '1.12.8-0.0.0',
			'a21glossary' => '6.2.0-0.0.0',
			'contrast' => '0.1.4-0.0.0',
			'menu_balancer' => '0.9.1-0.0.0',
			'news' => '3.0.1-0.0.0',
			't3colorbox' => '3.0.0-0.0.0',
			'wt_spamshield' => '1.3.0-0.0.0',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);
