<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Book
 *
 * @ORM\Table(name="book")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BookRepository")
 * @JMS\ExclusionPolicy("none")
 */
class Book
{
    const API_SITE_URL = 'https://siteurl.com/upload/';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\ReadOnly()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Groups({"edit"})
     * @JMS\Type("string")
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     * @Assert\NotBlank(message="Author is required.")
     * @JMS\Groups({"edit"})
     * @JMS\Type("string")
     */
    private $author;

    /**
     * @var int
     *
     * @ORM\Column(name="image", type="string", nullable=true)
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" })
     * @JMS\Accessor(getter="getImagePath")
     * @JMS\Expose(if="object.getDownloadable() === true")
     * @JMS\ReadOnly()
     */

    private $image;


    /**
     * @var int
     *
     * @ORM\Column(name="file", type="string", nullable=true)
     * @Assert\Image(
     *     mimeTypes={"application/pdf"},
     *     maxSize = "5M"
     * )
     * @JMS\Accessor(getter="getFilePath")
     * @JMS\Expose(if="object.getDownloadable() === true")
     * @JMS\ReadOnly()
     */
    private $file;

    /**
     * @var bool
     *
     * @ORM\Column(name="date", type="date")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"edit"})
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_downloadable", type="boolean")
     * @Assert\Type(type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Groups({"edit"})
     */
    private $downloadable;

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return int
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param int $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param int $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return bool
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @param bool $isDownloadable
     */
    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;

        return $this;
    }

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
     * @return Book
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
     * @return string
     */
    public function getImagePath()
    {
        return $this->image ? self::API_SITE_URL . $this->image : null;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file ? self::API_SITE_URL . $this->file : null;
    }

    public function getWebPath()
    {
        return $this->image ? (string) $this->image : null;
    }
}
