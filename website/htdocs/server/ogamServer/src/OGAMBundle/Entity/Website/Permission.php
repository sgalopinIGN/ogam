<?php
namespace OGAMBundle\Entity\Website;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 *
 * @ORM\Table(name="website.permission")
 * @ORM\Entity(repositoryClass="OGAMBundle\Repository\Website\PermissionRepository")
 */
class Permission {

	/**
	 *
	 * @var string @ORM\Id
	 *      @ORM\Column(name="permission_code", type="string", length=36, unique=true)
	 */
	private $code;

	/**
	 *
	 * @var string @ORM\Column(name="permission_label", type="string", length=255, nullable=true)
	 */
	private $label;

	/**
	 * Set code
	 *
	 * @param string $code
	 *
	 * @return Permission
	 */
	public function setCode($code) {
		$this->code = $code;

		return $this;
	}

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Set label
	 *
	 * @param string $label
	 *
	 * @return Permission
	 */
	public function setLabel($label) {
		$this->label = $label;

		return $this;
	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
}

