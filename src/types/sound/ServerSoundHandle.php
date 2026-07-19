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

namespace pocketmine\network\mcpe\protocol\types\sound;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;

final class ServerSoundHandle{

	public function __construct(
		private int $handle,
	){}

	public function getHandle() : int{
		return $this->handle;
	}

	public static function read(ByteBufferReader $in) : self{
		return new self(
			LE::readUnsignedLong($in)
		);
	}

	public function write(ByteBufferWriter $out) : void{
		LE::writeUnsignedLong($out, $this->handle);
	}
}
