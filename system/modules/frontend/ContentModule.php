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
 * Class ContentModule
 *
 * Front end content element "module".
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class ContentModule extends ContentElement
{

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		$objModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?")
									->limit(1)
									->execute($this->module);

		if ($objModule->numRows < 1)
		{
			return '';
		}

		$strClass = $this->findFrontendModule($objModule->type);

		if (!$this->classFileExists($strClass))
		{
			return '';
		}

		$objModule->typePrefix = 'ce_';
		$objModule = new $strClass($objModule);

		// Overwrite spacing, alignment and CSS ID
		$objModule->space = $this->space;
		$objModule->align = $this->align;
		$objModule->cssID = $this->cssID;

		return $objModule->generate();
	}


	/**
	 * Generate content element
	 */
	protected function compile()
	{
		return;
	}
}

?>