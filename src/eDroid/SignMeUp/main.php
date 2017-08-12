<?php
namespace eDroid\SignMeUp;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\block\SignPost;
use pocketmine\block\WallSign;

use pocketmine\math\Vector3;

use pocketmine\utils\TextFormat as COLOR;

class main extends PluginBase implements Listener {
	private $usersChoosingSign = array();
	private $usersEditingSign = array();
	private $prefix = COLOR::BOLD . COLOR::GOLD . "[" . COLOR::YELLOW . "SignMeUp" . COLOR::GOLD . "] " . COLOR::RESET;
	private $replaceTextMessage = COLOR::GRAY . "Please type in chat what you'd like to replace the text on " . COLOR::GREEN . "line {number}" . COLOR::GRAY . " with.";

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register("signmeup", new commands\SignMeUpCommand($this));
	}

	public function onTouch(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$block  = $event->getBlock();
		if($block instanceof SignPost || $block instanceof WallSign && $this->isInChoosing($player->getName())){
			$this->removeFromChoosing($player->getName());
			$this->addToEditing($player->getName(), [0, [$block->getX(), $block->getY(), $block->getZ()]]);
			$player->sendMessage($this->getPrefix() . COLOR::GRAY . "You have " . COLOR::GREEN . "selected" . COLOR::GRAY . " a sign.");
			$player->sendMessage($this->getPrefix() . COLOR::GRAY . "If you would like to leave the line " . COLOR::GREEN . "unchanged" . COLOR::GRAY . " type a period ('.'), to leave the line " . COLOR::GREEN . "blank" . COLOR::GRAY . " type a underscore ('_').");
			$player->sendMessage($this->getPrefix() . str_replace("{number}", "1", $this->replaceTextMessage));
		}
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$player  = $event->getPlayer();
		$message = $event->getMessage();
		if($this->isInEditing($player->getName())){
			$signData = $this->getFromEditing($player->getName());
			$sign     = $player->getLevel()->getTile(new Vector3($signData[1][0], $signData[1][1], $signData[1][2]));
			$signText = $sign->getText();

			$newText = $message == "." ? $signText[0] : ($message == "_" ? "" : $message);
			switch($signData[0]){
				case 0:
					$sign->setText($newText, $signText[1], $signText[2], $signText[3]);
					$player->sendMessage($this->getPrefix() . str_replace("{number}", "2", $this->replaceTextMessage));
					$this->addToEditing($player->getName(), [1, $signData[1]]);
					break;
				case 1:
					$sign->setText($signText[0], $newText, $signText[2], $signText[3]);
					$player->sendMessage($this->getPrefix() . str_replace("{number}", "3", $this->replaceTextMessage));
					$this->addToEditing($player->getName(), [2, $signData[1]]);
					break;
				case 2:
					$sign->setText($signText[0], $signText[1], $newText, $signText[3]);
					$player->sendMessage($this->getPrefix() . str_replace("{number}", "4", $this->replaceTextMessage));
					$this->addToEditing($player->getName(), [3, $signData[1]]);
					break;
				case 3:
					$sign->setText($signText[0], $signText[1], $signText[2], $newText);
					$player->sendMessage($this->getPrefix() . COLOR::GRAY . "You have " . COLOR::GREEN . "successfully" . COLOR::GRAY . " changed the text on the sign.");
					$this->removeFromEditing($player->getName());
					break;
			}
			$event->setCancelled(true);
		}
	}

	public function addToChoosing($user){
		$this->usersChoosingSign[] = strtolower($user);
	}
	public function isInChoosing($user){
		return isset($this->usersChoosingSign[strtolower($user)]);
	}
	public function removeFromChoosing($user){
		unset($this->usersChoosingSign[strtolower($user)]);
	}
	public function addToEditing($user, $data){
		$this->usersEditingSign[strtolower($user)] = $data;
	}
	public function isInEditing($user){
		return isset($this->usersEditingSign[strtolower($user)]);
	}
	public function getFromEditing($user){
		return $this->usersEditingSign[strtolower($user)];
	}
	public function removeFromEditing($user){
		unset($this->usersEditingSign[strtolower($user)]);
	}
	public function getPrefix(){
		return $this->prefix;
	}
}