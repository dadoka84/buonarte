
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<table cellpadding="0" cellspacing="0" class="single_record" summary="">
<tbody>
<?php foreach ($this->record as $col): ?>
  <tr class="<?php echo $col['class']; ?>">
    <td class="label"><?php echo $col['label']; ?></td>
    <td class="value"><?php echo $col['content']; ?></td>
  </tr>
<?php endforeach; ?> 
</tbody>
</table>

<div class="go_back"><a href="<?php echo $this->referer; ?>" title="<?php echo $this->back; ?>"><?php echo $this->back; ?></a></div>

</div>
