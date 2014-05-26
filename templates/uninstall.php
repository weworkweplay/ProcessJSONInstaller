<h2><?php echo $headline?></h2>

<?php foreach ($modules as $slug => $module): ?>

	<hr class="json-installer-hr">

	<?php if (!empty($module->uninstalledDependencies)): ?>
		<h3>Uninstalled dependencies of <em><?php echo $module->name?></em></h3>
		<ul class="json-installer-list">
			<?php foreach ($module->uninstalledDependencies as $uninstalledDependency): ?>

				<li><?php echo $uninstalledDependency->name?></li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->deletedFields)): ?>
		<h3>Uninstalled fields of <em><?php echo $module->name?></em></h3>
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
		<h3>Uninstalled templates of <em><?php echo $module->name?></em></h3>
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
		<h3>Uninstalled pages of <em><?php echo $module->name?></em></h3>
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
<?php endforeach ?>


<p><a href="../">Go Back</a></p>