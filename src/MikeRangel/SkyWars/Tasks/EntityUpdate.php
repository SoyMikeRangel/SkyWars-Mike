<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Entity\types\EntityHuman};
use pocketmine\{Server, Player, utils\TextFormat as Color, entity\Effect, entity\EffectInstance, scheduler\Task};

class EntityUpdate extends Task {
    public function onRun(int $currentTick) : void {
		foreach (Server::getInstance()->getDefaultLevel()->getEntities() as $entity) {
			if ($entity instanceof EntityHuman) {
				$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 999));
				$entity->setNameTag(self::setName());
				$entity->setNameTagAlwaysVisible(true);
				$entity->setScale(2);
			}
		}
    }
    
    public static function setName() : string {
        $colors = [Color::AQUA . '[Beta]', Color::AQUA . '[1.0.0]'];
        $title = Color::GREEN . Color::BOLD . '»' . Color::GRAY . 'NEW GAME' . Color::GREEN . '«' . "\n";
		$subtitle1 = Color::BOLD . Color::GOLD . 'SKYWARS ' . Color::RESET . $colors[array_rand($colors)] . "\n";
		$subtitle2 = Color::GREEN . self::getAllPlayers() . ' now playing';
		return $title . $subtitle1 . $subtitle2;
    }

    public static function getAllPlayers() : int {
    	$players = [];
        foreach (Arena::getArenas() as $arena) {
        	if (Server::getInstance()->getLevelByName(Arena::getName($arena)) !== null) {
        	    foreach (Server::getInstance()->getLevelByName(Arena::getName($arena))->getPlayers() as $player) {
        	        array_push($players, $player->getName());
        	    }
        	}
        }
        return count($players);
    }
}
?>