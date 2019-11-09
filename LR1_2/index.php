<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>ЛР 1-2</title>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div id="autors">
			Лабораторная работа 1-2<br />
			Выполнили: 
			<ul>
				<li>Шаблий А.Д.</li>
				<li>Язьков В.В.</li>
				<li>Масликова Е.П.</li>
			</ul>
		</div>
		<form action="<?=$_SERVER['SCRIPT_NAME']?>">
			<div id="Lsdic">
				<center>
					<input type="radio" name="checkType" value="ЛСС"><label>Лексико-семантический словарь</label><br /><br />
				</center>
				<input class="text" type="text" name="enteredWord" value="<?=$_REQUEST['enteredWord']?>">
				<label>Слово</label>
				<br /><br />
				<input class="text" type="text" name="enteredType" value="<?=$_REQUEST['enteredType']?>">
				<label>Часть речи (сущ, глаг, прил, нар)</label>
				<br /><br />
				<input class="text" type="text" name="enteredMeaning" value="<?=$_REQUEST['enteredMeaning']?>">
				<label>Значение слова</label>
				<br /><br />
				<input class="text" type="text" name="enteredSem1" value="<?=$_REQUEST['enteredSem1']?>">
				<label>Семантика 1</label>
				<br /><br />
				<input class="text" type="text" name="enteredSem2" value="<?=$_REQUEST['enteredSem2']?>">
				<label>Семантика 2</label>
				<br /><br />
				<input class="text" type="text" name="enteredSem3" value="<?=$_REQUEST['enteredSem3']?>">
				<label>Семантика 3</label>
				<br /><br />
				<input class="text" type="text" name="enteredComment" value="<?=$_REQUEST['enteredComment']?>">
				<label>Комментарий</label>
			</div><div id="Frp">
				<center>
					<input type="radio" name="checkType" value="Словарь предложных семантико-синтаксических фреймов"><label>Словарь предложных семантико-синтаксических фреймов</label><br /><br />
				</center>
				<input class="text" type="text" name="enteredPrep" value="<?=$_REQUEST['enteredPrep']?>">
				<label>Предлог</label>
				<br /><br />
				<input class="text" type="text" name="enteredSr1" value="<?=$_REQUEST['enteredSr1']?>">
				<label>Семантика 1</label>
				<br /><br />
				<input class="text" type="text" name="enteredSr2" value="<?=$_REQUEST['enteredSr2']?>">
				<label>Семантика 2</label>
				<br /><br />
				<input class="text" type="text" name="enteredCase" value="<?=$_REQUEST['enteredCase']?>">
				<label>Падеж (ИМ, Р, Д, В, Т, П)</label>
				<br /><br />
				<input class="text" type="text" name="enteredRel" value="<?=$_REQUEST['enteredRel']?>">
				<label>Смысловое отношение</label>
				<br /><br />
				<input class="text" type="text" name="enteredExpl" value="<?=$_REQUEST['enteredExpl']?>">
				<div style="display: inline-block; width: 300px; vertical-align: middle;">
					Пример выражения, в котором реализуется то же отношение
				</div>
				<br /><br />
			</div>
				<br /><br />
				<center>
					<input class="text submit" type="submit" name="createButton" value="Ввод" style="width: 100px;">
				</center>
		</form>
		<?php
			if ($_REQUEST['checkType'] == "ЛСС"){
				if($_REQUEST['createButton'] != null and
					$_REQUEST['enteredWord'] != null and
					$_REQUEST['enteredType'] != null and
					$_REQUEST['enteredMeaning'] != null and
					($_REQUEST['enteredSem1'] != null or $_REQUEST['enteredSem2'] != null or $_REQUEST['enteredSem3'] != null)){
					$content = json_decode(file_get_contents("../Lsdic.json"), JSON_UNESCAPED_UNICODE);
						// Если файл пустой, инициализируем пустой массив
					if ($content == null){
						$content = [];
					}

					$id = count($content) + 1;
					$word = mb_strtoupper($_REQUEST['enteredWord']);
					$type = mb_strtoupper($_REQUEST['enteredType']);
						// Проверка, введен ли корректная часть речи
					$types = ["СУЩ", "ГЛАГ", "ПРИЛ", "НАР"];
					if (!in_array($type, $types)){
						echo "<center>" . "<br />" . "Неверная часть речи" . "<br />" . "</center>";
					}
					else{
						$meaning = mb_strtoupper($_REQUEST['enteredMeaning']);
						$sem1 = ($_REQUEST['enteredSem1'] != null) ? mb_strtoupper($_REQUEST['enteredSem1']) : "nil";
						$sem2 = ($_REQUEST['enteredSem2'] != null) ? mb_strtoupper($_REQUEST['enteredSem2']) : "nil";
						$sem3 = ($_REQUEST['enteredSem3'] != null) ? mb_strtoupper($_REQUEST['enteredSem3']) : "nil";
						$comment = ($_REQUEST['enteredComment'] != null) ? $_REQUEST['enteredComment'] : "nil";

							// Итоговый набор значений
						$newObj = [
							"id" => $id,
							"word" => $word,
							"type" => $type,
							"meaning" => $meaning,
							"sem1" => $sem1,
							"sem2" => $sem2,
							"sem3" => $sem3,
							"comment" => $comment
						];

						array_push($content, $newObj);
						echo "<center>" . "<br />" . "Значения добавлены в словарь" . "<br />" . "</center>";
						

						file_put_contents("../Lsdic.json", json_encode($content, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					}
				}
				else{
					echo "<center>" . "<br />" . "Введите все необходимые поля" . "<br />" . "</center>";
				}
			}
			else if ($_REQUEST['checkType'] == "Словарь предложных семантико-синтаксических фреймов"){
				if( //$_REQUEST['enteredPrep'] != null and
				   $_REQUEST['enteredSr1'] != null and
				   $_REQUEST['enteredSr2'] != null and
				   $_REQUEST['enteredCase'] != null and
				   $_REQUEST['enteredRel'] != null and
				   $_REQUEST['enteredExpl'] != null){
					$content = json_decode(file_get_contents("../Frp.json"), JSON_UNESCAPED_UNICODE);
						// Если файл пустой, инициализируем пустой массив
					if ($content == null){
						$content = [];
					}

					$id = count($content) + 1;
					$prep = ($_REQUEST['enteredPrep'] != null) ? mb_strtoupper($_REQUEST['enteredPrep']) : "";
					$str1 = mb_strtoupper($_REQUEST['enteredSr1']);
					$str2 = mb_strtoupper($_REQUEST['enteredSr2']);

					$case = mb_strtoupper($_REQUEST['enteredCase']);
					switch ($case) {
						case 'ИМ':	$case = 1; break;
						case 'Р':	$case = 2; break;
						case 'Д':	$case = 3; break;
						case 'В':	$case = 4; break;
						case 'Т':	$case = 5; break;
						case 'П':	$case = 6; break;
						default: echo "<center>" . "<br />" . "Падеж не определен" . "<br />" . "</center>";
					}

					$rel = mb_strtoupper($_REQUEST['enteredRel']);
					$expl = $_REQUEST['enteredExpl'];

						// Итоговый набор значений
					if (gettype($case) == "integer"){
						$newObj = [
							"id" => $id,
							"prep" => $prep,
							"str1" => $str1,
							"str2" => $str2,
							"case" => $case,
							"rel" => $rel,
							"expl" => $expl,
						];

						array_push($content, $newObj);
						echo "<center>" . "<br />" . "Значения добавлены в словарь" . "<br />" . "</center>";

						file_put_contents("../Frp.json", json_encode($content, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
					}
				}
				else{
					echo "<center>" . "<br />" . "Введите все необходимые поля" . "<br />" . "</center>";
				}
			}
			else{
				echo "<center>" . "<br />" . "Укажите словарь" . "<br />" . "</center>";
			}
		?>
	</body>
</html>