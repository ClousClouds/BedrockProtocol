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

namespace pocketmine\network\mcpe\protocol\types\resourcepacks;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;

abstract class ResourcePackClientResponse{

	abstract public function getType() : ResourcePackClientResponseType;

	abstract public function write(ByteBufferWriter $out) : void;

	public static function read(ByteBufferReader $in) : self{
		return match(ResourcePackClientResponseType::fromPacket(VarInt::readUnsignedInt($in))){
			ResourcePackClientResponseType::CANCEL => CancelResourcePackClientResponse::read($in),
			ResourcePackClientResponseType::DOWNLOADING => DownloadingResourcePackClientResponse::read($in),
			ResourcePackClientResponseType::DOWNLOADING_FINISHED => DownloadingFinishedResourcePackClientResponse::read($in),
			ResourcePackClientResponseType::RESOURCE_PACK_STACK_FINISHED => ResourcePackStackFinishedResourcePackClientResponse::read($in),
		};
	}
}
