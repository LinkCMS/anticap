Контоллер: <?php echo App::$instance -> requset -> controller -> name ?><br>
Действие: <?php echo App::$instance -> requset -> action ?><br>
Вот так вот отображаются параметры модели: <?php echo $model -> variable ?>
<hr>
<?php $this -> render('test') ?>
