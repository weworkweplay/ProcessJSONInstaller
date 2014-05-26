<h2><?php echo $headline?></h2>

<?php foreach ($modules as $slug => $module): ?>

	<hr class="json-installer-hr">

	<?php if (!empty($module->installedDependencies)): ?>
		<h3>Installed <em><?php echo $module->name?></em> dependencies</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->installedDependencies as $installedDependency): ?>
				<li><?php echo $installedDependency->name?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->fields)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> fields</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->fields as $installedField): ?>

				<li>
					<code><?php echo $installedField->name?></code>
					<?php if ($installedField->label): ?>
						 - <?php echo $installedField->label?>
					<?php endif ?>
				</li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->templates)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> templates</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->templates as $installedTemplate): ?>

				<li>
					<code><?php echo $installedTemplate->name?></code>
					<?php if ($installedTemplate->label): ?>
						 - <?php echo $installedTemplate->label?>
					<?php endif ?>
				</li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->pages)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> pages</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->pages as $installedPage): ?>

				<li>
					<code><?php echo $installedPage->url?></code>
					<?php if ($installedPage->title): ?>
						 - <?php echo $installedPage->title?>
					<?php endif ?>
				</li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($skippedItems)): ?>
		<h3>Skipped items</h3>
		<ul class="json-installer-list">
			<?php foreach ($skippedItems as $skippedItem): ?>

				<li>
					<strong><?php echo $skippedItem->type?></strong>: <?php echo $skippedItem->name?>,
					<strong>Reason</strong>: <?php echo $skippedItem->reason?>,
					<strong>From</strong>: <?php echo $skippedItem->module->name?>
				</li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

<?php endforeach ?>



<p><a href="../">Go Back</a></p>