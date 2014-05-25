<h2><?php echo $headline?></h2>

<?php foreach ($modules as $slug => $module): ?>

	<hr class="json-installer-hr">

	<?php if (!empty($module->installedDependencies)): ?>
		<h3>Installed <em><?php echo $module->name?></em> dependencies</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->installedDependencies as $installedDependency): ?>

				<li>
					<code>
						<?php echo $installedDependency->name?>
						<?php if ($installedDependency->json): ?>
							(<?php echo $installedDependency->json?>)
						<?php endif ?>
					</code>
				</li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->fields)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> fields</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->fields as $installedField): ?>

				<li><code><?php echo $installedField->name?></code> - <?php echo $installedField->label?></li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->templates)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> templates</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->templates as $installedTemplate): ?>

				<li><code><?php echo $installedTemplate->name?></code> - <?php echo $installedTemplate->label?></li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (!empty($module->pages)): ?>
		<h3>Created or edited <em><?php echo $module->name?></em> pages</h3>
		<ul class="json-installer-list">
			<?php foreach ($module->pages as $installedPage): ?>

				<li><code><?php echo $installedPage->url?></code></li>

			<?php endforeach ?>
		</ul>
	<?php endif ?>

<?php endforeach ?>



<p><a href="../">Go Back</a></p>