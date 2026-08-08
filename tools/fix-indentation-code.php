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

/**
 * Calculates indentation for every PHP source line.
 *
 * {} controls normal block indentation.
 * () controls multiline parameter/call indentation.
 * [] controls multiline array indentation.
 *
 * Only indentation is changed. Source code itself is untouched.
 */
function getIndentationLevels(string $content) : array{
	$tokens = token_get_all($content);
	$levels = [];
	$stack = [];
	$line = 1;
	$insideCase = false;

	// Tracks whether the most recently seen keyword-like token before an
	// upcoming '{' was `switch`, so we can tell a real switch block apart
	// from a `match(...){...}` expression (which also legally contains a
	// `default` token, but it is NOT a case label and must not affect
	// indentation tracking).
	$lastSignificantKeyword = null;

	// Parallel stack to $stack: for every '{' pushed, records whether that
	// specific block is a switch body. Only a switch body may set/consume
	// $caseLevel / $insideCase.
	$blockIsSwitchStack = [];

	// $caseStackDepthAtEntry records count($stack) at the moment we entered
	// the current case body (i.e. right after the case label, when no
	// nested block has been opened yet). The "virtual" extra indent that a
	// case body gets (+1 versus the switch body) must be added on top of
	// however deep $stack currently is, not just maxed against a flat
	// $caseLevel - otherwise nested blocks inside a case (if/for/etc.)
	// collapse to the same level as their own opening line.
	$caseStackDepthAtEntry = null;

	// Tracks, for the line currently being processed, whether every token
	// seen so far on this line has been a closing bracket (}, ), ]). While
	// true, a new closing bracket is allowed to keep updating the line's
	// level (so a line like "))," that closes multiple nested brackets ends
	// up at the level of the OUTERMOST bracket it closes, i.e. the last one
	// processed). As soon as any other token appears on the line, this
	// flips to false and the line's level is locked in for good - later
	// closing brackets on that same line must not override the level the
	// line's first meaningful token already established.
	$lineStartsWithOnlyClosers = true;
	$currentTrackedLine = 0;

	$noteToken = function(int $tokLine, bool $isCloser) use (&$currentTrackedLine, &$lineStartsWithOnlyClosers) : void{
		if($tokLine !== $currentTrackedLine){
			$currentTrackedLine = $tokLine;
			$lineStartsWithOnlyClosers = true;
		}
		if(!$isCloser){
			$lineStartsWithOnlyClosers = false;
		}
	};

	$getLevel = static function() use (&$stack, &$caseStackDepthAtEntry, &$insideCase) : int{
		$level = count($stack);

		if($insideCase && $caseStackDepthAtEntry !== null && $level >= $caseStackDepthAtEntry){
			$level += 1;
		}

		return $level;
	};

	foreach($tokens as $token){
		if(is_array($token)){
			[$id, $text, $tokenLine] = $token;
			$line = $tokenLine;

			if($id === T_WHITESPACE){
				$line += substr_count($text, "\n");
				continue;
			}

			if($id === T_COMMENT || $id === T_DOC_COMMENT){
				$noteToken($line, false);
				$levels[$line] ??= $getLevel();
				$line += substr_count($text, "\n");
				continue;
			}

			if($id === T_SWITCH){
				$noteToken($line, false);
				$lastSignificantKeyword = 'switch';
				$levels[$line] ??= $getLevel();
				continue;
			}

			if($id === T_MATCH){
				$noteToken($line, false);
				$lastSignificantKeyword = 'match';
				$levels[$line] ??= $getLevel();
				continue;
			}

			if($id === T_CASE || $id === T_DEFAULT){
				$noteToken($line, false);

				// Only treat this as a switch case label if we're currently
				// inside a switch body (not inside a match() expression,
				// where `default` is just an arm, not a case label).
				$currentBlockIsSwitch = $blockIsSwitchStack !== []
					&& end($blockIsSwitchStack) === true;

				if($currentBlockIsSwitch){
					$levels[$line] = count($stack);
					$caseStackDepthAtEntry = count($stack);
					$insideCase = true;
				}else{
					$levels[$line] ??= $getLevel();
				}
				continue;
			}

			if($id === T_FUNCTION || $id === T_FN || $id === T_CLASS){
				$noteToken($line, false);
				$lastSignificantKeyword = null;
				$levels[$line] ??= $getLevel();
				continue;
			}

			$noteToken($line, false);
			$levels[$line] ??= $getLevel();
			continue;
		}

		$text = $token;

		if($text === "\n"){
			++$line;
			continue;
		}

		if($text === '{'){
			$noteToken($line, false);
			$levels[$line] ??= $getLevel();
			$stack[] = '{';
			// Only the '{' that immediately follows switch(...)/match(...) at
			// the same nesting depth is the switch/match body itself; a '{'
			// belonging to a nested closure inside the condition would be
			// preceded by other tokens (e.g. `function`, `fn`) that we treat
			// as clearing the pending keyword below.
			$blockIsSwitchStack[] = ($lastSignificantKeyword === 'switch');
			$lastSignificantKeyword = null;
			continue;
		}

		if($text === '(' || $text === '['){
			$noteToken($line, false);
			$levels[$line] ??= $getLevel();
			$stack[] = $text;
			continue;
		}

		if($text === '}' || $text === ')' || $text === ']'){
			if($stack !== []){
				$opening = array_pop($stack);

				if($opening === '{'){
					$closedBlockWasSwitch = array_pop($blockIsSwitchStack) === true;

					// Reset case tracking only when the BLOCK that closes is
					// the switch body itself (not any nested block/closure),
					// and it takes us back above the depth the switch's
					// cases were opened at. This correctly handles the
					// switch's own closing brace even when the last
					// case/default has no trailing `break;` before it,
					// without misfiring on nested blocks, or on ')'/']'
					// closing an expression inside a case body.
					if(
						$closedBlockWasSwitch
						&& $insideCase
						&& $caseStackDepthAtEntry !== null
						&& count($stack) < $caseStackDepthAtEntry
					){
						$caseStackDepthAtEntry = null;
						$insideCase = false;
					}
				}
			}

			// A run of closing brackets at the START of a line (nothing else
			// seen yet on this line) keeps updating the line's level as each
			// bracket closes, so the line ends up at the level of the LAST
			// (outermost) bracket it closes - e.g. "), $x);" closing both an
			// inner call and its outer call lands at the outer call's level.
			// Once a non-closer token has appeared on the line, the line's
			// level is already locked by that earlier token and must not be
			// overridden by a later closing bracket on the same line.
			$wasLineStillOnlyClosers = $lineStartsWithOnlyClosers && $line === $currentTrackedLine;
			$noteToken($line, true);

			if($lineStartsWithOnlyClosers){
				$levels[$line] = $getLevel();
			}else{
				$levels[$line] ??= $getLevel();
			}
			continue;
		}

		$noteToken($line, false);

		if(trim($text) !== ''){
			$levels[$line] ??= $getLevel();
		}
	}

	return $levels;
}

/**
 * Fixes only leading indentation.
 */
function fixIndent(string $content) : string{
	$lineEnding = str_contains($content, "\r\n") ? "\r\n" : "\n";

	$lines = preg_split('/\r\n|\n|\r/', $content);

	if($lines === false){
		return $content;
	}

	$levels = getIndentationLevels($content);

	foreach($lines as $index => &$line){
		$lineNumber = $index + 1;

		if(trim($line) === ''){
			continue;
		}

		if(!isset($levels[$lineNumber])){
			continue;
		}

		if(preg_match('/^[ \t]*/', $line, $match) !== 1){
			continue;
		}

		$leadingLength = strlen($match[0]);

		$line = str_repeat("\t", max(0, $levels[$lineNumber]))
			. substr($line, $leadingLength);
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
		if(str_starts_with($line, '---') || str_starts_with($line, '+++')){
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
