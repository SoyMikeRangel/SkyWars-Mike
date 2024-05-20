<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena};
use pocketmine\{Server, player\Player, scheduler\Task, math\Vector3};
use pocketmine\network\mcpe\protocol\{ChangeDimensionPacket, PlayStatusPacket, types\DimensionIds};

class Dimension extends Task {
    public $player;
    public $arena;

    public function __construct(Player $player, string $arena) {
        $this->player = $player;
        $this->arena = $arena;
    }

    public function onRun() : void {
        $player = $this->player;
        $arena = $this->arena;
        $pk = new ChangeDimensionPacket();
		$pk->dimension = DimensionIds::OVERWORLD;
        $pk->position = $player->asVector3();
        $player->dataPacket($pk);
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena))->getSafeSpawn());
        $player->sendPlayStatus(PlayStatusPacket::PLAYER_SPAWN);
        SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}
?>