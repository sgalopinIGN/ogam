<?php
namespace OGAMBundle\Entity\Website;

use Doctrine\ORM\Mapping as ORM;
use OGAMBundle\Entity\Website\Role as Role;
use OGAMBundle\Entity\Website\Provider as Provider;

/**
 * User.
 *
 * @ORM\Table(name="users", schema="website")
 * @ORM\Entity(repositoryClass="OGAMBundle\Repository\Website\UserRepository")
 */
class User {

	/**
	 *
	 * @var string
	 * @ORM\Column(name="user_login", type="string", length=50, nullable=false, unique=true)
	 * @ORM\Id
	 */
	private $login;

	/**
	 *
	 * @var string
	 * @ORM\Column(name="user_name", type="string", length=50, nullable=true)
	 */
	private $username;

	/**
	 *
	 * @var string
	 * @ORM\Column(name="user_password", type="string", length=50, nullable=true)
	 */
	private $password;

	/**
	 *
	 * @var string
	 * @ORM\ManyToOne(targetEntity="Provider")
	 * @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
	 */
	private $provider;


	/**
	 *
	 * @var bool
	 * @ORM\Column(name="active", type="boolean", nullable=true)
	 */
	private $active;

	/**
	 *
	 * @var string
	 * @ORM\Column(name="email", type="string", length=255, nullable=true)
	 */
	private $email;

	/**
	 * @ORM\ManyToMany(targetEntity="Role")
	 * @ORM\JoinTable(name="role_to_user",
	 * joinColumns={@ORM\JoinColumn(name="user_login", referencedColumnName="user_login")},
	 * inverseJoinColumns={@ORM\JoinColumn(name="role_code", referencedColumnName="role_code")}
	 * )
	 */
	private $roles = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->roles = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Set login
	 *
	 * @param string $login
	 *
	 * @return User
	 */
	public function setLogin($login) {
		$this->login = $login;

		return $this;
	}

	/**
	 * Get login
	 *
	 * @return string
	 */
	public function getLogin() {
		return $this->login;
	}

	/**
	 * Set username
	 *
	 * @param string $username
	 *
	 * @return User
	 */
	public function setUsername($username) {
		$this->username = $username;

		return $this;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 *
	 * @return User
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Set providerId
	 *
	 * @param integer $providerId
	 *
	 * @return User
	 */
	public function setProviderId($providerId) {
		$this->providerId = $providerId;

		return $this;
	}

	/**
	 * Get providerId
	 *
	 * @return int
	 */
	public function getProviderId() {
		return $this->providerId;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return User
	 */
	public function setActive($active) {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return bool
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 *
	 * @return User
	 */
	public function setEmail($email) {
		$this->email = $email;

		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Indicate if the user is allowed for a permission.
	 *
	 * @param String $permissionName
	 *        	The permission
	 * @return Boolean
	 */
	function isAllowed($permissionName) {
		// The user is allowed if one of its role is.
		foreach ($this->roles as $role) {
			if ($role->isAllowed($permissionName)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Indicate if the user is allowed for a schema.
	 *
	 * @param String $schemaName
	 *        	The schema
	 * @return Boolean
	 */
	function isSchemaAllowed($schemaName) {
		// The user is allowed if one of its role is.
		foreach ($this->roles as $role) {
			if (in_array($schemaName, $role->schemasList)) {
				return true;
			}
		}
		return false;
	}

    /**
     * Add role
     *
     * @param \OGAMBundle\Entity\Website\Role $role
     *
     * @return User
     */
    public function addRole(\OGAMBundle\Entity\Website\Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \OGAMBundle\Entity\Website\Role $role
     */
    public function removeRole(\OGAMBundle\Entity\Website\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set provider
     *
     * @param \OGAMBundle\Entity\Website\Provider $provider
     *
     * @return User
     */
    public function setProvider(\OGAMBundle\Entity\Website\Provider $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \OGAMBundle\Entity\Website\Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
