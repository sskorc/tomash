<?php

namespace Application\AppBundle\Slack;

use Application\AppBundle\Slack\Command\NoCommandMatchesException;
use Application\AppBundle\Slack\Command\SlackCommand;

class Matcher
{
    /** @var SlackCommand[] */
    private $commands;

    public function registerCommands(array $commands)
    {
        $this->commands = $commands;
    }

    public function matchCommand($message) : SlackCommand
    {
        foreach ($this->commands as $command)
        {
            $command->configure();

            if($command->matches($message))
                return $command;
        }

        throw new NoCommandMatchesException();
    }
}
