<?php

  namespace applqpak\TimeBan;

  use pocketmine\plugin\PluginBase;
  use pocketmine\utils\Config;
  use pocketmine\utils\TextFormat;
  
  use applqpak\TimeBan\command\TimeBanCommand;
  use applqpak\TimeBan\event\EventListener;
  
  class Main extends PluginBase
  {
      public $cfg;
      public $usage = 'Usage: /timeban <ban | pardon | list> [username | time(in minutes) | reason] [username]';
      public function onLoad()
      {
          @mkdir($this->getDataFolder());
          $this->saveDefaultConfig();
          $this->cfg = $this->getConfig();
      }
      
      public function onEnable()
      {
          $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
          $this->getServer()->getCommandMap()->register('timeban', new TimeBanCommand($this));
          $this->getLogger()->info(TextFormat::GREEN . 'Enabled.');
      }
      
      public function onDisable()
      {
          $this->getLogger()->info(TextFormat::RED . 'Disabled.');
      }
  }
