<?php
namespace OGAMBundle\Repository\Metadata;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use OGAMBundle\Entity\Website\User;

/**
 * DatasetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DatasetRepository extends \Doctrine\ORM\EntityRepository {

	public function getDatasetsForUpload() {
		/*
		 * $req = "SELECT DISTINCT dataset_id as id, COALESCE(t.label, d.label) as label, COALESCE(t.definition, d.definition) as definition, is_default ";
		 * $req .= " FROM dataset d";
		 * $req .= " LEFT JOIN translation t ON (lang = '" . $this->lang . "' AND table_format = 'DATASET' AND row_pk = dataset_id) ";
		 * $req .= " INNER JOIN dataset_files using (dataset_id) ";
		 *
		 * // Check the role restrictions
		 * $userSession = new Zend_Session_Namespace('user');
		 * $params = array();
		 * if ($userSession != null && $userSession->user != null) {
		 * $req .= ' WHERE (dataset_id NOT IN (SELECT dataset_id FROM dataset_role_restriction JOIN role_to_user USING (role_code) WHERE user_login = ?))';
		 * $params[] = $userSession->login;
		 * }
		 *
		 * $req .= " ORDER BY dataset_id";
		 */
		$dql = "SELECT d " . "FROM OGAMBundle\Entity\Metadata\Dataset d " . "WHERE SIZE(d.files) > 0 ORDER BY d.id";
		
		$query = $this->getEntityManager()->createQuery($dql);
		
		return $query->getResult();
	}

	/**
	 * Get the available datasets for display.
	 *
	 * @return Array[Application_Object_Metadata_Dataset]
	 */
	public function getDatasetsForDisplay($locale, User $user) {
		$rsm = new ResultSetMappingBuilder($this->_em);
		$rsm->addRootEntityFromClassMetadata('OGAMBundle\Entity\Metadata\Dataset', 'd');
		
		$sql = "SELECT DISTINCT dataset_id, COALESCE(t.label, d.label) as label, COALESCE(t.definition, d.definition) as definition, is_default";
		$sql .= " FROM dataset d";
		$sql .= " LEFT JOIN translation t ON (lang = ? AND table_format = 'DATASET' AND row_pk = dataset_id)";
		$sql .= " INNER JOIN dataset_fields using (dataset_id)";
		$sql .= " WHERE (dataset_id NOT IN (SELECT dataset_id FROM dataset_role_restriction JOIN role_to_user USING (role_code) WHERE user_login = ?))";
		$sql .= " ORDER BY dataset_id";
		
		$query = $this->_em->createNativeQuery($sql, $rsm);
		$query->setParameter(1, $locale);
		$query->setParameter(2, $user->getLogin());
		
		return $query->getResult();
	}
}
