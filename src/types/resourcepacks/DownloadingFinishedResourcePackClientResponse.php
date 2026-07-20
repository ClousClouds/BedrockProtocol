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

use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;

final class DownloadingFinishedResourcePackClientResponse extends ResourcePackClientResponse{

	public function getType() : ResourcePackClientResponseType{
		return ResourcePackClientResponseType::DOWNLOADING_FINISHED;
	}

	public static function read(ByteBufferReader $in) : static{
		Byte::readUnsigned($in);
		return new self();
	}

	public function write(ByteBufferWriter $out) : void{
		Byte::writeUnsigned($out, $this->getType()->value);
	}
}
