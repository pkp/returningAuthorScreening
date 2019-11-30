<?php
/**
 * @defgroup plugins_generic_defaultScreening
 */
/**
 * @file plugins/generic/defaultScreening/index.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_defaultScreening
 * @brief Wrapper for the Default Screening plugin.
 *
 */
require_once('DefaultScreeningPlugin.inc.php');
return new DefaultScreeningPlugin();