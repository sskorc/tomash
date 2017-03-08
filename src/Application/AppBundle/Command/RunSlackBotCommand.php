<?php

namespace Application\AppBundle\Command;

use Application\AppBundle\Slack\Command\Absence\DelegationCommand;
use Application\AppBundle\Slack\Command\Absence\HolidayCommand;
use Application\AppBundle\Slack\Command\Absence\SickLeaveCommand;
use Application\AppBundle\Slack\Command\Absence\WorkFromHomeCommand;
use Application\AppBundle\Slack\Command\Lunch\IAmEatingCommand;
use Application\AppBundle\Slack\Command\Lunch\WeAreEatingCommand;
use Application\AppBundle\Slack\Command\Lunch\SumUpCommand;
use Application\AppBundle\Slack\Command\Lunch\VindicateCommand;
use Application\AppBundle\Slack\Command\Lunch\CloseCommand;
use Application\AppBundle\Slack\Command\PingCommand;
use Application\AppBundle\Slack\Command\Absence\WhereIsCommand;
use Application\AppBundle\Slack\Command\Absence\WhoIsAbsentCommand;
use Application\AppBundle\Slack\Executor;
use Application\AppBundle\Slack\Matcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunSlackBotCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('slackbot:run')
            ->setDescription('Run Slackbot')
            ->addArgument('token', InputArgument::REQUIRED, 'Token')
            ->addArgument('botid', InputArgument::REQUIRED, 'Bot ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = \React\EventLoop\Factory::create();

        $client = new \Slack\RealTimeClient($loop);
        $client->setToken($input->getArgument('token'));

        $botId = $input->getArgument('botid');

        $client->on('message', function ($data) use ($client, $botId) {
            if (!$data['text']) {
                return;
            }

            $message = $this->parseMessage($data['text'], $botId);

            if (!$message) {
                return;
            }

            $commands = [
                new PingCommand(),
                new WeAreEatingCommand(),
                new IAmEatingCommand(),
                new SumUpCommand(),
                new VindicateCommand(),
                new CloseCommand(),
                new DelegationCommand(),
                new HolidayCommand(),
                new SickLeaveCommand(),
                new WorkFromHomeCommand(),
                new WhereIsCommand(),
                new WhoIsAbsentCommand(),
            ];

            $username = $client->getUserById($data['user'])->then(function ($user) {
                return $user;
            });

            $channel = $client->getChannelById($data['channel'])->then(function (\Slack\Channel $channel) use ($client) {
                return $channel;
            });

            $matcher = new Matcher();
            $matcher->registerCommands($commands);
            $executor = new Executor($client, $matcher);

            \React\Promise\all([$username, $channel])->then(function ($data) use ($executor, $message) {
                $executor->run($message, $data[0], $data[1]);
            });
        });

        $client->connect()->then(function () {
            echo "Connected!\n";
        });

        $loop->run();
    }

    protected function parseMessage(string $message, string $botId)
    {
        $matches = preg_match('/\<\@' . $botId . '\>:?(?<text>.+)/', $message, $results);

        if (!$matches) {
            return false;
        }

        return trim($results['text']);
    }
}
