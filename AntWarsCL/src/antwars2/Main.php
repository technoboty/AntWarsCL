<?php

namespace antwars2;

use antwars\Arena\Arena;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use FormAPI\FormAPI;
use pocketmine\utils\Config;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\Server;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener {

    public array $arenas_search_solo = [];
    public array $arenas_search_duo = [];
    public array $arenas_search_squad = [];
    public array $arenas_search_extra = [];

    public array $arenas_playing_solo = [];
    public array $arenas_playing_duo = [];
    public array $arenas_playing_squad = [];
    public array $arenas_playing_extra = [];

    public array $arenas = [];

    public const SMALL_ARENA_SIZE = 50;
    public const BIG_ARENA_SIZE = 70;


    public function onEnable(): void{
        $this->getLogger()->info(">>>Плагин успешно запущен<<<");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder() . "Maps/",0777,true);
        $this->saveResource('Maps/antwars_small.zip');
        $this->saveResource('Maps/antwars_big.zip');
    }
    public function onDisable(): void{
        $this->getLogger()->info("[ANTWARS]>>>Плагин выключен!");
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        switch ($command->getName()){
            case "ant":
                if($sender instanceof Player){
                    $this->onMenu($sender);
                } else {
                    $this->getLogger()->info("Error");
                }
                break;
        }
        return true;
    }
    public function onMenu(Player $p){
        $f = FormAPI::getInstance()->createSimpleForm(function (Player $p, int $data = null) {
            $result = $data;
            if ($result === null) {
                return false;
            }
            switch ($result) {
                case 0:
                    $size = 50;
                    $choice = 1;
                    $this->onAddGame($p,$size,$choice);
                    break;
                case 1:
                    $size = 50;
                    $choice = 2;
                    $this->onAddGame($p,$size,$choice);
                    break;
                case 2:
                    $size = 70;
                    $choice = 3;
                    $this->onAddGame($p,$size,$choice);
                    break;
                case 3:
                    $size = 70;
                    $choice = 4;
                    $this->onAddGame($p,$size,$choice);
                    break;
            }
            return true;
        });
        $f->setTitle("§l§2>> §eМеню §2<<");
        $f->addButton("§l§e> §fSOLO §e<");
        $f->addButton("§l§e> §fDUO §e<");
        $f->addButton("§l§e> §fSQUAD §e<");
        $f->addButton("§l§e> §fEXTRA[NOT] §e<");
        $f->sendToPlayer($p);
        return $f;
    }
    public function onAddGame(Player $player,int $size,int $choice) : bool{
        $nick = strtolower($player->getName());
        if($size === self::SMALL_ARENA_SIZE){
            if($choice == 1){
                if($this->arenas_search_solo != null){
                    $rand = array_rand($this->arenas_search_solo,1);
                    $world_name = $this->arenas_search_solo[$rand];
                    $world = Server::getInstance()->getWorldManager()->getWorldByName($world_name);
                    if(count($world->getPlayers()) < 4) {
                        $this->getServer()->getWorldManager()->loadWorld($nick, true);
                        $position = new Position(256, 117, 256, $world);
                        $world->loadChunk(256, 256);
                        $player->teleport($position);
                        return true;
                    } else {
                        $tag = 2;
                        while ($tag <= 12){
                            $dir_name = $nick.$tag;
                            $directory = Server::getInstance()->getDataPath()."worlds/".$dir_name;
                            if(!is_dir($directory)){
                                $src = $this->getDataFolder() . "/Maps/antwars_small.zip";
                                $zip = new \ZipArchive;
                                $zip->open($src);
                                $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                                rename($this->getServer()->getDataPath() . "worlds/antwars_small", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                                array_push($this->arenas_search_solo,$dir_name);
                                $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                                $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                                $position = new Position(256, 117, 256, $world);
                                $world->loadChunk(256, 256);
                                $player->teleport($position);
                                $this->MapFiller($world,$size,$choice);
                                return true;
                            } else {
                                $tag++;
                            }
                        }
                    }
                } else {
                    $tag = 2;
                    while ($tag <= 12) {
                        $dir_name = $nick . $tag;
                        $directory = Server::getInstance()->getDataPath() . "worlds/" . $dir_name;
                        if (!is_dir($directory)) {
                            $src = $this->getDataFolder() . "/Maps/antwars_small.zip";
                            $zip = new \ZipArchive;
                            $zip->open($src);
                            $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                            rename($this->getServer()->getDataPath() . "worlds/antwars_small", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                            array_push($this->arenas_search_solo, $dir_name);
                            $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                            $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                            $position = new Position(256, 117, 256, $world);
                            $world->loadChunk(256, 256);
                            $player->teleport($position);
                            $this->MapFiller($world, $size, $choice);
                            return true;
                        } else {
                            $tag++;
                        }
                    }
                }
            }elseif($choice == 2){
                if($this->arenas_search_duo != null){
                    $rand = array_rand($this->arenas_search_duo,1);
                    $world_name = $this->arenas_search_duo[$rand];
                    $world = Server::getInstance()->getWorldManager()->getWorldByName($world_name);
                    if(count($world->getPlayers()) < 4) {
                        $this->getServer()->getWorldManager()->loadWorld($nick, true);
                        $position = new Position(256, 117, 256, $world);
                        $world->loadChunk(256, 256);
                        $player->teleport($position);
                        return true;
                    } else {
                        $tag = 2;
                        while ($tag <= 12){
                            $dir_name = $nick.$tag;
                            $directory = Server::getInstance()->getDataPath()."worlds/".$dir_name;
                            if(!is_dir($directory)){
                                $src = $this->getDataFolder() . "/Maps/antwars_small.zip";
                                $zip = new \ZipArchive;
                                $zip->open($src);
                                $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                                rename($this->getServer()->getDataPath() . "worlds/antwars_small", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                                array_push($this->arenas_search_duo,$dir_name);
                                $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                                $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                                $position = new Position(256, 117, 256, $world);
                                $world->loadChunk(256, 256);
                                $player->teleport($position);
                                $this->MapFiller($world,$size,$choice);
                                return true;
                            } else {
                                $tag++;
                            }
                        }
                    }
                } else {
                    $tag = 2;
                    while ($tag <= 12) {
                        $dir_name = $nick . $tag;
                        $directory = Server::getInstance()->getDataPath() . "worlds/" . $dir_name;
                        if (!is_dir($directory)) {
                            $src = $this->getDataFolder() . "/Maps/antwars_small.zip";
                            $zip = new \ZipArchive;
                            $zip->open($src);
                            $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                            rename($this->getServer()->getDataPath() . "worlds/antwars_small", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                            array_push($this->arenas_search_duo, $dir_name);
                            $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                            $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                            $position = new Position(256, 117, 256, $world);
                            $world->loadChunk(256, 256);
                            $player->teleport($position);
                            $this->MapFiller($world, $size, $choice);
                            return true;
                        } else {
                            $tag++;
                        }
                    }
                }
            }
        }elseif($size === self::BIG_ARENA_SIZE){
            if($choice == 3){
                if($this->arenas_search_squad != null){
                    $rand = array_rand($this->arenas_search_squad,1);
                    $world_name = $this->arenas_search_squad[$rand];
                    $world = $this->getServer()->getWorldManager()->getWorldByName($world_name);
                    if(count($world->getPlayers()) < 4) {
                        $this->getServer()->getWorldManager()->loadWorld($nick, true);
                        $position = new Position(256, 127, 256, $world);
                        $world->loadChunk(256, 256);
                        $player->teleport($position);
                        return true;
                    } else {
                        $tag = 2;
                        while ($tag <= 12){
                            $dir_name = $nick.$tag;
                            $directory = Server::getInstance()->getDataPath()."worlds/".$dir_name;
                            if(!is_dir($directory)){
                                $src = $this->getDataFolder() . "/Maps/antwars_big.zip";
                                $zip = new \ZipArchive;
                                $zip->open($src);
                                $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                                rename($this->getServer()->getDataPath() . "worlds/antwars_big", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                                array_push($this->arenas_search_squad,$dir_name);
                                $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                                $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                                $position = new Position(256, 127, 256, $world);
                                $world->loadChunk(256, 256);
                                $player->teleport($position);
                                $this->MapFiller($world,$size,$choice);
                                return true;
                            } else {
                                $tag++;
                            }
                        }
                    }
                } else {
                    $tag = 2;
                    while ($tag <= 12) {
                        $dir_name = $nick.$tag;
                        $directory = Server::getInstance()->getDataPath()."worlds/".$dir_name;
                        if(!is_dir($directory)){
                            $src = $this->getDataFolder() . "/Maps/antwars_big.zip";
                            $zip = new \ZipArchive;
                            $zip->open($src);
                            $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                            rename($this->getServer()->getDataPath() . "worlds/antwars_big", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                            array_push($this->arenas_search_squad, $dir_name);
                            $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                            $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                            $position = new Position(256, 127, 256, $world);
                            $world->loadChunk(256, 256);
                            $player->teleport($position);
                            $this->MapFiller($world, $size, $choice);
                            return true;
                        } else {
                            $tag++;
                        }
                    }
                }
            }elseif ($choice == 4){
                if($this->arenas_search_extra != null){
                    $rand = array_rand($this->arenas_search_extra,1);
                    $world_name = $this->arenas_search_extra[$rand];
                    $world = Server::getInstance()->getWorldManager()->getWorldByName($world_name);
                    if(count($world->getPlayers()) < 4) {
                        $this->getServer()->getWorldManager()->loadWorld($nick, true);
                        $position = new Position(256, 127, 256, $world);
                        $world->loadChunk(256, 256);
                        $player->teleport($position);
                        return true;
                    } else {
                        $tag = 2;
                        while ($tag <= 12){
                            $dir_name = $nick.$tag;
                            $directory = Server::getInstance()->getDataPath()."worlds/".$dir_name;
                            if(!is_dir($directory)){
                                $src = $this->getDataFolder() . "/Maps/antwars_big.zip";
                                $zip = new \ZipArchive;
                                $zip->open($src);
                                $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                                rename($this->getServer()->getDataPath() . "worlds/antwars_big", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                                array_push($this->arenas_search_extra,$dir_name);
                                $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                                $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                                $position = new Position(256, 127, 256, $world);
                                $world->loadChunk(256, 256);
                                $player->teleport($position);
                                $this->MapFiller($world,$size,$choice);
                                return true;
                            } else {
                                $tag++;
                            }
                        }
                    }
                } else {
                    $tag = 2;
                    while ($tag <= 12) {
                        $dir_name = $nick . $tag;
                        $directory = Server::getInstance()->getDataPath() . "worlds/" . $dir_name;
                        if(!is_dir($directory)) {
                            $src = $this->getDataFolder() . "/Maps/antwars_big.zip";
                            $zip = new \ZipArchive;
                            $zip->open($src);
                            $zip->extractTo($this->getServer()->getDataPath() . "worlds/");
                            rename($this->getServer()->getDataPath() . "worlds/antwars_big", $this->getServer()->getDataPath() . "worlds/" . $dir_name);
                            array_push($this->arenas_search_extra, $dir_name);
                            $this->getServer()->getWorldManager()->loadWorld($dir_name, true);
                            $world = $this->getServer()->getWorldManager()->getWorldByName($dir_name);
                            $position = new Position(256, 127, 227, $world);
                            $world->loadChunk(256, 227);
                            $player->teleport($position);
                            $this->MapFiller($world, $size, $choice);
                            return true;
                        } else {
                            $tag++;
                        }
                    }
                }
            }
        }
    }
    public function MapFiller(World $world,int $size,$choice){
        $factor = $size/2;
        $x_size1 = 256 - $factor;
        $x_size2 = 256 + $factor;
        $y_size1 = 90 - $factor;
        $y_size2 = 90 + $factor;
        $z_size1 = 256 - $factor;
        $z_size2 = 256 + $factor;
        for($y = $y_size1; $y <= $y_size2;$y++) {
            for ($x = $x_size1; $x <= $x_size2; $x++) {
                for ($z = $z_size1; $z <= $z_size2; $z++) {
                    $rand = mt_rand(1,100);
                    $rand_2 = mt_rand(1,4);
                    if($rand == 1 || $rand == 2 || $rand == 3){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::DIAMOND_ORE(),true);
                    } elseif($rand == 4){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::LAPIS_LAZULI_ORE(),true);
                    } elseif($rand == 5 || $rand == 6){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::REDSTONE_ORE(),true);
                    } elseif($rand == 7 || $rand == 8){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::GOLD_ORE(),true);
                    } elseif($rand >= 9 && $rand <= 12){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                    } elseif($rand == 13){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::CRAFTING_TABLE(),true);
                    } elseif($rand == 14){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::ANVIL(),true);
                    } elseif($rand == 15){
                        if($rand_2 == 1) {
                            $world->setBlockAt($x, $y, $z, VanillaBlocks::ENCHANTING_TABLE(), true);
                        } else {
                            $world->setBlockAt($x, $y, $z, VanillaBlocks::COBBLESTONE(), true);
                        }
                    } elseif($rand == 16){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::COBWEB(),true);
                    } elseif($rand == 17){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::BOOKSHELF(),true);
                    } elseif($rand >= 18 && $rand <= 20){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::MELON(),true);
                    } elseif($rand >= 21 && $rand <= 26){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_PLANKS(),true);
                    } elseif($rand >= 27 && $rand <= 36){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                    } elseif($rand >= 37 && $rand <= 43){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LEAVES(),true);
                    } elseif($rand >= 44 && $rand <= 48){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::GLASS(),true);
                    } elseif($rand >= 49 && $rand <= 53){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::SAND(),true);
                    } elseif($rand >= 54 && $rand <= 63){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::GRAVEL(),true);
                    } elseif($rand >= 64 && $rand <= 73){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::DIRT(),true);
                    }elseif($rand >= 74 && $rand <= 78){
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::HAY_BALE(),true);
                    } else {
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                    }
                }
            }
        }
        if($size === self::SMALL_ARENA_SIZE){
            # генерация блоков вне комнаты
            $indent = 3;
            $indent_2 = 7;
            $sirer_x_1 = 256 - $factor + $indent;
            $sirer_z_1 = 256 - $factor + $indent;
            $sirer_x_2 = 256 + $factor - $indent;
            $sirer_z_2 = 256 + $factor - $indent;
            $sirer_y_1 = 90 - $factor + $indent;
            $sirer_y_2 = 90 + $factor - $indent;
            $sirer_x_3 = 256 - $factor + $indent_2;
            $sirer_z_3 = 256 - $factor + $indent_2;
            $sirer_x_4 = 256 + $factor - $indent_2;
            $sirer_z_4 = 256 + $factor - $indent_2;
            $sirer_y_3 = 90 - $factor + $indent_2;
            $sirer_y_4 = 90 + $factor - $indent_2;
            for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                    for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                        $rand = mt_rand(1,6);
                        if($rand == 1){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                        }elseif ($rand == 2){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                        }else {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                        }
                    }
                }
            }
            for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                    for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                        $rand = mt_rand(1,6);
                        if($rand == 1){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                        }elseif ($rand == 2){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                        }else {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                        }
                    }
                }
            }
            for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                    for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                        $rand = mt_rand(1,6);
                        if($rand == 1){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                        }elseif ($rand == 2){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                        }else {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                        }
                    }
                }
            }
            for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                    for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                        $rand = mt_rand(1,6);
                        if($rand == 1){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                        }elseif ($rand == 2){
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                        }else {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                        }
                    }
                }
            }
            # генерация комнаты
            $indent = 4;
            $indent_2 = 6;
            $sirer_x_1 = 256 - $factor + $indent;
            $sirer_z_1 = 256 - $factor + $indent;
            $sirer_x_2 = 256 + $factor - $indent;
            $sirer_z_2 = 256 + $factor - $indent;
            $sirer_y_1 = 90 - $factor + $indent;
            $sirer_y_2 = 90 + $factor - $indent;
            $sirer_x_3 = 256 - $factor + $indent_2;
            $sirer_z_3 = 256 - $factor + $indent_2;
            $sirer_x_4 = 256 + $factor - $indent_2;
            $sirer_z_4 = 256 + $factor - $indent_2;
            $sirer_y_3 = 90 - $factor + $indent_2;
            $sirer_y_4 = 90 + $factor - $indent_2;
            for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                    for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                    }
                }
            }
            for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                    for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                    }
                }
            }
            for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                    for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                    }
                }
            }
            for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                    for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                        $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                    }
                }
            }
        }elseif ($size === self::BIG_ARENA_SIZE){
            if($choice == 3){
                # генерация блоков вне комнаты
                $indent = 3;
                $indent_2 = 7;
                $sirer_x_1 = 256 - $factor + $indent;
                $sirer_z_1 = 256 - $factor + $indent;
                $sirer_x_2 = 256 + $factor - $indent;
                $sirer_z_2 = 256 + $factor - $indent;
                $sirer_y_1 = 90 - $factor + $indent;
                $sirer_y_2 = 90 + $factor - $indent;
                $sirer_x_3 = 256 - $factor + $indent_2;
                $sirer_z_3 = 256 - $factor + $indent_2;
                $sirer_x_4 = 256 + $factor - $indent_2;
                $sirer_z_4 = 256 + $factor - $indent_2;
                $sirer_y_3 = 90 - $factor + $indent_2;
                $sirer_y_4 = 90 + $factor - $indent_2;
                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }
                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }
                # генерация комнаты
                $indent = 4;
                $indent_2 = 6;
                $sirer_x_1 = 256 - $factor + $indent;
                $sirer_z_1 = 256 - $factor + $indent;
                $sirer_x_2 = 256 + $factor - $indent;
                $sirer_z_2 = 256 + $factor - $indent;
                $sirer_y_1 = 90 - $factor + $indent;
                $sirer_y_2 = 90 + $factor - $indent;
                $sirer_x_3 = 256 - $factor + $indent_2;
                $sirer_z_3 = 256 - $factor + $indent_2;
                $sirer_x_4 = 256 + $factor - $indent_2;
                $sirer_z_4 = 256 + $factor - $indent_2;
                $sirer_y_3 = 90 - $factor + $indent_2;
                $sirer_y_4 = 90 + $factor - $indent_2;
                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_4; $x <= $sirer_x_2; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_1; $x <= $sirer_x_3; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
            } elseif($choice == 4){
                # генерация блоков вне комнаты
                $indent = 5;
                $indent_2 = 9;

                $sirer_x_1 = 256 + $indent;
                $sirer_x_2 = 256 - $indent;

                $sirer_z_1 = 256 - $factor + $indent;
                $sirer_z_3 = 256 - $factor + $indent_2;

                $sirer_z_4 = 256 + $factor - $indent_2;
                $sirer_z_2 = 256 + $factor - $indent;

                $sirer_y_1 = 90 - $factor + $indent;
                $sirer_y_3 = 90 - $factor + $indent_2;

                $sirer_y_4 = 90 + $factor - $indent_2;
                $sirer_y_2 = 90 + $factor - $indent;

                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_2; $x <= $sirer_x_1; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_2; $x <= $sirer_x_1; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $rand = mt_rand(1,6);
                            if($rand == 1){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::IRON_ORE(),true);
                            }elseif ($rand == 2){
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::OAK_LOG(),true);
                            }else {
                                $world->setBlockAt($x,$y,$z,VanillaBlocks::COBBLESTONE(),true);
                            }
                        }
                    }
                }

                # генерация комнаты
                $indent = 4;
                $indent_2 = 8;
                $sirer_x_1 = 256 + $indent;
                $sirer_x_2 = 256  - $indent;

                $sirer_z_1 = 256 - $factor + $indent;
                $sirer_z_2 = 256 + $factor - $indent;
                $sirer_z_3 = 256 - $factor + $indent_2;
                $sirer_z_4 = 256 + $factor - $indent_2;
                $sirer_y_1 = 90 - $factor + $indent;
                $sirer_y_2 = 90 + $factor - $indent;
                $sirer_y_3 = 90 - $factor + $indent_2;
                $sirer_y_4 = 90 + $factor - $indent_2;
                for($y = $sirer_y_1; $y <= $sirer_y_3;$y++) {
                    for ($x = $sirer_x_2; $x <= $sirer_x_1; $x++) {
                        for ($z = $sirer_z_4; $z <= $sirer_z_2; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
                for($y = $sirer_y_4; $y <= $sirer_y_2;$y++) {
                    for ($x = $sirer_x_2; $x <= $sirer_x_1; $x++) {
                        for ($z = $sirer_z_1; $z <= $sirer_z_3; $z++) {
                            $world->setBlockAt($x,$y,$z,VanillaBlocks::AIR(),true);
                        }
                    }
                }
            }
        }
        $world->saveChunks();
    }
}