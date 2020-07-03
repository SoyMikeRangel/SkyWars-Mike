<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 1.0.0
 * Status: Private
*/
namespace MikeRangel\SkyWars\Entity;
use MikeRangel\SkyWars\{SkyWars, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, Player, utils\TextFormat as Color, level\Level, entity\Entity, math\Vector3, entity\Skin};

final class EntityManager {

	public function setSkins($player, string $value) {
		$dir = SkyWars::getInstance()->getDataFolder() . $value . '.png';
		$load = @imagecreatefrompng($dir);
		$skinbytes = '';
		$values = (int)@getimagesize($dir)[1];
		for($y = 0; $y < $values; $y++) {
			for($x = 0; $x < 64; $x++) {
				$bytes = @imagecolorat($load, $x, $y);
                $a = ((~((int)($bytes >> 24))) << 1) & 0xff;
                $b = ($bytes >> 16) & 0xff;
                $c = ($bytes >> 8) & 0xff;
                $d = $bytes & 0xff;
                $skinbytes .= chr($b) . chr($c) . chr($d) . chr($a);
			}
		}
		@imagedestroy($load);
		$player->setSkin(new Skin($player->getSkin()->getSkinId(), $skinbytes, '', 'geometry.' . $value,file_get_contents(SkyWars::getInstance()->getDataFolder() . $value. '.json')));
        $player->sendSkin();
	}

    public function setGame(Player $player) {
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag('Skin'));
		$human = new EntityHuman($player->getLevel(), $nbt);
		$this->setSkins($human, 'NPC');
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
	public function setStats(Player $player) {
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag($player->namedtag->getTag('Skin'));
		$human = new EntityStats($player->getLevel(), $nbt);
		$human->setSkin(new Skin('textfloat', $human->getInvisibleSkin()));
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->spawnToAll();
	}
}
?>