<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 1.0.0
 * Status: @PacmanLivePE
*/
namespace MikeRangel\SkyWars\Entity;
use MikeRangel\SkyWars\{SkyWars, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, player\Player, utils\TextFormat as Color, level\Level, entity\Entity, math\Vector3, entity\Skin};
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag, StringTag, Tag};

final class EntityManager {

    public function convertPngToSkinData(string $filePath): string {
        $load = @imagecreatefrompng($filePath);
        $skinBytes = '';
        $values = (int)@getimagesize($filePath)[1];
        for ($y = 0; $y < $values; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $bytes = @imagecolorat($load, $x, $y);
                $a = ((~((int)($bytes >> 24))) << 1) & 0xff;
                $b = ($bytes >> 16) & 0xff;
                $c = ($bytes >> 8) & 0xff;
                $d = $bytes & 0xff;
                $skinBytes .= chr($b) . chr($c) . chr($d) . chr($a);
            }
        }
        @imagedestroy($load);
        return $skinBytes;
    }

	public function createEntity(Player $player, string $entityType, string $skinValue = null) {
        $location = $player->getLocation();
        if ($skinValue != null) {
            $skinBytes = $this->convertPngToSkinData(SkyWars::getInstance()->getDataFolder() . $skinValue . '.png');
            $geometryData = file_get_contents(SkyWars::getInstance()->getDataFolder() . $skinValue . '.json');
            $skin = new Skin('CustomSkin', $skinBytes, '', 'geometry.' . $skinValue, $geometryData);
        } else {
            $skin = $player->getSkin();
        }
    
        $nbt = new CompoundTag();
        $nbt->setTag("Pos", new ListTag([
            new DoubleTag($location->x),
            new DoubleTag($location->y),
            new DoubleTag($location->z)
        ]));
        $nbt->setTag("Motion", new ListTag([
            new DoubleTag(0),
            new DoubleTag(0),
            new DoubleTag(0)
        ]));
        $nbt->setTag("Rotation", new ListTag([
            new FloatTag($location->yaw),
            new FloatTag($location->pitch)
        ]));
    
        $skinTag = new CompoundTag();
        $skinTag->setString("Data", $skin->getSkinData());
        $skinTag->setString("Name", $skin->getSkinId());
        $skinTag->setString("CapeData", $skin->getCapeData());
        $skinTag->setString("GeometryName", $skin->getGeometryName());
        $skinTag->setString("GeometryData", $skin->getGeometryData());
        $nbt->setTag("Skin", $skinTag);
    
        $entityClass = "\\MikeRangel\\SkyWars\\Entity\\types\\" . $entityType;
        if (class_exists($entityClass)) {
            $entity = new $entityClass($player->getWorld(), $nbt);
            if ($entity !== null) {
                $entity->spawnToAll();
            }
        } else {
            $player->sendMessage(Color::RED . 'Entity type not found: ' . Color::GREEN . $entityType);
        }
    }
}
?>