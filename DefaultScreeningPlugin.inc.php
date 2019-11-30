<?php
/**
 * @file plugins/generic/defaultScreening/DefaultScreeningPlugin.inc.php
 *
 * Copyright (c) 2017-2019 Simon Fraser University
 * Copyright (c) 2017-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DefaultScreeningPlugin
 * @ingroup plugins_generic_defaultScreening
 *
 * @brief Plugin class for the DefaultScreening plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
class DefaultScreeningPlugin extends GenericPlugin {

	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled($mainContextId)) {
			// Add a new ruleset for publishing
			// Always apply rules to just authors
			\HookRegistry::register('Publication::validatePublish', [$this, 'applyRuleset']);
		}
		return $success;
	}

	/**
	 * Provide a name for this plugin
	 *
	 * The name will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return __('plugins.generic.defaultScreening.displayName');
	}

	/**
	 * Provide a description for this plugin
	 *
	 * The description will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDescription() {
		return __('plugins.generic.defaultScreening.description');
	}

	/**
	 * Add a new ruleset for publishing
	 *
	 * @param string $hookName string
	 * @param array $args [[
	 * 	@option array Additional parameters passed with the hook
	 * 	@option errors array
	 * 	@option Publication
	 * ]]
	 * @return errors
	 */
	function applyRuleset($hookName, $args) {
		$errors =& $args[0];
		$publication = $args[1];
		$currentUser = \Application::get()->getRequest()->getUser();

		// Only apply rules to authors, editors can always publish if other criteria is met
		if ($this->_isAuthor($currentUser->getId(), $publication->getData('submissionId'))){

			// Check that user has published before
			$currentContext = \Application::get()->getRequest()->getContext();
			if (!$this->_hasPublishedBefore($currentUser->getId(), $currentContext->getId())) {
				$errors['hasPublishedBefore'] = __('plugins.generic.defaultScreening.required.publishedBefore');
			}

		}
		return false;
	}

	/**
	 * Check if user has published before in this context
	 * @param int $userId
	 * @param int $contextId
	 * @return boolean
	 */
	function _hasPublishedBefore($userId, $contextId) {
		$submissionsIterator = Services::get('submission')->getMany([
			'contextId' => $contextId,
			'status' => STATUS_PUBLISHED,
		]);
		foreach ($submissionsIterator as $submission) {
			$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
	 		$usersAssignments = $stageAssignmentDao->getBySubmissionAndRoleId($submission->getId(), ROLE_ID_AUTHOR, null, $userId);
	 		if (!$usersAssignments->wasEmpty()){
	 			return true;
	 		}
 		}
		return false;
	}

	/**
	 * Check if current user is the author
	 * @param int $userId
	 * @param int $submissionId
	 * @return boolean
	 */
	function _isAuthor($userId, $submissionId) {
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$usersAssignments = $stageAssignmentDao->getBySubmissionAndRoleId($submissionId, ROLE_ID_AUTHOR, WORKFLOW_STAGE_ID_PRODUCTION, $userId);
		if (!$usersAssignments->wasEmpty()){
			return true;
		}
		return false;
	}
}