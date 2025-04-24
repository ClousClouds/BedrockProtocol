<?php
declare(strict_types=1); 
namespace pocketmine\network\mcpe\protocol\types; 
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer; 
final class BiomeStringIndex{  
    public function __construct(private int $index){}     
    public static function read(PacketSerializer $in) : self{      
        return new self($in->getLShort()); 
        }      
        public function write(PacketSerializer $out) : void{  
            $out->putLShort($this->index);  
            }      
            public function getIndex() : int{ return $this->index; } }