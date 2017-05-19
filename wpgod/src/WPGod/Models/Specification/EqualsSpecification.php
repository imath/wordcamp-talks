<?php

namespace WPGodWordcamptalks\Models\Specification;

use WPGodWordcamptalks\Models\Specification\AbstractSpecification;

class EqualsSpecification extends AbstractSpecification
{
	public function __construct($string){
		$this->string = $string;
	}

    public function isSatisfiedBy($item){	
        return $this->string == $item;
    }
}