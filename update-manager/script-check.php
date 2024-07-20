<?php

function err() {
	die(0);
}

function readDirs($path) {
	$dirHandle = opendir($path);
	$list = [];
	while ($item = readdir($dirHandle)) {
		$newPath = $path."/".$item;
		if ($item != '.' && $item != '..') {
			if (is_dir($newPath)) {
				readDirs($newPath);
			} else {
				array_push($list, "$path/$item");
		 	}
	  	}
	}
	return $list;
}

function main($argc, $argv) {
	if ($argc < 2) err();
	
	ini_set('memory_limit','2048M');
	
	switch ($argv[1]) {
		case 'check':
			if ($argc < 3) err();
			$path = dirname(__FILE__, 2);
			$story_vi = [];
			$story_en = [];
			$exit = false;
			for ($i = 1; $i <= 8; $i++) {
				$story_vi[$i] = readDirs("$path/story/ep$i/{$argv[2]}");
				$story_en[$i] = readDirs("$path/story/ep$i/en");
			}
			for ($i = 1; $i <= count($story_vi); $i++) {
				for ($x = 0; $x < count($story_vi[$i]); $x++) {
					$chapter_vi = $story_vi[$i][$x];
					$chapter_en = $story_en[$i][$x];

					// check : line counts
					$lines_vi = count(file($chapter_vi));
					$lines_en = count(file($chapter_en));
					if ($lines_vi != $lines_en) {
						echo "==========================================".PHP_EOL;
						echo "!! ERROR !!".PHP_EOL;
						echo "Line counts don't match ";
						$diff = $lines_en - $lines_vi;
						if ($diff > 0) {
							echo "( $diff missing line(s) )".PHP_EOL;
						} else {
							$diff = abs($diff);
							echo "( $diff extra line(s) )".PHP_EOL;
						}
						echo "File: $chapter_vi".PHP_EOL;
						echo "Default count: $lines_en".PHP_EOL;
						echo "New count: $lines_vi".PHP_EOL;
						$exit = true;
					}

					// check : backticks
					$exp = "/(`)(.*)(`)(\n|)/";

					$handle = fopen($chapter_vi, "r");
					if ($handle) {
						$n = 1;
						while (($line = fgets($handle)) !== false) {
							if (preg_match($exp, $line) == 0) {
								echo "==========================================".PHP_EOL;
								echo "!! ERROR !!".PHP_EOL;
								echo "Missing backtick(s)".PHP_EOL;
								echo "File: $chapter_vi".PHP_EOL;
								echo "Line: $n".PHP_EOL;
								$exit = true;
							}
							$n++;
						}
						fclose($handle);
					}
				}
			}
			if ($exit) {
				echo "==========================================".PHP_EOL;
				exit(1);
			}
			echo "All good.";
			break;
		default:
			err();
	}
}

main($argc, $argv);