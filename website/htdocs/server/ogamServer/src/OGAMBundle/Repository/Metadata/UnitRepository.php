<?php
namespace OGAMBundle\Repository\Metadata;

use OGAMBundle\Entity\Metadata\Unit;
use OGAMBundle\OGAMBundle;
use OGAMBundle\Entity\Metadata\Mode;
use OGAMBundle\Entity\Metadata\Dynamode;
use OGAMBundle\Entity\Metadata\ModeTree;
use OGAMBundle\Entity\Metadata\ModeTaxref;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;

/**
 * UnitRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UnitRepository extends \Doctrine\ORM\EntityRepository
{

    public function getModes(Unit $unit)
    {
        /*
         * Must handle these cases :
         * CODE MODE
         * CODE DYNAMIC
         * CODE TREE
         * CODE TAXREF
         * ARRAY MODE
         * ARRAY DYNAMIC
         * ARRAY TREE
         * ARRAY TAXREF
         */
        if ($unit->getType() === "CODE" or $unit->getType() === "ARRAY") {
            switch ($unit->getSubtype()) {
                case "MODE":
                    return $this->_em->getRepository(Mode::class)->findBy(array(
                        'unit' => $unit->getUnit()
                    ));
                case "DYNAMIC":
                    return $this->_em->getRepository(Dynamode::class)->getModes($unit);
                case "TREE": // TODO: Manage the TREE case
                case "TAXREF": // TODO: Manage the TAXREF case
                default:
                    return null;
            }
        }
    }
    /**
     * @param Unit $unit
     * @param mixed $value
     * @return array
     */
    public function getModesLabel(Unit $unit, $value){
        $res = $this->getModes($unit);
        if ($res === null){
            return null;
        }
        
        
        if (is_array($value)) {
            $criteria = Criteria::create()->where(Criteria::expr()->in("code", $value));
        }else {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("code", $value));
        }
        if (false ===($res  instanceof  Selectable)){
            $res= new ArrayCollection($res);
        }
        
        $result = $res->matching($criteria);
        $array_res = $result->map(function ($element){
            return ['code'=>$element->getCode(), 'label'=>$element->getLabel()]; 
        });
       
        return  array_column($array_res->toArray(), 'label','code');
    }
}