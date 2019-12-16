<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>ЛР 3</title>
		<meta charset='utf-8'>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div id="autors">
			Лабораторная работа 3<br />
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
						// echo $e->getMessage();
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
					$adjectives = [];
					
					$index_of_word = 0;
					$sequence_of_words = [];
					$sequence_of_part_of_speech = [];
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
							$adjectives[] = $oneWordForm->getBaseForm();
							
							$sequence_of_words[$index_of_word] = $oneWordForm->getBaseForm();
							$sequence_of_part_of_speech[$index_of_word] = "adj";
							$index_of_word += 1;
						}
						foreach($findWordFromMorphy->getByPartOfSpeech('С') as $oneWordForm) {
							if ($oneWordForm->getBaseForm() != "ИЗА" &&
								$oneWordForm->getBaseForm() != "КРАСНЫЙ" &&
								$oneWordForm->getBaseForm() != "КРАСНОЕ" &&
								$oneWordForm->getBaseForm() != "ДЕШЕВАЯ"
							){	
								$isNoun = true;
								$findNounBaseForm = $oneWordForm->getBaseForm();
								$sequence_of_words[$index_of_word] = $oneWordForm->getBaseForm();
								$sequence_of_part_of_speech[$index_of_word] = "noun";
								$index_of_word += 1;
							}
						}
						foreach($findWordFromMorphy->getByPartOfSpeech('ПРЕДЛ') as $oneWordForm) {
							$isPretext = true;
							$findPretextBaseForm = $oneWordForm->getBaseForm();

							$sequence_of_words[$index_of_word] = $oneWordForm->getBaseForm();
							$sequence_of_part_of_speech[$index_of_word] = "pretext";
							$index_of_word += 1;
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
					// printM(106, $wordFromLsdic1);

					$wordFromFrp = ReadingFile('prep', $pretext[0]);
					if (count($wordFromFrp) == 0){
						array_push($wordFromFrp, "");
					}
					// printM(112, $wordFromFrp);

					$wordFromLsdic2 = ReadingFile('lec', $nouns_base_array[1]);
					// printM(115, $wordFromLsdic2);

					foreach ($wordFromFrp as $word_Frp){
						// printM(118, $word_Frp);
						foreach($wordFromLsdic1 as $word_Lsdic){
							// printM(120, $word_Lsdic);
							foreach ($word_Lsdic as $key) {
								if ($key == $word_Frp['str1']){
									$str1 = true;
									break;
								}
							}
						}
						foreach($wordFromLsdic2 as $word_Lsdic){
							// printM(129, $word_Lsdic);
							foreach ($word_Lsdic as $key) {
								if($key == $word_Frp['str2']){
									$str2 = true;
									break;
								}
							}
						}
						if($str1 && $str2){
							foreach ($nouns_array[1] as $case) {
								// printM(139, $case);
								if($case == $word_Frp['case']){
									$semantic_array[] = $word_Frp['rel'];
									// printM(142, $word_Frp['rel']);
									// printM(143, $semanticRelation);
								}

							}
						}

					}
					// echo 'Найдены следующие смысловые отношения: ' . implode(',', $semantic_array);
					// echo "<br />";
					// echo "Предлог: ";
					// print_r($pretext);
					// echo "<br />";
					// echo "Прилагательные: ";
					// print_r($adjectives);
					// echo "<br />";
					// echo "Последовательность слов: ";
					// print_r($sequence_of_words);
					// echo "<br />";
					// echo "Последовательность частей речи";
					// print_r($sequence_of_part_of_speech);
					// echo "<br />";

					// Поиск первого существительного
					$index_of_first_noun = 0;
					for ($i = 0; $i < count($sequence_of_part_of_speech); $i ++){
						if ($sequence_of_part_of_speech[$i] == "noun"){
							$index_of_first_noun = $i;
							break;
						}
					}
					// echo "Индекс первого существительного: ";
					// echo $index_of_first_noun;
					// echo "<br />";
					// Поиск последнего существительного
					$index_of_last_noun = count($sequence_of_words) - 1;
					for ($i = $index_of_first_noun + 1; $i < count($sequence_of_words); $i ++){
						if ($sequence_of_part_of_speech[$i] == "noun"){
							$index_of_last_noun = $i;
							break;
						}
					}
					// echo "Индекс последнего существительного: ";
					// echo $index_of_last_noun;
					// echo "<br />";
					// Поиск прилагательных до первого существительного
					$is_adj_before_first_noun = false;
					for ($i = 0; $i < $index_of_first_noun; $i ++){
						if ($sequence_of_part_of_speech[$i] == "adj"){
							$is_adj_before_first_noun = true;
							break;
						}
					}
					// echo "Наличие прилагательных до первого существительного: ";
					// echo $is_adj_before_first_noun ? "true" : "false";
					// echo "<br />";
					// Если нет прилагательных до существительного
					
					$concept1;
					// if ($is_adj_before_first_noun == false){
						// echo "concept1: ";
						// подбираем все сематики существительного 1
						
						$content = json_decode(file_get_contents("../Lsdic.json"), JSON_UNESCAPED_UNICODE);
						for ($i = 0; $i < count($content); $i ++){
							if ($content[$i]["word"] == $sequence_of_words[$index_of_first_noun]){
								$concept1 = $content[$i]["meaning"];
							}
						}
						// echo $concept1;
					// }
					// Если есть прилагательные до существительного
					// else{
						// Если непосредественно перед существительным идет прилагательное вида 
						if ($sequence_of_part_of_speech[$index_of_first_noun - 1] == "adj"){
							// echo "concept1: ";
							// подбираем все сематики существительного 1
							
							$content = json_decode(file_get_contents("../Lsdic.json"), JSON_UNESCAPED_UNICODE);
							
							// echo "<br />" . "----" . "<br />" . $sequence_of_words[$index_of_first_noun] . "<br />" . "----" . "<br />";

							for ($i = 0; $i < count($content); $i ++){
								if ($content[$i]["word"] == $sequence_of_words[$index_of_first_noun]){
									$concept1 = $content[$i]["meaning"];

								}
							}
							// ищем семантики соответствующего прилагательного
							$concept_of_adj;
							for ($i = 0; $i < count($content); $i ++){
								if ($content[$i]["word"] == $sequence_of_words[$index_of_first_noun - 1]){
									$concept_of_adj = $content[$i]["meaning"];
									$parts = explode("(", $concept_of_adj);
									$fPart = $parts[0];
									$parts = explode(" ", $concept_of_adj);
									$sPart = $parts[1];
									$sPart = str_replace(")", "", $sPart);
									$concept_of_adj = "(" . $fPart . ", " . $sPart . ")";
								}
							}
							$concept1 = $concept1 . "*" . $concept_of_adj;
							// echo $concept1;
						}
					// }


					$Semrepr = "Запрос(S1, " . "Качеств-состав(S1, " . $concept1;



					$concept2;
					$content = json_decode(file_get_contents("../Lsdic.json"), JSON_UNESCAPED_UNICODE);
					for ($i = 0; $i < count($content); $i ++){
						if ($content[$i]["word"] == $sequence_of_words[$index_of_last_noun]){
							// // echo "<br />";
							// // echo "------------";
							// // echo "$content[$i]['meaning']";
							$concept2 =$content[$i]["sem1"] . " нек, " . $content[$i]["meaning"];
							break;
						}
					}
					// $concept2 = "нек. " . $concept2;
					// echo "<br />";
					// echo "concept2: ";
					// echo $concept2;


					

					$Semrepr = $Semrepr . "(" . $semantic_array[0] . ", " . $concept2;
					
					// // echo "<br />" . "---" . $sequence_of_words[$index_of_last_noun + 1] . "<br />" . "--------" . "<br />";

					// for ($i = 0; $i < count($content); $i ++){
					// 	if($sequence_of_words[$index_of_last_noun + 1] == $content[$i]["word"]){
					// 		// echo "<br />" . "---------" . "<br />" . $content[$i]["meaning"] . "<br />" . "---------" . "<br />";
					// 	}
					// }

					// Если после второго существительного идет имя собственное
					if ($sequence_of_words[$index_of_last_noun] < count($sequence_of_words) - 1){
						if ($sequence_of_part_of_speech[$index_of_last_noun + 1] == "noun"){
							for ($i = 0; $i < count($content); $i ++){
								if ($sequence_of_words[$index_of_last_noun + 1] == $content[$i]["word"]){
									if ($content[$i]["sem1"] == "НАЗВАНИЕ"){
										$Semrepr = $Semrepr . '*(Назв, "' . $content[$i]["word"] . '")';
									}
								}
							}
						}
					}
					echo "<br />" . "Semrepr: " . $Semrepr . ")))";

				}

				function ReadingFile($where, $what){
					if ($where == 'prep'){
						// // print_r("<br />" . "179: " . $what . "<br />");
						$result = [];
						$content = json_decode(file_get_contents("../Frp.json"), JSON_UNESCAPED_UNICODE);
						if ($content == null or count($content) == 0){
							// echo "<center>" . "<br />" . "Словарь Frp пуст" . "<br />" . "</center>";
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
							// echo "<center>" . "<br />" . "Словарь Lsdic пуст" . "<br />" . "</center>";
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
				// 	// print_r("<pre>" . $number . ": ");
				// 	try{
				// 		$message = json_encode($message, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
				// 	}
				// 	catch(Exception $e){}
				// 	// print_r($message . "</pre>");
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