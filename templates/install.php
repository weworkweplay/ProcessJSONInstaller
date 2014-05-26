<h2><?php echo $headline?></h2>

<?php if (!empty($installedDependencies)): ?>
	<h3>Installed dependencies</h3>
	<ul class="json-installer-list">
		<?php foreach ($installedDependencies as $installedDependency): ?>

			<li><code><?php echo $installedDependency->name?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($installedFields)): ?>
	<h3>Created or edited fields</h3>
	<ul class="json-installer-list">
		<?php foreach ($installedFields as $installedField): ?>

			<li><code><?php echo $installedField->name?></code> - <?php echo $installedField->label?></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($installedTemplates)): ?>
	<h3>Created or edited templates</h3>
	<ul class="json-installer-list">
		<?php foreach ($installedTemplates as $installedTemplate): ?>

			<li><code><?php echo $installedTemplate->name?></code> - <?php echo $installedTemplate->label?></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if (!empty($installedPages)): ?>
	<h3>Created or edited pages</h3>
	<ul class="json-installer-list">
		<?php foreach ($installedPages as $installedPage): ?>

			<li><code><?php echo $installedPage->url?></code></li>

		<?php endforeach ?>
	</ul>
<?php endif ?>

<p><a href="../">Go Back</a></p>