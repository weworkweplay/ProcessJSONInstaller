<h2><?php echo $headline?></h2>

<?php foreach ($modules as $slug => $module): ?>

	<hr class="json-installer-hr">

	<?php if ($module->hasDeletableItems()): ?>

		<?php if (!empty($module->uninstalledDependencies)): ?>
			<h3><?php echo $dependenciesHeadline?> <em><?php echo $module->name?></em></h3>
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
			<h3><?php echo $fieldsHeadline?> <em><?php echo $module->name?></em></h3>
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
			<h3><?php echo $templatesHeadline?> <em><?php echo $module->name?></em></h3>
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
			<h3><?php echo $pagesHeadline?> <em><?php echo $module->name?></em></h3>
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

		<h3><em><?php echo $module->name?></em> <?php echo $notInstalledHeadline ?></h3>

	<?php endif ?>




<?php endforeach ?>


<?php if (isset($isNotInstalledYet) && $isNotInstalledYet): ?>
<p>This module does not seem to be installed yet.</p>
<?php endif ?>
<p><a href="../">Go Back</a></p>