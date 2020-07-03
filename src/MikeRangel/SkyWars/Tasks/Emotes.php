<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, PluginUtils};
use pocketmine\{Server, Player, entity\Skin, scheduler\Task, utils\TextFormat as Color};

class Emotes extends Task {
    public $time = 3;
    public $player;
    public $emote;

    public function __construct(Player $player, string $emote) {
        $this->player = $player;
        $this->emote = $emote;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['emotes'])) {
            $this->time--;
            if ($this->time == 2) {
                $player->setSkin(new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), $player->getSkin()->getCapeData(), 'geometry.humanoid.custom', file_get_contents(SkyWars::getInstance()->getDataFolder() . 'Emotes/' . $this->emote . '.json')));
                $player->sendSkin();
                $player->sendPopup(Color::GOLD . 'Delete emotes in: ' . $this->time);
            } else if ($this->time == 1) {
                $player->sendPopup(Color::GOLD . 'Delete emotes in: ' . $this->time);
            } else if ($this->time == 0) {
                $player->sendPopup(Color::GOLD . 'Delete emotes in: ' . $this->time);
                $player->setSkin(SkyWars::$data['skins'][$player->getName()]);
                $player->sendSkin();
                $index = array_search($player->getName(), SkyWars::$data['emotes']);
		        if ($index != -1) {
			        unset(SkyWars::$data['emotes'][$index]);
                }
                $player->sendPopup(Color::GOLD . 'Delete emotes in: ' . $this->time);
                $player->setSkin(SkyWars::$data['skins'][$player->getName()]);
                $player->sendSkin();
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}