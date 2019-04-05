<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(['options' => ['class' => 'form-inline']]); ?>

    <?= $form->field($model, 'user') ?>

    <div class="form-group">
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<?php foreach ($userTweets as $user => $tweet): ?>
	<h1><?= $user ?></h1>
	<div class="list-group">
		<?php foreach ($tweet as $n => $data): ?>
			<div class="list-group-item tweet">
				<img class="list-group-item-image" src="<?= $data['profile_image_url'] ?>">
				<h4 class="list-group-item-heading"><?= $data['name'] ?></h4>
				<p class="list-group-item-text"><?= $data['text'] ?></p>
				<p class="list-group-item-created"><?= $data['created_at'] ?></p>
				<?php if ($data['media_url']): ?>
					<img class="list-group-item-media" src="<?= $data['media_url'] ?>">
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endforeach; ?>

<?php



