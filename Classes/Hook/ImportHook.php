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
use TYPO3\CMS\Impexp\ImportExport;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Dbal\Database\DatabaseConnection;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;

/**
 * Class ImportHook
 * @package TYPO3\Government\Hook
 */
class ImportHook implements SingletonInterface {

	/**
	 * @var array|int[]
	 */
	protected $pageIds;

	/**
	 * @var array
	 */
	protected $updates;

	/**
	 * Updates the page id mapping in defined constants to be processed.
	 *
	 * @param array $parameters
	 * @param ImportExport $importExport
	 */
	public function updateMapping(array $parameters, ImportExport $importExport) {
		if (!isset($importExport->import_mapId['pages']) || !isset($importExport->import_mapId['sys_template'])) {
			return;
		}

		$templateIds = $importExport->import_mapId['sys_template'];
		$this->handlePageIds($importExport->import_mapId['pages']);
		$firstPageId = current($this->pageIds);

		foreach ($templateIds as $templateId) {
			$template = BackendUtility::getRecord('sys_template', $templateId);
			if ((int)$template['pid'] === $firstPageId) {
				$constantData = $this->getTemplateConstantData($template);
				if ($constantData === NULL) {
					continue;
				}
				$this->processTemplateData($constantData);
				$this->updateTemplate($template);
			}
		}
	}

	/**
	 * Handles page ids and purges pages, that did
	 * not get any new page id (e.g. have a forced uid).
	 *
	 * @param array $pageIds
	 */
	protected function handlePageIds(array $pageIds) {
		$this->pageIds = $pageIds;
		foreach ($this->pageIds as $originalPageId => $importedPageId) {
			if ((int)$originalPageId === (int)$importedPageId) {
				unset($this->pageIds[$originalPageId]);
			}
		}
		reset($this->pageIds);
	}

	/**
	 * Updates template record.
	 *
	 * @param array $template
	 */
	protected function updateTemplate(array $template) {
		if (empty($this->updates)) {
			return;
		}

		if (!empty($template['constants'])) {
			$template['constants'] .= PHP_EOL;
		}

		$template['constants'] .= '# Automatic updated pageId mapping' . PHP_EOL;
		foreach ($this->updates as $pageIdName => $pageIdValue) {
			$template['constants'] .= PHP_EOL . $pageIdName . ' = ' . $pageIdValue;
		}

		$this->getDatabaseConnection()->exec_UPDATEquery(
			'sys_template',
			'uid=' . (int)$template['uid'],
			array('constants' => $template['constants'])
		);
	}

	/**
	 * Processes a template record.
	 *
	 * @param $constantData
	 */
	protected function processTemplateData($constantData) {
		$pageIdNames = $this->determinePageIdNames($constantData);
		$constants = $this->parseConstants($constantData);

		if (empty($pageIdNames) ||  empty($constants)) {
			return;
		}

		$this->updates = array();
		foreach ($pageIdNames as $pageIdName) {
			$value = $this->walkConstants($pageIdName, $constants);
			if (is_array($value)) {
				$this->processUpdates($pageIdName, $value);
			} elseif ($value !== NULL) {
				$this->processUpdate($pageIdName, $value);
			}
		}
	}

	/**
	 * Processes updates for given multiple (possible) page id values.
	 *
	 * @param int $pageIdName
	 * @param int|array $value
	 */
	protected function processUpdates($pageIdName, $value) {
		if (is_array($value)) {
			foreach ($value as $propertyName => $propertyValue) {
				$this->processUpdates($pageIdName . $propertyName, $propertyValue);
			}
		} else {
			$this->processUpdate($pageIdName, $value);
		}
	}

	/**
	 * Processes an update for a given (possible) page id value.
	 *
	 * @param int $pageIdName
	 * @param int $pageId
	 */
	protected function processUpdate($pageIdName, $pageId) {
		$pageId = (int)$pageId;
		if ($pageId <= 0 || !isset($this->pageIds[$pageId])) {
			return;
		}

		$this->updates[$pageIdName] = $this->pageIds[$pageId];
	}

	/**
	 * Walks the TypoScript tree.
	 *
	 * @param string $name
	 * @param array $constants
	 * @return string|array|NULL
	 */
	protected function walkConstants($name, $constants) {
		$steps = explode('.', $name);
		foreach ($steps as $index => $step) {
			if ($index !== count($steps) - 1) {
				$step .= '.';
			}
			if ($step === '' && is_array($constants)) {
				return $constants;
			} elseif (isset($constants[$step])) {
				$constants = $constants[$step];
			} else {
				return NULL;
			}
		}
		return $constants;
	}

	/**
	 * Gets the raw constant data of a template record.
	 *
	 * @param array $template
	 * @return NULL|array
	 */
	protected function getTemplateConstantData(array $template) {
		$constantData = trim($template['constants']);
		$staticTemplates = GeneralUtility::trimExplode(',', $template['include_static_file'], TRUE);
		foreach ($staticTemplates as $staticTemplate) {
			$staticTemplateFile = GeneralUtility::getFileAbsFileName($staticTemplate);
			$staticTemplateFile = rtrim($staticTemplateFile, '/') . '/constants.txt';
			if (file_exists($staticTemplateFile)) {
				$constantData .= PHP_EOL . file_get_contents($staticTemplateFile);
			}
		}

		if (empty($constantData)) {
			return NULL;
		}

		return $constantData;
	}

	/**
	 * Determines the page id names, that shall be processed.
	 * Those names need to be defined in the template as a comment line like:
	 *   # hasPageId:contentpage.homeID
	 *   # hasPageId:menu.
	 *
	 * @param string $constantData
	 * @return array
	 */
	protected function determinePageIdNames($constantData) {
		$pageIdNames = array();
		if (preg_match_all('/#\s*hasPageId:(.+)\s*$/m', $constantData, $matches)) {
			foreach ($matches[1] as $pageIdName) {
				$pageIdName = trim($pageIdName);
				if (!empty($pageIdName)) {
					$pageIdNames[] = $pageIdName;
				}
			}
		}
		return $pageIdNames;
	}

	/**
	 * Parses constants from raw data.
	 *
	 * @param string $constantData
	 * @return array
	 */
	protected function parseConstants($constantData) {
		$typoScriptParser = $this->createTypoScriptParser();
		$typoScriptParser->parse($constantData);
		return $typoScriptParser->setup;
	}

	/**
	 * @return TypoScriptParser
	 */
	protected function createTypoScriptParser() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser'
		);
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}