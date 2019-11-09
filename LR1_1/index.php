<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>ЛР 1-1</title>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div id="autors">
			Лабораторная работа 1-1<br />
			Выполнили: 
			<ul>
				<li>Шаблий А.Д.</li>
				<li>Язьков В.В.</li>
				<li>Масликова Е.П.</li>
			</ul>
		</div>
		<center>
			<form action="<?=$_SERVER['SCRIPT_NAME']?>">
				<p> Введите слово для начала анализа</p>
				<input class="text" type="text" name="enteredWord" value="<?=$_REQUEST['enteredWord']?>">
				<input class="text submit" type="submit" name="findWordForms" value="Ввод">
			</form>
			<?php 
				require_once('phpMorphyConnection.php');
				if($_REQUEST['findWordForms'] != null){
					$string = $morphy->findWord(mb_strtoupper($_REQUEST['enteredWord']));
					if(is_scalar($string)){
						echo 'Данное слово не найдено в словаре!';
					}
					else{
						foreach ($string as $word){
							$foundWordForm = $word->getFoundWordForm();
							foreach($foundWordForm as $wordForm){
								echo "<p>" . $wordForm->getWord(), ' - образовано от ', $word->GetBaseForm(), '(', $wordForm->getPartOfSpeech(), ' ', implode(',', $wordForm->getGrammems()),')' . "</p>";
							}
						}
					}
				}
			?>
		</center>
	</body>
</html>