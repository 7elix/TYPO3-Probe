<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Christian Kuhn <lolli@schwarzbu.ch>
 *  (c) 2013 Felix Kopp <felix-source@phorax.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Extraction of system environment status from the TYPO3 project
 * Picked on 2013-03-14, commit 009f307bd2e1aca577f82044800286529ebaa5e5
 * @see http://git.typo3.org/TYPO3v4/Core.git/commit/009f307bd2e1aca577f82044800286529ebaa5e5
 *
 *
 * Check system environment status
 *
 * This class is a hardcoded requirement check of the underlying
 * server and PHP system.
 *
 * The class *must not* check for any TYPO3 specific things like
 * specific configuration values or directories. It should not fail
 * if there is no TYPO3 at all.
 *
 * This class is instantiated as the *very first* class during
 * installation. It is meant to be *standalone* und must not have
 * any requirements, except the status classes. It must be possible
 * to run this script separated from the rest of the core, without
 * dependencies.
 *
 * This means especially:
 * * No hooks or anything like that
 * * No usage of *any* TYPO3 code like GeneralUtility
 * * No require of anything but the status classes
 * * No localization
 *
 * The status messages and title *must not* include HTML, use plain
 * text only. The return values of this class are not bound to HTML
 * and can be used in different scopes (eg. as json array).
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @see TYPO3 project: typo3/sysext/install/Classes/SystemEnvironment/StatusInterface.php
 */
interface StatusInterface {

	/**
	 * @return string The title
	 */
	public function getTitle();

	/**
	 * Set title
	 *
	 * @param string $title The title
	 * @return void
	 */
	public function setTitle($title);

	/**
	 * Get status message
	 *
	 * @return string Status message
	 */
	public function getMessage();

	/**
	 * Set status message
	 *
	 * @param string $message Status message
	 * @return void
	 */
	public function setMessage($message);
}


/**
 * Class AbstractStatus
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @see TYPO3 project: typo3/sysext/install/Classes/SystemEnvironment/AbstractStatus.php
 */
class AbstractStatus implements StatusInterface {

	/**
	 * @var string Title
	 */
	protected $title;

	/**
	 * @var string Status message
	 */
	protected $message;

	/**
	 * @return string The title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param string $title The title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Get status message
	 *
	 * @return string Status message
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Set status message
	 *
	 * @param string $message Status message
	 * @return void
	 */
	public function setMessage($message) {
		$this->message = $message;
	}
}


class WarningStatus extends AbstractStatus implements StatusInterface { }

class ErrorStatus extends AbstractStatus implements StatusInterface {}

class InfoStatus extends AbstractStatus implements StatusInterface { }

class NoticeStatus extends AbstractStatus implements StatusInterface { }

class OkStatus extends AbstractStatus implements StatusInterface { }


/**
 * Class for system requirement checks
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 * @see TYPO3 project: typo3/sysext/install/Classes/SystemEnvironment/Check.php
 */
class Check {
	/**
	 * @var array List of required PHP extensions
	 */
	protected $requiredPhpExtensions = array(
		'fileinfo',
		'filter',
		'gd',
		'hash',
		'json',
		'mysql',
		'openssl',
		'pcre',
		'session',
		'soap',
		'SPL',
		'standard',
		'xml',
		'zip',
		'zlib'
	);

	/**
	 * Get all status information as array with status objects
	 *
	 * @return array<\TYPO3\CMS\Install\SystemEnvironment\StatusInterface>
	 */
	public function getStatus() {
		$statusArray = array();
		$statusArray[] = $this->checkCurrentDirectoryIsInIncludePath();
		$statusArray[] = $this->checkFileUploadEnabled();
		$statusArray[] = $this->checkMaximumFileUploadSize();
		$statusArray[] = $this->checkPostUploadSizeIsHigherOrEqualMaximumFileUploadSize();
		$statusArray[] = $this->checkMemorySettings();
		$statusArray[] = $this->checkPhpVersion();
		$statusArray[] = $this->checkMaxExecutionTime();
		$statusArray[] = $this->checkDisableFunctions();
		$statusArray[] = $this->checkSafeMode();
		$statusArray[] = $this->checkDocRoot();
		$statusArray[] = $this->checkSqlSafeMode();
		$statusArray[] = $this->checkOpenBaseDir();
		$statusArray[] = $this->checkSuhosinLoaded();
		$statusArray[] = $this->checkSuhosinRequestMaxVars();
		$statusArray[] = $this->checkSuhosinPostMaxVars();
		$statusArray[] = $this->checkSuhosinGetMaxValueLength();
		$statusArray[] = $this->checkSuhosinExecutorIncludeWhitelistContainsPhar();
		$statusArray[] = $this->checkSuhosinExecutorIncludeWhitelistContainsVfs();
		$statusArray[] = $this->checkSomePhpOpcodeCacheIsLoaded();
		$statusArray[] = $this->checkReflectionDocComment();
		$statusArray[] = $this->checkWindowsApacheThreadStackSize();
		foreach ($this->requiredPhpExtensions as $extension) {
			$statusArray[] = $this->checkRequiredPhpExtension($extension);
		}
		$statusArray[] = $this->checkMailCapabilities();
		$statusArray[] = $this->checkGdLibTrueColorSupport();
		$statusArray[] = $this->checkGdLibGifSupport();
		$statusArray[] = $this->checkGdLibJpgSupport();
		$statusArray[] = $this->checkGdLibPngSupport();
		$statusArray[] = $this->checkGdLibFreeTypeSupport();
		$statusArray[] = $this->checkPhpMagicQuotes();
		$statusArray[] = $this->checkRegisterGlobals();
		return $statusArray;
	}

	/**
	 * Checks if current directory (.) is in PHP include path
	 *
	 * @return WarningStatus|OkStatus
	 */
	protected function checkCurrentDirectoryIsInIncludePath() {
		$includePath = ini_get('include_path');
		$delimiter = $this->isWindowsOs() ? ';' : ':';
		$pathArray = $this->trimExplode($delimiter, $includePath);
		if (!in_array('.', $pathArray)) {
			$status = new WarningStatus();
			$status->setTitle('Current directory (./) is not in include path');
			$status->setMessage(
				'include_path = ' . implode(' ', $pathArray) .
						' Normally the current path, \'.\', is included in the' .
						' include_path of PHP. Although TYPO3 does not rely on this,' .
						' it is an unusual setting that may introduce problems for' .
						' some extensions.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Current directory (./) is in include path.');
		}
		return $status;
	}

	/**
	 * Check if file uploads are enabled in PHP
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkFileUploadEnabled() {
		if (!ini_get('file_uploads')) {
			$status = new ErrorStatus();
			$status->setTitle('File uploads not allowed');
			$status->setMessage(
				'file_uploads=' . ini_get('file_uploads') .
						' TYPO3 uses the ability to upload files from the browser in various cases.' .
						' As long as this flag is disabled, you\'ll not be able to upload files.' .
						' But it doesn\'t end here, because not only are files not accepted by' .
						' the server - ALL content in the forms are discarded and therefore' .
						' nothing at all will be editable if you don\'t set this flag!' .
						' However if you cannot enable fileupload for some reason alternatively' .
						' you change the default form encoding value with \\$TYPO3_CONF_VARS[SYS][form_enctype].'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('File uploads allowed');
		}
		return $status;
	}

	/**
	 * Check maximum file upload size against default value of 10MB
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkMaximumFileUploadSize() {
		$maximumUploadFilesize = $this->getBytesFromSizeMeasurement(ini_get('upload_max_filesize'));
		if ($maximumUploadFilesize < 1024 * 1024 * 10) {
			$status = new ErrorStatus();
			$status->setTitle('Maximum upload filesize too small');
			$status->setMessage(
				'upload_max_filesize=' . ini_get('upload_max_filesize') .
						' By default TYPO3 supports uploading, copying and moving' .
						' files of sizes up to 10MB (You can alter the TYPO3 defaults' .
						' by the config option TYPO3_CONF_VARS[BE][maxFileSize]).' .
						' Your current value is below this, so at this point, PHP sets' .
						' the limits for uploaded filesizes and not TYPO3.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Maximum file upload size is higher or equal to 10MB');
		}
		return $status;
	}

	/**
	 * Check maximum post upload size correlates with maximum file upload
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkPostUploadSizeIsHigherOrEqualMaximumFileUploadSize() {
		$maximumUploadFilesize = $this->getBytesFromSizeMeasurement(ini_get('upload_max_filesize'));
		$maximumPostSize = $this->getBytesFromSizeMeasurement(ini_get('post_max_size'));
		if ($maximumPostSize < $maximumUploadFilesize) {
			$status = new ErrorStatus();
			$status->setTitle('Maximum size for POST requests is smaller than max. upload filesize');
			$status->setMessage(
				'upload_max_filesize=' . ini_get('upload_max_filesize') .
						', post_max_size=' . ini_get('post_max_size') .
						' You have defined a maximum size for file uploads which' .
						' exceeds the allowed size for POST requests. Therefore the' .
						' file uploads can not be larger than ' . ini_get('post_max_size')
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Maximum post upload size correlates with maximum upload file size');
		}
		return $status;
	}

	/**
	 * Check memory settings
	 *
	 * @return ErrorStatus|WarningStatus|OkStatus
	 */
	protected function checkMemorySettings() {
		$memoryLimit = $this->getBytesFromSizeMeasurement(ini_get('memory_limit'));
		if ($memoryLimit <= 0) {
			$status = new WarningStatus();
			$status->setTitle('Unlimited memory limit!');
			$status->setMessage(
				'Your webserver is configured to not limit PHP memory usage at all. This is a risk' .
						' and should be avoided in production setup. In general it\'s best practice to limit this' .
						' in the configuration of your webserver. To be safe, ask the system administrator of the' .
						' webserver to raise the limit to something over 64MB'
			);
		} elseif ($memoryLimit < 1024 * 1024 * 32) {
			$status = new ErrorStatus();
			$status->setTitle('Memory limit below 32MB');
			$status->setMessage(
				'memory_limit=' . ini_get('memory_limit') .
						' Your system is configured to enforce a memory limit of PHP scripts lower than 32MB.' .
						' There is nothing else to do than raise the limit. To be safe, ask the system' .
						' administrator of the webserver to raise the limit to 64MB.'
			);
		} elseif ($memoryLimit < 1024 * 1024 * 64) {
			$status = new WarningStatus();
			$status->setTitle('Memory limit below 64MB');
			$status->setMessage(
				'memory_limit=' . ini_get('memory_limit') .
						' Your system is configured to enforce a memory limit of PHP scripts lower than 64MB.' .
						' A slim TYPO3 instance without many extensions will probably work, but you should ' .
						' monitor your system for exhausted messages, especially if using the backend. ' .
						' To be on the safe side, it would be better to raise the PHP memory limit to 64MB or more.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Memory limit equal 64MB or more');
		}
		return $status;
	}

	/**
	 * Check minimum PHP version
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkPhpVersion() {
		$minimumPhpVersion = '5.3.0';
		$recommendedPhpVersion = '5.3.7';
		$currentPhpVersion = phpversion();
		if (version_compare($currentPhpVersion, $minimumPhpVersion) < 0) {
			$status = new ErrorStatus();
			$status->setTitle('PHP version too low');
			$status->setMessage(
				'Your PHP version ' . $currentPhpVersion . ' is too old. TYPO3 CMS does not run' .
						' with this version. Update to at least PHP ' . $recommendedPhpVersion
			);
		} elseif (version_compare($currentPhpVersion, $recommendedPhpVersion) < 0) {
			$status = new WarningStatus();
			$status->setTitle('PHP version below recommended version');
			$status->setMessage(
				'Your PHP version ' . $currentPhpVersion . ' is below the recommended version' .
						' ' . $recommendedPhpVersion . '. TYPO3 CMS will mostly run with your PHP' .
						' version, but it is not officially supported. Expect some problems,' .
						' and a performance penalty, monitor your system for errors and watch' .
						' out for an upgrade, soon.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP version is fine');
		}
		return $status;
	}

	/**
	 * Check maximum execution time
	 *
	 * @return ErrorStatus|WarningStatus|OkStatus
	 */
	protected function checkMaxExecutionTime() {
		$minimumMaximumExecutionTime = 30;
		$recommendedMaximumExecutionTime = 240;
		$currentMaximumExecutionTime = ini_get('max_execution_time');
		if ($currentMaximumExecutionTime == 0 && PHP_SAPI !== 'cli') {
			$status = new WarningStatus();
			$status->setTitle('Infinite PHP script execution time');
			$status->setMessage(
				'Your max_execution_time is set to 0 (infinite). While TYPO3 is fine' .
						' with this, you risk a denial-of-service of you system if for whatever' .
						' reason some script hangs in an infinite loop. You are usually on safe side ' .
						' if max_execution_time is reduced to ' . $recommendedMaximumExecutionTime
			);
		} elseif ($currentMaximumExecutionTime < $minimumMaximumExecutionTime) {
			$status = new ErrorStatus();
			$status->setTitle('Low PHP script execution time');
			$status->setMessage(
				'Your max_execution_time is set to ' . $currentMaximumExecutionTime .
						'. Some expensive operation in TYPO3 can take longer than that. It is advised' .
						' to raise max_execution_time to ' . $recommendedMaximumExecutionTime
			);
		} elseif ($currentMaximumExecutionTime < $recommendedMaximumExecutionTime) {
			$status = new WarningStatus();
			$status->setTitle('Low PHP script execution time');
			$status->setMessage(
				'Your max_execution_time is set to ' . $currentMaximumExecutionTime .
						'. While TYPO3 often runs without problems with ' . $minimumMaximumExecutionTime .
						' it still may happen that script execution is stopped before finishing' .
						' calculations. You should monitor the system for messages in this area' .
						' and maybe raise the limit to ' . $recommendedMaximumExecutionTime . '.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Maximum PHP script execution equals ' . $recommendedMaximumExecutionTime . ' or more');
		}
		return $status;
	}

	/**
	 * Check for disabled functions
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkDisableFunctions() {
		$disabledFunctions = trim(ini_get('disable_functions'));
		if (strlen($disabledFunctions) > 0) {
			$status = new ErrorStatus();
			$status->setTitle('Some PHP functions disabled');
			$status->setMessage(
				'disable_functions=' . implode(' ', explode(',', $disabledFunctions)) . '. These function(s)' .
						'are disabled. If TYPO3 uses any of these there might be trouble. TYPO3 is designed to use the default' .
						' set of PHP functions plus some common extensions. Possibly these functions are disabled' .
						' due to security considerations and most likely the list would include a function like' .
						' exec() which is used by TYPO3 at various places. Depending on which exact functions' .
						' are disabled, some parts of the system may just break without further notice.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('No disabled PHP functions');
		}
		return $status;
	}

	/**
	 * Check if safe mode is enabled
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkSafeMode() {
		$safeModeEnabled = FALSE;
		if (version_compare(phpversion(), '5.4', '<')) {
			$safeModeEnabled = filter_var(
				ini_get('safe_mode'),
				FILTER_VALIDATE_BOOLEAN,
				array(FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE)
			);
		}
		if ($safeModeEnabled) {
			$status = new ErrorStatus();
			$status->setTitle('PHP safe mode on');
			$status->setMessage(
				'safe_mode enabled. This is unsupported by TYPO3 CMS, it must be turned off.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP safe mode off');
		}
		return $status;
	}

	/**
	 * Check for doc_root ini setting
	 *
	 * @return NoticeStatus|OkStatus
	 */
	protected function checkDocRoot() {
		$docRootSetting = trim(ini_get('doc_root'));
		if (strlen($docRootSetting) > 0) {
			$status = new NoticeStatus();
			$status->setTitle('doc_root is set');
			$status->setMessage(
				'doc_root=' . $docRootSetting . ' PHP cannot execute scripts' .
						' outside this directory. This setting is used seldom and must correlate' .
						' with your actual document root. You might be in trouble if your' .
						' TYPO3 CMS core code is linked to some different location.' .
						' If that is a problem, the setting must be adapted.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP doc_root is not set');
		}
		return $status;
	}

	/**
	 * Check sql.safe_mode
	 *
	 * @return OkStatus|WarningStatus
	 */
	protected function checkSqlSafeMode() {
		$sqlSafeModeEnabled = FALSE;
		if (version_compare(phpversion(), '5.4', '<')) {
			$sqlSafeModeEnabled = filter_var(
				ini_get('sql.safe_mode'),
				FILTER_VALIDATE_BOOLEAN,
				array(FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE)
			);
		}
		if ($sqlSafeModeEnabled) {
			$status = new WarningStatus();
			$status->setTitle('sql.safe_mode is enabled');
			$status->setMessage(
				'This means that you can only connect to the database with a' .
						' username corresponding to the user of the webserver process' .
						' or file owner. Consult your ISP for information about this.' .
						' The owner of the current file is: ' . get_current_user()
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP sql.safe_mode is off');
		}
		return $status;
	}

	/**
	 * Check open_basedir
	 *
	 * @return NoticeStatus|OkStatus
	 */
	protected function checkOpenBaseDir() {
		$openBaseDirSetting = trim(ini_get('open_basedir'));
		if (strlen($openBaseDirSetting) > 0) {
			$status = new NoticeStatus();
			$status->setTitle('open_basedir set');
			$status->setMessage(
				'open_basedir = ' . ini_get('open_basedir') .
						' This restricts TYPO3 to open and include files only in this' .
						' path. Please make sure that this does not prevent TYPO3 from running,' .
						' if for example your TYPO3 CMS core is linked to a different directory' .
						' not included in this path.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP open_basedir is off');
		}
		return $status;
	}

	/**
	 * Check enabled suhosin
	 *
	 * @return NoticeStatus|OkStatus
	 */
	protected function checkSuhosinLoaded() {
		if ($this->isSuhosinLoaded()) {
			$status = new OkStatus();
			$status->setTitle('PHP suhosin extension loaded');
		} else {
			$status = new NoticeStatus();
			$status->setTitle('PHP suhosin extension not loaded');
			$status->setMessage(
				'suhosin is an extension to harden the PHP environment. In general, it is' .
						' good to have it from a security point of view. While TYPO3 CMS works' .
						' fine with suhosin, it has some requirements different from default settings' .
						' to be set if enabled.'
			);
		}
		return $status;
	}

	/**
	 * Check suhosin.request.max_vars
	 *
	 * @return ErrorStatus|InfoStatus|OkStatus
	 */
	protected function checkSuhosinRequestMaxVars() {
		$recommendedRequestMaxVars = 400;
		if ($this->isSuhosinLoaded()) {
			$currentRequestMaxVars = ini_get('suhosin.request.max_vars');
			if ($currentRequestMaxVars < $recommendedRequestMaxVars) {
				$status = new ErrorStatus();
				$status->setTitle('PHP suhosin.request.max_vars not high enough');
				$status->setMessage(
					'suhosin.request.max_vars=' . $currentRequestMaxVars . '. This setting' .
							' can lead to lost information if submitting big forms in TYPO3 CMS like' .
							' it is done in the install tool. It is heavily recommended to raise this' .
							' to at least ' . $recommendedRequestMaxVars
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP suhosin.request.max_vars ok');
			}
		} else {
			$status = new InfoStatus();
			$status->setTitle('Suhosin not loaded');
			$status->setMessage(
				'If enabling suhosin, suhosin.request.max_vars' .
						' should be set to at least ' . $recommendedRequestMaxVars
			);
		}
		return $status;
	}

	/**
	 * Check suhosin.post.max_vars
	 *
	 * @return ErrorStatus|InfoStatus|OkStatus
	 */
	protected function checkSuhosinPostMaxVars() {
		$recommendedPostMaxVars = 400;
		if ($this->isSuhosinLoaded()) {
			$currentPostMaxVars = ini_get('suhosin.post.max_vars');
			if ($currentPostMaxVars < $recommendedPostMaxVars) {
				$status = new ErrorStatus();
				$status->setTitle('PHP suhosin.post.max_vars not high enough');
				$status->setMessage(
					'suhosin.post.max_vars=' . $currentPostMaxVars . '. This setting' .
							' can lead to lost information if submitting big forms in TYPO3 CMS like' .
							' it is done in the install tool. It is heavily recommended to raise this' .
							' to at least ' . $recommendedPostMaxVars
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP suhosin.post.max_vars ok');
			}
		} else {
			$status = new InfoStatus();
			$status->setTitle('Suhosin not loaded');
			$status->setMessage(
				'If enabling suhosin, suhosin.post.max_vars' .
						' should be set to at least ' . $recommendedPostMaxVars
			);
		}
		return $status;
	}

	/**
	 * Check suhosin.get.max_value_length
	 *
	 * @return ErrorStatus|InfoStatus|OkStatus
	 */
	protected function checkSuhosinGetMaxValueLength() {
		$recommendedGetMaxValueLength = 2000;
		if ($this->isSuhosinLoaded()) {
			$currentGetMaxValueLength = ini_get('suhosin.get.max_value_length');
			if ($currentGetMaxValueLength < $recommendedGetMaxValueLength) {
				$status = new ErrorStatus();
				$status->setTitle('PHP suhosin.get.max_value_length not high enough');
				$status->setMessage(
					'suhosin.get.max_value_length=' . $currentGetMaxValueLength . '. This setting' .
							' can lead to lost information if submitting big forms in TYPO3 CMS like' .
							' it is done in the install tool. It is heavily recommended to raise this' .
							' to at least ' . $recommendedGetMaxValueLength
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP suhosin.get.max_value_length ok');
			}
		} else {
			$status = new InfoStatus();
			$status->setTitle('Suhosin not loaded');
			$status->setMessage(
				'If enabling suhosin, suhosin.get.max_value_length' .
						' should be set to at least ' . $recommendedGetMaxValueLength
			);
		}
		return $status;
	}

	/**
	 * Check suhosin.executor.include.whitelist contains phar
	 *
	 * @return NoticeStatus|InfoStatus|OkStatus
	 */
	protected function checkSuhosinExecutorIncludeWhiteListContainsPhar() {
		if ($this->isSuhosinLoaded()) {
			$currentWhiteListArray = $this->trimExplode(' ', ini_get('suhosin.executor.include.whitelist'));
			if (!in_array('phar', $currentWhiteListArray)) {
				$status = new NoticeStatus();
				$status->setTitle('PHP suhosin.executor.include.whitelist does not contain phar');
				$status->setMessage(
					'suhosin.executor.include.whitelist= ' . implode(' ', $currentWhiteListArray) . '. phar' .
							' is currently not a hard requirement of TYPO3 CMS but is nice to have and a possible requirement' .
							' in future versions. A useful setting is "suhosin.executor.include.whitelist = phar vfs"'
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP suhosin.executor.include.whitelist contains phar');
			}
		} else {
			$status = new InfoStatus();
			$status->setTitle('Suhosin not loaded');
			$status->setMessage(
				'If enabling suhosin, a useful setting is "suhosin.executor.include.whitelist = phar vfs"'
			);
		}
		return $status;
	}

	/**
	 * Check suhosin.executor.include.whitelist contains vfs
	 *
	 * @return NoticeStatus|InfoStatus|OkStatus
	 */
	protected function checkSuhosinExecutorIncludeWhiteListContainsVfs() {
		if ($this->isSuhosinLoaded()) {
			$currentWhiteListArray = $this->trimExplode(' ', ini_get('suhosin.executor.include.whitelist'));
			if (!in_array('vfs', $currentWhiteListArray)) {
				$status = new WarningStatus();
				$status->setTitle('PHP suhosin.executor.include.whitelist does not contain vfs');
				$status->setMessage(
					'suhosin.executor.include.whitelist= ' . implode(' ', $currentWhiteListArray) . '. vfs' .
							' is currently not a hard requirement of TYPO3 CMS but tons of unit tests rely on it.' .
							' Furthermore, vfs is likely a base for an additional compatibilyt layer in the future.' .
							' A useful setting is "suhosin.executor.include.whitelist = phar vfs"'
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP suhosin.executor.include.whitelist contains vfs');
			}
		} else {
			$status = new InfoStatus();
			$status->setTitle('Suhosin not loaded');
			$status->setMessage(
				'If enabling suhosin, a useful setting is "suhosin.executor.include.whitelist = phar vfs"'
			);
		}
		return $status;
	}

	/**
	 * Check apt, xcache or eaccelerator is loaded
	 *
	 * @return WarningStatus|OkStatus
	 */
	protected function checkSomePhpOpcodeCacheIsLoaded() {
		$eAcceleratorLoaded = extension_loaded('eaccelerator');
		$xCacheLoaded = extension_loaded('xcache');
		$apcLoaded = extension_loaded('apc');
		if ($eAcceleratorLoaded || $xCacheLoaded || $apcLoaded) {
			$status = new OkStatus();
			$status->setTitle('A PHP opcode cache is loaded');
		} else {
			$status = new WarningStatus();
			$status->setTitle('No PHP opcode cache loaded');
			$status->setMessage(
				'PHP opcode caches hold a compiled version of executed PHP scripts in' .
						' memory and do not require to recompile any script on each access.' .
						' This can be a massive performance improvement and can put load off a' .
						' server in general, a parse time reduction by factor three for full cached' .
						' pages can be achieved easily if using some opcode cache.' .
						' If in doubt choosing one, apc is officially supported by PHP and can be' .
						' used as data cache layer in TYPO3 CMS as additional feature.'
			);
		}
		return $status;
	}

	/**
	 * Check doc comments can be fetched by reflection
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkReflectionDocComment() {
		$testReflection = new \ReflectionMethod(get_class($this), __FUNCTION__);
		if (strlen($testReflection->getDocComment()) === 0) {
			$status = new ErrorStatus();
			$status->setTitle('Doc comment reflection broken');
			$status->setMessage(
				'TYPO3 CMS core extensions like extbase and fluid heavily rely on method' .
						' comment parsing to fetch annotations and add magic according to them.' .
						' This does not work in the current environment and will lead to a lot of' .
						' broken extensions. The PHP extension eaccelerator is known to break this if' .
						' it is compiled without --with-eaccelerator-doc-comment-inclusion flag.' .
						' This compile flag must be given, otherwise TYPO3 CMS is no fun.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('Document comment reflection works');
		}
		return $status;
	}

	/**
	 * Checks thread stack size if on windows with apache
	 *
	 * @return WarningStatus|OkStatus
	 */
	protected function checkWindowsApacheThreadStackSize() {
		if (
			$this->isWindowsOs()
			&& substr($_SERVER['SERVER_SOFTWARE'], 0, 6) === 'Apache'
		) {
			$status = new WarningStatus();
			$status->setTitle('Windows apache thread stack size');
			$status->setMessage(
				'This current value can not be checked by the system, so please ignore this warning if it' .
						' is already taken care off: Fluid uses complex regular expressions which require a lot' .
						' of stack space during the first processing.' .
						' On Windows the default stack size for Apache is a lot smaller than on unix.' .
						' You can increase the size to 8MB (default on unix) by adding to the httpd.conf:' .
						' ThreadStackSize 8388608. Restart Apache after this change.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('ThreadStackSize is not an issue on unix systems');
		}
		return $status;
	}

	/**
	 * Check if a specific required PHP extension is loaded
	 *
	 * @param string $extension
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkRequiredPhpExtension($extension) {
		if (!extension_loaded($extension)) {
			$status = new ErrorStatus();
			$status->setTitle('PHP extension ' . $extension . ' not loaded');
			$status->setMessage(
				'TYPO3 CMS uses PHP extension ' . $extension . ' but it is not loaded' .
						' in your environment. Change your environment to provide this extension.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP extension ' . $extension . ' loaded');
		}
		return $status;
	}

	/**
	 * Check smtp settings
	 *
	 * @return ErrorStatus|OkStatus|WarningStatus
	 */
	protected function checkMailCapabilities() {
		if ($this->isWindowsOs()) {
			$smtpIni = ini_get('SMTP');
			$brokenSmtp = FALSE;
			$smtpIpAddress = '';
			if (!$this->isValidIp($smtpIni)) {
				if (!$this->isValidIp(@gethostbyname($smtpIni))) {
					$brokenSmtp = TRUE;
				} else {
					$smtpIpAddress = @gethostbyname($smtpIni);
				}
			} else {
				$smtpIpAddress = $smtpIni;
			}

			$smtpPortIni = intval(ini_get('smtp_port'));
			$brokenSmtpPort = FALSE;
			if (intval($smtpPortIni) < 1 || intval($smtpPortIni) > 65535) {
				$brokenSmtpPort = TRUE;
			}

			if ($brokenSmtp || $brokenSmtpPort) {
				$status = new ErrorStatus();
				$status->setTitle('Mail configuration is not set correctly');
				$status->setMessage(
					'PHP mail() function requires SMTP and smtp_port to have' .
							' correct values on Windows. If installation is completed,' .
							' the mail system can be tested in the install tool.'
				);
			} elseif ($smtpIpAddress === '127.0.0.1' || $smtpIpAddress === '::1') {
				$status = new WarningStatus();
				$status->setTitle('Mail is configured, potential problem exists');
				$status->setMessage(
					'smtp=' . $smtpIni . ' - This server! Are you sure it runs SMTP server?' .
							' If installation is completed, the mail system can be tested in the install tool.'
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('Mail is configured');
				$status->setMessage(
					'smtp=' . $smtpIni . ', smtp_port=' . ini_get('smtp_port') . '.' .
							' Values for mail setup look ok. If installation is completed,' .
							' the mail system can be tested in the install tool. '
				);
			}
		} elseif (!ini_get('sendmail_path')) {
			$status = new WarningStatus();
			$status->setTitle('PHP sendmail_path not defined');
			$status->setMessage(
				'This may be critical to TYPO3\'s use of the mail() function.' .
						' Your setup is rather uncommon. If installation is completed, the' .
						' mail system can be tested in the install tool.'
			);
		} else {
			list($mailBinary) = explode(' ', ini_get('sendmail_path'));
			if (!@is_executable($mailBinary)) {
				$status = new ErrorStatus();
				$status->setTitle('Mail program not found or not executable');
				$status->setMessage(
					'sendmail_path = ' . ini_get('sendmail_path') .
							' This may be critical to TYPO3\'s use of the mail() function. Please' .
							' be sure that the mail() function in your php-installation works.  If' .
							' installation is completed, the mail system can be tested in the install tool.'
				);
			} else {
				$status = new OkStatus();
				$status->setTitle('PHP sendmail path given');
				$status->setMessage(
					'sendmail_path = ' . ini_get('sendmail_path') . '.' .
							' This setting is crucial for TYPO3\'s use of the mail() function. The' .
							' current value looks fine. The mail system can be tested in the' .
							' install tool if the installation is completed'
				);
			}
		}
		return $status;
	}

	/**
	 * Check imagecreatetruecolor to verify gdlib works as expected
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkGdLibTrueColorSupport() {
		if (function_exists('imagecreatetruecolor')) {
			$imageResource = @imagecreatetruecolor(50, 100);
			if (is_resource($imageResource)) {
				imagedestroy($imageResource);
				$status = new OkStatus();
				$status->setTitle('PHP GD library true color works');
			} else {
				$status = new ErrorStatus();
				$status->setTitle('PHP GD library true color support broken');
				$status->setMessage(
					'GD is loaded, but calling a imagecreatetruecolor returned an error.' .
							' This must be fixed, TYPO3 CMS won\'t work well otherwise'
				);
			}
		} else {
			$status = new ErrorStatus();
			$status->setTitle('PHP GD library true color support missing');
			$status->setMessage(
				'Gdlib is essential for TYPO3 CMS to work properly.'
			);
		}
		return $status;
	}

	/**
	 * Check gif support of GD library
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkGdLibGifSupport() {
		if (
			function_exists('imagecreatefromgif')
			&& function_exists('imagegif')
			&& (imagetypes() & IMG_GIF)
		) {
			$imageResource = @imagecreatefromgif(__DIR__ . '/TestImages/jesus.gif');
			if (is_resource($imageResource)) {
				imagedestroy($imageResource);
				$status = new OkStatus();
				$status->setTitle('PHP GD library has gif support');
			} else {
				$status = new ErrorStatus();
				$status->setTitle('PHP GD library gif support broken');
				$status->setMessage(
					'GD is loaded, but calling a gif related message gives errors.' .
							' This must be fixed, TYPO3 CMS won\'t work well otherwise'
				);
			}
		} else {
			$status = new ErrorStatus();
			$status->setTitle('PHP GD library gif support missing');
			$status->setMessage(
				'GD must be compiled with gif support. This is essential for' .
						' TYPO3 CMS to work properly.'
			);
		}
		return $status;
	}

	/**
	 * Check jgp support of GD library
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkGdLibJpgSupport() {
		if (
			function_exists('imagecreatefromjpeg')
			&& function_exists('imagejpeg')
			&& (imagetypes() & IMG_JPG)
		) {
			$status = new OkStatus();
			$status->setTitle('PHP GD library has jpg support');
		} else {
			$status = new ErrorStatus();
			$status->setTitle('PHP GD library jpg support missing');
			$status->setMessage(
				'GD must be compiled with jpg support. This is essential for' .
						' TYPO3 CMS to work properly.'
			);
		}
		return $status;
	}

	/**
	 * Check png support of GD library
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkGdLibPngSupport() {
		if (
			function_exists('imagecreatefrompng')
			&& function_exists('imagepng')
			&& (imagetypes() & IMG_PNG)
		) {
			$imageResource = @imagecreatefrompng(__DIR__ . '/TestImages/jesus.png');
			if (is_resource($imageResource)) {
				imagedestroy($imageResource);
				$status = new OkStatus();
				$status->setTitle('PHP GD library has png support');
			} else {
				$status = new ErrorStatus();
				$status->setTitle('PHP GD library png support broken');
				$status->setMessage(
					'GD is compiled with png support, but a test call fails.' .
							' Check your environment and fix it, png in GD lib is important' .
							' for TYPO3 CMS to work properly.'
				);
			}
		} else {
			$status = new ErrorStatus();
			$status->setTitle('PHP GD library png support missing');
			$status->setMessage(
				'GD must be compiled with png support. This is essential for' .
						' TYPO3 CMS to work properly'
			);
		}
		return $status;
	}

	/**
	 * Check gdlib supports freetype
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkGdLibFreeTypeSupport() {
		if (function_exists('imagettftext')) {
			$status = new OkStatus();
			$status->setTitle('PHP GD library has freettype font support');
			$status->setMessage(
				'There is a difference between the font size setting the GD' .
						' library should be feeded with. If installation is completed' .
						' a test in the install tool helps to find out the value you need'
			);
		} else {
			$status = new ErrorStatus();
			$status->setTitle('PHP GD library freetype support missing');
			$status->setMessage(
				'Some core functionality and extension rely on the GD' .
						' to render fonts on images. This support is missing' .
						' in your environment. Install it.'
			);
		}
		return $status;
	}

	/**
	 * Check php magic quotes
	 *
	 * @return OkStatus|WarningStatus
	 */
	protected function checkPhpMagicQuotes() {
		if (get_magic_quotes_gpc()) {
			$status = new WarningStatus();
			$status->setTitle('PHP magic quotes on');
			$status->setMessage(
				'PHP ini setting magic_quotes_gpc in on. The setting is deprecated since PHP 5.3.' .
						' You are advised to set it to "Off" until it gets completely removed.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP magic quotes off');
		}
		return $status;
	}

	/**
	 * Check register globals
	 *
	 * @return ErrorStatus|OkStatus
	 */
	protected function checkRegisterGlobals() {
		$registerGlobalsEnabled = filter_var(
			ini_get('register_globals'),
			FILTER_VALIDATE_BOOLEAN,
			array(FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE)
		);
		if ($registerGlobalsEnabled === TRUE) {
			$status = new ErrorStatus();
			$status->setTitle('PHP register globals on');
			$status->setMessage(
				'TYPO3 requires PHP setting "register_globals" set to off.' .
						' This ancient PHP setting is a big security problem and should' .
						' never be enabled.'
			);
		} else {
			$status = new OkStatus();
			$status->setTitle('PHP register globals off');
		}
		return $status;
	}

	/**
	 * Helper methods
	 */

	/**
	 * Validate a given IP address.
	 *
	 * @param string $ip IP address to be tested
	 * @return boolean
	 */
	protected function isValidIp($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP) !== FALSE;
	}

	/**
	 * Test if this instance runs on windows OS
	 *
	 * @return boolean TRUE if operating system is windows
	 */
	protected function isWindowsOs() {
		$windowsOs = FALSE;
		if (stristr(PHP_OS, 'darwin') && stristr(PHP_OS, 'win')) {
			$windowsOs = TRUE;
		}
		return $windowsOs;
	}

	/**
	 * Helper method to find out if suhosin extension is loaded
	 *
	 * @return boolean TRUE if suhosin PHP extension is loaded
	 */
	protected function isSuhosinLoaded() {
		$suhosinLoaded = FALSE;
		if (extension_loaded('suhosin')) {
			$suhosinLoaded = TRUE;
		}
		return $suhosinLoaded;
	}

	/**
	 * Helper method to explode a string by delimeter and throw away empty values.
	 * Removes empty values from result array.
	 *
	 * @param string $delimiter Delimiter string to explode with
	 * @param string $string The string to explode
	 * @return array Exploded values
	 */
	protected function trimExplode($delimiter, $string) {
		$explodedValues = explode($delimiter, $string);
		$resultWithPossibleEmptyValues = array_map('trim', $explodedValues);
		$result = array();
		foreach ($resultWithPossibleEmptyValues as $value) {
			if ($value !== '') {
				$result[] = $value;
			}
		}
		return $result;
	}

	/**
	 * Helper method to get the bytes value from a measurement string like "100k".
	 *
	 * @param string $measurement The measurement (e.g. "100k")
	 * @return integer The bytes value (e.g. 102400)
	 */
	protected function getBytesFromSizeMeasurement($measurement) {
		$bytes = doubleval($measurement);
		if (stripos($measurement, 'G')) {
			$bytes *= 1024 * 1024 * 1024;
		} elseif (stripos($measurement, 'M')) {
			$bytes *= 1024 * 1024;
		} elseif (stripos($measurement, 'K')) {
			$bytes *= 1024;
		}
		return $bytes;
	}
}

$check = new Check();
$statuses = $check->getStatus();

function printStatus($statuses) {
	$content = '';
	$mode = '';

	foreach ($statuses as $status) {
		switch (get_class($status)) {
			case 'OkStatus':
			{
				$mode = 'ok';
				break;
			}

			case 'WarningStatus':
			{
				$mode = 'warning';
				break;
			}

			case 'NoticeStatus':
			{
				$mode = 'notice';
				break;
			}

			case 'InfoStatus':
			{
				$mode = 'info';
				break;
			}
			
			case 'ErrorStatus':
			{
				$mode = 'error';
				break;
			}
		}

		$content .= '<div class="' . $mode . '">';
		$content .= '<h2>';
		$content .= $status->getTitle();
		$content .= '</h2>';
		$content .= '<p>';
		$content .= $status->getMessage();
		$content .= '</p>';
		$content .= '</div>';
	}
	return $content;
}

if (PHP_SAPI === 'cli') {
    foreach ($statuses as $status) {
        /** @var StatusInterface $status */
        switch (get_class($status)) {
            case 'OkStatus':
            {
                $mode = "\033[32m" . "OK";
                break;
            }

            case 'WarningStatus':
            {
                $mode = "\033[33m" . "WARNING";
                break;
            }

            case 'NoticeStatus':
            {
                $mode = "\033[36m" . "NOTICE";
                break;
            }

            case 'InfoStatus':
            {
                $mode = "\033[36m" . "INFO";
                break;
            }

            case 'ErrorStatus':
            {
                $mode = "\033[31m" . "ERROR";
                break;
            }
        }
        print $mode . "\t" . $status->getTitle() . "\033[0m\n";
        if ($status->getMessage())
            print "\t\t\t" . $status->getMessage() . "\n";
    }
    die();
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>TYPO3 Probe</title>
		<meta name="robots" content="noindex, nofollow" />

		<link href="data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAQAQAABMLAAATCwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIb/AwCG/4QAhv/1AIb/2gCG/0kAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACG/5kAhv//AIb//wCG//8Ahv/9AIb/aQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACG/18Ahv//AIb//wCG//8Ahv//AIb//wCG//8Ahv9YAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACG/xYAhv/wAIb//wCG//8Ahv//AIb//wCG//8Ahv//AIb/9gCG/ycAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAhv+cAIb//wCG//8Ahv//AIb//wCG//8Ahv//AIb//wCG//8Ahv/EAIb/BQAAAAAAAAAAAAAAAAAAAAAAhv8eAIb//ACG//8Ahv//AIb//wCG//8Ahv//AIb//wCG//0Ahv9eAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIb/kgCG//8Ahv//AIb//wCG//8Ahv//AIb//wCG//8Ahv98AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIb/BwCG/+8Ahv//AIb//wCG//8Ahv//AIb//wCG//8Ahv/RAIb/AwAAAAAAAAAAAIb/MwCG/zsAAAAAAAAAAACG/1EAhv//AIb//wCG//8Ahv//AIb//wCG//8Ahv//AIb/SQAAAAAAAAAAAIb/UACG//oAhv/9AIb/PwAAAAAAhv+bAIb//wCG//8Ahv//AIb//wCG//8Ahv//AIb/0AAAAAAAAAAAAIb/FgCG//AAhv//AIb//wCG/9MAhv8CAIb/1wCG//8Ahv//AIb//wCG//8Ahv//AIb//wCG/2IAAAAAAAAAAACG/5MAhv//AIb//wCG//8Ahv//AIb/RgCG//gAhv//AIb//wCG//8Ahv//AIb//wCG//cAhv8PAAAAAACG/wwAhv/0AIb//wCG//8Ahv//AIb//wCG/54Ahv/5AIb//wCG//8Ahv//AIb//wCG//8Ahv+3AAAAAAAAAAAAhv9bAIb//wCG//8Ahv//AIb//wCG//8Ahv/bAIb/jACG//8Ahv//AIb//wCG//8Ahv//AIb/fQAAAAAAAAAAAIb/mwCG//8Ahv//AIb//wCG//8Ahv//AIb//AAAAAAAhv9GAIb/uACG//4Ahv//AIb//wCG/2UAAAAAAAAAAACG/7oAhv//AIb//wCG//8Ahv//AIb//wCG/+kAAAAAAAAAAAAAAAAAhv8UAIb/WgCG/5AAhv9wAAAAAAAAAAAAhv+FAIb//wCG//8Ahv/yAIb/zwCG/5cAhv8n8H8AAPA/AADgHwAAwA8AAMAHAACAH/C/gD8AAAAzAAAAYQAAAMAAAADAAAAAgAAAAYAAAAGAAACBgAAA4YAAAA==" rel="icon" type="image/x-icon" />

		<style type="text/css">
			body {
				background-image: url(data:image/jpeg;base64,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);
				background-color: #ececec;
				color: #000;
				font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
				font-size: 13px;
				line-height: 1.3em;
				padding: 30px;
				width: 700px;
			}

			footer { color: #4f4f4f; font-size: 12px; line-height: 1.2em; padding: 30px 60px 0 0; }

			p { padding: 0; }
			a { color: #4f4f4f; }

			h2 { font-size: 1.1em; margin-top: 0; }
			h1 { font-size: 1.5em; }

			.logo { margin-bottom: 30px; padding-left: 5px; }
			.well { margin-bottom: 30px; }
			p.well { font-size: 14px; }

			div { padding: 10px 20px 10px 40px; border: 1px solid; margin-bottom: 10px; background: transparent 10px 10px no-repeat; border-radium: 1px; }

			.notice { border-color: #C2CBCF; background-color: #F6F7FA; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAvVBMVEXU1NTy8vLc3Nzt7e3Pz8/z8/PT09PY2Njb29vm5ubi4uLq6ur09PT19fWpqanQ0NDr6+ufn5/Dw8PJycnx8fHf39/Z2dn7+/vLy8vn5+fv7++jo6Pw8PD5+fnR0dGurq6cnJy7u7vMzMz39/e+vr7BwcGwsLDl5eXNzc3s7OyysrLu7u7j4+Ovr6/V1dXOzs69vb21tbXk5OT29vbGxsb6+vrKysrd3d2xsbHHx8fp6eng4OD9/f3+/v7l4+OE80p/AAAAP3RSTlP//////////////////////////////////////////////////////////////////////////////////wCOJnwXAAAAmklEQVR42m3P1xKCMBCF4QQSekCagBR7710T8f0fS1kcLxj+u/PN7MWid6M2sHpyhydzQ2gqAqDlPwJglq9fZQzAo6w60k9ifAFIorVscn0jpPMUILxW2xBSlw0BtGV419B379gRQDHyHCuSigprWwPxg4CoyNlTuwZ88LIRchY3nAIQyv1CMIti7ALET28ye6z6duoOWp9r9AHbjytSNYDAAAAAAABJRU5ErkJggg==); }
			.info { border-color: #8AAFC4; background-color: #DDEEF9; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACm0lEQVR42qWT3U+SYRiH+1v0T+h9O2ltetBcmw3yTTvIrGGaqK10FWaDVYqJ+AUoYEMwP/KD8gtKUoQEXsnekI/Q1DcHZtDWVieQtSx/PdKGtVwnHlwnz577up/f7uc+BOBA/HOQp1/JPKkNS3PVIf54sy+RreQSWcoFPkvplR67x2b+VyDQLTMnVMF4eU8APc4oRtnNFCZ7BCLdC9DS2TgtszP7CkhXJqfFn2yeXIXNF0f3dBRy8xvcHV6GyrIKs2sDDaNh0BUTSfrKBPOXIFcTyshpXYztFltexiElRTcHghjyvMOQewM3egOQ9AfRO7sO+aMA6FJzjC4ZzkgLclSLsjKDD1buA2oHwpCQCNeMfnz5tp2iysihqpuD5IEPA88jONv0DFRhvywtyG7leJM9Co2Fx/UeP6qNPlQZOPQ519FLqOhif6P3kFhB6KZXQBcY+bTgqJzdGl94j9q+AOn0CpX3veQyCwDYIYi1bpR1zBFcuKx3w8xGQDGdW2kBLXNujXk3d7OmOpXrPKTIg3D0M5Jft1Ha7kRJmwMXCWK1AyPzRCBU/ymw8wY7D8XjJVTqvRB3unFJM4fXkU9E8B3FyhkUk9yiJhtqullobCHQp9r5PUGNTVakcmLIFUWlloVY5YKN28CPnzupCIG3H1EktxIs6HoSRsGtUSJok+0Jro5l0GJz7M6IH732NZSqHBA1T+O8YgpF9Racq5tAYd04WkZ8ZLzz5PmKGC1UpMeYghINMofzTUnpIAfTzAokBjd58lNcaLCiWuuAzhKEpI8FLahPkmJm369MFZgYKr8rfub2JDRTS3jo5glraLeGkFdrBiVoiNPCRua/y0Sf1mVSTIeUylPzlLApQQkaE6QjT5AeEdbvu0wH4hc4hsvsbED0hwAAAABJRU5ErkJggg==); }
			.ok { border-color: #58B549; background-color: #CDEACA; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACnUlEQVR42qWT/UsTcRzH+zt2t50pSCAjChEkhNpPQSKx02wo5sOyNN1uDz7SNmPYTIsycj6MkVJaZqnV5pzZ1KnNrSll4rJTQsz90gPVhtGDvftyP8wi8Rd/eMFx3Ov1ue9977sHwK7470ah/3BC3vQhQ443lc98eiAifyKNyN1JvNyVZMhw7EvYMZDvS2MV3pSw3qvArZAV/cvdAl2LLSh1H4esjwnLehl22wCZymaNJ0evzZngWh2A/fV1mOeqQBCunav9aJqpxkG7OJpiF7P/BHK9qfFZnuR1IgsP1gbLURkohXbmLPSECkKN/xwcb+/j0lQlpFZ6XXqDjo8FFJ5kIzeWLUwmsiByvtNQTRdBNVUIzVQRPGtDMPrUcJJIXu9RJF6ljbFA5hMp3xVqgS3UjEp/CdTPlEQsgHqS4C3A+Ds3ACAcXUP7yybY55oQ10DzscAxR+LGw5U7qAtqofMVkzcZxOKneXATRRhbGxbkX5s/0f7iMowTJegPdUFiojZiAdkDZmNguQemAIe6gA5fvn8WpI/f3sdk62wDNO4cGDxnMBDqhKT270Avw9+cb0bHwhXoJ5Ww+GsQ/fFVkDd/b8L6/CLKXdnQkkBrsB4dPguYamprCWm3JUblYDocK/dQM6GExnMK9dMVWPqwgPbZRpQNnYDKeRJ6Vy4cb3qQYTsCpora+ogpNnH8frI1Fq8Wj/i7qBrNh24kF9ywAmonQZBz8HipG6bhEkg0onVGQ8W2USCpmWbjGqmo2VMOJ4m0BS0wjBbj/IgSbWQJDiIbXMUQl4miRGa3/ZX3NtIsY6bC8k4ZOgIW9L2yoW/eBqvPjPTWNEhUojDDEXmnw8RcoBMYI2VgaileohNFJJwoQibyBANTRm17mHbFH2fQrOV6oi5QAAAAAElFTkSuQmCC); }
			.warning { border-color: #C4B70D; background-color: #FBFFB3; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACMklEQVR42qWTXUhTYRjHBwMhsCCDuoggahUhopKZjfQq6EKICmtjrS7EC4O68Cq6KCIQg6QuXFJUgjMKrShr9mHRjEFp3+k+zj501nE7+2zb2TlrO1//Xk7ahMgSL343z8vv9/BcvBoAS+KvDwl7vTb+YpczNqx3RJ7u1C46QOTLGWc70l/OgRnacWxRASKXRYf1rCxkIKRcCD/cnpy5v63svwPRZ3obS1mgSFkoAoP0xCXQd6u7Fw4U5arIkzoZcgGFqAVCvIdEEvjaXylN36qo+meAyE4u2E/8JNx9DSoS70TGY8VUX7ljwUDkcZ0pOdoGKAVwgaNwWRtUOK8ZiphE6Hkr/Nc3m+Y78+XSsK028SM2ClkIg/Mdgau3XoX1GCBl34CjR+C9qpuhujeU/hEIP6odSI93kk0x5MMWEjBjcnDPbOAQeL8Zcn4KzMhpOC+u65jz5mRdaLBGVMQsRNaBXMCsBnLBFsTfGcC6D4L3NiHPWCByND53rCl8OLtK9zsQelDzNuO5AbnAIB+6gJzfRAKHiye4m8BTB8C590HKUQjZz2Ps1AqbGiByY8TeDEXiIKSGwPuMKlmKbPYYiWwE5zWQwH5wnr3I+ZrVM8e7dsNxoqRRQ9+rpnn6pRpQxBTh+y+klEpxlpwlQeYs4h/v4FWrltJ8G6i0Tt+uQPBmOSZ7tyLQswW+a5vgvbIR7q71mOhci0/tq/H+zEqMnVyO123L4DheAiLD3qKxLvk7/wRGMAClf/I+KAAAAABJRU5ErkJggg==); }
			.error { border-color: #DC4C42; background-color: #FBB19B; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACZklEQVR42qWT+0tTYRjH+1s0TLsQEkH4g+aUYXYxdsxL22EXndtqeWFkUp65m+SKRBdiEqFmDoWgwAoqNZGQiDDKIupkpOtsbW7qPGfzzIJv5xzsjEj8xQe+v7y8n++X53nedxeAHem/g9/t5zI3PBYq5TLR6w4jm7DXsGyrno5f1lIrl8jMbQ0EkEi5zMFldz04vw/8owFJrL8bYcqMJVt1MNxUQWxpkHKbCd5Zx8V7Pfg1MQp+6BqS3vOS+LtebIyPIOprww+rilu0lBH/GPBOU0ayzcis9rolOOGqkfS3WLsebKsOqad+RG5cwbfaUoY2lGTIBkKf9qjTmoadBnAOg2wg9I/VFi1Wmknwj4cx30jiE1lklw2EC7TYJz/YIYJy4mZJ4LJNg2ijGvGbLkRvefG+Kp+WDWLN6iT/ZBCcxySCcuJmSeBS/VlErNUIN2gRH7mNWSIvKRuEmyqT62MDWHMYRVBO3Pg8J/IS+NNShZCpEiErifhwH16XHU4bMBdU9Gp/J7g+jwjKifE7XWAf+CUwaKwAYziDJS+FUHc7Zkpz0y0smE/Zv9t0WB+7J4Jy4mZJYEBfjkUtgbXRfrzTn8a08kB6iF9rjmV80SmZYEcLEg+H5MSYzyvsvkMCF0iVBM9TDZgq3sdMKnLkNUr6qFEQc9UFXMBzEdz9QUSvC4/GpEGgTo3IVUoYnAC3NuBFUQ43UbiH2PIpvy3PI96ojgQ/1KoQ6hLmMdCDWH8PmE43ZskTmCjMDj4vyCK2/UyvTh7KnDmeS70sOUhPK/ezU8V72UlFNj1+NIt6lr97y8+0I/0Bcq/UGoJ9DNcAAAAASUVORK5CYII=); }
		</style>
	</head>

	<body>
		<img src="data:image/png;base64,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" width="118" height="34" alt="TYPO3 Probe Tool" class="logo" />

		<h1>TYPO3 Probe</h1>
		<p class="well">Checks server for ability to run TYPO3 flawlessly.<br />
		Please remove this file immediately after probing.</p>

		<?= printStatus($statuses); ?>

		<footer>
			<p><a href="https://github.com/7elix/TYPO3-Probe" target="_blank">TYPO3 Probe</a>. Copyright © 2013 Felix Kopp; based on install check by Christian Kuhn. Extensions are copyright of their respective owners. Go to <a href="http://typo3.org/" target="_blank">http://typo3.org/</a> for details.<br />
			TYPO3 CMS and TYPO3 Probe comes with ABSOLUTELY NO WARRANTY; <a href="http://typo3.org/license" target="_blank">click for details</a>. This is free software, and you are welcome to redistribute it under certain conditions; <a href="http://typo3.org/license" target="_blank">click for details</a>. Obstructing the appearance of this notice is prohibited by law.</p>

			<p><a href="http://typo3.org/" target="_blank">TYPO3.org</a> &#124; <a href="http://typo3.org/donate/" target="_blank">Donate</a></p>

			<p><strong><a href="https://github.com/7elix/TYPO3-Probe/issues" target="_blank">Report bugs for TYPO3 Probe</a></strong></p>
		</footer>
	</body>
</html>