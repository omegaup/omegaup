<?php

$CreatePseudoLocFile = NULL;

# files with lang extension
$LanguagesArray = array();

# 2d array WordsData[word][lang]
$WordsData = array();


# command line options
for ($i = 0; $i < sizeof($argv); $i++)
{
	if ($argv[$i] == "/ps")
	{
		$CreatePseudoLocFile = $argv[++$i];
	}
}


# discover lang files
if ($handle = opendir('.'))
{
	while (false !== ($entry = readdir($handle)))
	{
		if ($entry != "." && $entry != ".."
			&& (strtolower(substr($entry, strrpos($entry, '.') + 1)) == 'lang')
			&& $entry != $CreatePseudoLocFile)
		{
			echo "Found Language file: $entry\n";
			array_push($LanguagesArray, $entry);
		}
	}
	closedir($handle);
}

# read files and fill array
for ($i = 0; $i < sizeof($LanguagesArray); $i++)
{
	$lineNumber = 1;
	$contents = file_get_contents($LanguagesArray[$i]);
	$lines = explode("\n", $contents);
	array_pop($lines);

	foreach ($lines as $line)
	{
		$parts = explode("=", $line);

		if (sizeof($parts) != 2) die ($LanguagesArray[$i] . ": Malformed line ". $lineNumber .": " . $line . "\n");

		$WordsData[$parts[0]][$LanguagesArray[$i]] = $parts[1];
		$lineNumber++;
	}
}

# find differences
foreach ($WordsData as $Word => $LangArray )
{
	$isError = false;
	$n = 0;
	foreach ($LangArray as $Lang => $Text )
	{
		$n++;
	}

	if ($n < sizeof($LanguagesArray))
	{
		echo "\033[0;31m $Word ---------\033[0m\n";
		foreach ($LangArray as $Lang => $Text )
		{
			echo $Lang;
			echo $Text;
			echo "\n";
		}
	}
}
