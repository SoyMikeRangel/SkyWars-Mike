<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Tasks\NewGame, Tasks\ArenaID, Entity\EntityManager};
use pocketmine\{Server, world\World, player\Player, player\GameMode, item\Item, block\tile\Chest, block\inventory\ChestInventory, entity\effect\EffectInstance, entity\effect\VanillaEffects, math\Vector3, entity\Entity, block\Block, item\VanillaItems, block\VanillaBlocks, utils\TextFormat as Color};
use pocketmine\network\mcpe\protocol\{AddActorPacket, ActorEventPacket, PlaySoundPacket, LevelSoundEventPacket, StopSoundPacket, types\entity\EntityIds, types\entity\PropertySyncData, types\LevelSoundEvent};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance, VanillaEnchantments};
use pocketmine\world\sound\{EndermanTeleportSound};

class PluginUtils {

    public static function getTimeParty(int $value) {
        return gmdate("i:s", $value);
    }

    public static function getTaskGame(Player $player) {
        SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
    }

    public static function playSound(Player $player, string $sound, float $volume = 0, float $pitch = 0) {
        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = (int) $player->getPosition()->getX();
        $pk->y = (int) $player->getPosition()->getY();
        $pk->z = (int) $player->getPosition()->getZ();
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public static function stopSound(Player $player, string $sound, $all = true) {
        $pk = new StopSoundPacket();
        $pk->soundName = $sound;
        $pk->stopAll = $all;
        $player->dataPacket($pk);
    }

    public static function getCage(Player $player, string $value) {
        $world = $player->getWorld();
        $pos = $player->getPosition();
    
        if ($value == 'Default') {
            $positions = [
                [0, -1, 0], [0, 3, 0], [1, 2, 0], [0, 2, 1],
                [-1, 2, 0], [0, 2, -1], [1, 0, 0], [0, 0, 1],
                [-1, 0, 0], [0, 0, -1]
            ];
            foreach ($positions as $offset) {
                $world->setBlock($pos->add($offset[0], $offset[1], $offset[2]), VanillaBlocks::GLASS(), false, true);
            }

        } else {
            $config = SkyWars::getConfigs('Cages/' . $value);
            $array = $config->get($value, []);
            if ($array != null) {
                $blocks = $array['BLOCKS'];
                foreach ($blocks as $data => $block) {
                    if ($block['X'] == 0 && $block['Y'] == 1 && $block['Z'] == 0) continue;
                    if ($block['X'] == 0 && $block['Y'] == 2 && $block['Z'] == 0) continue;
                    $player->getWorld()->setBlock($player->getPosition()->add(intval($block['X']), intval($block['Y'])-1, intval($block['Z'])), VanillaBlocks::{$block['BLOCK']}(), false, false);
                }
            }
        }
    }

    public static function unsetCage(Player $player, string $value) {
        if ($value == 'Default') {
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() - 1, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX() + 1, $player->getPosition()->getY() + 2, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 2, $player->getPosition()->getZ() + 1), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX() - 1, $player->getPosition()->getY() + 2, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 2, $player->getPosition()->getZ() - 1), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX() + 1, $player->getPosition()->getY() - 0, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() - 0, $player->getPosition()->getZ() + 1), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX() - 1, $player->getPosition()->getY() - 0, $player->getPosition()->getZ()), VanillaBlocks::AIR(), false, true);
            $player->getWorld()->setBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() - 0, $player->getPosition()->getZ() - 1), VanillaBlocks::AIR(), false, true);
        } else {
            $config = SkyWars::getConfigs('Cages/' . $value);
            $array = $config->get($value, []);
            if ($array != null) {
                $blocks = $array['BLOCKS'];
                foreach ($blocks as $data => $block) {
                    if ($block['X'] == 0 && $block['Y'] == 1 && $block['Z'] == 0) continue;
                    if ($block['X'] == 0 && $block['Y'] == 2 && $block['Z'] == 0) continue;
                    $player->getWorld()->setBlock($player->getPosition()->add(intval($block['X']), intval($block['Y'])-1, intval($block['Z'])), VanillaBlocks::AIR(), false, false);
                }
            }
        }
    }

    public static function addStrike(array $players, Player $deathPlayer): void {
        $packet = new AddActorPacket();
        $packet->actorRuntimeId = Entity::nextRuntimeId();
        $packet->actorUniqueId = $packet->actorRuntimeId;
        $packet->type = EntityIds::LIGHTNING_BOLT;
        $packet->position = $deathPlayer->getPosition()->asVector3();
        $packet->yaw = $deathPlayer->getLocation()->getYaw();
        $packet->pitch = $deathPlayer->getLocation()->getPitch();
        $packet->headYaw = $deathPlayer->getLocation()->getYaw();
        $packet->metadata = [];
        $packet->syncedProperties = new PropertySyncData([], []);

        foreach ($players as $player) {
            $player->getNetworkSession()->sendDataPacket($packet);
            self::playSound($player, 'ambient.weather.lightning.impact', 1, 1);
        }
    }

    public static function setVote(Player $player, string $arena, string $value) {
        switch ($value) {
            case 'op':
                if (isset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()])) {
                    unset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()]);
                    $player->sendMessage(Color::RED . 'Your vote has been smashed.');
                } else {
                    if (!isset(SkyWars::$data['vote'][$arena]['op'][$player->getName()])) {
                        SkyWars::$data['vote'][$arena]['op'][$player->getName()] = $player->getName();
                        foreach (Server::getInstance()->getWorldManager()->getWorldByName($arena)->getPlayers() as $players) {
                            $players->sendMessage(Color::GOLD . $player->getName() . ' You voted for chests OP.');
                            $player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelSoundEventPacket::create(LevelSoundEvent::ENDERCHEST_OPEN, new Vector3($player->getPosition()->getFloorX(), $player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ()), 0, ":", false, false));
                        }
                    } else {
                        unset(SkyWars::$data['vote'][$arena]['op'][$player->getName()]);
                        $player->sendMessage(Color::RED . 'Your vote has been smashed.');
                    }
                }
            break;
            case 'normal':
                if (isset(SkyWars::$data['vote'][$arena]['op'][$player->getName()])) {
                    unset(SkyWars::$data['vote'][$arena]['op'][$player->getName()]);
                    $player->sendMessage(Color::RED . 'Your vote has been smashed.');
                } else {
                    if (!isset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()])) {
                        SkyWars::$data['vote'][$arena]['normal'][$player->getName()] = $player->getName();
                        foreach (Server::getInstance()->getWorldManager()->getWorldByName($arena)->getPlayers() as $players) {
                            $players->sendMessage(Color::GOLD . $player->getName() . ' You voted for chests Basic.');
                            $player->getWorld()->broadcastPacketToViewers($player->getPosition(), LevelSoundEventPacket::create(LevelSoundEvent::ENDERCHEST_OPEN, new Vector3($player->getPosition()->getFloorX(), $player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ()), 0, ":", false, false));
                        }
                    } else {
                        unset(SkyWars::$data['vote'][$arena]['normal'][$player->getName()]);
                        $player->sendMessage(Color::RED . 'Your vote has been smashed.');
                    }
                }
            break;
        }
    }

    public static function getEventDamage(Player $player, string $arena, string $cause, Player $damager = null) {
        $config = SkyWars::getConfigs('Arenas/' . $arena);
        $lobby = $config->get('lobbyspecters');
        if (count(Arena::getPlayers($arena)) != 1) {
            if (!$player->getGamemode()->equals(GameMode::SPECTATOR())) {
                if ($damager != false) {
                    SkyWars::$data['kills'][Arena::getName($arena)][$damager->getName()] = SkyWars::$data['kills'][Arena::getName($arena)][$damager->getName()] + 1;
                    $kills = SkyWars::getConfigs('kills');
                    $kills->set($damager->getName(), $kills->get($damager->getName()) + 1);
                    $kills->save();
                    foreach ($damager->getWorld()->getPlayers() as $players) {
                        $players->sendMessage(Color::RED . $player->getName() . Color::GRAY . ' ' . $cause . ' ' . Color::GOLD . $damager->getName() . '.');
                        $remain = (count(Arena::getPlayers($arena)) - 1);
                        if ($remain > 1) {
                            $players->sendMessage(Color::RED . $remain . ' players remain alive.');
                        }
                    }
                    $getcoins = SkyWars::getConfigs('Profiles/' . $player->getName());
                    if ($getcoins->get('coins') >= 0) {
                        $getcoins->set('coins', $getcoins->get('coins') - 1);
                        $getcoins->save();
                        $player->sendMessage(Color::LIGHT_PURPLE . '-1 Coins.');
                    }
                    $coins = SkyWars::getConfigs('Profiles/' . $damager->getName());
                    $coins->set('coins', $config->get('coins') + 2);
                    $coins->save();
                    $damager->sendMessage(Color::LIGHT_PURPLE . '+2 Coins.');
                } else {
                    foreach ($player->getWorld()->getPlayers() as $players)  {
                        $players->sendMessage(Color::RED . $player->getName() . Color::GRAY . ' ' . $cause . '.');
                        $remain = (count(Arena::getPlayers($arena)) - 1);
                        if ($remain > 1) {
                            $players->sendMessage(Color::RED . $remain . ' players remain alive.');
                        }
                    }
                }
                foreach ($player->getDrops() as $drops) {
                    $player->getWorld()->dropItem(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), $drops);
                }
                self::addStrike(Server::getInstance()->getWorldManager()->getWorldByName($player->getWorld()->getFolderName())->getPlayers(), $player);
                $player->getEffects()->clear();
                $player->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 20, 3, false));
                switch (rand(1, 2)) {
                    case 1:
                        $player->sendTitle(Color::BOLD . Color::RED . '¡You died!', Color::YELLOW . 'You lost the game');
                    break;
                    case 2:
                        $player->sendTitle(Color::BOLD . Color::RED . '¡You died!', Color::YELLOW . 'Good luck next time');
                    break;
                }
                $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                $player->setGamemode(GameMode::spectator());
                $player->setHealth(20);
                $player->getHungerManager()->setFood(20);
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->getInventory()->setItem(0, VanillaItems::ENDER_PEARL()->setCount(1)->setCustomName(Color::GOLD . "Players Reaming\n§r§fClick to select"));
                $player->getInventory()->setItem(4, VanillaBlocks::END_PORTAL_FRAME()->asItem()->setCount(1)->setCustomName(Color::LIGHT_PURPLE . "Random Game\n§r§fClick to select"));
                $player->getInventory()->setItem(8, VanillaBlocks::BED()->asItem()->setCount(1)->setCustomName(Color::RED . "Leave\n§r§fClick to select"));
            } else {
                $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
            }
        } else {
            $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
        }
    }
                   
    public static function joinSolo(Player $player, int $id) {
        if ($player instanceof Player) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName('SW-' . $id));
            if (Arena::getStatus('SW-' . $id) == 'waiting') {
                if (count(Arena::getPlayers('SW-' . $id)) < Arena::getSpawns('SW-' . $id)) {
                    SkyWars::$data['skins'][$player->getName()] = $player->getSkin();
                    SkyWars::$data['damager'][$player->getName()] = 'string';
                    SkyWars::$data['kills'][Arena::getName('SW-' . $id)][$player->getName()] = 0;
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->sendMessage(Color::GREEN . Color::BOLD . '» ' . Color::RESET . Color::GREEN . 'An available game has been found: ' . 'SW-' . $id);
                    $config = SkyWars::getConfigs('Arenas/' . 'SW-' . $id);
                    $lobby = $config->get('lobby');
                    $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName('SW-' . $id))->getSpawnLocation());
                    $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                    $player->setAllowFlight(false);
                    $player->setFlying(false);    
                    $player->getEffects()->clear();
                    $player->setGamemode(GameMode::adventure());
                    $player->setHealth(20);
                    $player->getHungerManager()->setFood(20);
                    $player->setScale(1);
                    $player->getInventory()->setItem(0, VanillaItems::BOOK()->setCount(1)->setCustomName(Color::LIGHT_PURPLE . "Kits\n§r§fClick to select"));
                    $player->getInventory()->setItem(3, VanillaItems::COMPASS()->setCount(1)->setCustomName(Color::GREEN . "Start\n§r§fClick to select"));
                    $player->getInventory()->setItem(4, VanillaItems::TOTEM()->setCount(1)->setCustomName(Color::AQUA . "Settings\n§r§fClick to select"));
                    $player->getInventory()->setItem(5, VanillaBlocks::CHEST()->asItem()->setCount(1)->setCustomName(Color::GOLD . "Vote Chest\n§r§fClick to select"));
                    $player->getInventory()->setItem(8, VanillaBlocks::BED()->asItem()->setCount(1)->setCustomName(Color::RED . "Leave\n§r§fClick to select"));
                    foreach ($world->getPlayers() as $players) {
                        $players_array[] = $players->getName();
                    }
                    $player->sendMessage(Color::GOLD . join(Color::GOLD  . ', ' . Color::GOLD, $players_array) . Color::GOLD . '.');
                    $player->getWorld()->addSound($player->getPosition(), new EndermanTeleportSound($player));
                    foreach ($world->getPlayers() as $players) {
                        $players->sendMessage(Color::GREEN . Color::BOLD . '» ' . Color::RESET . Color::DARK_GRAY . $player->getName() . ' ' . 'Joined the game.' . ' ' . Color::DARK_GRAY . '[' . Color::DARK_GRAY . count($world->getPlayers()) . Color::DARK_GRAY . '/' . Color::DARK_GRAY . Arena::getSpawns('SW-' . $id) . Color::DARK_GRAY . ']');
                        $players->getWorld()->addSound($players->getPosition(), new EndermanTeleportSound($players));
                    }
                }
            }
        }
    }

    public static function viewHealth(Player $player): string {
        $health = (int)round($player->getHealth() / 2);
        $greenHearts = str_repeat(Color::GREEN . '❤', $health);
        $grayHearts = str_repeat(Color::GRAY . '❤', 10 - $health);
        return $greenHearts . $grayHearts;
    }    

    public static function setZip(string $arena) {
		$level = Server::getInstance()->getWorldManager()->getWorldByName($arena);
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
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        $inventory->clearAll();
        $armorInventory->clearAll();
        /*$colorBlack = new Color(0, 0, 0);
        $colorRed = new Color(255, 0, 0);
        $colorGreen = new Color(0, 255, 0);*/
    
        switch ($value) {
            case 'rusher':
                $dp = VanillaItems::IRON_SWORD()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 1));
                $inventory->addItem($dp);
                $inventory->addItem(VanillaItems::COOKED_PORKCHOP()->setCount(8));
                $inventory->addItem(VanillaBlocks::STONE()->asItem()->setCount(64));
                break;
    
            case 'baseball':
                $dp = VanillaItems::STICK()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 3));
                $inventory->addItem($dp);
                break;
    
            case 'ghost':
                $inventory->addItem(VanillaItems::POTION()->setCount(1)->setMeta(7));
                $inventory->addItem(VanillaItems::WOODEN_SWORD()->setCount(1));
                break;
    
            case 'healer':
                $inventory->addItem(VanillaItems::SPLASH_POTION()->setCount(3)->setMeta(21));
                $inventory->addItem(VanillaItems::SPLASH_POTION()->setCount(2)->setMeta(29));
                $inventory->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
                break;
    
            case 'archer':
                $dp = VanillaItems::BOW()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 2));
                $inventory->addItem($dp);
                $inventory->addItem(VanillaItems::ARROW()->setCount(20));
                break;
    
            case 'piromo':
                $dp = VanillaItems::STONE_SWORD()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 1));
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 1));
                $inventory->addItem($dp);
                break;
    
            case 'asesino':
                $dp = VanillaItems::IRON_SWORD()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1));
                $inventory->addItem($dp);
                $chestplate = VanillaItems::LEATHER_CHESTPLATE();
                /*$chestplate->setCustomColor($colorBlack);*/
                $armorInventory->setChestplate($chestplate);
                break;
    
            case 'lumberhack':
                $dp = VanillaItems::IRON_AXE()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2));
                $inventory->addItem($dp);
                $inventory->addItem(VanillaBlocks::OAK_LOG()->asItem()->setCount(15));
                break;
    
            case 'enderman':
                $inventory->addItem(VanillaItems::ENDER_PEARL()->setCount(2));
                $chestplate = VanillaItems::LEATHER_CHESTPLATE();
                $leggings = VanillaItems::LEATHER_LEGGINGS();
                $boots = VanillaItems::LEATHER_BOOTS();
                /*$chestplate->setCustomColor($colorBlack);
                $leggings->setCustomColor($colorBlack);
                $boots->setCustomColor($colorBlack);*/
                $armorInventory->setChestplate($chestplate);
                $armorInventory->setLeggings($leggings);
                $armorInventory->setBoots($boots);
                break;
    
            case 'rusheryt':
                $dp = VanillaItems::STONE_SWORD()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 1));
                $inventory->addItem($dp);
                $inventory->addItem(VanillaBlocks::OAK_PLANKS()->asItem()->setCount(20));
                break;
    
            case 'boomberman':
                $inventory->addItem(VanillaItems::FLINT_AND_STEEL()->setCount(1));
                $inventory->addItem(VanillaItems::TNT()->setCount(3));
                $inventory->addItem(VanillaItems::WOODEN_SWORD()->setCount(1));
                $chestplate = VanillaItems::LEATHER_CHESTPLATE();
                /*$chestplate->setCustomColor($colorRed);*/
                $armorInventory->setChestplate($chestplate);
                break;
    
            case 'builder':
                $inventory->addItem(VanillaBlocks::BRICKS()->asItem()->setCount(20));
                break;
    
            case 'tools':
                $dp = VanillaItems::WOODEN_SWORD()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1));
                $inventory->addItem($dp);
                $dp = VanillaItems::WOODEN_PICKAXE()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1));
                $inventory->addItem($dp);
                $dp = VanillaItems::WOODEN_AXE()->setCount(1);
                $dp->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1));
                $inventory->addItem($dp);
                break;
    
            case 'saltamontes':
                $chestplate = VanillaItems::LEATHER_CHESTPLATE();
                $leggings = VanillaItems::LEATHER_LEGGINGS();
                $boots = VanillaItems::LEATHER_BOOTS();
                $chestplate->setCustomColor($colorGreen);
                $leggings->setCustomColor($colorGreen);
                $boots->setCustomColor($colorGreen);
                $armorInventory->setChestplate($chestplate);
                $armorInventory->setLeggings($leggings);
                $armorInventory->setBoots($boots);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 9999, 1));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 9999, 0));
                break;
        }
    }

    public static function chestOP(string $arena) {
        $level = Server::getInstance()->getWorldManager()->getWorldByName($arena);
    
        if (!$level instanceof World) {
            Server::getInstance()->getLogger()->error("The world '$arena' could not be found.");
            return;
        }
    
        foreach ($level->getLoadedChunks() as $chunk) {
            foreach ($chunk->getTiles() as $tile) {
                if ($tile instanceof Chest) {
                    $tile->getInventory()->clearAll();
                    if ($tile->getInventory() instanceof ChestInventory) {
                        $usedSlots = [];
                        for ($i = 0; $i <= 26; $i++) {
                            $random = rand(1, 3);
                            if ($random == 1) {
                                $item = self::getItemChest();
                                if (!in_array($i, $usedSlots)) {
                                    if ($item->equals(VanillaItems::DIAMOND_SWORD()) ||
                                        $item->equals(VanillaItems::DIAMOND_AXE()) ||
                                        $item->equals(VanillaItems::DIAMOND_PICKAXE()) ||
                                        $item->equals(VanillaItems::IRON_SWORD()) ||
                                        $item->equals(VanillaItems::IRON_PICKAXE()) ||
                                        $item->equals(VanillaItems::IRON_AXE()) ||
                                        $item->equals(VanillaItems::WOODEN_SWORD()) ||
                                        $item->equals(VanillaItems::STONE_SWORD()) ||
                                        $item->equals(VanillaItems::GOLDEN_SWORD()) ||
                                        $item->equals(VanillaItems::WOODEN_AXE()) ||
                                        $item->equals(VanillaItems::STONE_AXE()) ||
                                        $item->equals(VanillaItems::GOLDEN_AXE())) {
                                        $enchantments = [VanillaEnchantments::SHARPNESS(), VanillaEnchantments::UNBREAKING(), VanillaEnchantments::FIRE_ASPECT()];
                                        shuffle($enchantments);
                                        $item->addEnchantment(new EnchantmentInstance($enchantments[0], mt_rand(1, 5)));
                                    } else if ($item->equals(VanillaItems::BOW())) {
                                        $enchantments = [VanillaEnchantments::POWER(), VanillaEnchantments::UNBREAKING(), VanillaEnchantments::FLAME()];
                                        shuffle($enchantments);
                                        $item->addEnchantment(new EnchantmentInstance($enchantments[0], mt_rand(1, 5)));
                                    } else if ($item->equals(VanillaItems::DIAMOND_HELMET()) ||
                                        $item->equals(VanillaItems::IRON_HELMET()) ||
                                        $item->equals(VanillaItems::GOLDEN_HELMET()) ||
                                        $item->equals(VanillaItems::LEATHER_CAP()) ||
                                        $item->equals(VanillaItems::DIAMOND_CHESTPLATE()) ||
                                        $item->equals(VanillaItems::LEATHER_TUNIC()) ||
                                        $item->equals(VanillaItems::IRON_CHESTPLATE()) ||
                                        $item->equals(VanillaItems::GOLDEN_CHESTPLATE()) ||
                                        $item->equals(VanillaItems::DIAMOND_LEGGINGS()) ||
                                        $item->equals(VanillaItems::LEATHER_PANTS()) ||
                                        $item->equals(VanillaItems::IRON_LEGGINGS()) ||
                                        $item->equals(VanillaItems::GOLDEN_LEGGINGS()) ||
                                        $item->equals(VanillaItems::DIAMOND_BOOTS()) ||
                                        $item->equals(VanillaItems::LEATHER_BOOTS()) ||
                                        $item->equals(VanillaItems::IRON_BOOTS())) {
                                        $enchantments = [VanillaEnchantments::PROTECTION(), VanillaEnchantments::FIRE_PROTECTION(), VanillaEnchantments::UNBREAKING()];
                                        shuffle($enchantments);
                                        $item->addEnchantment(new EnchantmentInstance($enchantments[0], mt_rand(1, 5)));
                                    }
                                    $tile->getInventory()->setItem($i, $item);
                                    $usedSlots[] = $i;
                                }
                            }
                        }
                    }
                }
            }
        }
    }    

    public static function chestDefault(string $arena) {
        $level = Server::getInstance()->getWorldManager()->getWorldByName($arena);
        
        if (!$level instanceof World) {
            Server::getInstance()->getLogger()->error("The world '$arena' could not be found.");
            return;
        }
    
        foreach ($level->getLoadedChunks() as $chunk) {
            foreach ($chunk->getTiles() as $tile) {
                if ($tile instanceof Chest) {
                    $tile->getInventory()->clearAll();
                    if ($tile->getInventory() instanceof ChestInventory) {
                        $usedSlots = [];
                        for ($i = 0; $i <= 26; $i++) {
                            $random = rand(1, 3);
                            if ($random == 1) {
                                $item = self::getItemChest();
                                if (!in_array($i, $usedSlots)) {
                                    $tile->getInventory()->setItem($i, $item);
                                    $usedSlots[] = $i;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private static function getItemChest() {
        $blocks = [
            VanillaBlocks::DIORITE()->asItem()->setCount(15),
            VanillaBlocks::DOUBLE_PITCHER_CROP()->asItem()->setCount(1),
            VanillaItems::IRON_SWORD()->setCount(1),
            VanillaItems::DIAMOND_SWORD()->setCount(1),
            VanillaItems::DIAMOND_AXE()->setCount(1),
            VanillaItems::DIAMOND_PICKAXE()->setCount(1),
            VanillaItems::IRON_PICKAXE()->setCount(1),
            VanillaItems::GOLDEN_APPLE()->setCount(2),
            VanillaItems::GOLDEN_APPLE()->setCount(4),
            VanillaItems::ENDER_PEARL()->setCount(1),
            VanillaItems::COOKED_SALMON()->setCount(3),
            VanillaItems::COOKED_FISH()->setCount(7),
            VanillaBlocks::STONE()->asItem()->setCount(32),
            VanillaBlocks::OAK_PLANKS()->asItem()->setCount(32),
            VanillaItems::BOW()->setCount(1),
            VanillaItems::ARROW()->setCount(15),
            VanillaItems::ARROW()->setCount(10),
            VanillaItems::SNOWBALL()->setCount(7),
            VanillaItems::SNOWBALL()->setCount(14),
            VanillaItems::COMPASS()->setCount(1),
            VanillaBlocks::STONE()->asItem()->setCount(32),
            VanillaBlocks::OAK_PLANKS()->asItem()->setCount(32),
            VanillaBlocks::TNT()->asItem()->setCount(3),
            VanillaItems::WATER_BUCKET()->setCount(1),
            VanillaItems::WATER_BUCKET()->setCount(1),
            VanillaItems::LAVA_BUCKET()->setCount(1),
            VanillaItems::LAVA_BUCKET()->setCount(1),
            VanillaItems::WOODEN_SWORD()->setCount(1),
            VanillaItems::STONE_SWORD()->setCount(1),
            VanillaItems::GOLDEN_SWORD()->setCount(1),
            VanillaItems::WOODEN_AXE()->setCount(1),
            VanillaItems::STONE_AXE()->setCount(1),
            VanillaItems::GOLDEN_AXE()->setCount(1),
            VanillaItems::COOKED_PORKCHOP()->setCount(3),
            VanillaItems::COOKED_CHICKEN()->setCount(3),
            VanillaItems::BREAD()->setCount(3),
            VanillaItems::APPLE()->setCount(3),
            VanillaItems::STICK()->setCount(5),
            VanillaItems::GOLDEN_HELMET()->setCount(1),
            VanillaItems::GOLDEN_CHESTPLATE()->setCount(1),
            VanillaItems::GOLDEN_LEGGINGS()->setCount(1),
            VanillaItems::GOLDEN_BOOTS()->setCount(1),
            VanillaItems::DIAMOND_HELMET()->setCount(1),
            VanillaItems::DIAMOND_CHESTPLATE()->setCount(1),
            VanillaItems::DIAMOND_LEGGINGS()->setCount(1),
            VanillaItems::DIAMOND_BOOTS()->setCount(1),
            VanillaItems::LEATHER_CAP()->setCount(1),
            VanillaItems::LEATHER_TUNIC()->setCount(1),
            VanillaItems::LEATHER_PANTS()->setCount(1),
            VanillaItems::LEATHER_BOOTS()->setCount(1)
        ];
    
        return $blocks[array_rand($blocks)];
    }
    
}