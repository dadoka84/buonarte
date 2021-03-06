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
 * @package    Newsletter
 * @license    LGPL
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['email']  = array('E-mail address', 'Please enter the recipient\'s e-mail address.');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['active'] = array('Activated', 'E-mail addresses are usually activated by clicking a link in the confirmation e-mail (double-opt-in).');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['source'] = array('File source', 'Please choose the CSV file you want to import from the files directory.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['confirm'] = '%s recipients have been imported.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['new']    = array('Add recipient', 'Add a new recipient');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['edit']   = array('Edit recipient', 'Edit recipient ID %s');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['copy']   = array('Copy recipient', 'Copy recipient ID %s');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['delete'] = array('Delete recipient', 'Delete recipient ID %s');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['show']   = array('Recipient details', 'Show details of recipient ID %s');
$GLOBALS['TL_LANG']['tl_newsletter_recipients']['import'] = array('CSV import', 'Import recipients from a CSV file');

?>