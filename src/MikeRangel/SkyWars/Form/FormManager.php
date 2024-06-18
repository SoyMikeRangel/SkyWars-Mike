<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Form;
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Arena\Arena, Tasks\Emotes, Tasks\ArenaID, Form\MenuForm, Form\elements\Button};
use pocketmine\{Server, player\Player, permission\DefaultPermissions, item\Item, entity\Effect, math\Vector3, entity\EffectInstance, utils\TextFormat as Color};

class FormManager {

    #Settings UI.
    public static function getSettingsUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'SkyWars Setings UI', Color::GRAY . 'Select an option.',
        [
            new Button(Color::BOLD . Color::DARK_AQUA . 'Profile' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Look at your profile'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Emotes' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Get emotes'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Cages' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Buy Cages')
        ], 
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    $player->sendMessage(Color::RED . 'This function is in development for future versions, wait for them.'); 
                break;
                case 1:
                    self::getEmotes($player);
                break;
                case 2:
                    self::getCages($player);
                break;
            }
        }));
    }

    #Buy.
    public static function getAllEmotes() : array {
        $cages = [];
		if ($handle = opendir(SkyWars::getInstance()->getDataFolder() . 'Emotes/')) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry !== '.' && $entry !== '..') {
					$name = str_replace('.json', '', $entry);
                    $cages[] = $name;
				}
			}
			closedir($handle);
		}
		return $cages;
    }

    public static function alreadyEmote(Player $player, string $value) {
        $config = SkyWars::getConfigs('buyemotes');
        $array = $config->get($player->getName());
        if (in_array($value, $array, true)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getEmotes(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Emotes', Color::GRAY . 'This option is still in experimental mode, in future updates it will be improved.',
        self::getButtonsEmotes($player),
        static function (Player $player, Button $button) : void {
            $config = SkyWars::getConfigs('config');
            $profile = SkyWars::getConfigs('Profiles/' . $player->getName());
            $emotes = SkyWars::getConfigs('buyemotes');
            $array = $emotes->get($player->getName());
            $explode = explode("\n", $button->getText());
            $emote = substr($explode[0], 3);
            if (!self::alreadyEmote($player, $emote)) {
                if ($profile->get('coins') >= $config->get($emote . 'emote')) {
                    array_push($array, $emote);
                    $emotes->set($player->getName(), $array);
                    $emotes->save();
                    $player->sendMessage(Color::GREEN . 'You have purchased the emote: ' . Color::WHITE . $emote);
                } else {
                    $player->sendMessage(Color::RED . 'You need more coins to buy this emote.');
                }
            } else {
                $player->sendMessage(Color::GREEN . 'This emote is already yours.');
            }
        }));
    }

    public static function getButtonsEmotes(Player $player) {
        $config = SkyWars::getConfigs('config');
        $buttons = [];
        foreach (self::getAllEmotes() as $emotes) {
            if (self::alreadyEmote($player, $emotes)) {
                $buttons[] = new Button(Color::BOLD . $emotes . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Purchased');
            } else {
                $buttons[] = new Button(Color::BOLD . $emotes . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked' . '   ' . Color::BLACK . 'Cost: ' . Color::GREEN . $config->get($emotes . 'emote'));
            }
        }
        return $buttons;
    }

    #My emotes.
    public static function myEmotes(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'My Emotes', Color::GRAY . 'This option is still in experimental mode, in future updates it will be improved.',
        self::getButtonsMyEmotes($player),
        static function (Player $player, Button $button) : void {
            $explode = explode("\n", $button->getText());
            $emote = substr($explode[0], 3);
            if (!in_array($player->getName(), SkyWars::$data['emotes'])) {
                SkyWars::$data['emotes'][] = $player->getName();
                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new Emotes($player, $emote), 10);
            }
        }));
    }

    public static function getButtonsMyEmotes(Player $player) {
        $config = SkyWars::getConfigs('buyemotes');
        $array = $config->get($player->getName());
        $buttons = [];
        foreach ($array as $emotes) {
            $buttons[] = new Button(Color::BOLD . $emotes . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        }
        return $buttons;
    }

    #Alives.
    public static function getAlives(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Players', Color::GRAY . 'Choose who to see.',
        self::getButtonsAlive($player),
        static function (Player $player, Button $button) : void {
            $explode = explode("\n", $button->getText());
            $name = substr($explode[0], 3);
            $pl = Server::getInstance()->getPlayer($name);
            $player->teleport(new Vector3($pl->getX(), $pl->getY(), $pl->getZ()));
        }));
    }

    public static function getButtonsAlive(Player $player) {
        $buttons = [];
        foreach (Arena::getArenas() as $arena) {
            if ($player->getWorld()->getFolderName() == Arena::getName($arena)) {
                foreach ($player->getWorld()->getPlayers() as $players) {
                    if ($players->getGamemode() == 0) {
                        $buttons[] = new Button(Color::BOLD . $players->getName() . "\n" . Color::RESET . Color::BLACK . 'Click to view');
                    }
                }           
            }
        }
        return $buttons;
    }

    #Vote Chest.
    public static function getVotesUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Vote Chest', Color::GRAY . 'Vote for your favorite chest.',
        [
            new Button(Color::BOLD . Color::YELLOW . 'OP' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Votes: ' . Color::GREEN . count(SkyWars::$data['vote'][$player->getWorld()->getFolderName()]['op'])),
            new Button(Color::BOLD . Color::YELLOW . 'Basic' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Votes: ' . Color::GREEN . count(SkyWars::$data['vote'][$player->getWorld()->getFolderName()]['normal'])),
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    if ($player->hasPermission('skywars.vote.perm') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        if (count(Server::getInstance()->getWorldManager()->getWorldByName($player->getWorld()->getFolderName())->getPlayers()) < 2) {
                            $player->sendMessage(Color::RED . 'More players are needed to access this feature.');
                        } else {
                            PluginUtils::setVote($player, $player->getWorld()->getFolderName(), 'op');
                        }
                    } else {
                        $player->sendMessage(Color::RED . 'Adquire a range to access this function.');
                    }
                break;
                case 1:
                    if ($player->hasPermission('skywars.vote.perm') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        if (count(Server::getInstance()->getWorldManager()->getWorldByName($player->getWorld()->getFolderName())->getPlayers()) < 2) {
                            $player->sendMessage(Color::RED . 'More players are needed to access this feature');
                        } else {
                            PluginUtils::setVote($player, $player->getWorld()->getFolderName(), 'normal');
                        }
                    } else {
                        $player->sendMessage(Color::RED . 'Adquire a range to access this function.');
                    }
                break;
            }
        }));
    }

    #Game UI.
    public static function getGameUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'SkyWars UI', Color::GRAY . 'Select an option.',
        [
            new Button(Color::BOLD . Color::DARK_AQUA . 'SkyWars Modes' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'See available modes'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Create Room' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Invite your friends'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Enter The Room' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Enter an arena')
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    self::getModes($player);
                break;
                case 1:
                    $player->sendMessage(Color::RED . 'This function is in development for future versions, wait for them.');
                break;
                case 2:
                    $player->sendMessage(Color::RED . 'This function is in development for future versions, wait for them.');
                break;
            }
        }));
    }

    public static function getModes(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'SkyWars Modes', Color::GRAY . 'Select your mode.',
        [
            new Button(Color::BOLD . Color::DARK_AQUA . 'SkyWars' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Mode: '  . Color::GREEN . 'SOLO')
        ], 
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                        SkyWars::$data['queue'][] = $player->getName();
                        SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
                    }
                break;
            }
        }));
    }

    #Kits UI.
    public static function getKits(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Kits', Color::GRAY . 'Select your option.',
        [
            new Button(Color::BOLD . Color::DARK_AQUA . 'Kits FREE' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'See your available kits'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Kits YT' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'See your available kits'),
            new Button(Color::BOLD . Color::DARK_AQUA . 'Kits Ranks' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'See your available kits')
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0;
                    self::getKitsFree($player);
                break;
                case 1:
                    self::getKitsYT($player);
                break;
                case 2:
                    self::getKitsRanks($player);
                break;
            }
        }));
    }

    public static function getKitsFree(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Kits Free', Color::GRAY . 'Select your option.',
        [
            new Button(Color::BOLD . 'Builder' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked'),
            new Button(Color::BOLD . 'Tools' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked')
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0;
                    $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                    $config->set('kit', 'builder');
                    $config->save();
                    $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Builder');
                break;
                case 1:
                    $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                    $config->set('kit', 'tools');
                    $config->save();
                    $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Tools');
                break;
            }
        }));
    }

    public static function getKitsYT(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Kits YT', Color::GRAY . 'Select your option.',
        self::getButtonsYT($player),
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    if ($player->hasPermission('kit.famous') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'asesino');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Asesino');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 1:
                    if ($player->hasPermission('kit.yt') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'lumberhack');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'LumberHack');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 2:
                    if ($player->hasPermission('kit.youtuber') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'enderman');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Enderman');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 3:
                    if ($player->hasPermission('kit.youtuber') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'rusheryt');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Rusher');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 4:
                    if ($player->hasPermission('kit.famous') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'boomberman');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Boomberman');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
            }
        }));
    }

    public static function getButtonsYT(Player $player) {
        $buttons = [];
        if ($player->hasPermission('kit.famous') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Asesino' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Asesino' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.yt') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'LumberHack' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'LumberHack' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.youtuber') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Enderman' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Enderman' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.youtuber') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Rusher' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Rusher' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.famous') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Boomberman' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Boomberman' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        return $buttons;
    }

    public static function getKitsRanks(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Kits Ranks', Color::GRAY . 'Select your option.',
        self::getButtonsRanks($player),
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    if ($player->hasPermission('kit.Pacman') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'rusher');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Rusher');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 1:
                    if ($player->hasPermission('kit.Pacman') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'baseball');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Baseball'); 
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 2:
                    if ($player->hasPermission('kit.Happier') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'ghost');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Ghost');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 3:
                    if ($player->hasPermission('kit.Happier') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'healer');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Healer');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 4:
                    if ($player->hasPermission('kit.Vip') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'archer');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Archer');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 5:
                    if ($player->hasPermission('kit.Donator') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'piromo');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Piromo');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
                case 6:
                    if ($player->hasPermission('kit.saltamontes') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $config = SkyWars::getConfigs('Profiles/' . $player->getName());
                        $config->set('kit', 'saltamontes');
                        $config->save();
                        $player->sendMessage(Color::GOLD . 'You have selected the kit: ' . Color::WHITE . 'Saltamontes');
                    } else {
                        $player->sendMessage(Color::RED . 'You do not have permission to choose this kit.');
                    }
                break;
            }
        }));
    }

    public static function getButtonsRanks(Player $player) {
        $buttons = [];
        if ($player->hasPermission('kit.Pacman') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Rusher' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Rusher' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.Pacman') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Baseball' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Baseball' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.Happier') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Ghost' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Ghost' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.Happier') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Healer' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Healer' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.Vip') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Archer' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Archer' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.Donator') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Piromo' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Piromo' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        if ($player->hasPermission('kit.saltamontes') || $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $buttons[] = new Button(Color::BOLD . 'Saltamontes' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked');
        } else {
            $buttons[] = new Button(Color::BOLD . 'Saltamontes' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked');
        }
        return $buttons;
    }

    #Cages UI.
    public static function getAllCages() : array {
        $cages = [];
		if ($handle = opendir(SkyWars::getInstance()->getDataFolder() . 'Cages/')) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry !== '.' && $entry !== '..') {
					$name = str_replace('.yml', '', $entry);
                    $cages[] = $name;
				}
			}
			closedir($handle);
		}
		return $cages;
    }

    public static function alreadyCage(Player $player, string $value) {
        $config = SkyWars::getConfigs('buycages');
        $array = $config->get($player->getName());
        if (in_array($value, $array, true)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getCages(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::DARK_AQUA . 'Cages', Color::GRAY . 'Select your cage.',
        self::getButtonsCages($player),
        static function (Player $player, Button $button) : void {
            $config = SkyWars::getConfigs('config');
            $profile = SkyWars::getConfigs('Profiles/' . $player->getName());
            $cages = SkyWars::getConfigs('buycages');
            $array = $cages->get($player->getName());
            $explode = explode("\n", $button->getText());
            $cage = substr($explode[0], 3);
            if (!self::alreadyCage($player, $cage)) {
                if ($profile->get('coins') >= $config->get($cage . 'cost')) {
                    array_push($array, $cage);
                    $cages->set($player->getName(), $array);
                    $cages->save();
                    $profile->set('cage', $cage);
                    $profile->save();
                    $player->sendMessage(Color::GREEN . 'You have purchased the cage: ' . Color::WHITE . $cage);
                } else {
                    $player->sendMessage(Color::RED . 'You need more coins to buy this cage.');
                }
            } else {
                $profile->set('cage', $cage);
                $profile->save();
                $player->sendMessage(Color::GREEN . 'Correctly placed cage: ' . Color::WHITE . $cage);
            }
        }));
    }


    public static function getButtonsCages(Player $player) {
        $config = SkyWars::getConfigs('config');
        $buttons = [];
        foreach (self::getAllCages() as $cages) {
            if (self::alreadyCage($player, $cages)) {
                $buttons[] = new Button(Color::BOLD . $cages . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Purchased');
            } else {
                $buttons[] = new Button(Color::BOLD . $cages . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::RED . 'Locked' . '   ' . Color::BLACK . 'Cost: ' . Color::GREEN . $config->get($cages . 'cost'));
            }
        }
        return $buttons;
    }
}