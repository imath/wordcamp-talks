<?php
namespace WPGodWordcamptalks\Models\Specification;

use WPGodWordcamptalks\Models\Specification\SpecificationInterface;
use WPGodWordcamptalks\Models\Specification\AndX;
use WPGodWordcamptalks\Models\Specification\OrX;
use WPGodWordcamptalks\Models\Specification\NotX;

/**
 * @version 1.0.0
 * @since 1.0.0
 * 
 * @author Thomas DENEULIN <contact@wp-god.com> 
 */
abstract class AbstractSpecification implements SpecificationInterface
{
    /**
     *
     * @param $item
     *
     * @return bool
     */
    abstract public function isSatisfiedBy($item);

    /**
     *
     * @param SpecificationInterface $spec
     *
     * @return SpecificationInterface
     */
    public function andX(SpecificationInterface $spec)
    {
        return new AndX($this, $spec);
    }

    /**
     *
     * @param SpecificationInterface $spec
     *
     * @return SpecificationInterface
     */
    public function orX(SpecificationInterface $spec)
    {
        return new OrX($this, $spec);
    }

    /**
     *
     * @return SpecificationInterface
     */
    public function notX()
    {
        return new NotX($this);
    }
}