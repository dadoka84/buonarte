<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Class FrontendTemplate
 *
 * Provide methods to handle front end templates.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class FrontendTemplate extends Template
{

	/**
	 * Add a hook to modify the template output
	 * @return string
	 */
	public function parse()
	{
		$strBuffer = parent::parse();

		// HOOK: add custom parse filter
		if (array_key_exists('parseFrontendTemplate', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['parseFrontendTemplate']))
		{
			foreach ($GLOBALS['TL_HOOKS']['parseFrontendTemplate'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->$callback[0]->$callback[1]($strBuffer, $this->strTemplate);
			}
		}

		return $strBuffer;
	}


	/**
	 * Parse the template file, replace insert tags and print it to the screen
	 */
	public function output()
	{
		global $objPage;

		// Remove index.php fragment
		$strUrl = preg_replace('@^(index.php/)?([^\?]+)(\?.*)?@i', '$2', $this->Environment->request);

		// Add $_GET variables if alias usage is disabled
		if ($GLOBALS['TL_CONFIG']['disableAlias'])
		{
			$arrChunks = array();

			foreach (array_keys($_GET) as $key)
			{
				if ($key == 'id' || $key == 'articles' || $key == 'items' || $key == 'events')
				{
					$arrChunks[] = $key . '=' . $this->Input->get($key);
				}
			}

			$strUrl .= '?' . implode('&', $arrChunks);
		}

		// Rebuild URL to eliminate duplicate parameters
		else
		{
			$strUrl = (strlen($objPage->alias) ? $objPage->alias : $objPage->id);

			foreach (array_keys($_GET) as $key)
			{
				if ($key == 'day' || $key == 'page' || $key == 'id' || $key == 'file')
				{
					continue;
				}

				$strUrl .= '/' . $key . '/' . $this->Input->get($key);
			}

			$strUrl .= $GLOBALS['TL_CONFIG']['urlSuffix'];
		}

		$this->keywords = '';
		$arrKeywords = array_map('trim', explode(',', $GLOBALS['TL_KEYWORDS']));

		// Add keywords
		if (strlen($arrKeywords[0]))
		{
			$this->keywords = implode(', ', array_unique($arrKeywords));
		}

		// Parse template
		$strBuffer = str_replace(' & ', ' &amp; ', $this->parse());

		// HOOK: add custom output filter
		if (array_key_exists('outputFrontendTemplate', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['outputFrontendTemplate']))
		{
			foreach ($GLOBALS['TL_HOOKS']['outputFrontendTemplate'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->$callback[0]->$callback[1]($strBuffer, $this->strTemplate);
			}
		}

		// Cache page if it is not protected
		if (!BE_USER_LOGGED_IN && !FE_USER_LOGGED_IN && intval($objPage->cache) > 0 && !$objPage->protected)
		{
			// Create a unique key
			$strUniqueKey = $this->Environment->base . $strUrl;

			// Overwrite the key on empty requests
			if (!strlen($this->Environment->request) || $this->Environment->request == 'index.php')
			{
				$strUniqueKey = $this->Environment->base;
			}

			// Replace insert tags for caching
			$strBuffer = $this->replaceInsertTags($strBuffer, true);
			$intCache = intval($objPage->cache) + time();

			// Create cache file
			$objFile = new File('system/tmp/' . md5($strUniqueKey));
			$objFile->write('<?php $expire = ' . $intCache . '; ?>' . $strBuffer);
			$objFile->close();

			// Send cache header
			if (!headers_sent())
			{
				header('Cache-Control: public, max-age=' . $intCache);
				header('Expires: '.gmdate('D, d M Y H:i:s', $intCache).' GMT');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
				header('Pragma: public');
			}
		}

		// Send no-cache header
		elseif (!headers_sent())
		{
			header('Cache-Control: no-cache');
			header('Cache-Control: pre-check=0, post-check=0', false);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Expires: Wed, 28 Jan 1976 11:52:00 GMT');
			header('Pragma: no-cache');
		}

		$this->strBuffer = $this->replaceInsertTags($strBuffer);
		$this->strBuffer = str_replace(array('[lt]', '[gt]', '[&]'), array('&lt;', '&gt;', '&amp;'), $this->strBuffer);

		// Index page if searching is allowed and there is no back end user
		if ($GLOBALS['TL_CONFIG']['enableSearch'] && $objPage->type == 'regular' && !BE_USER_LOGGED_IN && !$objPage->noSearch)
		{
			// Index protected pages if enabled
			if ($GLOBALS['TL_CONFIG']['indexProtected'] || (!FE_USER_LOGGED_IN && !$objPage->protected))
			{
				$this->import('Search');

				$arrData = array
				(
					'url' => $strUrl,
					'content' => $this->strBuffer,
					'title' => (strlen($objPage->pageTitle) ? $objPage->pageTitle : $objPage->title),
					'protected' => ($objPage->protected ? '1' : ''),
					'groups' => $objPage->groups,
					'pid' => $objPage->id
				);

				$this->Search->indexPage($arrData);
			}
		}

		parent::output();
	}


	/**
	 * Return a custom layout section
	 * @param string
	 * @return string
	 */
	public function getCustomSection($strKey)
	{
		return sprintf("\n<div id=\"%s\">\n%s\n</div>\n", $strKey, (strlen($this->sections[$strKey]) ? $this->sections[$strKey] : '&nbsp;'));
	}


	/**
	 * Return all custom layout sections
	 * @param string
	 * @return string
	 */
	public function getCustomSections($strKey=false)
	{
		if ($strKey && $this->sPosition != $strKey)
		{
			return '';
		}

		$sections = '';

		foreach ($this->sections as $k=>$v)
		{
			$sections .= sprintf("\n<div id=\"%s\">\n<div class=\"inside\">\n%s\n</div>\n</div>\n", $k, $v);
		}

		return strlen($sections) ? "\n<div class=\"custom\">\n$sections\n</div>\n" : '';
	}
}

?>