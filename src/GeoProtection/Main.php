<?php

  namespace GeoProtection;

  use pocketmine\plugin\PluginBase;
  use pocketmine\event\Listener;
  use pocketmine\event\player\PlayerPreLoginEvent;
  use pocketmine\utils\TextFormat as TF;
  use pocketmine\utils\Config;

  class Main extends PluginBase implements Listener
  {

    public function dataPath()
    {

      return $this->getDataFolder();

    }

    public function configGet($file, $key)
    {

      $file = file_get_contents($file);

      if(preg_match("#$key: ([^\r\n]+)#i", $file, $matches))
      {

        return str_replace($key . ": ", "", $matches);

      }

    }

    public function getUserCity($ip)
    {

      $geo = json_decode(file_get_contents("http://ipinfo.io/" . $ip));

      $user_city = $geo->city;

      return $user_city;

    }

    public function onEnable()
    {

      $this->getServer()->getPluginManager()->registerEvents($this, $this);

      @mkdir($this->dataPath(), 0777, true);

      $this->cfg = new Config($this->dataPath() . "users.yml", Config::YAML, array("player_names" => array()));

      touch($this->dataPath() . "user_city.txt");

      file_put_contents($this->dataPath() . "user_city.txt", "Format: player: city\n");

      $this->getServer()->getLogger()->info("GeoProtection files created.");

    }

    public function onPreLogin(PlayerPreLoginEvent $event)
    {

      $players = $this->cfg->get("player_names");

      $player = $event->getPlayer();

      $player_name = $player->getName();

      $player_ip = $player->getAddress();

      if(in_array($player_name, $players))
      {

        $file = file_get_contents($this->dataPath() . "user_city.txt");

        $player_city = getUserCity($player_ip);

        if($this->configGet($this->dataPath() . "user_city.txt", $player_name) !== $player_city)
        {

          $player->kick("Invalid City.", false);

        }

      }

    }

  }

?>
