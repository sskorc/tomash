<?php

namespace Application\AppBundle\Slack\Command;

class PingCommand extends AbstractCommand implements SlackCommand
{
    public function configure()
    {
        $this->setRegex('/ping/');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $output->setText('pong!');
    }
}
