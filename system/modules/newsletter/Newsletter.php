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
 * Class Newsletter
 *
 * Provide methods to handle newsletters.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class Newsletter extends Backend
{

	/**
	 * Renturn a form to choose an existing style sheet and import it
	 * @param object
	 * @return string
	 */
	public function send(DataContainer $objDc)
	{
		$objNewsletter = $this->Database->prepare("SELECT * FROM tl_newsletter WHERE id=?")
										->limit(1)
										->execute($objDc->id);

		// Return if there is no newsletter
		if ($objNewsletter->numRows < 1)
		{
			return '';
		}

		// Add default sender address
		if (!strlen($objNewsletter->sender))
		{
			$objNewsletter->sender = $GLOBALS['TL_CONFIG']['adminEmail'];
		}

		$arrAttachments = array();

		// Add attachments
		if ($objNewsletter->addFile)
		{
			$files = deserialize($objNewsletter->files);

			if (is_array($files) && count($files) > 0)
			{
				foreach ($files as $file)
				{
					if (is_file(TL_ROOT . '/' . $file))
					{
						$arrAttachments[] = $file;
					}
				}
			}
		}

		$css = '';

		// Add style sheet newsletter.css
		if (!$objNewsletter->sendText && file_exists(TL_ROOT . '/newsletter.css'))
		{
			$buffer = file_get_contents(TL_ROOT . '/newsletter.css');
			$buffer = preg_replace('@/\*\*.*\*/@Us', '', $buffer);

			$css  = '<style type="text/css">' . "\n";
			$css .= trim($buffer) . "\n";
			$css .= '</style>' . "\n";
		}

		// Replace insert tags
		$html = $this->replaceInsertTags($objNewsletter->content);
		$text = $this->replaceInsertTags($objNewsletter->text);

		// Send newsletter
		if (strlen($this->Input->get('token')) && $this->Input->get('token') == $this->Session->get('tl_newsletter_send'))
		{
			$referer = preg_replace('/&(amp;)?(start|mpc|token|recipient|preview)=[^&]*/', '', $this->Environment->request);

			// Preview
			if (array_key_exists('preview', $_GET))
			{
				// Check e-mail address
				if (!preg_match('/^\w+([_\.-]*\w+)*@\w+([_\.-]*\w+)*\.[a-z]{2,6}$/i', $this->Input->get('recipient', true)))
				{
					$_SESSION['TL_PREVIEW_ERROR'] = true;
					$this->redirect($referer);
				}

				$arrRecipient['email'] = urldecode($this->Input->get('recipient', true));

				// Send
				$objEmail = $this->generateEmailObject($objNewsletter, $arrAttachments);
				$this->sendNewsletter($objEmail, $objNewsletter, $arrRecipient, $text, $html, $css);

				// Redirect
				$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['tl_newsletter']['confirm'], 1);
				$this->redirect($referer);
			}

			// Get total number of recipients
			$objTotal = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_newsletter_recipients WHERE pid=? AND active=?")
									   ->execute($objNewsletter->pid, 1);

			// Return if there are no recipients
			if ($objTotal->total < 1)
			{
				$this->Session->set('tl_newsletter_send', null);
				$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['tl_newsletter']['error'];

				$this->redirect($referer);
			}

			$intTotal = $objTotal->total;

			// Get page and timeout
			$intTimeout = ($this->Input->get('timeout') > 0) ? $this->Input->get('timeout') : 1;
			$intStart = $this->Input->get('start') ? $this->Input->get('start') : 0;
			$intPages = $this->Input->get('mpc') ? $this->Input->get('mpc') : 10;

			// Get recipients
			$objRecipients = $this->Database->prepare("SELECT *, r.email FROM tl_newsletter_recipients r LEFT JOIN tl_member m ON(r.email=m.email) WHERE r.pid=? AND r.active=?")
											->limit($intPages, $intStart)
											->execute($objNewsletter->pid, 1);

			echo '<div style="font-family:Verdana, sans-serif; font-size:11px; line-height:16px; margin-bottom:12px;">';

			// Send newsletter
			if ($objRecipients->numRows > 0)
			{
				$objEmail = $this->generateEmailObject($objNewsletter, $arrAttachments);

				while ($objRecipients->next())
				{
					$this->sendNewsletter($objEmail, $objNewsletter, $objRecipients->row(), $text, $html, $css);
					echo 'Sending newsletter to <strong>' . $objRecipients->email . '</strong><br />';
				}
			}

			echo '<div style="margin-top:12px;">';

			// Redirect back home
			if ($objRecipients->numRows < 1 || ($intStart + $intPages) >= $intTotal)
			{
				$this->Session->set('tl_newsletter_send', null);

				// Update status
				$this->Database->prepare("UPDATE tl_newsletter SET sent=?, date=? WHERE id=?")
							   ->execute(1, time(), $objNewsletter->id);

				// Confirm total number of sent items
				$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['tl_newsletter']['confirm'], $intTotal);

				echo '<script type="text/javascript">setTimeout(\'window.location="' . $this->Environment->base . $referer . '"\', 1000);</script>';
				echo '<a href="' . $this->Environment->base . $referer . '">Please click here to proceed if you are not using JavaScript</a>';
			}

			// Redirect to the next cycle
			else
			{
				$url = preg_replace('/&(amp;)?(start|mpc|recipient)=[^&]*/', '', $this->Environment->request) . '&start=' . ($intStart + $intPages) . '&mpc=' . $intPages;

				echo '<script type="text/javascript">setTimeout(\'window.location="' . $this->Environment->base . $url . '"\', ' . ($intTimeout * 1000) . ');</script>';
				echo '<a href="' . $this->Environment->base . $url . '">Please click here to proceed if you are not using JavaScript</a>';
			}

			echo '</div></div>';
			exit;
		}

		$strToken = md5(uniqid('', true));
		$this->Session->set('tl_newsletter_send', $strToken);
		$sprintf = strlen($objNewsletter->senderName) ? $objNewsletter->senderName . ' &lt;%s&gt;' : '%s';

		// Preview newsletter
		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_newsletter']['headline'].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->script, ENCODE_AMPERSANDS).'" id="tl_newsletter_send" class="tl_form" method="get">
<div class="tl_formbody_edit tl_newsletter_send">
<input type="hidden" name="do" value="' . $this->Input->get('do') . '" />
<input type="hidden" name="table" value="' . $this->Input->get('table') . '" />
<input type="hidden" name="key" value="' . $this->Input->get('key') . '" />
<input type="hidden" name="id" value="' . $this->Input->get('id') . '" />
<input type="hidden" name="token" value="' . $strToken . '" />
<table cellpadding="0" cellspacing="0" class="prev_header" summary="">
  <tr class="row_0">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['from'] . '</td>
    <td class="col_1">' . sprintf($sprintf, $objNewsletter->sender) . '</td>
  </tr>
  <tr class="row_1">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['subject'][0] . '</td>
    <td class="col_1">' . $objNewsletter->subject . '</td>
  </tr>
  <tr class="row_2">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['template'][0] . '</td>
    <td class="col_1">' . $objNewsletter->template . '.tpl</td>
  </tr>' . ((is_array($arrAttachments) && count($arrAttachments) > 0) ? '
  <tr class="row_3">
    <td class="col_0">' . $GLOBALS['TL_LANG']['tl_newsletter']['attachments'] . '</td>
    <td class="col_1">' . implode(', ', $arrAttachments) . '</td>
  </tr>' : '') . '
</table>' . (!$objNewsletter->sendText ? '
<div class="preview_html">
' . $html . '
</div>' : '') . '
<div class="preview_text">
' . nl2br($text) . '
</div>
<div class="tl_tbox">
  <h3><label for="ctrl_recipient">' . $GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][0] . '</label></h3>' . (strlen($_SESSION['TL_PREVIEW_ERROR']) ? '
  <div class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['email'] . '</div>' : '') . '
  <input type="text" name="recipient" id="ctrl_recipient" value="'.$GLOBALS['TL_CONFIG']['adminEmail'].'" class="tl_text" onfocus="Backend.getScrollOffset();" />' . (($GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help">' . $GLOBALS['TL_LANG']['tl_newsletter']['sendPreviewTo'][1] . '</p>' : '') . '
  <h3><label for="ctrl_mpc">' . $GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][0] . '</label></h3>
  <input type="text" name="mpc" id="ctrl_mpc" value="10" class="tl_text" onfocus="Backend.getScrollOffset();" />' . (($GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help">' . $GLOBALS['TL_LANG']['tl_newsletter']['mailsPerCycle'][1] . '</p>' : '') . '
  <h3><label for="ctrl_timeout">' . $GLOBALS['TL_LANG']['tl_newsletter']['timeout'][0] . '</label></h3>
  <input type="text" name="timeout" id="ctrl_timeout" value="1" class="tl_text" onfocus="Backend.getScrollOffset();" />' . (($GLOBALS['TL_LANG']['tl_newsletter']['timeout'][1] && $GLOBALS['TL_CONFIG']['showHelp']) ? '
  <p class="tl_help">' . $GLOBALS['TL_LANG']['tl_newsletter']['timeout'][1] . '</p>' : '') . '
</div>
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="preview" class="tl_submit" alt="preview newsletter" accesskey="p" value="'.specialchars($GLOBALS['TL_LANG']['tl_newsletter']['preview']).'" /> 
<input type="submit" id="send" class="tl_submit" alt="send newsletter" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_newsletter']['send'][0]).'" /> 
</div>

</div>
</form>';

		$_SESSION['TL_PREVIEW_ERROR'] = false;
		return $return;
	}


	/**
	 * Generate the e-mail object and return it
	 * @param object
	 * @param array
	 * @return object
	 */
	private function generateEmailObject(Database_Result $objNewsletter, $arrAttachments)
	{
		$objEmail = new Email();

		$objEmail->from = $objNewsletter->sender;
		$objEmail->subject = $objNewsletter->subject;

		// Add sender name
		if (strlen($objNewsletter->senderName))
		{
			$objEmail->fromName = $objNewsletter->senderName;
		}

		// Attachments
		if (is_array($arrAttachments) && count($arrAttachments) > 0)
		{
			foreach ($arrAttachments as $strAttachment)
			{
				$objEmail->attachFile(TL_ROOT . '/' . $strAttachment);
			}
		}

		return $objEmail;
	}


	/**
	 * Compile the newsletter and send it
	 * @param object
	 * @param object
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	private function sendNewsletter(Email $objEmail, Database_Result $objNewsletter, $arrRecipient, $text, $html, $css)
	{
		// Prepare text content
		$objEmail->text = $this->parseSimpleTokens($text, $arrRecipient);

		// Add HTML content
		if (!$objNewsletter->sendText)
		{
			// Get mail template
			$objTemplate = new FrontendTemplate((strlen($objNewsletter->template) ? $objNewsletter->template : 'mail_default'));

			$objTemplate->title = $objNewsletter->subject;
			$objTemplate->body = $this->parseSimpleTokens($html, $arrRecipient);
			$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
			$objTemplate->css = $css;

			// Parse template
			$objEmail->html = $objTemplate->parse();
			$objEmail->imageDir = TL_ROOT . '/';
		}

		$objEmail->sendTo($arrRecipient['email']);
	}


	/**
	 * Return a form to choose a CSV file and import it
	 * @param object
	 * @return string
	 */
	public function importRecipients()
	{
		if ($this->Input->get('key') != 'import')
		{
			return '';
		}

		// Import CSS
		if ($this->Input->post('FORM_SUBMIT') == 'tl_recipients_import')
		{
			if (!$this->Input->post('source') || !is_array($this->Input->post('source')))
			{
				$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['all_fields'];
				$this->reload();
			}

			foreach ($this->Input->post('source') as $strCsvFile)
			{
				$objFile = new File($strCsvFile);

				if ($objFile->extension != 'csv')
				{
					$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
					continue;
				}

				// Get separator
				switch ($this->Input->post('separator'))
				{
					case 'semicolon':
						$strSeparator = ';';
						break;

					case 'tabulator':
						$strSeparator = '\t';
						break;

					case 'linebreak':
						$strSeparator = '\n';
						break;

					default:
						$strSeparator = ',';
						break;
				}

				$strFile = $objFile->getContent();
				$arrRecipients = trimsplit($strSeparator, $strFile);
				$time = time();

				foreach ($arrRecipients as $strRecipient)
				{
					$this->Database->prepare("DELETE FROM tl_newsletter_recipients WHERE pid=? AND email=?")->execute($this->Input->get('id'), $strRecipient);
					$this->Database->prepare("INSERT INTO tl_newsletter_recipients SET pid=?, tstamp=?, email=?, active=?")->execute($this->Input->get('id'), $time, $strRecipient, 1);
				}
			}

			setcookie('BE_PAGE_OFFSET', 0, 0, '/');
			$this->redirect(str_replace('&key=import', '', $this->Environment->request));
		}

		$objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['source'], 'source', null, 'source', 'tl_newsletter_recipients'));

		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_newsletter_recipients']['import'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, ENCODE_AMPERSANDS).'" id="tl_recipients_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_recipients_import" />

<div class="tl_tbox">
  <h3><label for="separator">'.$GLOBALS['TL_LANG']['MSC']['separator'][0].'</label></h3>
  <select name="separator" id="separator" class="tl_select" onfocus="Backend.getScrollOffset();">
    <option value="comma">'.$GLOBALS['TL_LANG']['MSC']['comma'].'</option>
    <option value="semicolon">'.$GLOBALS['TL_LANG']['MSC']['semicolon'].'</option>
    <option value="tabulator">'.$GLOBALS['TL_LANG']['MSC']['tabulator'].'</option>
    <option value="linebreak">'.$GLOBALS['TL_LANG']['MSC']['linebreak'].'</option>
  </select>'.(strlen($GLOBALS['TL_LANG']['MSC']['separator'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['MSC']['separator'][1].'</p>' : '').'
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_newsletter_recipients']['source'][0].'</label> <a href="typolight/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>
'.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_newsletter_recipients']['source'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_newsletter_recipients']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import style sheet" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_newsletter_recipients']['import'][0]).'" /> 
</div>

</div>
</form>';
	}


	/**
	 * Synchronize newsletter subscription of new users
	 * @param object
	 * @param array
	 */
	public function createNewUser($userId, $arrData)
	{
		$arrNewsletters = deserialize($arrData['newsletter'], true);

		// Return if there are no newsletters
		if (!is_array($arrNewsletters))
		{
			return;
		}

		$time = time();

		// Add recipients
		foreach ($arrNewsletters as $intNewsletter)
		{
			if ($intNewsletter < 1)
			{
				continue;
			}

			$this->Database->prepare("INSERT INTO tl_newsletter_recipients SET pid=?, tstamp=?, email=?, active=1")
						   ->execute(intval($intNewsletter), $time, $arrData['email']);
		}
	}


	/**
	 * Synchronize newsletter subscription of existing users
	 * @param mixed
	 * @param object
	 * @return mixed
	 */
	public function synchronize($varValue, $objUser)
	{
		// If called from the back end, the second argument is a DataContainer object
		if ($objUser instanceof DataContainer)
		{
			$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
									  ->limit(1)
									  ->execute($objUser->id);

			if ($objUser->numRows < 1)
			{
				return $varValue;
			}
		}

		// Delete existing recipients
		$this->Database->prepare("DELETE FROM tl_newsletter_recipients WHERE email=?")
					   ->execute($objUser->email);

		$time = time();
		$varValue = deserialize($varValue, true);

		// Add recipients
		if (is_array($varValue))
		{
			foreach ($varValue as $intId)
			{
				if ($intId < 1)
				{
					continue;
				}

				$this->Database->prepare("INSERT INTO tl_newsletter_recipients SET pid=?, tstamp=?, email=?, active=1")
							   ->execute(intval($intId), $time, $objUser->email);
			}
		}

		return serialize($varValue);
	}


	/**
	 * Update a particular member account
	 * @param integer
	 * @param object
	 */
	public function updateAccount()
	{
		$intUser = $this->Input->get('id');

		// Front end call
		if (TL_MODE == 'FE')
		{
			$this->import('FrontendUser', 'User');
			$intUser = $this->User->id;
		}

		// Edit account
		if (TL_MODE == 'FE' || $this->Input->get('act') == 'edit')
		{
			$objUser = $this->Database->prepare("SELECT email FROM tl_member WHERE id=?")
									  ->limit(1)
									  ->execute($intUser);

			if ($objUser->numRows)
			{
				$objSubscriptions = $this->Database->prepare("SELECT pid FROM tl_newsletter_recipients WHERE email=?")
												   ->execute($objUser->email);

				$strNewsletters = serialize($objSubscriptions->fetchEach('pid'));

				$this->Database->prepare("UPDATE tl_member SET newsletter=? WHERE id=?")
							   ->execute($strNewsletters, $intUser);

				// Update the front end user object
				if (TL_MODE == 'FE')
				{
					$this->User->newsletter = $strNewsletters;
				}
			}
		}

		// Delete account
		elseif ($this->Input->get('act') == 'delete')
		{
			$objUser = $this->Database->prepare("SELECT email FROM tl_member WHERE id=?")
									  ->limit(1)
									  ->execute($intUser);

			if ($objUser->numRows)
			{
				$objSubscriptions = $this->Database->prepare("DELETE FROM tl_newsletter_recipients WHERE email=?")
												   ->execute($objUser->email);
			}
		}
	}


	/**
	 * Get all editable newsletters and return them as array
	 * @param object
	 * @return array
	 */
	public function getNewsletters($dc)
	{
		$objNewsletter = $this->Database->execute("SELECT id, title FROM tl_newsletter_channel");

		if ($objNewsletter->numRows < 1)
		{
			return array();
		}

		$arrNewsletters = array();

		// Back end
		if (TL_MODE == 'BE')
		{
			while ($objNewsletter->next())
			{
				$arrNewsletters[$objNewsletter->id] = $objNewsletter->title;
			}

			return $arrNewsletters;
		}

		// Front end
		$newsletters = deserialize($dc->newsletters, true);

		if (!is_array($newsletters) || count($newsletters) < 1)
		{
			return array();
		}

		while ($objNewsletter->next())
		{
			if (in_array($objNewsletter->id, $newsletters))
			{
				$arrNewsletters[$objNewsletter->id] = $objNewsletter->title;
			}
		}

		return $arrNewsletters;
	}


	/**
	 * Add newsletters to the indexer
	 * @param array
	 * @param integer
	 * @return array
	 */
	public function getSearchablePages($arrPages, $intRoot=0)
	{
		$arrRoot = array();

		if ($intRoot > 0)
		{
			$arrRoot = $this->getChildRecords($intRoot, 'tl_page', true);
		}

		$time = time();
		$arrProcessed = array();

		// Get all channels
		$objNewsletter = $this->Database->execute("SELECT id, jumpTo FROM tl_newsletter_channel");

		// Walk through each channel
		while ($objNewsletter->next())
		{
			if (is_array($arrRoot) && count($arrRoot) > 0 && !in_array($objNewsletter->jumpTo, $arrRoot))
			{
				continue;
			}

			// Get the URL of the jumpTo page
			if (!array_key_exists($objNewsletter->jumpTo, $arrProcessed))
			{
				$arrProcessed[$objNewsletter->jumpTo] = false;

				// Get target page
				$objParent = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=? AND (start='' OR start<?) AND (stop='' OR stop>?) AND published=1")
											->limit(1)
											->execute($objNewsletter->jumpTo, $time, $time);

				// Determin domain
				if ($objParent->numRows)
				{
					$domain = $this->Environment->base;
					$objParent = $this->getPageDetails($objParent->id);

					if (strlen($objParent->domain))
					{
						$domain = ($this->Environment->ssl ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
					}

					$arrProcessed[$objNewsletter->jumpTo] = $domain . $this->generateFrontendUrl($objParent->row(), '/items/%s');
				}
			}

			// Skip events without target page
			if ($arrProcessed[$objNewsletter->jumpTo] === false)
			{
				continue;
			}

			$strUrl = $arrProcessed[$objNewsletter->jumpTo];

			// Get items
			$objItem = $this->Database->prepare("SELECT * FROM tl_newsletter WHERE pid=? AND sent=1 ORDER BY date DESC")
									  ->execute($objNewsletter->id);

			// Add items to the indexer
			while ($objItem->next())
			{
				$arrPages[] = sprintf($strUrl, ((strlen($objItem->alias) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objItem->alias : $objItem->id));
			}
		}

		return $arrPages;
	}
}

?>