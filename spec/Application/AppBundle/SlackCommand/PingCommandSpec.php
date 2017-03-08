<?php

namespace spec\Application\AppBundle\SlackCommand;

use Application\AppBundle\SlackCommand\PingCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slack\ApiClient;
use Slack\Channel;
use Slack\User;

class PingCommandSpec extends ObjectBehavior
{
    public function let(ApiClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_should_return_pong_message(ApiClient $client, User $user, Channel $channel)
    {
        $user->getUsername()->willReturn('Janush');

        $this->execute('ping', $user, $channel);

        $client->send('@Janush pong!', $channel)->shouldBeCalled();
    }
}
