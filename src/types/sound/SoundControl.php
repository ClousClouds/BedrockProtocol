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
use pmmp\encoding\VarInt;

abstract class SoundControl{

	abstract public function getType() : SoundControlType;

	abstract public function write(ByteBufferWriter $out) : void;

	public static function read(ByteBufferReader $in) : self{
		return match(SoundControlType::fromPacket(VarInt::readUnsignedInt($in))){
			SoundControlType::STOP => StopSoundControl::read($in),
			SoundControlType::SET_VOLUME => SetVolumeSoundControl::read($in),
			SoundControlType::SET_PITCH => SetPitchSoundControl::read($in),
			SoundControlType::FADE => FadeSoundControl::read($in),
			SoundControlType::SEEK_TO => SeekToSoundControl::read($in),
			SoundControlType::PAUSE => PauseSoundControl::read($in),
			SoundControlType::RESUME => ResumeSoundControl::read($in),
		};
	}
}
