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

final class BlockRuntimeId{
	public function __construct(private int $runtimeId){}

	public static function read(PacketSerializer $in) : self{
		return new self($in->getUnsignedVarInt());
	}

	public function write(PacketSerializer $out) : void{
		$out->putUnsignedVarInt($this->runtimeId);
	}

	public function getRuntimeId() : int{ return $this->runtimeId; }
}
