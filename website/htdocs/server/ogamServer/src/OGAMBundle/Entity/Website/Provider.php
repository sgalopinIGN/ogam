<?php
namespace OGAMBundle\Entity\Website;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provider
 *
 * @ORM\Table(name="website.provider")
 * @ORM\Entity(repositoryClass="OGAMBundle\Repository\Website\ProviderRepository")
 */
class Provider {

	/**
	 *
	 * @var int @ORM\Column(name="id", type="integer")
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="label", type="string", nullable=true)
	 */
	private $label;

	/**
	 *
	 * @var string @ORM\Column(name="definition", type="string", nullable=true)
	 */
	private $definition;

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set label
	 *
	 * @param string $label
	 *
	 * @return Provider
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

	/**
	 * Set definition
	 *
	 * @param string $definition
	 *
	 * @return Provider
	 */
	public function setDefinition($definition) {
		$this->definition = $definition;

		return $this;
	}

	/**
	 * Get definition
	 *
	 * @return string
	 */
	public function getDefinition() {
		return $this->definition;
	}
}
