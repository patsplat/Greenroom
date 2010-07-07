<h3>Select Model</h3>

<ul>
<?php foreach(Model::defined() as $klass): ?>
<li><a href="<?php echo config('greenroom_admin') ?>?_m=<?php echo $klass ?>"><?php echo $klass ?></a></li>
<?php endforeach; ?>
</ul>