
<div class="layout_short block<?php echo $this->class; ?>">
<?php if ($this->hasMetaFields): ?>
<p class="info"><?php echo $this->date; ?> <?php echo $this->author; ?> <?php echo $this->commentCount; ?></p>
<?php endif; ?>
<h2><?php echo $this->linkHeadline; ?></h2>
<p class="teaser"><?php echo $this->teaser; ?></p>
<?php if ($this->text): ?>
<p align="right" class="more"><?php echo $this->more; ?></p>
<?php endif; ?><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="12" background="images/bglinija.jpg"> </td>
  </tr>
</table>
</div>
