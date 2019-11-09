<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>ЛР 1-3</title>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div id="autors">
			Лабораторная работа 1-3<br />
			Выполнили: 
			<ul>
				<li>Шаблий А.Д.</li>
				<li>Язьков В.В.</li>
				<li>Масликова Е.П.</li>
			</ul>
		</div>
		<center>
			<form action="<?=$_SERVER['SCRIPT_NAME']?>">
				<p> Введите исследуемую фразу </p>
				<input type="text" class="text" name="sentence" style="width: 50%;" value="<?=$_REQUEST['sentence']?>">
				<input type="submit" class="text submit" name="submit" value="Ввод">
			</form>
			<br /> <br />
			<?php 
				require_once('phpMorphyConnection.php');
				if(null!=$_REQUEST['submit']){
					try{
						Main($morphy);
					}
					catch(Exception $e){
						echo $e->getMessage();
					}
				}

				function Main($morphy){
					$sentence = explode(' ', $_REQUEST['sentence']);
					for($i = 0; $i < count($sentence); $i++){
						$sentence[$i] = trim($sentence[$i]);
					}
					$casesMorphy = ['ИМ', 'РД', 'ДТ', 'ВН', 'ТВ', 'ПР'];
					$nouns_array = [];
					$nouns_base_array = [];
					$nouns = [];
					$pretext = [];
					//нахождение существительных и предлога
					foreach($sentence as $parse_sentence){
						$findWordFromMorphy = $morphy->findWord(mb_strtoupper($parse_sentence));
						if(is_scalar($findWordFromMorphy)){
							throw new Exception('Данное слово не найдено в словаре!');
						}

						$isAdj = false;
						$isNoun = false;
						$isPretext = false;

						$findNounBaseForm;
						$findPretextBaseForm;

						foreach($findWordFromMorphy->getByPartOfSpeech('П') as $oneWordForm) {
							$isAdj = true;
						}
						foreach($findWordFromMorphy->getByPartOfSpeech('С') as $oneWordForm) {
							$isNoun = true;
							$findNounBaseForm = $oneWordForm->getBaseForm();
						}
						foreach($findWordFromMorphy->getByPartOfSpeech('ПРЕДЛ') as $oneWordForm) {
							$isPretext = true;
							$findPretextBaseForm = $oneWordForm->getBaseForm();
						}

						if($isAdj == false){
							if($isNoun){
								$nouns_base_array[] = $findNounBaseForm;
								$nouns[] = mb_strtoupper($parse_sentence);
							}
							else if($isPretext){
								$pretext[] = $findPretextBaseForm;
							}
						}
					}
					//нахождение возможных падежей существительных
					foreach ($nouns as $parse_sentence) {
						$findWordFromMorphy = $morphy->findWord(mb_strtoupper($parse_sentence));
						$cases = [];
						
						foreach($findWordFromMorphy->getByPartOfSpeech('С') as $oneWordForm) {
							foreach ($oneWordForm as $form){
								if($form->getWord() == $parse_sentence){
									foreach($casesMorphy as $case){
										if($form->hasGrammems($case)){
											$cases[] = getNumber($case);
										}
									}
								}
							}
						}
						$nouns_array[] = $cases;
					}
					
					$str1 = false;
					$str2 = false;
					$semantic_array = [];

					$wordFromLsdic1 = ReadingFile('lec', $nouns_base_array[0]);
					// printM(113, $wordFromLsdic1);

					$wordFromFrp = ReadingFile('prep', $pretext[0]);
					if (count($wordFromFrp) == 0){
						array_push($wordFromFrp, "");
					}
					// printM(117, $wordFromFrp);

					$wordFromLsdic2 = ReadingFile('lec', $nouns_base_array[1]);
					// printM(121, $wordFromLsdic2);

					foreach ($wordFromFrp as $word_Frp){
						// printM(127, $word_Frp);
						foreach($wordFromLsdic1 as $word_Lsdic){
							// printM(130, $word_Lsdic);
							foreach ($word_Lsdic as $key) {
								if ($key == $word_Frp['str1']){
									$str1 = true;
									break;
								}
							}
						}
						foreach($wordFromLsdic2 as $word_Lsdic){
							// printM(140, $word_Lsdic);
							foreach ($word_Lsdic as $key) {
								if($key == $word_Frp['str2']){
									$str2 = true;
									break;
								}
							}
						}
						if($str1 && $str2){
							foreach ($nouns_array[1] as $case) {
								// printM(157, $case);
								if($case == $word_Frp['case']){
									$semantic_array[] = $word_Frp['rel'];
									// printM(161, $word_Frp['rel']);
									// printM(162, $semanticRelation);
								}

							}
						}

					}
					echo 'Найдены следующие смысловые отношения: ' . implode(',', $semantic_array);
				}

				function ReadingFile($where, $what){
					if ($where == 'prep'){
						// print_r("<br />" . "179: " . $what . "<br />");
						$result = [];
						$content = json_decode(file_get_contents("../Frp.json"), JSON_UNESCAPED_UNICODE);
						if ($content == null or count($content) == 0){
							echo "<center>" . "<br />" . "Словарь Frp пуст" . "<br />" . "</center>";
							return;
						}
						for ($i = 0; $i < count($content); $i ++){
							if ($content[$i]["prep"] == $what){
								array_push($result, $content[$i]);
							}
						}
						return $result;
					}
					else{
						$result = [];
						$content = json_decode(file_get_contents("../Lsdic.json"), JSON_UNESCAPED_UNICODE);
						if ($content == null or count($content) == 0){
							echo "<center>" . "<br />" . "Словарь Lsdic пуст" . "<br />" . "</center>";
							return;
						}
						for ($i = 0; $i < count($content); $i ++){
							if ($content[$i]["word"] == $what){
								array_push($result, $content[$i]);
							}
						}
						return $result;
					}
				}

				// function printM($number, $message){
				// 	print_r("<pre>" . $number . ": ");
				// 	try{
				// 		$message = json_encode($message, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				// 	}
				// 	catch(Exception $e){}
				// 	print_r($message . "</pre>");
				// }

				function getNumber($case){
					$result;
					switch($case){
						case 'ИМ': $result = 1; break;
						case 'РД': $result = 2; break;
						case 'ДТ': $result = 3; break;
						case 'ВН': $result = 4; break;
						case 'ТВ': $result = 5; break;
						case 'ПР': $result = 6; break;
					}
					return $result;
				}
			?>
		</center>
	</body>
</html>