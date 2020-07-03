<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Arena\Arena};
use pocketmine\{Server, Player, scheduler\Task, math\Vector3, item\Item, utils\TextFormat as Color};
use pocketmine\network\mcpe\protocol\{AddActorPacket, PlaySoundPacket, LevelSoundEventPacket, StopSoundPacket};
use pocketmine\level\sound\{EndermanTeleportSound};

class ArenaID extends Task {
    public $time = 4;
    public static $id = 1;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 3) {
                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Looking for an available game…');
            } else if ($this->time == 0) {
                $index = array_search($player->getName(), SkyWars::$data['queue']);
		        if ($index != -1) {
			        unset(SkyWars::$data['queue'][$index]);
                }
                if (Arena::getArenas() == null) {
                    $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . ' No arenas available for now,' . Color::RED . ' try again.');
                }
                $arenas = (count(Arena::getArenas()) + 1);
                if (self::$id >= $arenas) {
                    self::$id = 1;
                    $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' New found arena, you will be transferred.');
                    if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                        SkyWars::$data['queue'][] = $player->getName();
                        SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new NewID($player), 10);
                    }
                } else {
                    if (count(Arena::getArenas()) > 0) {
                        if (Arena::getStatus('SW-' . self::$id) == 'waiting') {
                            PluginUtils::joinSolo($player, self::$id);
                        } else {
                            self::$id++;
                            $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' New found arena, you will be transferred.');
                            if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                                SkyWars::$data['queue'][] = $player->getName();
                                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new NewID($player), 10);
                            }
                        }
                    }
                }
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}

class NewGame extends Task {
    public $time = 4;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 0) {
                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}


class NewID extends Task {
    public $time = 1;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 0) {
                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}
?>