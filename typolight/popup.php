<?php

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../system/initialize.php');


/**
 * Class Popup
 *
 * Preview images in a back end pop up window.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class Popup extends Backend
{

	/**
	 * File
	 * @var string
	 */
	protected $strFile;


	/**
	 * Initialize the controller
	 *
	 * 1. Import user
	 * 2. Call parent constructor
	 * 3. Authenticate user
	 * 4. Load language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		$this->User->authenticate();
		$this->loadLanguageFile('default');

		$this->strFile = preg_replace('@^/+@', '', str_replace('%20', ' ' , $this->Input->get('src', true)));
	}


	/**
	 * Run controller and parse the template
	 */
	public function run()
	{
		// Make sure there are no attempts to hack the file system
		if (preg_match('@^\.+@i', $this->strFile) || preg_match('@\.+/@i', $this->strFile) || preg_match('@(://)+@i', $this->strFile))
		{
			die('Invalid file name');
		}

		// Limit preview to the tl_files directory
		if (!preg_match('@^' . preg_quote($GLOBALS['TL_CONFIG']['uploadPath'], '@') . '@i', $this->strFile))
		{
			die('Invalid path');
		}

		// Check whether the file exists
		if (!file_exists(TL_ROOT . '/' . $this->strFile))
		{
			die('File not found');
		}

		// Open download dialogue
		if ($this->Input->get('download') && $this->strFile)
		{
			$objFile = new File($this->strFile);

			header('Content-Type: '.$objFile->mime);
			header('Content-Transfer-Encoding: binary');
			header('Content-Disposition: attachment; filename="'.$objFile->basename.'"');
			header('Content-Length: '.$objFile->filesize);
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Expires: 0');

			$resFile = fopen(TL_ROOT . '/' . $this->strFile, 'rb');
			fpassthru($resFile);
			fclose($resFile);

			$this->redirect(str_replace('&download=1', '', $this->Environment->request));
		}

		$this->Template = new BackendTemplate('be_popup');
		$objFile = new File($this->strFile);

		// Add file info
		$this->Template->icon = $objFile->icon;
		$this->Template->ctime = date($GLOBALS['TL_CONFIG']['datimFormat'], $objFile->ctime);
		$this->Template->mtime = date($GLOBALS['TL_CONFIG']['datimFormat'], $objFile->mtime);
		$this->Template->atime = date($GLOBALS['TL_CONFIG']['datimFormat'], $objFile->atime);
		$this->Template->filesize = number_format(($objFile->filesize/1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']) . ' kB (' . number_format($objFile->filesize, 0, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']) . ' Bytes)';

		// Image
		if ($objFile->isGdImage)
		{
			$this->Template->isImage = true;

			$this->Template->width = $objFile->width;
			$this->Template->height = $objFile->height;
			$this->Template->src = $this->strFile;
		}

		$this->output();
	}


	/**
	 * Output the template file
	 */
	private function output()
	{
		$this->Template->theme = $this->getTheme();
		$this->Template->base = $this->Environment->base;
		$this->Template->language = $GLOBALS['TL_LANGUAGE'];
		$this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
		$this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$this->Template->href = ampersand($this->Environment->request, ENCODE_AMPERSANDS) . '&amp;download=1';
		$this->Template->headline = basename(utf8_convert_encoding($this->strFile, $GLOBALS['TL_CONFIG']['characterSet']));
		$this->Template->label_filesize = $GLOBALS['TL_LANG']['MSC']['fileSize'];
		$this->Template->label_ctime = $GLOBALS['TL_LANG']['MSC']['fileCreated'];
		$this->Template->label_mtime = $GLOBALS['TL_LANG']['MSC']['fileModified'];
		$this->Template->label_atime = $GLOBALS['TL_LANG']['MSC']['fileAccessed'];
		$this->Template->label_atime = $GLOBALS['TL_LANG']['MSC']['fileAccessed'];
		$this->Template->download = $GLOBALS['TL_LANG']['MSC']['fileDownload'];

		$this->Template->output();
	}
}


/**
 * Instantiate controller
 */
$objPopup = new Popup();
$objPopup->run();

?>