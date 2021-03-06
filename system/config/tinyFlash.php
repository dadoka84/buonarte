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
 * @package    Config
 * @license    LGPL
 * @filesource
 */


/**
 * This is the tinyMCE (rich text editor) configuration file for flash content. 
 * Please visit http://tinymce.moxiecode.com for more information.
 */
?>
<script type="text/javascript" src="<?php echo $this->base; ?>plugins/tinyMCE/tiny_mce_gzip.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
tinyMCE_GZ.init({
  plugins : "autosave,contextmenu,directionality,inlinepopups,paste,safari,save,searchreplace,spellchecker,template,typolinks",
  themes : "advanced",
  languages : "<?php echo $this->language; ?>",
  disk_cache : false,
  debug : false
});
//--><!]]>
</script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
tinyMCE.init({
  mode : "exact",
  height : "300",
  language : "<?php echo $this->language; ?>",
  elements : "<?php echo $this->rteFields; ?>",
  inline_styles : false,
<?php if ($this->brNewLine): ?>
  forced_root_block : false,
  force_p_newlines : false,
  force_br_newlines : true,
<?php endif; ?>
  remove_linebreaks : false,
  force_hex_style_colors : true,
  fix_list_elements : true,
  fix_table_elements : true,
  font_size_style_values : "1,2,3,4,5,6,7",
  document_base_url : "<?php echo $this->base; ?>",
  entities : "160,nbsp,60,lt,62,gt",
  cleanup_on_startup : true,
  save_enablewhendirty : true,
  save_on_tinymce_forms : true,
  save_callback : "TinyCallback.cleanHTML",
  plugins : "autosave,contextmenu,directionality,inlinepopups,paste,safari,save,searchreplace,spellchecker,template,typolinks",
  spellchecker_languages : "+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv",
  valid_elements : "+a[href|target|class],-b/strong,-i/em,-u,+div[align],+p[class|align],-li,br,-span[class],font[face|size|color]",
  content_css : "<?php echo TL_PATH; ?>/basic.css,<?php echo TL_PATH; ?>/system/themes/tinymce.css",
  tab_focus : ":prev,:next",
  theme : "advanced",
  theme_advanced_resizing : true,
  theme_advanced_resize_horizontal : false,
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_statusbar_location : "bottom",
  theme_advanced_source_editor_width : "700",
  theme_advanced_blockformats : "div,p,address,pre,h1,h2,h3,h4,h5,h6",
  theme_advanced_buttons1 : "newdocument,save,separator,spellchecker,separator,typolinks,unlink,separator,charmap,separator,search,replace,pasteword,separator,undo,redo,separator,removeformat,cleanup,separator,code",
  theme_advanced_buttons2 : "fontsizeselect,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,separator,forecolor",
  theme_advanced_buttons3 : ""
});
//--><!]]>
</script>
