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
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use function count;

final class DownloadingResourcePackClientResponse extends ResourcePackClientResponse{

	/**
	 * @param list<string> $packIds
	 */
	public function __construct(
		private array $packIds
	){}

	/**
	 * @return list<string>
	 */
	public function getPackIds() : array{
		return $this->packIds;
	}

	public function getType() : ResourcePackClientResponseType{
		return ResourcePackClientResponseType::DOWNLOADING;
	}

	public static function read(ByteBufferReader $in) : static{
		Byte::readUnsigned($in);

		$packIds = [];

		for($i = 0, $count = LE::readUnsignedShort($in); $i < $count; ++$i){
			$packIds[] = CommonTypes::getString($in);
		}

		return new self($packIds);
	}

	public function write(ByteBufferWriter $out) : void{
		Byte::writeUnsigned($out, $this->getType()->value);

		LE::writeUnsignedShort($out, count($this->packIds));

		foreach($this->packIds as $id){
			CommonTypes::putString($out, $id);
		}
	}
}
