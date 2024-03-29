<?php

namespace CD\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="CD\PlatformBundle\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var CD\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="CD\UserBundle\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var CD\PlatformBundle\Entity\Lang
     *
     * @ORM\ManyToOne(targetEntity="CD\PlatformBundle\Entity\Lang")
     * @ORM\JoinColumn(name="lang_code", referencedColumnName="code")
     */
    private $lang;

    /**
     * @var boolean
     *
     * @ORM\Column(name="frozen", type="boolean")
     */
    private $frozen = false;

    /**
     * @var CD\PlatformBundle\Entity\Traduction_Source
     *
     * @ORM\OneToMany(targetEntity="CD\PlatformBundle\Entity\Traduction_Source", mappedBy="project")
     */
    private $sources;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Project
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set lang
     *
     * @param \CD\PlatformBundle\Entity\Lang $lang
     *
     * @return Project
     */
    public function setLang(\CD\PlatformBundle\Entity\Lang $lang = null)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return \CD\PlatformBundle\Entity\Lang
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set user
     *
     * @param \CD\UserBundle\Entity\User $user
     *
     * @return Project
     */
    public function setUser(\CD\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \CD\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set frozen
     *
     * @param boolean $frozen
     *
     * @return Project
     */
    public function setFrozen($frozen)
    {
        $this->frozen = $frozen;

        return $this;
    }

    /**
     * Get frozen
     *
     * @return boolean
     */
    public function getFrozen()
    {
        return $this->frozen;
    }

    /**
     * Add source
     *
     * @param \CD\PlatformBundle\Entity\Traduction_Source $source
     *
     * @return Project
     */
    public function addSource(\CD\PlatformBundle\Entity\Traduction_Source $source)
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * Remove source
     *
     * @param \CD\PlatformBundle\Entity\Traduction_Source $source
     */
    public function removeSource(\CD\PlatformBundle\Entity\Traduction_Source $source)
    {
        $this->sources->removeElement($source);
    }

    /**
     * Get sources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSources()
    {
        return $this->sources;
    }
}
