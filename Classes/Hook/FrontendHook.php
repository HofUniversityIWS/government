<?php
namespace TYPO3\Government\Hook;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FrontendHook
 * @package TYPO3\Government\Hook
 */
class FrontendHook implements SingletonInterface {

	/**
	 * Executes TypoScript stdWrap on config.absRefPrefix.
	 *
	 * Example to set the value automatically to a sub-directory
	 * if your site is running at e.g. http://domain.com/sub-dir/):
	 * config.absRefPrefix.data = getindpenv:TYPO3_SITE_PATH
	 *
	 * @param array $parameters
	 * @param TypoScriptFrontendController $frontendController
	 */
	public function stdWrapAbsRefPrefix(array $parameters, TypoScriptFrontendController $frontendController) {
		$configuration = &$parameters['config'];

		if (empty($configuration['absRefPrefix.'])) {
			return;
		}

		$absRefPrefix = (isset($configuration['absRefPrefix']) ? $configuration['absRefPrefix'] : '');

		if (empty($frontendController->cObj)) {
			$frontendController->newCObj();
		}

		$configuration['absRefPrefix'] = $frontendController->cObj->stdWrap(
			$absRefPrefix,
			$configuration['absRefPrefix.']
		);
	}

}