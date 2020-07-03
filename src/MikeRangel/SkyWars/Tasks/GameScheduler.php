<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use Core\Core;
use MikeRangel\{Loader};
use MikeRangel\SkyWars\{SkyWars, ResetMap, PluginUtils, Arena\Arena};
use pocketmine\{Server, Player, level\Level, item\Item, level\Position, entity\Skin, math\Vector3, scheduler\Task, utils\TextFormat as Color};
use pocketmine\network\mcpe\protocol\{ActorEventPacket, LevelEventPacket, LevelSoundEventPacket, ChangeDimensionPacket, PlayStatusPacket, types\DimensionIds};

class GameScheduler extends Task {

    public function onRun(int $currentTick) : void {
        if (count(Arena::getArenas()) > 0) {
            foreach (Arena::getArenas() as $arena) {
                $arenas = Server::getInstance()->getLevelByName(Arena::getName($arena));
                $timelobby = Arena::getTimeWaiting($arena);
                $timestarting = Arena::getTimeStarting($arena);
                $timegame = Arena::getTimeGame($arena);
                $timerefill = Arena::getTimeRefill($arena);
                $timeend = Arena::getTimeEnd($arena);
                if ($arenas instanceof Level) {
                    if (Arena::getStatus($arena) == 'waiting') {
                        foreach ($arenas->getPlayers() as $player) {
                            SkyWars::$data['skins'][$player->getName()] = $player->getSkin();
                            SkyWars::$data['damager'][$player->getName()] = 'string';
                            SkyWars::$data['kills'][Arena::getName($arena)][$player->getName()] = 0;
                            $player->getInventory()->setItem(0, Item::get(340, 0, 1)->setCustomName(Color::LIGHT_PURPLE . "Kits\n§r§fClick to select"));
                            $player->getInventory()->setItem(3, Item::get(345, 0, 1)->setCustomName(Color::GREEN . "Start\n§r§fClick to select"));
                            $player->getInventory()->setItem(5, Item::get(54, 0, 1)->setCustomName(Color::GOLD . "Vote Chest\n§r§fClick to select"));
                            $player->getInventory()->setItem(8, Item::get(355, 14, 1)->setCustomName(Color::RED . "Leave\n§r§fClick to select"));
                        }
                        if (count(Arena::getPlayers($arena)) < 2) {
                            foreach ($arenas->getPlayers() as $player) {
                                $arenas->setTime(0);
                                $arenas->stopTime();
                                SkyWars::getReloadArena($arena);
                                $player->sendPopup(Color::RED . 'More players are needed to start counting.');
                            }
                        } else {
                            $timelobby--;
                            Arena::setTimeWaiting($arena, $timelobby);
                            foreach ($arenas->getPlayers() as $player) {
                                if (count(Arena::getPlayers($arena)) == Arena::getSpawns($arena)) {
                                    $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' The arena has reached its maximum capacity, starting the game.');
                                    Arena::setStatus($arena, 'starting');
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                }
                                #animations.
                                if ($timelobby >= 20 && $timelobby <= 40) {
                                    $player->sendPopup(Color::GREEN . 'Starting game in ' . $timelobby . ' seconds.');
                                } else if ($timelobby >= 10 && $timelobby <= 20) {
                                    $player->sendPopup(Color::YELLOW . 'Starting game in ' . $timelobby . ' seconds.');
                                } else if ($timelobby >= 1 && $timelobby <= 10) {
                                    $player->sendPopup(Color::RED . 'Starting game in ' . $timelobby . ' seconds.');
                                }
                                if ($timelobby == 0) {
                                    Arena::setStatus($arena, 'starting');
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                }
                            }
                        }
                    } else if (Arena::getStatus($arena) == 'starting') {
                        $alive = 0;
                        $timestarting--;
                        Arena::setTimeStarting($arena, $timestarting);
                        foreach ($arenas->getPlayers() as $player) {
                            $profile = SkyWars::getConfigs('Profiles/' . $player->getName());
                            $alive++;
                            if ($timestarting >= 0 && $timestarting <= 10) {
                                if (count(Arena::getPlayers($arena)) == 1) {
                                    $player->setImmobile(false);
                                    $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                                    $player->setGamemode(2);
                                }
                            }
                            if ($timestarting >= 7 && $timestarting <= 10) {
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_NOTE, $timestarting);
                                $player->sendPopup(Color::GREEN . 'Opening cage in -> ' . $timestarting);
                            } else if ($timestarting >= 3 && $timestarting <= 7) {
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_NOTE, $timestarting);
                                $player->sendPopup(Color::YELLOW . 'Opening cage in -> ' . $timestarting);
                            } else if ($timestarting >= 1 && $timestarting <= 3) {
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_NOTE, $timestarting);
                                $player->sendPopup(Color::RED . 'Opening cage in -> ' . $timestarting);
                            }
                            /*if ($timestarting == 11) {
                                $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                                $pk = new ChangeDimensionPacket();
		                        $pk->dimension = DimensionIds::NETHER;
                                $pk->position = $player->asVector3();
                                $player->dataPacket($pk);
                                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new Dimension($player, $arena), 10);
                            } else */
                            if ($timestarting == 10) {
                                $player->setImmobile(true);
                                $lobby = SkyWars::getConfigs('Arenas/' . $arena);
                                $spawn = $lobby->get('slot-' . $alive);
                                $spawns = new Position($spawn[0], $spawn[1], $spawn[2], $arenas);
                                $arenas->loadChunk($spawns->getFloorX(), $spawns->getFloorZ());
                                $player->teleport($spawns);
                                PluginUtils::getCage($player, $profile->get('cage'));
                            } else if ($timestarting == 0) {
                                $player->getInventory()->clearAll();
                                $player->getArmorInventory()->clearAll();
                                Arena::setStatus($arena, 'ingame');
                                SkyWars::$data['skins'][$player->getName()] = $player->getSkin();
                                SkyWars::$data['damager'][$player->getName()] = 'string';
                                SkyWars::$data['kills'][Arena::getName($arena)][$player->getName()] = 0;
                                PluginUtils::unsetCage($player, $profile->get('cage'));
                                PluginUtils::playSound($player, 'conduit.activate', 1, 1);
                                PluginUtils::getKit($player, $profile->get('kit'));
                                $player->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
                                if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                                    $player->addTitle(Color::GOLD . '¡Starting!', Color::GRAY . 'Chest: OP');
                                    PluginUtils::chestOP(Arena::getName($arena));
                                } else {
                                    $player->addTitle(Color::GOLD . '¡Starting!', Color::GRAY . 'Chest: Default');
                                    PluginUtils::chestDefault(Arena::getName($arena));
                                }
                                $player->setGamemode(0);
                                $player->setImmobile(false);
                            }
                        }
                    } else if (Arena::getStatus($arena) == 'ingame') {
                        $timegame--;
                        $timerefill--;
                        Arena::setTimeGame($arena, $timegame);
                        Arena::setTimeRefill($arena, $timerefill);
                        foreach ($arenas->getPlayers() as $player) {
                            $profile = SkyWars::getConfigs('Profiles/' . $player->getName());
                            if (in_array($player->getName(), SkyWars::$data['kills'][Arena::getName($arena)])) {
                                $from = 0;
                                $api = SkyWars::getScore();
                                $api->new($player, $player->getName(), Color::GOLD . Color::BOLD . 'SKYWARS');
                                $wins = SkyWars::getConfigs('wins');
                                if ($player->getGamemode() == 0) {
                                    $setlines = [
                                        Color::GOLD . 'Specters: ' . Color::WHITE . count(Arena::getSpecters($arena)),
                                        Color::GOLD . 'Alive: ' . Color::WHITE . count(Arena::getPlayers($arena)) . '/' . Arena::getSpawns($arena),
                                        Color::YELLOW . "   ",
                                        Color::GOLD . 'Refill in: ' . Color::WHITE . PluginUtils::getTimeParty($timerefill),
                                        Color::GOLD . 'EndTime in: ' . Color::WHITE . PluginUtils::getTimeParty($timegame),
                                        Color::BLUE . "     ",
                                        Color::GOLD . 'Coins: ' . Color::WHITE . $profile->get('coins'),
                                        Color::GOLD . 'Wins: ' . Color::WHITE . $wins->get($player->getName()),
                                        Color::GOLD . 'Kills: ' . Color::WHITE . SkyWars::$data['kills'][Arena::getName($arena)][$player->getName()],
                                        Color::LIGHT_PURPLE . "     ",
                                        Color::WHITE . 'pacmanlife.cf:19132'
                                    ];
                                } else {
                                    $setlines = [
                                        Color::GOLD . 'Specters: ' . Color::WHITE . count(Arena::getSpecters($arena)),
                                        Color::GOLD . 'Alive: ' . Color::WHITE . count(Arena::getPlayers($arena)) . '/' . Arena::getSpawns($arena),
                                        Color::YELLOW . "   ",
                                        Color::GOLD . 'Refill in: ' . Color::WHITE . PluginUtils::getTimeParty($timerefill),
                                        Color::GOLD . 'EndTime in: ' . Color::WHITE . PluginUtils::getTimeParty($timegame),
                                        Color::BLUE . "     ",
                                        Color::GOLD . 'Coins: ' . Color::WHITE . $profile->get('coins'),
                                        Color::GOLD . 'Wins: ' . Color::WHITE . $wins->get($player->getName()),
                                        Color::LIGHT_PURPLE . "     ",
                                        Color::WHITE . 'pacmanlife.cf:19132'
                                    ];
                                }
                                foreach ($setlines as $lines) {
                                    if ($from < 15) {
                                        $from++;
                                        $api->setLine($player, $from, $lines);
                                        $api->getObjectiveName($player);
                                    }
                                }
                            }
                            if ($timerefill == 0) {
                                if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                                    PluginUtils::chestOP(Arena::getName($arena));
                                } else {
                                    PluginUtils::chestDefault(Arena::getName($arena));
                                }
                                switch (rand(1, 2)) {
                                    case 1:
                                        $player->addTitle(Color::GOLD . '¡Filled chests!', Color::GRAY . 'Go for that victory');
                                    break;
                                    case 2:
                                        $player->addTitle(Color::GOLD . '¡Filled chests!', Color::GRAY . 'Equip yourself better');
                                    break;
                                }
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_CHEST_OPEN);
                                Arena::setTimeRefill($arena, 120);
                            }
                            #animations.
                            if ($timegame == 599) {
                                $player->sendPopup(Color::GREEN . 'Activating blows and damage in the game in: 10');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 598) {
                                $player->sendPopup(Color::GREEN . 'Activating blows and damage in the game in: 9');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 597) {
                                $player->sendPopup(Color::GREEN . 'Activating blows and damage in the game in: 8');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 596) {
                                $player->sendPopup(Color::GREEN . 'Activating blows and damage in the game in: 7');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 595) {
                                $player->sendPopup(Color::YELLOW . 'Activating blows and damage in the game in: 6');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 594) {
                                $player->sendPopup(Color::YELLOW . 'Activating blows and damage in the game in: 5');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 593) {
                                $player->sendPopup(Color::YELLOW . 'Activating blows and damage in the game in: 4');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 592) {
                                $player->sendPopup(Color::RED . 'Activating blows and damage in the game in: 3');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 591) {
                                $player->sendPopup(Color::RED . 'Activating blows and damage in the game in: 2');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 590) {
                                $player->sendPopup(Color::RED . 'Activating blows and damage in the game in: 1');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            }
                            #game.
                            if ($timegame == 589) {
                                $player->sendMessage(Color::GOLD . '¡Damage activated!');
                                $this->pvp = 11;
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_UP);
                            } else if ($timegame == 0) {
                                Arena::setStatus($arena, 'end');
                                Server::getInstance()->broadcastMessage(SkyWars::getPrefix() . Color::RED . ' There were no winners in the arena: ' . Color::RED . $arena);
                            }
                            if (count(Arena::getPlayers($arena)) == 1) {
                                Arena::setStatus($arena, 'end');
                                $api = SkyWars::getScore();
                                $api->remove($player);
                                $lobbywin = SkyWars::getConfigs('Arenas/' . $arena);
                                $lobby = $lobbywin->get('lobbywin');
                                if ($player->getGamemode() == 0) {
                                    $arenas->setTime(20000);
                                    $arenas->stopTime();
                                    Server::getInstance()->broadcastMessage(SkyWars::getPrefix() . Color::GRAY . $player->getName() . Color::WHITE . ' You have won the game in the arena: ' . Color::DARK_AQUA . $arena);
                                    $tops = [];
                                    $kills = SkyWars::$data['kills'][Arena::getName($arena)];
                                    foreach ($kills as $key => $top) {
                                        array_push($tops, $top);
                                    }
                                    natsort($tops);
                                    $players = array_reverse($tops);
                                    if (max($tops) != null) {
                                        $max = array_search(max($tops), $kills);
                                        $top1 = Color::GOLD . '#1 ' . Color::WHITE . $max . Color::GOLD . ' - ' . Color::GREEN . max($tops);
                                    } else {
                                        $top1 = Color::GOLD . '#1 ' . Color::WHITE . 'Null' . Color::GOLD . ' - ' . Color::GREEN . '0';
                                    }
                                    if ($players[1] != null) {
                                        $medium = array_search($players[1], $kills);
                                        $top2 = Color::YELLOW . '#2 ' . Color::WHITE . $medium . Color::GOLD . ' - ' . Color::GREEN . $players[1];
                                    } else {
                                        $top2 = Color::YELLOW . '#2 ' . Color::WHITE . 'Null' . Color::GOLD . ' - ' . Color::GREEN . '0';
                                    }
                                    if ($players[2] != null) {
                                        $minimun = array_search($players[2], $kills);
                                        $top3 = Color::RED . '#3 ' . Color::WHITE . $minimun . Color::GOLD . ' - ' . Color::GREEN . $players[2];
                                    } else {
                                        $top3 = Color::RED . '#3 ' . Color::WHITE . 'Null' . Color::GOLD . ' - ' . Color::GREEN . '0';
                                    }
                                    Server::getInstance()->broadcastMessage(Color::GRAY . '=(' . Color::DARK_AQUA . 'Top Kills' . Color::GRAY . ')=' . "\n" .
                                    Color::GRAY . '================' . "\n" .
                                    $top1 . "\n" .
                                    $top2 . "\n" .
                                    $top3 . "\n" .
                                    Color::GRAY . '================'
                                    );
                                    $player->addTitle(Color::GOLD . '¡Victory!', Color::GRAY . '+5 Coins.');
                                    $player->sendMessage(Color::LIGHT_PURPLE . '+5 Coins.');
                                    $player->setGamemode(2);
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                    $profile->set('coins', $profile->get('coins') + 5);
                                    $profile->save();
                                    $wins = SkyWars::getConfigs('wins');
                                    $wins->set($player->getName(), $wins->get($player->getName()) + 1);
                                    $wins->save();
                                    $player->getInventory()->setItem(4, Item::get(388, 0, 1)->setCustomName(Color::GOLD . "Emotes\n§r§fClick to select"));
                                    $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                                    //Loader::addRocketSW($player);
                                    /* Solo por ahora */
                                    $db = SkyWars::getInstance()->getServer()->getPluginManager()->getPlugin('Core');
                                    if ($db instanceof Core) {
                                        for ($win = 0; $win <= 5; $win++) {
                                            $db->addFireworks($player, rand(1, 3), 5);
                                        }
                                    }
                                } else if ($player->getGamemode() == 3) {
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                    $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                                }
                            }
                        }
                    } else if (Arena::getStatus($arena) == 'end') {
                        $timeend--;
                        Arena::setTimeEnd($arena, $timeend);
                        if ($timeend == 7) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->getInventory()->clearAll();
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::YELLOW . 'The search for a new game will begin, cancel the wait using the remaining item of the players to continue watching.');
                                if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                                    SkyWars::$data['queue'][] = $player->getName();
                                    SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
                                }
                            }
                        } else if ($timeend == 3) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::GREEN . 'Reseting game in 3 seconds.');
                            }
                        } else if ($timeend == 2) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::YELLOW . 'Reseting game in 2 seconds.');
                            }
                        } else if ($timeend == 1) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::RED . 'Reseting game in 1 second.');
                            }
                        } else if ($timeend == 0) {
                            ResetMap::resetZip(Arena::getName($arena));
                            SkyWars::getReloadArena($arena);
                            foreach ($arenas->getPlayers() as $player) {
                                $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                                $player->getInventory()->clearAll();
                                $player->getArmorInventory()->clearAll();
                                $player->setImmobile(false);
                                $player->setAllowFlight(false);
                                $player->setFlying(false);
                                $player->removeAllEffects();
                                $player->setGamemode(2);
                                $player->setHealth(20);
                                $player->setFood(20);   
                            }
                        }
                    }
                }
            }
        }
    }
}
?>