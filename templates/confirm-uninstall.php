<h2><?php echo $headline?></h2>


<?php if (!empty($deletedFields)): ?>
	<h3>Fields to be deleted</h3>
	<ul class="json-installer-list">
		<?php foreach ($deletedFields as $deletedField): ?>

			<li><code><?php echo $deletedField?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($deletedTemplates)): ?>
	<h3>Templates to be deleted</h3>
	<ul class="json-installer-list">
		<?php foreach ($deletedTemplates as $deletedTemplate): ?>

			<li><code><?php echo $deletedTemplate?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($deletedPages)): ?>
	<h3>Pages to be deleted</h3>
	<ul class="json-installer-list">
		<?php foreach ($deletedPages as $deletedPage): ?>

			<li><code><?php echo $deletedPage?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ($isNotInstalledYet): ?>
<p>This module does not seem to be installed yet.</p>
<p><a href="../">Go Back</a></p>
<?php endif ?>