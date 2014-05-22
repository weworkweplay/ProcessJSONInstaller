<?php if (!empty($deletedFields)): ?>
	<h2>Deleted fields</h2>
	<ul class="json-installer-list">
		<?php foreach ($deletedFields as $deletedField): ?>

			<li><code><?php echo $deletedField?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($deletedTemplates)): ?>
	<h2>Deleted templates</h2>
	<ul class="json-installer-list">
		<?php foreach ($deletedTemplates as $deletedTemplate): ?>

			<li><code><?php echo $deletedTemplate?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($deletedPages)): ?>
	<h2>Deleted templates</h2>
	<ul class="json-installer-list">
		<?php foreach ($deletedPages as $deletedPage): ?>

			<li><code><?php echo $deletedPage?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<p><a href="../">Go Back</a></p>