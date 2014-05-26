<h2><?php echo $headline?></h2>

<?php foreach ($modules as $slug => $module): ?>

	<hr class="json-installer-hr">

	<?php if ($module->hasDeletableItems()): ?>

		<?php if (!empty($module->uninstalledDependencies)): ?>
			<h3>Dependencies of <em><?php echo $module->name?></em> to be uninstalled</h3>
			<ul class="json-installer-list">
				<?php foreach ($module->uninstalledDependencies as $uninstalledDependency): ?>

					<li>
						<code><?php echo $uninstalledDependency->slug?></code>
						<?php if ($uninstalledDependency->name): ?>
							 - <?php echo $uninstalledDependency->name?>
						<?php endif ?>
					</li>

				<?php endforeach ?>
			</ul>
		<?php endif ?>

		<?php if (!empty($module->deletedFields)): ?>
			<h3>Fields of <em><?php echo $module->name?></em> to be deleted</h3>
			<ul class="json-installer-list">
				<?php foreach ($module->deletedFields as $deletedField): ?>

					<li>
						<code><?php echo $deletedField->name?></code>
						<?php if ($deletedField->label): ?>
							 - <?php echo $deletedField->label?>
						<?php endif ?>
					</li>

				<?php endforeach ?>
			</ul>
		<?php endif ?>

		<?php if (!empty($module->deletedTemplates)): ?>
			<h3>Templates of <em><?php echo $module->name?></em> to be deleted</h3>
			<ul class="json-installer-list">
				<?php foreach ($module->deletedTemplates as $deletedTemplate): ?>

					<li>
						<code><?php echo $deletedTemplate->name?></code>
						<?php if ($deletedTemplate->label): ?>
							 - <?php echo $deletedTemplate->label?>
						<?php endif ?>
					</li>

				<?php endforeach ?>
			</ul>
		<?php endif ?>

		<?php if (!empty($module->deletedPages)): ?>
			<h3>Pages of <em><?php echo $module->name?></em> to be deleted</h3>
			<ul class="json-installer-list">
				<?php foreach ($module->deletedPages as $deletedPage): ?>

					<li>
						<code><?php echo $deletedPage->url?></code>
						<?php if ($deletedPage->title): ?>
							 - <?php echo $deletedPage->title?>
						<?php endif ?>
					</li>

				<?php endforeach ?>
			</ul>
		<?php endif ?>

	<?php else: ?>

		<h3><em><?php echo $module->name?></em> not installed, skipping</h3>

	<?php endif ?>




<?php endforeach ?>


<?php if ($isNotInstalledYet): ?>
<p>This module does not seem to be installed yet.</p>
<p><a href="../">Go Back</a></p>
<?php endif ?>