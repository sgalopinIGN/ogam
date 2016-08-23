<?php

namespace OGAMBundle\Repository\Metadata;

/**
 * SchemaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SchemaRepository extends \Doctrine\ORM\EntityRepository
{

	public function findAll()
	{
		return $this->getEntityManager()
		->createQuery(
			'SELECT s FROM OGAMBundle\Entity\Metadata\Schema s INDEX BY s.code ORDER BY s.code ASC'
			)
			->getResult();
	}
}
