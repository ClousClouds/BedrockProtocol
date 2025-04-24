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

final class BiomeSurfaceMaterialData{
	public function __construct(
		private BlockRuntimeId $topBlock,
		private BlockRuntimeId $midBlock,
		private BlockRuntimeId $seaFloorBlock
	){}

	public static function read(PacketSerializer $in) : self{
		return new self(
			BlockRuntimeId::read($in),
			BlockRuntimeId::read($in),
			BlockRuntimeId::read($in)
		);
	}

	public function write(PacketSerializer $out) : void{
		$this->topBlock->write($out);
		$this->midBlock->write($out);
		$this->seaFloorBlock->write($out);
	}

	public function getTopBlock() : BlockRuntimeId{ return $this->topBlock; }
	public function getMidBlock() : BlockRuntimeId{ return $this->midBlock; }
	public function getSeaFloorBlock() : BlockRuntimeId{ return $this->seaFloorBlock; }
}
