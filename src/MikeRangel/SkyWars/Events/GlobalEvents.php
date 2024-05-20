<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Events;
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Form\FormManager, Tasks\ArenaID, Arena\Arena, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, player\Player, player\GameMode, event\Listener, math\Vector3, item\Item, utils\TextFormat as Color};
use pocketmine\event\{entity\EntityItemPickupEvent, player\PlayerJoinEvent, player\PlayerChatEvent, player\PlayerQuitEvent, player\PlayerDropItemEvent, player\PlayerMoveEvent, player\PlayerItemHeldEvent, player\PlayerInteractEvent, player\PlayerExhaustEvent, block\BlockBreakEvent, block\BlockPlaceEvent, entity\EntityTeleportEvent, entity\EntityDamageEvent, entity\EntityDamageByChildEntityEvent, entity\EntityDamageByEntityEvent};
use pocketmine\level\sound\{BlazeShootSound};

class GlobalEvents implements Listener {

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $args = explode(' ',$event->getMessage());
        if (in_array($player->getName(), SkyWars::$data['configurator'])) {
            $event->cancel();
            switch ($args[0]) {
                case 'help':
                    default:
                    $date = [
                        'help: Help commands.',
                        'setlobby: Register the lobby.',
                        'setlobbysp: Register the lobby specters.',
                        'setlobbywin: Register the lobby win.',
                        'setspawn <slot>: Set spawns.',
                        'setvoid: Set minimun void.',
                        'done: Enable arena.'
                    ];
                    $player->sendMessage(Color::GOLD . 'SkyWars Configuration Commands:');
                    foreach ($date as $help) {
                        $player->sendMessage(Color::GRAY . $help);
                    }
                break;
                case 'setlobby':
                    Arena::setLobbyWaiting($player);
                break;
                case 'setlobbysp':
                    Arena::setLobbySpecters($player);
                break;
                case 'setlobbywin':
                    Arena::setLobbyWin($player);
                break;
                case 'setspawn':
                    if (!empty($args[1])) {
                        Arena::setSpawns($player, $args[1]);
                    } else {
                        $player->sendMessage(Color::RED . 'Usage: setspawn <slot>');
                    }
                break;
                case 'done':
                    Arena::setStatus('SW-' . SkyWars::$data['id'], 'waiting');
                    SkyWars::$data['id'] = '';
                    $index = array_search($player->getName(), SkyWars::$data['configurator']);
		            if  ($index != -1)  {
			        unset(SkyWars::$data['configurator'][$index]);
                    }
                    $player->sendMessage(Color::GREEN . 'Installation mode has been completed, arena created.');
                    $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                    $player->setGamemode(GameMode::adventure());
                break;
            }
        }
    }

    /*public function Commands(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $cmd = explode(' ', strtolower($event->getMessage()));
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() == 0 || $player->getGamemode() == 2 || $player->getGamemode() == 3) {
                    if ($cmd[0] === '/gamemode') {
                        $event->cancel();
                    } else if ($cmd[0] === '/gm') {
                        $event->cancel();
                    } else if ($cmd[0] === '/fly') {
                        $event->cancel();
                    } else if ($cmd[0] === '/tp') {
                        $event->cancel();
                    } else if ($cmd[0] === '/kick') {
                        $event->cancel();
                    } else if ($cmd[0] === '/stop') {
                        $event->cancel();
                    } else if ($cmd[0] === '/kill') {
                        $event->cancel();
                    } else if ($cmd[0] === '/give') {
                        $event->cancel();
                    } else if ($cmd[0] === '/hub') {
                        $event->cancel();
                        $player->sendMessage(Color::RED . 'Please enter /sw leave to exit the game.');
                    } else if ($cmd[0] === '/lobby') {
                        $event->cancel();
                        $player->sendMessage(Color::RED . 'Please enter /sw leave to exit the game.');
                    } else if ($cmd[0] === '/spawn') {
                        $event->cancel();
                        $player->sendMessage(Color::RED . 'Please enter /sw leave to exit the game.');
                    }
                }
            }
        }
    }*/
                   
    public function setFunctions(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $id = $event->getItem()->getTypeId();
        $damage = $event->getItem()->getMeta();
        $name = $event->getItem()->getCustomName();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() == 0 || $player->getGamemode() == 2 || $player->getGamemode() == 3) {
                    if ($id == 340 && $name == Color::LIGHT_PURPLE . "Kits\n§r§fClick to select") {
                        FormManager::getKits($player);
                    } else if ($id == 345 && $name == Color::GREEN . "Start\n§r§fClick to select") {
                        if ($player->hasPermission('skywars.start.perm')) {
                            if (count(Server::getInstance()->getWorldManager()->getWorldByName($player->getWorld()->getFolderName())->getPlayers()) < 2) {
                                $player->sendMessage(Color::RED . 'More players are needed to access this feature.');
                            } else {
                                if (Arena::getTimeWaiting($arena) >= 0 && Arena::getTimeWaiting($arena) <= 5) {
                                    $player->sendMessage(Color::RED . '¡Opps! It seems that the game is about to begin.');
                                    $player->getWorld()->addSound(new BlazeShootSound($player));
                                } else {
                                    Arena::setTimeWaiting($arena, 5);
                                    foreach ($player->getWorld()->getPlayers() as $players) {
                                        $players->sendMessage(Color::GOLD . $player->getName() . ' You have decided to start in 5 seconds.');
                                    }
                                }
                            }
                        } else {
                            $player->sendMessage(Color::RED . 'Adquire a range to access this function.');
                            $player->getWorld()->addSound(new BlazeShootSound($player));
                        }
                    } else if ($id == 54 && $name == Color::GOLD . "Vote Chest\n§r§fClick to select") {
                        FormManager::getVotesUI($player);
                    } else if ($id == 355 && $damage == 14 && $name == Color::RED . "Leave\n§r§fClick to select") {
                        $index = array_search($player->getName(), SkyWars::$data['queue']);
		                if ($index != -1) {
                            unset(SkyWars::$data['queue'][$index]);
                        }
                        Server::getInstance()->dispatchCommand($player, 'sw leave');
                    }
                }
            }
        }
    }

    public function onHeld(PlayerItemHeldEvent $event) {
    	$player = $event->getPlayer();
        $item = $event->getItem()->getCustomName();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() == 3) {
                    if ($item == Color::GOLD . "Players Reaming\n§r§fClick to select") {
                        $index = array_search($player->getName(), SkyWars::$data['queue']);
		                if ($index != -1) {
                            if (in_array($player->getName(), SkyWars::$data['queue'])) {
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::YELLOW . 'The browser for a new game has been canceled.');
                            }
                            unset(SkyWars::$data['queue'][$index]);
                        }
                        FormManager::getAlives($player);
                    } else if ($item == Color::LIGHT_PURPLE . "Random Game\n§r§fClick to select") {
                        if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                            SkyWars::$data['queue'][] = $player->getName();
                            SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
                        }
                    }
                } else {
                    if ($item == Color::GOLD . "Emotes\n§r§fClick to select") {
                        FormManager::myEmotes($player);
                    }
                }
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        if (!file_exists(SkyWars::getInstance()->getDataFolder() . 'Profiles/' . $player->getName() . '.yml')) {
            $config = SkyWars::getConfigs('Profiles/' . $player->getName());
            $config->set('user', $player->getName());
            $config->set('cage', 'Default');
            $config->set('kit', 'null');
            $config->set('coins', 0);
            $config->save();
            $buycages = SkyWars::getConfigs('buycages');
            $buycages->set($player->getName(), []);
            $buycages->save();
            $buyemotes = SkyWars::getConfigs('buyemotes');
            $buyemotes->set($player->getName(), []);
            $buyemotes->save();
        }
    }

    public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        $position = $player->getPosition();
        foreach (Arena::getArenas() as $arena) {   
            $config = SkyWars::getConfigs('Arenas/' . $arena);
            $lobby = $config->get('lobby');
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                    if ($position->getY() < 3) {
                        $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                    }
                }
            }
        }
    }

    public function onProtect(EntityDamageEvent $event) {
        $player = $event->getEntity();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if ($event instanceof EntityDamageEvent && ($event->getEntity() instanceof Player)) {
                    if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                        $event->cancel();
                    } else {
                        if ($event instanceof EntityDamageByEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                SkyWars::$data['damager'][$player->getName()] = $damager->getName();
                            }
                        }
                        if ($event instanceof EntityDamageByChildEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                SkyWars::$data['damager'][$player->getName()] = $damager->getName();
                            }
                        }
                        if (Arena::getTimeGame($arena) >= 589 && Arena::getTimeGame($arena) <= 600) {
                            $event->cancel();
                        }
                        if ($player->getGamemode() == 3) {
                            $event->cancel();
                        }
                    }
                }
            }
        }
    }

    public function onInventory(EntityItemPickupEvent $event) {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) {
            return;
        }
        
        $player = $entity;
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() === Arena::getName($arena)) {
                if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                    $event->cancel();
                } else {
                    if ($player->getGamemode()->getId() === Player::SPECTATOR) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                    $event->cancel();
                } else {
                    if ($player->getGamemode() == 3) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onBlock(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                    $event->cancel();
                } else {
                    if ($block->getID() == 56) {
                        $items = [310, 311, 312, 313, 276];
                        Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($items[array_rand($items)], 0, 1));
                    } else if ($block->getID() == 14) {
                        $items = [314, 315, 316, 317, 286];
                        Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($items[array_rand($items)], 0, 1));
                    } else if ($block->getID() == 15){
                        $items = [306, 307, 308, 309, 257];
                        Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($items[array_rand($items)], 0, 1));
                    }
                    $event->setCancelled(false);
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                    $event->cancel();
                } else {
                    $event->setCancelled(false);
                }
            }
        }
    }

    public function onAction(EntityDamageByEntityEvent $event) {
        if ($event->getEntity() instanceof EntityHuman) {
            $player = $event->getDamager();
            if ($player instanceof Player) {
                $event->cancel();
                FormManager::getGameUI($player);
            }
        }
    }

    public function onActionStats(EntityDamageByEntityEvent $event) {
        if ($event->getEntity() instanceof EntityStats) {
            $player = $event->getDamager();
            if ($player instanceof Player) {
                $event->cancel();
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (Arena::getTimeWaiting($arena) >= 0 && Arena::getTimeWaiting($arena) <= 10) {
                    PluginUtils::unsetCage($player, $config->get('cage'));
                }
                if ($player->getGamemode() == 0 || $player->getGamemode() == 2) {
                    foreach ($player->getWorld()->getPlayers() as $players) {
                        if (in_array(Arena::getStatus($arena), ['waiting', 'ingame'])) {
                            $remain = (count(Arena::getPlayers($arena)) - 1);
                            $players->sendMessage(Color::GREEN . Color::BOLD . '» ' . Color::RESET . Color::RED . $player->getName() . ' ' . 'Left the game.' . ' ' . Color::RED . '[' . Color::RED . $remain . Color::RED . '/' . Color::RED . Arena::getSpawns($arena) . Color::RED . ']');
                            if (Arena::getStatus($arena) == 'ingame') {
                                if ($remain > 1) {
                                    $players->sendMessage(Color::RED . $remain . ' players remain alive.');
                                }
                            }
                        }
                    }
                }
                $index = array_search($player->getName(), SkyWars::$data['queue']);
		        if ($index != -1) {
			        unset(SkyWars::$data['queue'][$index]);
                }
                $api = SkyWars::getScore();
                $api->remove($player);
                $player->getArmorInventory()->clearAll();
                $player->setMovementSpeed(0);
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->getEffects()->clear();
                $player->setGamemode(GameMode::adventure());
                $player->setHealth(20);
                $player->getHungerManager()->setFood(20);
                $world = Server::getInstance()->getWorldManager()->getWorldByName(Arena::getName($arena));
            }
        }
    }

    public function onChange(EntityTeleportEvent $event) {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) {
            return;
        }
        $player = $entity;
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($from->getWorld()->getFolderName() !== $to->getWorld()->getFolderName()) {
            $config = SkyWars::getConfigs('Profiles/' . $player->getName());
            foreach (Arena::getArenas() as $arena) {
                if ($to->getWorld()->getFolderName() === Arena::getName($arena)) {
                    if (Arena::getTimeWaiting($arena) >= 0 && Arena::getTimeWaiting($arena) <= 10) {
                        PluginUtils::unsetCage($player, $config->get('cage'));
                    }
                    if ($player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE()) {
                        foreach ($to->getWorld()->getPlayers() as $players) {
                            if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'ingame'])) {
                                $remain = (count(Arena::getPlayers($arena)) - 1);
                                $players->sendMessage(Color::GREEN . Color::BOLD . '» ' . Color::RESET . Color::RED . $player->getName() . ' Left the game. ' . Color::RED . '[' . $remain . '/' . Arena::getSpawns($arena) . ']');
                                if (Arena::getStatus($arena) === 'ingame' && $remain > 1) {
                                    $players->sendMessage(Color::RED . $remain . ' players remain alive.');
                                }
                            }
                        }
                    }
                    $index = array_search($player->getName(), SkyWars::$data['queue']);
                    if ($index !== false) {
                        unset(SkyWars::$data['queue'][$index]);
                    }
                    $api = SkyWars::getScore();
                    $api->remove($player);
                    $player->getArmorInventory()->clearAll();
                    $player->setMovementSpeed(0);
                    $player->setAllowFlight(false);
                    $player->setFlying(false);
                    $player->getEffects()->clear();
                    $player->setGamemode(GameMode::adventure());
                    $player->setHealth(20);
                    $player->getHungerManager()->setFood(20);
                }
            }
        }
    }

    public function onHunger(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            foreach (Arena::getArenas() as $arena) {
                if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                    if (in_array(Arena::getStatus($arena), ['waiting', 'starting', 'end'])) {
                        $event->cancel();
                    } else {
                        if ($player->getGamemode() == 3) {
                            $event->cancel();
                        }
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        $player = $event->getEntity();
        if (!$player instanceof Player) return;
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                if (Arena::getStatus($arena) == 'ingame') {
                    switch ($event->getCause()) {
                        case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                            if ($event instanceof EntityDamageByEntityEvent) {
                                $damager = $event->getDamager();
                                if ($damager instanceof Player) {
                                    if ($event->getFinalDamage() >= $player->getHealth()) {
                                        $event->cancel();
                                        PluginUtils::getEventDamage($player, $arena, 'He has been killed by', $damager);
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_PROJECTILE:
                            if ($event instanceof EntityDamageByEntityEvent) {
                                $damager = $event->getDamager();
                                if ($damager instanceof Player) {
                                    if ($event->getFinalDamage() >= $player->getHealth()) {
                                        $event->cancel();
                                        PluginUtils::getEventDamage($player, $arena, 'He has been killed with arrows by', $damager);
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_FIRE:
                        case EntityDamageEvent::CAUSE_FIRE_TICK:
                        case EntityDamageEvent::CAUSE_LAVA:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                $damage = null;
                                foreach ($player->getWorld()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                    if ($damage != null) {
                                        $damager = Server::getInstance()->getPlayer($damage);
                                        PluginUtils::getEventDamage($player, $arena, 'Has died burned by', $damager);
                                    } else {
                                        PluginUtils::getEventDamage($player, $arena, 'Has died burned');
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                        case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                $damage = null;
                                foreach ($player->getWorld()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                    if ($damage != null) {
                                        $damager = Server::getInstance()->getPlayer($damage);
                                        PluginUtils::getEventDamage($player, $arena, 'It has exploded into a thousand pieces by', $damager);
                                    } else {
                                        PluginUtils::getEventDamage($player, $arena, 'It has exploded into a thousand pieces');
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_FALL:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                $damage = null;
                                foreach ($player->getWorld()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                    if ($damage != null) {
                                        $damager = Server::getInstance()->getPlayer($damage);
                                        PluginUtils::getEventDamage($player, $arena, 'He died from a strong blow to the floor by', $damager);
                                    } else {
                                        PluginUtils::getEventDamage($player, $arena, 'He died from a strong blow to the floor');
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_VOID:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                $damage = null;
                                foreach ($player->getWorld()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                    if ($damage != null) {
                                        $damager = Server::getInstance()->getPlayer($damage);
                                        PluginUtils::getEventDamage($player, $arena, 'Has fallen into the void by', $damager);
                                    } else {
                                        PluginUtils::getEventDamage($player, $arena, 'Has fallen into the void');
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_MAGIC:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                $damage = null;
                                foreach ($player->getWorld()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                    if ($damage != null) {
                                        $damager = Server::getInstance()->getPlayer($damage);
                                        PluginUtils::getEventDamage($player, $arena, 'Has died for potions by', $damager);
                                    } else {
                                        PluginUtils::getEventDamage($player, $arena, 'Has died for potions');
                                    }
                                }
                            }
                        break;
                        default:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->cancel();
                                PluginUtils::getEventDamage($player, $arena, 'Has died');
                            }
                        break;
                    }
                }
            }
        }
    }
}
?>