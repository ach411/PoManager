<?php

namespace Ach\PoManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ach\PoManagerBundle\Entity\NotificationCategoryRepository")
 */
class NotificationCategory
{
    /**
     * @var integer
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
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="sendTo", type="string", length=255, nullable=true)
     */
    private $sendTo;

    /**
     * @var string
     *
     * @ORM\Column(name="ccTo", type="string", length=255, nullable=true)
     */
    private $ccTo;

    /**
     * @var string
     *
     * @ORM\Column(name="bccTo", type="string", length=255, nullable=true)
     */
    private $bccTo;

    /**
     * @var string
     *
     * @ORM\Column(name="attachedFile", type="string", length=255, nullable=true)
     */
    private $attachedFile;

    /**
     * @var string
     *
     * @ORM\Column(name="listMessage", type="text", nullable=true)
     */
    private $listMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="listClass", type="string", length=255, nullable=true)
     */
    private $listClass;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return NotificationCategory
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
     * Set subject
     *
     * @param string $subject
     * @return NotificationCategory
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return NotificationCategory
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set sendTo
     *
     * @param string $sendTo
     * @return NotificationCategory
     */
    public function setSendTo($sendTo)
    {
        $this->sendTo = $sendTo;
    
        return $this;
    }

    /**
     * Get sendTo
     *
     * @return string 
     */
    public function getSendTo()
    {
        return $this->sendTo;
    }

    /**
     * Set ccTo
     *
     * @param string $ccTo
     * @return NotificationCategory
     */
    public function setCcTo($ccTo)
    {
        $this->ccTo = $ccTo;
    
        return $this;
    }

    /**
     * Get ccTo
     *
     * @return string 
     */
    public function getCcTo()
    {
        return $this->ccTo;
    }

    /**
     * Set bccTo
     *
     * @param string $bccTo
     * @return NotificationCategory
     */
    public function setBccTo($bccTo)
    {
        $this->bccTo = $bccTo;
    
        return $this;
    }

    /**
     * Get bccTo
     *
     * @return string 
     */
    public function getBccTo()
    {
        return $this->bccTo;
    }

    /**
     * Set attachedFile
     *
     * @param string $attachedFile
     * @return NotificationCategory
     */
    public function setAttachedFile($attachedFile)
    {
        $this->attachedFile = $attachedFile;
    
        return $this;
    }

    /**
     * Get attachedFile
     *
     * @return string 
     */
    public function getAttachedFile()
    {
        return $this->attachedFile;
    }

    /**
     * Set listMessage
     *
     * @param string $listMessage
     * @return NotificationCategory
     */
    public function setListMessage($listMessage)
    {
        $this->listMessage = $listMessage;
    
        return $this;
    }

    /**
     * Get listMessage
     *
     * @return string 
     */
    public function getListMessage()
    {
        return $this->listMessage;
    }

    /**
     * Set listClass
     *
     * @param string $listClass
     * @return NotificationCategory
     */
    public function setListClass($listClass)
    {
        $this->listClass = $listClass;
    
        return $this;
    }

    /**
     * Get listClass
     *
     * @return string 
     */
    public function getListClass()
    {
        return $this->listClass;
    }

    public function getTextFields()
    {
        $all['name'] = $this->name;
        $all['message'] = $this->message;
        $all['subject'] = $this->subject;
        $all['attachedFile'] = $this->attachedFile;

        return $all;
    }

	public function getEmailFields()
    {
        $all['sendTo'] = $this->sendTo;
        $all['ccTo'] = $this->ccTo;
        $all['bccTo'] = $this->bccTo;

        return $all;
    }
}
