<?php
namespace TYPO3\Government;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class Bootstrap
 * @package TYPO3\Government
 */
class Bootstrap {

	const PACKAGE_Key = 'government';
	const PACKAGE_Title = 'TYPO3 CMS Government Package';

	/**
	 * Initializes configuration and hooks.
	 *
	 * @return void
	 */
	static public function initialize() {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][static::PACKAGE_Key] =
			'TYPO3\\Government\\Hook\\FrontendHook->stdWrapAbsRefPrefix';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['after_setRelations'][static::PACKAGE_Key] =
			'TYPO3\\Government\\Hook\\ImportHook->updateMapping';
	}

	/**
	 * Initializes extension settings.
	 *
	 * @return void
	 */
	static public function initializeExtension() {
		ExtensionManagementUtility::addStaticFile(
			static::PACKAGE_Key,
			'Configuration/TypoScript/',
			static::PACKAGE_Title
		);
	}

}