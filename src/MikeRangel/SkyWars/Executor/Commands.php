<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Executor;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Form\FormManager, Entity\EntityManager, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, player\Player, player\GameMode, permission\DefaultPermissions, utils\TextFormat as Color};
use pocketmine\command\{CommandSender, Command};

class Commands extends Command {

    public function __construct(SkyWars $plugin) {
        parent::__construct("sw", "SkyWars 1.0 by Mike Rangel.", null, ["sw"]);
        $this->setPermission("skywars.command");
    }

    public function execute(CommandSender $player, $label, array $args) {
        if (!isset($args[0])) {
            $player->sendMessage(Color::RED . 'Usage: /sw help');
            return false;
        }
        switch ($args[0]) {
            case 'help':
                $date = [
                    '/sw help: Help commands.',
                    '/sw create <arena> <maxslots> <id>: Create arena.',
                    '/sw npc <game|stats|remove>: Set spawn point entity.',
                    '/sw settings: Settings UI.',
                    '/sw leave: Leave an arena.',
                    '/sw credits: View author.'
                ];
                $player->sendMessage(Color::GOLD . 'SkyWars Commands:');
                foreach ($date as $help) {
                    $player->sendMessage(Color::GREEN . $help);
                }
                break;
            case 'create':
                if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if (isset($args[1], $args[2], $args[3])) {
                        if (file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[1])) {
                            if (Arena::ArenaExiting($args[3])) {
                                Arena::addArena($player, $args[1], $args[2], $args[3]);
                            } else {
                                $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'This arena already exists.');
                            }
                        } else {
                            $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'This world does not exist.');
                        }
                    } else {
                        $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'Usage: /sw create <arena> <maxslots> <id>');
                    }
                } else {
                    $player->sendMessage(Color::RED . 'You do not have permission to use this command.');
                }
                break;
            case 'npc':
                if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if (!empty($args[1])) {
                        switch ($args[1]) {
                            case 'game':
                                $entity = new EntityManager();
                                $entity->createEntity($player, 'EntityHuman', 'NPC');
                                $player->sendMessage(SkyWars::getPrefix() . Color::GREEN . 'The npc of game has been placed successfully.');
                                break;
                            case 'stats':
                                $entity = new EntityManager();
                                $entity->createEntity($player, 'EntityStats', null);
                                $player->sendMessage(SkyWars::getPrefix() . Color::GREEN . 'Tops have been placed successfully.');
                                break;
                            case 'remove':
                                foreach ($player->getWorld()->getEntities() as $entity) {
                                    if ($entity instanceof EntityHuman || $entity instanceof EntityStats) {
                                        $entity->kill();
                                    }
                                }
                                break;
                            default:
                                $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'Usage: /sw npc <stats|game|remove>');
                                break;
                        }
                    } else {
                        $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'Usage: /sw npc <stats|game|remove>');
                    }
                } else {
                    $player->sendMessage(Color::RED . 'You do not have permission to use this command.');
                }
                break;
            case 'settings':
                FormManager::getSettingsUI($player);
                break;
            case 'leave':
                foreach (Arena::getArenas() as $arena) {
                    if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->setNoClientPredictions(false);
                        $player->setAllowFlight(false);
                        $player->setFlying(false);
                        $player->getEffects()->clear();
                        $player->setGamemode(GameMode::ADVENTURE());
                        $player->setHealth(20);
                        $player->getHungerManager()->setFood(20);
                    } else {
                        $player->sendMessage(Color::RED . 'You are not in any arena.');
                    }
                }
                break;
            case 'credits':
                $description = [
                    'Author: ' . Color::GRAY . '@MikeRangelMR',
                    'Status: ' . Color::GREEN . 'SkyWars is private.'
                ];
                foreach ($description as $credits) {
                    $player->sendMessage(Color::GOLD . $credits);
                }
                break;
            default:
                $player->sendMessage(Color::RED . 'Usage: /sw help');
            break;
        }        
        return true;
    }
}