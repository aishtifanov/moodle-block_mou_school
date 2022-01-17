<?php // $Id: importplan.php,v 1.13 2012/02/13 10:32:24 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/uploadlib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
    require_once('../../mou_ege/lib_ege.php');
   	require_once('../authbase.inc.php');

    $did = optional_param('did', 0, PARAM_INT);   // Discipline id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $planid = optional_param('planid', 0, PARAM_INT);   // Plan id
    $unitid = optional_param('unitid', 0, PARAM_INT);   // Unit id
    $analys = optional_param('analys', '');   // Unit id    

    $currenttab = 'importplan';
    include('tabsplan.php');
    
//    echo $analys . '<hr>';
    
	$edit_capability = has_capability('block/mou_school:editlessonsplan', $context);
/*
	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editlessonsplan', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	
*/
    $csv_delimiter = ';';
    $plansnew = 0;
	$planserrors  = 0;

	$strplan = get_string('plan', 'block_mou_school');   //1
	$strunit= get_string('unit', 'block_mou_school'); //2
	$strlesson = get_string('llesson', 'block_mou_school'); //3
	$linenum = 1; // since header is line 1
		
	/// If a file has been uploaded, then process it

//	if (!empty($frm) ) {
	
		$um = new upload_manager('userfile',false,false,null,false,0);
		$f = 0;
		if ($um->preprocess_files()) {
			$filename = $um->files['userfile']['tmp_name'];

		    @set_time_limit(0);
		    @raise_memory_limit("192M");
		    if (function_exists('apache_child_terminate')) {
		        @apache_child_terminate();
		    }

			$redirlink = "importplan.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=$pid";
						
			$text = file($filename);
			if($text == FALSE)	{
				error(get_string('errorfile', 'block_monitoring'), $redirlink);
			}
			$size = sizeof($text);

			$textlib = textlib_get_instance();
  			for($i=0; $i < $size; $i++)  {
				$text[$i] = $textlib->convert($text[$i], 'win1251');
            }

	        // --- get plan
            $header = split($csv_delimiter, $text[0]);
            $header0 = trim($header[0]);
            $aplans = array();

			if ($header0 === $strplan)	{
				// if ($analys == '') 
				echo '<b>'.$header[2] .  '<br>' . '</b>';
				$aplans[] = $header[2]; 
			} else {
				error('Название тематического плана не найдено.', $redirlink);
			}

            $line = split($csv_delimiter, $text[1]);
            $line0 = trim($line[0]);
            if ($line0 != $strunit) {
            	error('Во второй строке необходимо указать название раздела.', $redirlink);
            }	

			
            $handle = fopen($filename, "r");
            $data = fgetcsv_mou($handle, 1000, ";");
            // print_r($data); echo '<hr>';
             
			$aunits = array();
			$alessons = array(); 
			$alessonshour = array();
  			for($i=1; $i < $size; $i++)  {
                    
                $line = array();
                $lineF = fgetcsv_mou($handle, 1000, ";");
                // print_r($lineF); echo '<hr>';
                $num = count($lineF);
                for ($c=0; $c < $num; $c++) {
                    $line[$c] = $textlib->convert($lineF[$c], 'win1251');
                }
                // $line[0] = $lineTEXT[0];
                // print_r($line); echo '<hr>';
                
                /*
			    echo "Line $i. " . $text[$i];
				$atexti = trim($text[$i]);
				if (empty($atexti)) continue;
                $line = split($csv_delimiter, $text[$i]);
                */
                
	            $line0 = trim($line[0]);
	            if ($line0 === $strunit) {
	            	$j = $line[1];
					// if ($analys == '')  
					echo '<b><i>' . $j . '. ' . $line[2] . '</i></b>' . '<br>';
					$aunits[$j] = $line[2];
					$alessons[$j] = array();
					
				} else if ($line0 === $strlesson) {
					// if ($analys == '')  
					echo $line[1] . '. ' . $line[2] .  '.' . $line[3] . '<br>';
					$alessons[$j][$line[1]] = $line[2];
					$alessonshour[$j][$line[1]] = $line[3];
				} else {
					error('Не найдено название раздела или урока в строке ' . $i+1, $redirlink);
				}

                $linenum++;
                if ($linenum > 1000)	 {
					error(get_string('verybiglinenum', 'block_mou_ege'), $redirlink);
                }
            }    
            

				/*
	            print_r($aplans);	 echo '<hr>';
				print_r($aunits);	 echo '<hr>';
				print_r($alessons);  echo '<hr>';
				print_r($alessonshour);  echo '<hr>';
				// exit(1);
				*/
				if (empty($aplans)) {
					notify ('Внимание! ОШИБКА: Не указано название учебного плана.');
					$planserrors++;
				} else {
					
				}

				if (empty($aunits)) {
					notify ('Внимание! ОШИБКА: Отсутствует раздел плана. Необходимо наличие хотя бы одного раздела.');
					$planserrors++;
				} 

				if (empty($alessons)) {
					notify ('Внимание! ОШИБКА: Нет ни одной темы урока.');
					$planserrors++;
				} 
				
				foreach ($aunits as $number => $aunit)	{
					if (empty($number))	{
						notify ('Внимание! ОШИБКА: Не указан номер раздела для ' . $aunit);
						$planserrors++;
					}
					if (empty($aunit))	{
						notify ('Внимание! ОШИБКА: Не указано название раздела для раздела №' . $number );
						$planserrors++;
					}

					foreach ($alessons[$number] as $numles => $alesson)	{
						if (empty($numles))	{
							notify ('Внимание! ОШИБКА: Не указан номер урока для ' . $alesson);
							$planserrors++;
						}
						if (empty($alesson))	{
							notify ('Внимание! ОШИБКА: Не указано название урока для урока №' . $numles );
							$planserrors++;
						} else {
							$pos = strpos($alesson, "'");

							if ($pos !== false) {
							    notify ('Внимание! ОШИБКА: В строке ' . $numles . ' обнаружен апостроф. Его необходимо или удалить или заменить на кавычки.' );
								$planserrors++;
							}
						}

						if ($alessonshour[$number][$numles] == 0)	{
							notify ('Внимание! ОШИБКА: Не указано количество часов для для урока №' . $numles . '. ' . $alesson);
							$planserrors++;
						}
					}
				}							

				if ($planserrors == 0)	{
					echo '<hr><br><br><br>';
					notify('Анализ завершен успешно. Ошибок: 0.', 'green');
				} else {
					notify("Анализ завершен. Ошибок: $planserrors.");
				}
				echo '<br><br><br><hr>';				
				
			if ($planserrors == 0 && $analys == '') {
				$rec->yearid = $yid;
				$rec->schoolid = $sid;
				$rec->disciplineid = $did;
				$rec->parallelnum = $pid;
				$rec->textbooksids = '';
				$rec->description = '';
	
				foreach ($aplans as $aplan)	{
					$rec->name = addslashes($aplan);
					if (!$idplan = insert_record('monit_school_discipline_plan', $rec))	{
						print_r($rec);
						error(get_string('errorinaddingplan','block_mou_scholl'), $redirlink);
					}
					
					$rec->planid = $idplan; 
					foreach ($aunits as $number => $aunit)	{
						$rec->number = $number;
						$rec->name = addslashes($aunit);
						if (!$idunit = insert_record('monit_school_discipline_unit', $rec))	{
							print_r($rec);
							error(get_string('errorinaddingunit','block_mou_scholl'), $redirlink);
						}
	
						foreach ($alessons[$number] as $numles => $alesson)	{
							$rec->name = addslashes($alesson);
							$rec->number = $numles;
							$rec->hours = $alessonshour[$number][$numles];
							$rec->unitid = $idunit;
							if (!insert_record('monit_school_discipline_lesson_'.$rid, $rec))	{
								print_r($rec);
								error(get_string('errorinaddinglesson','block_mou_scholl'), $redirlink);
							}
						}
					}							
				}	
							
			    // $strusersnew = get_string("usersnew");
	    	    // notify("$strusersnew: $plansnew", 'green', 'center');
	            notify("Импорт завершен. " . get_string('errors', 'block_mou_ege') . ": $planserrors");
		        echo '<hr />';
		   }     
	        
       }

   
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	    listbox_parallel_all("importplan.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;did=$did&amp;pid=", $pid);
	    listbox_discipline_parallel("importplan.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;pid=$pid&amp;did=", $sid, $yid, $pid, $did);
		echo '</table>';
		
		if ($did !=0 && $pid != 0)	{
		    //$struploadusers = get_string('importplans', 'block_mou_ege', $strschool);
		    // print_heading_with_help($struploadusers, 'importclasspol', 'mou');
	    	$edit_capability_discipline = has_capability_editlessonsplan($sid, $did);
			
			if ($edit_capability || $edit_capability_discipline)	{
				/// Print the form
		   	    $struploadusers = get_string('importplan', 'block_mou_school');
		   	    $stranalys = get_string('importplanwithanalys', 'block_mou_school');
			    $maxuploadsize  = get_max_upload_file_size();
				$strchoose = ''; // get_string("choose"). ':';
		
			    echo '<center>';
			    echo '<form method="post" enctype="multipart/form-data" action="importplan.php">'.
			         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
			         '<input type="hidden" name="rid" value="'.$rid.'">'.
			         '<input type="hidden" name="sid" value="'.$sid.'">'.
			         '<input type="hidden" name="yid" value="'.$yid.'">'.
			         '<input type="hidden" name="did" value="'.$did.'">'.
			         '<input type="hidden" name="pid" value="'.$pid.'">'.				 		         
			         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">';
		
		
			    echo '<input type="file" name="userfile" size="50">'.
			         '<br><input type="submit" name="analys" value="'.$stranalys.'">'.
			         '<input type="submit" name="load" value="'.$struploadusers.'">'.
			         '</form><p>';
			    // $output = helpbutton('importclasspol', 'Как загрузить списки учеников нескольких классов', 'mou', true, true, '', true);
			    // echo $output;
			    echo '</center>';
					    
				
				?>
				<p><strong>Пример файла импорта в формате CSV (кодировка Windows-1251):</strong> </p>
				<p><font size="1" face="Courier New, Courier, mono"></font>
				план;-;Учебный план по математике;-<br />
				раздел;1;Натуральные числа и шкалы. ;-<br />
				урок;1;Обозначение натуральных чисел.;3<br />
				урок;2;Отрезок. Длина отрезка. Треугольник.;2<br />
				урок;3;Решение комбинаторных задач.;1<br />
				урок;4;Плоскость, прямая, луч.;2<br />
				урок;5;Шкалы и координаты.;3<br />
				урок;6;Меньше или больше.;3<br />
				урок;7;Контрольная работа № 1 «Натуральные числа и шкалы»;1<br />
				раздел;2;Сложение и вычитание натуральных чисел. ;-<br />
				урок;8;Сложение натуральных чисел и его свойства.;4<br />
				урок;9;Вычитание. Решение комбинаторных задач.;4<br />
				урок;10;Контрольная работа № 2 «Сложение и вычитание натуральных чисел».;1<br />
				урок;11;Числовые и буквенные выражения.;3<br />
				урок;12;Буквенная запись свойств сложения и вычитания.;3<br />
				урок;13;Решение комбинаторных задач.;1<br />
				урок;14;Уравнение.;4<br />
				урок;15;Контрольная работа № 3«Сложение и вычитание натуральных чисел».;1<br />
				</font></p>
				<?php
			}
	    }
 
    echo '<center>';

   	echo "<a href=\"$CFG->wwwroot/file.php/1/create_plan.pdf\"> Как загрузить предметный план. </a><p>";

   	echo "<a href=\"$CFG->wwwroot/file.php/1/shablon_plan.xls\"> Шаблон предметного плана в XLS-формате. </a>";

    echo '</center>';

    print_footer();


function fgetcsv_mou($f, $length, $d=";", $q='"') 
{
		$list = array();
		$st = fgets($f, $length);
		if ($st === false || $st === null) return $st;
		while ($st !== "" && $st !== false) {
			if ($st[0] !== $q) {
				# Non-quoted.
				list ($field) = explode($d, $st, 2);
				$st = substr($st, strlen($field)+strlen($d));
			} else {
				# Quoted field.
				$st = substr($st, 1);
				$field = "";
				while (1) {
					# Find until finishing quote (EXCLUDING) or eol (including)
					preg_match("/^((?:[^$q]+|$q$q)*)/sx", $st, $p);
					$part = $p[1];
					$partlen = strlen($part);
					$st = substr($st, strlen($p[0]));
					$field .= str_replace($q.$q, $q, $part);
					if (strlen($st) && $st[0] === $q) {
						# Found finishing quote.
						list ($dummy) = explode($d, $st, 2);
						$st = substr($st, strlen($dummy)+strlen($d));
						break;
					} else {
						# No finishing quote - newline.
						$st = fgets($f, $length);
					}
				}

			}
			$list[] = $field;
		}
		return $list;
}

?>
