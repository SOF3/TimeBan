<?php

  namespace applqpak\TimeBan\command;
  
  use pocketmine\command\defaults\VanillaCommand;
  use pocketmine\command\Command;
  use pocketmine\command\CommandSender;
  use pocketmine\utils\TextFormat;
  
  use applqpak\TimeBan\Main;
  
  class TimeBanCommand extends VanillaCommand
  {
      private $plugin;
      public function __construct(Main $plugin)
      {
          parent::__construct(
            'timeban',
            'Ban a player for a specified amount of time.',
            '/timeban'
          );
          $this->setAliases(['tb']);
          $this->setPermission('timeban.command');
          $this->plugin = $plugin;
      }
      
      public function execute(CommandSender $sender, $label, array $args)
      {
          if(!($this->testPermission($sender))) return false;
          if(count($args) < 1)
          {
              $sender->sendMessage(TextFormat::RED . $this->plugin->usage);
              return false;
          }
          $arg = strtolower(array_shift($arg));
          switch($arg)
          {
              case 'ban':
                if(!($sender->hasPermission('timeban.command.ban')))
                {
                    $sender->sendMessage(TextFormat::RED . 'Insufficient permissions.');
                    return false;
                }
                if(count($args) <= 2)
                {
                    $sender->sendMessage(TextFormat::RED . $this->plugin->usage);
                    return false;
                }
                $name      = array_shift($args);
                $time      = array_shift($args);
                $reason    = implode(' ', $args);
                $player    = $this->plugin->getServer()->getPlayer($name);
                if($player === null)
                {
                    $sender->sendMessage(TextFormat::RED . $name . ' is not online.');
                    return false;
                }
                $full_name = $player->getName();
                if(isset($this->plugin->getCfg()[$player->getClientId()]))
                {
                    $sender->sendMessage(TextFormat::RED . $full_name . ' is already banned, remaining time until unban: ' . ($this->plugin->getCfg()[$player->getClientId()]['time'] - time()));
                    return false;
                }
                if(!(is_numeric($time)))
                {
                    $sender->sendMessage(TextFormat::RED . 'Please specify a valid time(in minutes).');
                    return false;
                }
                $time = strtotime($time . ' minutes');
                $this->plugin->getCfg()[$player->getClientId()]         = 'true';
                $this->plugin->getCfg()[$player->getClientId()]['time'] = $time;
                $this->plugin->getCfg()[$player->getClientId()]['name'] = strtolower($full_name);
                $this->plugin->saveConfig();
                $player->close('', $reason);
                $sender->sendMessage(TextFormat::GREEN . $full_name . ' has been banned.');
                return true;
              break;
              
              case 'pardon':
                if(!($sender->hasPermission('timeban.command.pardon')))
                {
                    $sender->sendMessage(TextFormat::RED . 'Insufficient permissions.');
                    return false;
                }
                if(!(isset($args[0])))
                {
                    $sender->sendMessage(TextFormat::RED . $this->plugin->usage);
                    return false;
                }
                $name      = array_shift($args);
                $player    = $this->plugin->getServer()->getOfflinePlayer($name);
                $full_name = $player->getName();
                if(!(isset($this->plugin->getCfg()[$player->getClientId()])))
                {
                    $sender->sendMessage(TextFormat::RED . $full_name . ' is not banned.');
                    return false;
                }
                unset($this->plugin->getCfg()[$player->getClientId()]);
                $this->plugin->saveConfig();
                $sender->sendMessage(TextFormat::GREEN . $full_name . ' has been pardoned.');
                return true;
              break;
              
              case 'list':
                if(!($sender->hasPermission('timeban.command.list')))
                {
                    $sender->sendMessage(TextFormat::RED . 'Insufficient permissions.');
                    return false;
                }
                foreach($this->plugin->getCfg() as $key)
                {
                    $sender->sendMessage($this->plugin->getCfg()[$key]['name'] . ': ' . $this->plugin->getCfg()[$key]['time']);
                }
                return true;
              break;
              default:
                $sender->sendMessage(TextFormat::RED . $this->plugin->usage);
          }
          return true;
      }
  }