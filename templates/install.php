<?php if (!empty($installedFields)): ?>
	<h2>Created or edited fields</h2>
	<ul class="json-installer-list">
		<?php foreach ($installedFields as $installedField): ?>

			<li><code><?php echo $installedField->name?></code> - <?php echo $installedField->label?></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($installedTemplates)): ?>
	<h2>Created or edited templates</h2>
	<ul class="json-installer-list">
		<?php foreach ($installedTemplates as $installedTemplate): ?>

			<li><code><?php echo $installedTemplate->name?></code> - <?php echo $installedTemplate->label?></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($installedPages)): ?>
	<h2>Created or edited templates</h2>
	<ul class="json-installer-list">
		<?php foreach ($installedPages as $installedPage): ?>

			<li><?php echo $installedPage->name?></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<p><a href="../">Go Back</a></p>