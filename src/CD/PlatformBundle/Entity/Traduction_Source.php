<?php

namespace CD\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Traduction_Source
 *
 * @ORM\Table(name="traduction_source")
 * @ORM\Entity(repositoryClass="CD\PlatformBundle\Repository\Traduction_SourceRepository")
 */
class Traduction_Source
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
     * @var CD\PlatformBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="CD\PlatformBundle\Entity\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="Source", type="string", length=45)
     */
    private $source;


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
     * Set project
     *
     * @param \CD\PlatformBundle\Entity\Project $project
     *
     * @return Traduction_Source
     */
    public function setProject(\CD\PlatformBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \CD\PlatformBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Traduction_Source
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
