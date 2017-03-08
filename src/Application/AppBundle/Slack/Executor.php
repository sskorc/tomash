<?php

namespace Application\AppBundle\Slack;

use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\NoCommandMatchesException;
use Slack\ApiClient;
use Slack\Channel;
use Slack\Message\MessageBuilder;
use Slack\User;

class Executor
{
    /** @var ApiClient */
    protected $client;

    /** @var \Application\AppBundle\Slack\Matcher */
    protected $matcher;

    public function __construct(ApiClient $client, Matcher $matcher)
    {
        $this->client = $client;
        $this->matcher = $matcher;
    }

    public function run(string $message, User $user, Channel $channel)
    {
        try {
            $command = $this->matcher->matchCommand($message);
        } catch (NoCommandMatchesException $e) {
            return;
        }

        $input = new CommandInput();
        $output = new CommandOutput();

        $input->setUsername($user->getUsername());

        $command->execute($input, $output);

        $messageBuilder = new MessageBuilder($this->client);
        $messageBuilder->setChannel($channel);
        $messageBuilder->setUser($user);

        $text = '<@' . $user->getId() . '> ' . $output->getText();

        if ($output->hasAttachment()) {
            $messageBuilder->addAttachment($output->getAttachment());
        }

        $messageBuilder->setText($text);
        $message = $messageBuilder->create();
        $this->client->postMessage($message);
    }
}
