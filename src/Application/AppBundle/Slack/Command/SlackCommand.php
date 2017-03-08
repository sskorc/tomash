<?php

namespace Application\AppBundle\Slack\Command;

interface SlackCommand
{
    public function setRegex(string $regex);

    public function matches(string $message) : bool;

    public function getPart(int $index) : string;

    public function configure();

    public function execute(CommandInput $input, CommandOutput $output);
}
