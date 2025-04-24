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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use function count;

final class BiomeStringList{
	public function __construct(
		/** @var array<string, int> */
		private array $stringToIndex,
		/** @var string[] */
		private array $indexToString
	){}

	public static function read(PacketSerializer $in) : self{
		$count = $in->getUnsignedVarInt();
		$stringToIndex = [];
		$indexToString = [];
		for($i = 0; $i < $count; $i++){
			$string = $in->getString();
			$stringToIndex[$string] = $i;
			$indexToString[$i] = $string;
		}
		return new self($stringToIndex, $indexToString);
	}

	public function write(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->indexToString));
		foreach($this->indexToString as $string){
			$out->putString($string);
		}
	}

	public function getStringIndex(string $string) : ?int{
		return $this->stringToIndex[$string] ?? null;
	}

	public function getStringFromIndex(int $index) : ?string{
		return $this->indexToString[$index] ?? null;
	}
}
