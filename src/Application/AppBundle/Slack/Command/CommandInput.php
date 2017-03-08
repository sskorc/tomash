<?php

namespace Application\AppBundle\Slack\Command;

class CommandInput
{
    protected $username;

    public function getUsername() : string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }
}
