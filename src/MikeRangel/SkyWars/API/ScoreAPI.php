<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\SkyWars\API;
use MikeRangel\SkyWars\SkyWars;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreAPI {
    private static $instance;
    private $scoreboards = [];
    private $plugin;
    
    public function __construct(SkyWars $plugin){
        $this->plugin = $plugin;
    }
    
    public function new(Player $pl, string $objectiveName, string $displayName) : void { 
        if (isset($this->scoreboards[$pl->getName()])) {
            $this->remove($pl);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $pl->getNetworkSession()->sendDataPacket($pk);
        $this->scoreboards[$pl->getName()] = $objectiveName;
    }
    
    public function remove(Player $pl) : void {
        if (isset($this->scoreboards[$pl->getName()])) {
            $objectiveName = $this->getObjectiveName($pl);
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $objectiveName;
            $pl->getNetworkSession()->sendDataPacket($pk);
            unset($this->scoreboards[$pl->getName()]);
        }
    }
    
    public function setLine(Player $pl, int $score, string $message) : void {
        if (!isset($this->scoreboards[$pl->getName()])) {
            $this->plugin->getLogger()->info("You have not set any scoreboards");
            return;
        }
        if ($score > 15 || $score < 1) {
            $this->plugin->getLogger()->info("Error, you exceeded the limit of parameters 1-15");
            return;
        }
        $objectiveName = $this->getObjectiveName($pl);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $pl->getNetworkSession()->sendDataPacket($pk);
    }
    
    public function getObjectiveName(Player $pl) : ?string {
        return $this->scoreboards[$pl->getName()] ?? null;
    }
}
?>