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

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

class MapTrackedObject{
	public const TYPE_BLOCK = 0;
	public const TYPE_ENTITY = 1;
  public const TYPE_OTHER = 2;

	public int $type;
	public ?BlockPosition $blockPosition = null;
	public ?int $actorUniqueId = null;

	public static function read(ByteBufferReader $in) : self{
		$result = new self;
		$result->type = LE::readUnsignedInt($in);
		if($result->type === self::TYPE_BLOCK){
			$result->blockPosition = CommonTypes::getBlockPosition($in);
		}elseif($result->type === self::TYPE_ENTITY){
			$result->actorUniqueId = CommonTypes::getActorUniqueId($in);
		}
		return $result;
	}

	public function write(ByteBufferWriter $out) : void{
		LE::writeUnsignedInt($out, $this->type);
		if($this->type === self::TYPE_BLOCK){
			if($this->blockPosition === null){
				throw new \InvalidArgumentException("Block position must be set for block map tracked object");
			}
			CommonTypes::putBlockPosition($out, $this->blockPosition);
		}elseif($this->type === self::TYPE_ENTITY){
			if($this->actorUniqueId === null){
				throw new \InvalidArgumentException("Actor unique ID must be set for entity map tracked object");
			}
			CommonTypes::putActorUniqueId($out, $this->actorUniqueId);
		}
	}
}
