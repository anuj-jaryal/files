<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Block {
	
	private $renderInPlace = false ;
	private $blockName =null;
	private $blocks = array();

	public function begin($name,$renderInPlace = false){
		$this->blockName = $name;
		$this->renderInPlace = $renderInPlace;
		ob_start();
		ob_implicit_flush(true);
	}
	public function end(){
		$block = ob_get_clean();
		if($this->renderInPlace){
			echo $block;
			if($this->existsBlock()){
				echo $this->blocks[$this->blockName];
			}
		}else{
			$this->blocks[$this->blockName]= $block;
		}
	}
	public function existsBlock(){
		return isset($this->blocks[$this->blockName]);
	}
}