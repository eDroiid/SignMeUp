<?php
namespace eDroid\SignMeUp\commands;

use eDroid\SignMeUp\main as SignMeUp;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use pocketmine\utils\TextFormat as COLOR;

class SignMeUpCommand extends Command implements PluginIdentifiableCommand {
	private $plugin;
	public function __construct(SignMeUp $plugin){
		parent::__construct("signmeup", "Edit signs in your server.", "/signmeup < help | edit >", ["smu"]);
		$this->setPermission("signmeup.command");
		$this->plugin = $plugin;
	}

	public function generateCustomCommandData(Player $player) {
	    $commandData = parent::generateCustomCommandData($player);
	    $commandData["overloads"]["default"]["input"]["parameters"] = [
			[
				"name" => "Option",
				"type" => "stringenum",
				"enum_values" => [
					"help",
					"edit"
				]
			]
		];
	    return $commandData;
	}

	public function execute(CommandSender $sender, $label, array $args){
		if($sender instanceof Player){
			switch($args[0]){
				case 'help':
					$sender->sendMessage($this->getPlugin()->getPrefix() . COLOR::RED . "Usage: /signmeup < help | edit >");
					break;
				case 'edit':
					$this->getPlugin()->addToChoosing($sender->getName());
					$sender->sendMessage($this->getPlugin()->getPrefix() . COLOR::GRAY . "Please " . COLOR::GREEN . "tap" . COLOR::GRAY . " on the sign you wish to edit.");
					break;

				default:
					$sender->sendMessage($this->getPlugin()->getPrefix() . COLOR::RED . "Usage: /signmeup < help | edit >");
					break;
			}
		}else{
			$sender->sendMessage($this->getPlugin()->getPrefix() . COLOR::RED . "/signmeup only works in-game!");
		}
	}

	public function getPlugin(){
		return $this->plugin;
	}
}