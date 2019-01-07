<?php

namespace CD\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Traduction_Source
 *
 * @ORM\Table(name="traduction_source",
 *    uniqueConstraints={
 *        @UniqueConstraint(name="source_unique",
 *            columns={"project_id", "Source"})
 *    })
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
     * @ORM\ManyToOne(targetEntity="CD\PlatformBundle\Entity\Project", inversedBy="sources")
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var CD\PlatformBundle\Entity\Traduction_Target
     *
     * @ORM\OneToMany(targetEntity="CD\PlatformBundle\Entity\Traduction_Target", mappedBy="source")
     */
    private $targets;


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
     * Add target
     *
     * @param \CD\PlatformBundle\Entity\Traduction_Target $target
     *
     * @return Traduction_Source
     */
    public function addTarget(\CD\PlatformBundle\Entity\Traduction_Target $target)
    {
        $this->targets[] = $target;

        return $this;
    }

    /**
     * Remove target
     *
     * @param \CD\PlatformBundle\Entity\Traduction_Target $target
     */
    public function removeTarget(\CD\PlatformBundle\Entity\Traduction_Target $target)
    {
        $this->targets->removeElement($target);
    }

    /**
     * Get targets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTargets()
    {
        return $this->targets;
    }
}
