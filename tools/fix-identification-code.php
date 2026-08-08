<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

/** please run `php-cs-fixer fix` after executing this code */
const RESET = "\033[0m";
const RED = "\033[31m";
const GREEN = "\033[32m";
const YELLOW = "\033[33m";
const CYAN = "\033[36m";
const DIM = "\033[2m";

if($argc < 2){
	fwrite(
		STDERR,
		"Usage: php fix-indent.php <file|directory> [--dry-run] [--diff]\n"
	);
	exit(1);
}

$target = null;
$dryRun = false;
$showDiff = false;

for($i = 1; $i < $argc; ++$i){
	switch($argv[$i]){
		case '--dry-run':
			$dryRun = true;
			break;

		case '--diff':
			$showDiff = true;
			break;

		default:
			if($target !== null){
				fwrite(
					STDERR,
					RED . "Unknown argument: {$argv[$i]}" . RESET . "\n"
				);
				exit(1);
			}

			$target = $argv[$i];
			break;
	}
}

if($target === null){
	fwrite(STDERR, "Target is required.\n");
	exit(1);
}

function fixIndent(string $content) : string{
	$lineEnding = str_contains($content, "\r\n") ? "\r\n" : "\n";

	$lines = preg_split('/\r\n|\n|\r/', $content);

	if($lines === false){
		return $content;
	}

	foreach($lines as &$line){
		if(preg_match('/^([ \t]+)/', $line, $match) !== 1){
			continue;
		}

		$leading = $match[1];
		$columns = 0;
		$length = strlen($leading);

		for($i = 0; $i < $length; ++$i){
			$columns += $leading[$i] === "\t" ? 4 : 1;
		}

		$tabs = (int) ceil($columns / 4);

		$line = str_repeat("\t", $tabs) . substr($line, $length);
	}

	unset($line);

	return implode($lineEnding, $lines);
}

function printDiff(string $old, string $new, string $file) : void{
	if($old === $new){
		return;
	}

	$oldFile = tempnam(sys_get_temp_dir(), 'indent-old-');
	$newFile = tempnam(sys_get_temp_dir(), 'indent-new-');

	if($oldFile === false || $newFile === false){
		fwrite(
			STDERR,
			RED . "Unable to create temporary files." . RESET . "\n"
		);
		return;
	}

	file_put_contents($oldFile, $old);
	file_put_contents($newFile, $new);

	$command =
		'diff -u ' .
		escapeshellarg($oldFile) . ' ' .
		escapeshellarg($newFile);

	$output = [];
	$returnCode = 0;

	exec($command, $output, $returnCode);

	unlink($oldFile);
	unlink($newFile);

	echo "\n";
	echo YELLOW . "=== $file ===" . RESET . "\n";

	foreach($output as $line){
		if(str_starts_with($line, '---')){
			echo CYAN . $line . RESET . "\n";
			continue;
		}

		if(str_starts_with($line, '+++')){
			echo CYAN . $line . RESET . "\n";
			continue;
		}

		if(str_starts_with($line, '@@')){
			echo YELLOW . $line . RESET . "\n";
			continue;
		}

		if(str_starts_with($line, '-')){
			echo RED . $line . RESET . "\n";
			continue;
		}

		if(str_starts_with($line, '+')){
			echo GREEN . $line . RESET . "\n";
			continue;
		}

		echo DIM . $line . RESET . "\n";
	}

	echo "\n";
}

function processFile(
	string $file,
	bool $dryRun,
	bool $showDiff
) : void{
	if(!is_file($file)){
		return;
	}

	if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php'){
		return;
	}

	$old = file_get_contents($file);

	if($old === false){
		fwrite(
			STDERR,
			RED . "ERROR: Cannot read $file" . RESET . "\n"
		);
		return;
	}

	$new = fixIndent($old);

	if($old === $new){
		echo GREEN . "OK" . RESET . "      $file\n";
		return;
	}

	echo YELLOW . "CHANGED" . RESET . " $file";

	if($dryRun){
		echo CYAN . " [DRY-RUN]" . RESET;
	}

	echo "\n";

	if($showDiff){
		printDiff($old, $new, $file);
	}

	if(!$dryRun){
		if(file_put_contents($file, $new) === false){
			fwrite(
				STDERR,
				RED . "ERROR: Cannot write $file" . RESET . "\n"
			);
			return;
		}

		echo GREEN . "  Fixed." . RESET . "\n";
	}
}

function processTarget(
	string $target,
	bool $dryRun,
	bool $showDiff
) : void{
	if(is_file($target)){
		processFile($target, $dryRun, $showDiff);
		return;
	}

	if(!is_dir($target)){
		fwrite(
			STDERR,
			RED . "Not found: $target" . RESET . "\n"
		);
		exit(1);
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$target,
			FilesystemIterator::SKIP_DOTS
		)
	);

	foreach($iterator as $file){
		processFile(
			$file->getPathname(),
			$dryRun,
			$showDiff
		);
	}
}

processTarget($target, $dryRun, $showDiff);
