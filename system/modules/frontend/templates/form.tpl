
<!-- indexer::stop -->
<div class="<?php echo $this->class; if ($this->tableless): ?> tableless<?php endif; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<form action="<?php echo $this->action; ?>" id="<?php echo $this->formId; ?>" method="<?php echo $this->method; ?>" enctype="<?php echo $this->enctype; ?>"<?php echo $this->attributes; ?>>
<div class="formbody">
<?php if ($this->method != 'get'): ?>
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize; ?>" />
<?php endif; ?>
<?php echo $this->hidden; ?>
<?php if (!$this->tableless): ?>
<table cellspacing="0" cellpadding="0" summary="Form fields">
<?php echo $this->fields; ?>
</table>
<?php else: echo $this->fields; endif; ?>
</div>
</form>

</div>
<?php if ($this->hasError): ?>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.scrollTo(null, ($('<?php echo $this->formId; ?>').getElement('div.error').getPosition().y - 20));
//--><!]]>
</script>
<?php endif; ?>

<!-- indexer::continue -->
