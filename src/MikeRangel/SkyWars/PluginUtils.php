<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Tasks\NewGame, Tasks\ArenaID};
use pocketmine\{Server, Player, item\Item, tile\Tile, tile\Chest, inventory\ChestInventory, entity\Effect, entity\EffectInstance, math\Vector3, entity\Entity, block\Block, utils\Color, utils\TextFormat};
use pocketmine\network\mcpe\protocol\{AddActorPacket, PlaySoundPacket, LevelSoundEventPacket, StopSoundPacket};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\level\sound\{EndermanTeleportSound};

class PluginUtils {

    public static function getTimeParty(int $value) {
        return gmdate("i:s", $value);
    }

    public static function getTaskGame(Player $player) {
        SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
    }

    public static function getContents() {
        $config = SkyWars::getConfigs('config');
        return array_rand($config->get('chestitems'));
    }

    public static function getContentsOP() {
        $config = SkyWars::getConfigs('config');
        return array_rand($config->get('chestitemsop'));
    }

    public static function getContentsTwo() {
        $config = SkyWars::getConfigs('config');
        return $config->get('chestitems');
    }

    public static function getContentsTwoOP() {
        $config = SkyWars::getConfigs('config');
        return $config->get('chestitemsop');
    }

    public static function playSound(Player $player, string $sound, float $volume = 0, float $pitch = 0) {
        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = (int)$player->x;
        $pk->y = (int)$player->y;
        $pk->z = (int)$player->z;
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        $player->dataPacket($pk);
    }

    public static function stopSound(Player $player, string $sound, $all = true) {
        $pk = new StopSoundPacket();
        $pk->soundName = $sound;
        $pk->stopAll = $all;
        $player->dataPacket($pk);
    }

    public static function getCage(Player $player, string $value) {
        if ($value == 'Default') {
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 1, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 3, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() + 1, $player->getY() + 2, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 2, $player->getZ() + 1), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() - 1, $player->getY() + 2, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 2, $player->getZ() - 1), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() + 1, $player->getY() - 0, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 0, $player->getZ() + 1), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() - 1, $player->getY() - 0, $player->getZ()), Block::get(20), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 0, $player->getZ() - 1), Block::get(20), false, true);
        } else {
            $config = SkyWars::getConfigs('Cages/' . $value);
            $array = $config->get($value, []);
            if ($array != null) {
                $blocks = $array['BLOCKS'];
                foreach ($blocks as $data => $block) {
                    if ($block['X'] == 0 && $block['Y'] == 1 && $block['Z'] == 0) continue;
                    if ($block['X'] == 0 && $block['Y'] == 2 && $block['Z'] == 0) continue;
                    $player->getLevel()->setBlock($player->getPosition()->add(intval($block['X']), intval($block['Y'])-1, intval($block['Z'])), Block::get($block['ID'], $block['META']), false, false);
                }
            }
        }
    }

    public static function unsetCage(Player $player, string $value) {
        if ($value == 'Default') {
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 1, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 3, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() + 1, $player->getY() + 2, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 2, $player->getZ() + 1), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() - 1, $player->getY() + 2, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() + 2, $player->getZ() - 1), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() + 1, $player->getY() - 0, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 0, $player->getZ() + 1), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX() - 1, $player->getY() - 0, $player->getZ()), Block::get(0), false, true);
            $player->getLevel()->setBlock(new Vector3($player->getX(), $player->getY() - 0, $player->getZ() - 1), Block::get(0), false, true);
        } else {
            $config = SkyWars::getConfigs('Cages/' . $value);
            $array = $config->get($value, []);
            if ($array != null) {
                $blocks = $array['BLOCKS'];
                foreach ($blocks as $data => $block) {
                    if ($block['X'] == 0 && $block['Y'] == 1 && $block['Z'] == 0) continue;
                    if ($block['X'] == 0 && $block['Y'] == 2 && $block['Z'] == 0) continue;
                    $player->getLevel()->setBlock($player->getPosition()->add(intval($block['X']), intval($block['Y'])-1, intval($block['Z'])), Block::get(0), false, false);
                }
            }
        }
    }

    public static function addStrike(array $players, Player $deathPlayer) {
		$packet = new AddActorPacket();
		$packet->type = 93;
		$packet->entityRuntimeId = Entity::$entityCount++;
		$packet->metadata = [];
		$packet->position = $deathPlayer->asVector3()->add(0, $height = 0);
		$packet->yaw = $deathPlayer->getYaw();
		$packet->pitch = $deathPlayer->getPitch();
        foreach ($players as $player) {
            $player->dataPacket($packet);
            self::playSound($player, 'ambient.weather.lightning.impact', 1, 1);
        }
    }

    public static function setVote(Player $player, string $arena, string $value) {
        switch ($value) {
            case 'op':
                if (isset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()])) {
                    unset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()]);
                    $player->sendMessage(TextFormat::RED . 'Your vote has been smashed.');
                } else {
                    if (!isset(SkyWars::$data['vote'][$arena]['op'][$player->getName()])) {
                        SkyWars::$data['vote'][$arena]['op'][$player->getName()] = $player->getName();
                        foreach (Server::getInstance()->getLevelByName($arena)->getPlayers() as $players) {
                            $players->sendMessage(TextFormat::GOLD . $player->getName() . ' You voted for chests OP.');
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ENDERCHEST_OPEN);
                        }
                    } else {
                        unset(SkyWars::$data['vote'][$arena]['op'][$player->getName()]);
                        $player->sendMessage(TextFormat::RED . 'Your vote has been smashed.');
                    }
                }
            break;
            case 'normal':
                if (isset(SkyWars::$data['vote'][$arena]['op'][$player->getName()])) {
                    unset(SkyWars::$data['vote'][$arena]['op'][$player->getName()]);
                    $player->sendMessage(TextFormat::RED . 'Your vote has been smashed.');
                } else {
                    if (!isset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()])) {
                        SkyWars::$data['vote'][$arena]['normal'][$player->getName()] = $player->getName();
                        foreach (Server::getInstance()->getLevelByName($arena)->getPlayers() as $players) {
                            $players->sendMessage(TextFormat::GOLD . $player->getName() . ' You voted for chests Basic.');
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ENDERCHEST_OPEN);
                        }
                    } else {
                        unset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()]);
                        $player->sendMessage(TextFormat::RED . 'Your vote has been smashed.');
                    }
                }
            break;
        }
    }

    public static function getEventDamage(Player $player, string $arena, string $cause, Player $damager = null) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $lobby = $config->get('lobbyspecters');
        if (count(Arena::getPlayers($arena)) != 1) {
            if ($player->getGamemode() != 3) {
                if ($damager != false) {
                    SkyWars::$data['kills'][Arena::getName($arena)][$damager->getName()] = SkyWars::$data['kills'][Arena::getName($arena)][$damager->getName()] + 1;
                    $kills = SkyWars::getConfigs('kills');
                    $kills->set($damager->getName(), $kills->get($damager->getName()) + 1);
                    $kills->save();
                    foreach ($damager->getLevel()->getPlayers() as $players) {
                        $players->sendMessage(TextFormat::RED . $player->getName() . TextFormat::GRAY . ' ' . $cause . ' ' . TextFormat::GOLD . $damager->getName() . '.');
                        $remain = (count(Arena::getPlayers($arena)) - 1);
                        if ($remain > 1) {
                            $players->sendMessage(TextFormat::RED . $remain . ' players remain alive.');
                        }
                    }
                    $getcoins = SkyWars::getConfigs('Profiles/' . $player->getName());
                    if ($getcoins->get('coins') >= 0) {
                        $getcoins->set('coins', $getcoins->get('coins') - 1);
                        $getcoins->save();
                        $player->sendMessage(TextFormat::LIGHT_PURPLE . '-1 Coins.');
                    }
                    $coins = SkyWars::getConfigs('Profiles/' . $damager->getName());
                    $coins->set('coins', $config->get('coins') + 2);
                    $coins->save();
                    $damager->sendMessage(TextFormat::LIGHT_PURPLE . '+2 Coins.');
                } else {
                    foreach ($player->getLevel()->getPlayers() as $players)  {
                        $players->sendMessage(TextFormat::RED . $player->getName() . TextFormat::GRAY . ' ' . $cause . '.');
                        $remain = (count(Arena::getPlayers($arena)) - 1);
                        if ($remain > 1) {
                            $players->sendMessage(TextFormat::RED . $remain . ' players remain alive.');
                        }
                    }
                }
                foreach ($player->getDrops() as $drops) {
                    $player->getLevel()->dropItem($player, $drops);
                }
                self::addStrike(Server::getInstance()->getLevelByName($player->getLevel()->getFolderName())->getPlayers(), $player);
                $player->removeAllEffects();
                $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20, 3));
                switch (rand(1, 2)) {
                    case 1:
                        $player->addTitle(TextFormat::BOLD . TextFormat::RED . '¡You died!', TextFormat::YELLOW . 'You lost the game');
                    break;
                    case 2:
                        $player->addTitle(TextFormat::BOLD . TextFormat::RED . '¡You died!', TextFormat::YELLOW . 'Good luck next time');
                    break;
                }
                $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                $player->setGamemode(3);
                $player->setHealth(20);
                $player->setFood(20);
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->sendMessage(TextFormat::BOLD . TextFormat::GREEN . '» ' . TextFormat::RESET . TextFormat::YELLOW . 'The search for a new game will begin, cancel the wait using the remaining item of the players to continue watching.');
                if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                    SkyWars::$data['queue'][] = $player->getName();
                    SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new NewGame($player), 10);
                }
                $player->getInventory()->setItem(0, Item::get(381, 0, 1)->setCustomName(TextFormat::GOLD . "Players Reaming\n§r§fClick to select"));
                $player->getInventory()->setItem(4, Item::get(120, 0, 1)->setCustomName(TextFormat::LIGHT_PURPLE . "Random Game\n§r§fClick to select"));
                $player->getInventory()->setItem(8, Item::get(355, 14, 1)->setCustomName(TextFormat::RED . "Leave\n§r§fClick to select"));
            } else {
                $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
            }
        } else {
            $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
        }
    }
                   
    public static function joinSolo(Player $player, int $id) {
        if ($player instanceof Player) {
            $world = Server::getInstance()->getLevelByName(Arena::getName('SW-' . $id));
            if (Arena::getStatus('SW-' . $id) == 'waiting') {
                if (count(Arena::getPlayers('SW-' . $id)) < Arena::getSpawns('SW-' . $id)) {
                    SkyWars::$data['skins'][$player->getName()] = $player->getSkin();
                    SkyWars::$data['damager'][$player->getName()] = 'string';
                    SkyWars::$data['kills'][Arena::getName('SW-' . $id)][$player->getName()] = 0;
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->sendMessage(TextFormat::GREEN . TextFormat::BOLD . '» ' . TextFormat::RESET . TextFormat::GREEN . 'An available game has been found: ' . 'SW-' . $id);
                    $config = SkyWars::getConfigs('Arenas/' . 'SW-' . $id);
                    $lobby = $config->get('lobby');
                    $player->teleport(Server::getInstance()->getLevelByName(Arena::getName('SW-' . $id))->getSpawnLocation());
                    $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                    $player->setAllowFlight(false);
                    $player->setFlying(false);    
                    $player->removeAllEffects();
                    $player->setGamemode(2);
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->setScale(1);
                    foreach ($world->getPlayers() as $players) {
                        $players_array[] = $players->getName();
                    }
                    $player->sendMessage(TextFormat::GOLD . join(TextFormat::GOLD  . ', ' . TextFormat::GOLD, $players_array) . TextFormat::GOLD . '.');
                    $player->getLevel()->addSound(new EndermanTeleportSound($player));
                    foreach ($world->getPlayers() as $players) {
                        $players->sendMessage(TextFormat::GREEN . TextFormat::BOLD . '» ' . TextFormat::RESET . TextFormat::DARK_GRAY . $player->getName() . ' ' . 'Joined the game.' . ' ' . TextFormat::DARK_GRAY . '[' . TextFormat::DARK_GRAY . count($world->getPlayers()) . TextFormat::DARK_GRAY . '/' . TextFormat::DARK_GRAY . Arena::getSpawns('SW-' . $id) . TextFormat::DARK_GRAY . ']');
                        $players->getLevel()->addSound(new EndermanTeleportSound($players));
                    }
                }
            }
        }
    }

    public static function setZip(string $arena) {
		$level = Server::getInstance()->getLevelByName($arena);
		if ($level !== null) {
			$level->save(true);
			$levelPath = SkyWars::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $arena;
			$zipPath = SkyWars::getInstance()->getDataFolder() . 'Backups' . DIRECTORY_SEPARATOR . $arena . '.zip';
			$zip = new \ZipArchive();
			if (is_file($zipPath)) {
				unlink($zipPath);
			}
			$zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath($levelPath)), \RecursiveIteratorIterator::LEAVES_ONLY);
			foreach ($files as $file) {
				if ($file->isFile()) {
					$filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
					$localPath = substr($filePath, strlen(SkyWars::getInstance()->getServer()->getDataPath() . 'worlds'));
					$zip->addFile($filePath, $localPath);
				}
			}
			$zip->close();
		}
	}

    public static function getKit(Player $player, string $value) {
        switch ($value) {
            case 'rusher':
                $player->getInventory()->clearAll();
                $dp = Item::get(267, 0, 1);
                $efficiency = Enchantment::getEnchantment(9);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $player->getInventory()->addItem(Item::get(Item::COOKED_PORKCHOP, 0, 8));
                $player->getInventory()->addItem(Item::get(Item::STONE, 0, 64));
            break;
            case 'baseball':
                $player->getInventory()->clearAll();
                $dp = Item::get(280, 0, 1);
                $efficiency = Enchantment::getEnchantment(12);
                $efficiency1 = new EnchantmentInstance($efficiency, 3);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
            break;
            case 'ghost':
                $player->getInventory()->clearAll();
        	    $player->getInventory()->addItem(Item::get(Item::POTION, 7, 1));
                $player->getInventory()->addItem(Item::get(Item::WOODEN_SWORD, 0, 1));
            break;
            case 'healer':
                $player->getInventory()->clearAll();
                $player->getInventory()->addItem(Item::get(Item::SPLASH_POTION, 21, 3));
                $player->getInventory()->addItem(Item::get(Item::SPLASH_POTION, 29, 2));
                $player->getInventory()->addItem(Item::get(Item::GOLDEN_APPLE, 0, 3));
            break;
            case 'archer':
                $player->getInventory()->clearAll();
                $dp = Item::get(261, 0, 1);
                $efficiency = Enchantment::getEnchantment(19);
                $efficiency1 = new EnchantmentInstance($efficiency, 2);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $player->getInventory()->addItem(Item::get(Item::ARROW, 0, 20));
            break;
            case 'piromo':
                $player->getInventory()->clearAll();
                $dp = Item::get(272, 0, 1);
                $eff = Enchantment::getEnchantment(9);
                $eff1 = new EnchantmentInstance($eff, 1);
                $efficiency = Enchantment::getEnchantment(13);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $dp->addEnchantment($eff1);
                $player->getInventory()->addItem($dp);
            break;
            case 'asesino':
                $player->getInventory()->clearAll();
                $dp = Item::get(267, 0, 1);
                $efficiency = Enchantment::getEnchantment(9);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $boots = Item::get(299, 0, 1);
                $color = new Color(0, 0, 0);
                $boots->setCustomColor($color);
                $player->getArmorInventory()->setChestplate($boots);
            break;
            case 'lumberhack':
                $player->getInventory()->clearAll();
                $dp = Item::get(258, 0, 1);
                $efficiency = Enchantment::getEnchantment(15);
                $efficiency1 = new EnchantmentInstance($efficiency, 2);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $player->getInventory()->addItem(Item::get(Item::LOG, 0, 15));
            break;
            case 'enderman':
                $player->getInventory()->clearAll();
                $dp = Item::get(368, 0, 2);
                $player->getInventory()->addItem($dp);
                $chest = Item::get(Item::LEATHER_CHESTPLATE);
                $leg = Item::get(Item::LEATHER_LEGGINGS);
                $boots = Item::get(Item::LEATHER_BOOTS);
                $color = new Color(0, 0, 0);
                $chest->setCustomColor($color);
                $leg->setCustomColor($color);
                $boots->setCustomColor($color);
                $player->getArmorInventory()->setChestplate($chest);
                $player->getArmorInventory()->setLeggings($leg);
                $player->getArmorInventory()->setBoots($boots);
            break;
            case 'rusheryt':
                $player->getInventory()->clearAll();
                $dp = Item::get(272, 0, 1);
                $efficiency = Enchantment::getEnchantment(9);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $player->getInventory()->addItem(Item::get(Item::WOODEN_PLANKS, 0, 20));
            break;
            case 'boomberman':
                $player->getInventory()->clearAll();
                $dp = Item::get(259, 0, 1);
                $player->getInventory()->addItem($dp);
                $player->getInventory()->addItem(Item::get(Item::TNT, 0, 3));
                $dp = Item::get(268, 0, 1);
                $player->getInventory()->addItem($dp);
                $chest = Item::get(Item::LEATHER_CHESTPLATE);
                $color = new Color(255, 0, 0);
                $chest->setCustomColor($color);
                $player->getArmorInventory()->setChestplate($chest);
            break;
            case 'builder':
                $player->getInventory()->clearAll();
                $player->getInventory()->addItem(Item::get(Item::BRICK_BLOCK, 0, 20));
            break;
            case 'tools':
                $player->getInventory()->clearAll();
                $dp = Item::get(268, 0, 1);
                $efficiency = Enchantment::getEnchantment(17);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $dp = Item::get(270, 0, 1);
                $efficiency = Enchantment::getEnchantment(17);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
                $dp = Item::get(271, 0, 1);
                $efficiency = Enchantment::getEnchantment(17);
                $efficiency1 = new EnchantmentInstance($efficiency, 1);
                $dp->addEnchantment($efficiency1);
                $player->getInventory()->addItem($dp);
            break;
            case 'saltamontes':
                $player->getInventory()->clearAll();
                $chest = Item::get(Item::LEATHER_CHESTPLATE);
                $leg = Item::get(Item::LEATHER_LEGGINGS);
                $boots = Item::get(Item::LEATHER_BOOTS);
                $color = new Color(0, 255, 0);
                $chest->setCustomColor($color);
                $leg->setCustomColor($color);
                $boots->setCustomColor($color);
                $player->getArmorInventory()->setChestplate($chest);
                $player->getArmorInventory()->setLeggings($leg);
                $player->getArmorInventory()->setBoots($boots);
                $jump = new EffectInstance(Effect::getEffect(8), 20 * 9999, 1); 
                $player->addEffect($jump);
                $speed = new EffectInstance(Effect::getEffect(1), 20 * 9999, 0); 
                $player->addEffect($speed);
            break;
        }
    }
    
    public static function chestOP(string $arena) {
        $level = Server::getInstance()->getLevelByName($arena);
        foreach ($level->getTiles() as $tiles) {
            if ($tiles instanceof Chest) {
                $tiles->getInventory()->clearAll();
                if ($tiles->getInventory() instanceof ChestInventory) {
                    for ($i = 0; $i <= 26; $i++) {
                        $random = rand(1, 3);
                        if ($random == 1) {
                            $contents = self::getContentsOP();
                            $contentstwoOP = self::getContentsTwoOP()[$contents];
                            $item = Item::get($contentstwoOP[0], $contentstwoOP[1], $contentstwoOP[2]);
                            if ($item->getId() == Item::DIAMOND_SWORD ||
                                $item->getId() == Item::IRON_SWORD ||
                                $item->getId() == Item::STONE_AXE ||
                                $item->getId() == Item::IRON_AXE ||
                                $item->getId() == Item::GOLD_SWORD) {
                                $flame = Enchantment::getEnchantment(Enchantment::FLAME);
                                $punch = Enchantment::getEnchantment(Enchantment::PUNCH);
                                $rray = array($flame, $punch);
                                shuffle($rray);
                                $item->addEnchantment(new EnchantmentInstance($rray[0], mt_rand(1, 5)));
                            } else if ($item->getId() == Item::BOW) {
                                $flame = Enchantment::getEnchantment(Enchantment::FLAME);
                                $rray = array($flame);
                                shuffle($rray);
                                $item->addEnchantment(new EnchantmentInstance($rray[0], mt_rand(1, 5)));
                            } else if ($item->getId() == Item::DIAMOND_HELMET ||
                                $item->getId() == Item::IRON_HELMET ||
                                $item->getId() == Item::GOLD_HELMET ||
                                $item->getId() == Item::DIAMOND_CHESTPLATE ||
                                $item->getId() == Item::IRON_CHESTPLATE ||
                                $item->getId() == Item::GOLD_CHESTPLATE ||
                                $item->getId() == Item::DIAMOND_LEGGINGS ||
                                $item->getId() == Item::IRON_LEGGINGS ||
                                $item->getId() == Item::GOLD_LEGGINGS ||
                                $item->getId() == Item::DIAMOND_BOOTS ||
                                $item->getId() == Item::IRON_BOOTS) {
                                $proteccion = Enchantment::getEnchantment(Enchantment::PROTECTION);
                                $proteccionfire = Enchantment::getEnchantment(Enchantment::FIRE_PROTECTION);
                                $rray = array($proteccion, $proteccionfire);
                                shuffle($rray);
                                $item->addEnchantment(new EnchantmentInstance($rray[0], mt_rand(1, 5)));
                            }
                            $tiles->getInventory()->setItem($i, $item);
                        }
                    }
                }
            }
        }
    }

    public static function chestDefault(string $arena) {
        $level = Server::getInstance()->getLevelByName($arena);
        foreach ($level->getTiles() as $tiles) {
            if ($tiles instanceof Chest) {
                $tiles->getInventory()->clearAll();
                if ($tiles->getInventory() instanceof ChestInventory) {
                    for ($i = 0; $i <= 26; $i++) {
                        $random = rand(1, 3);
                        if ($random == 1) {
                            $contents = self::getContents();
                            $contentstwo = self::getContentsTwo()[$contents];
                            $item = Item::get($contentstwo[0], $contentstwo[1], $contentstwo[2]);
                            $tiles->getInventory()->setItem($i, $item);
                        }
                    }
                }
            }
        }
    }
}
?>