<?php

namespace CD\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Traduction_Target
 *
 * @ORM\Table(name="traduction_target")
 * @ORM\Entity(repositoryClass="CD\PlatformBundle\Repository\Traduction_TargetRepository")
 */
class Traduction_Target
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
     * @var CD\PlatformBundle\Entity\Lang
     *
     * @ORM\ManyToOne(targetEntity="CD\PlatformBundle\Entity\Lang")
     * @ORM\JoinColumn(name="lang_code", referencedColumnName="code")
     */
    private $lang;

    /**
     * @var CD\PlatformBundle\Entity\Traduction_Source
     *
     * @ORM\ManyToOne(targetEntity="CD\PlatformBundle\Entity\Traduction_Source")
     * @ORM\JoinColumn(name="traduction_source_id", referencedColumnName="id")
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="Target", type="string", length=255)
     */
    private $target;


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
     * Set lang
     *
     * @param \CD\PlatformBundle\Entity\Lang $lang
     *
     * @return Traduction_Target
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
     * Set source
     *
     * @param \CD\PlatformBundle\Entity\Traduction_Source $source
     *
     * @return Traduction_Target
     */
    public function setSource(\CD\PlatformBundle\Entity\Traduction_Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \CD\PlatformBundle\Entity\Traduction_Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set target
     *
     * @param string $target
     *
     * @return Traduction_Source
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }
}
