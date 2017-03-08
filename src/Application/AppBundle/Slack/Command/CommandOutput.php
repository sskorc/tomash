<?php

namespace Application\AppBundle\Slack\Command;

class CommandOutput
{
    protected $text;

    protected $attachment;

    public function getText() : string
    {
        return $this->text ?? '';
    }

    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    public function hasAttachment() : bool
    {
        return !empty($this->getAttachment());
    }
}
