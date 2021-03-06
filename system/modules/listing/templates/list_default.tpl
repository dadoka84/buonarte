
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>
<?php if ($this->searchable): ?>

<div class="list_search">
<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="sort" value="<?php echo $this->sort; ?>" />
<input type="hidden" name="per_page" value="<?php echo $this->per_page; ?>" />
<select name="search" class="select">
<?php echo $this->search_fields; ?>
</select>
<input type="text" name="for" class="text" value="<?php echo $this->for; ?>" />
<input type="submit" class="submit" value="<?php echo $this->search_label; ?>" />
</div>
</form>
</div>
<?php endif; ?>
<?php if ($this->per_page): ?>

<div class="list_per_page">
<form action="<?php echo $this->action; ?>" method="get">
<div class="formbody">
<input type="hidden" name="order_by" value="<?php echo $this->order_by; ?>" />
<input type="hidden" name="sort" value="<?php echo $this->sort; ?>" />
<input type="hidden" name="search" value="<?php echo $this->search; ?>" />
<input type="hidden" name="for" value="<?php echo $this->for; ?>" />
<select name="per_page" class="select">
  <option value="10"<?php if ($this->per_page == 10): ?> selected="selected"<?php endif; ?>>10</option>
  <option value="20"<?php if ($this->per_page == 20): ?> selected="selected"<?php endif; ?>>20</option>
  <option value="50"<?php if ($this->per_page == 50): ?> selected="selected"<?php endif; ?>>50</option>
  <option value="100"<?php if ($this->per_page == 100): ?> selected="selected"<?php endif; ?>>100</option>
  <option value="250"<?php if ($this->per_page == 250): ?> selected="selected"<?php endif; ?>>250</option>
  <option value="500"<?php if ($this->per_page == 500): ?> selected="selected"<?php endif; ?>>500</option>
</select>
<input type="submit" class="submit" value="<?php echo $this->per_page_label; ?>" />
</div>
</form>
</div>
<?php endif; ?>

<table cellpadding="0" cellspacing="0" class="all_records" summary="">
<thead>
  <tr>
<?php foreach ($this->thead as $col): ?>
    <th class="head<?php echo $col['class']; ?>"><a href="<?php echo $col['href']; ?>" title="<?php echo $col['title']; ?>"><?php echo $col['link']; ?></a></th>
<?php endforeach; ?>
<?php if ($this->details): ?>
    <th class="head col_last">&nbsp;</th>
<?php endif; ?>
  </tr>
</thead>
<tbody>
<?php foreach ($this->tbody as $class=>$row): ?>
  <tr class="<?php echo $class; ?>">
<?php foreach ($row as $col): ?>
    <td class="body <?php echo $col['class']; ?>"><?php echo $col['content']; ?></td>
<?php endforeach; ?>
<?php if ($this->details): ?>
    <td class="body <?php echo $this->col_last; ?> col_last"><a href="<?php echo $this->url; ?>?id=<?php echo $col['id']; ?>"><img src="system/modules/listing/html/details.gif" alt="" /></a></td>
<?php endif; ?>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
<?php echo $this->pagination; ?>

</div>
