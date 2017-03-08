<?php

namespace Application\AppBundle\Slack\Command\Lunch;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\UseCase\Lunch\CloseOrder;
use Infrastructure\File\OrderStorage;

class CloseCommand extends AbstractCommand implements SlackCommand, CloseOrder\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/zamknij (\w+)/');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $restaurant = $this->getPart(1);

        $command = new CloseOrder\Command($restaurant);

        $useCase = new CloseOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function orderClosedSuccessfully(string $restaurantName)
    {
        $this->output->setText('Smacznego! :curry:');
    }

    public function closingOrderFailed(\Exception $e)
    {
        $this->output->setText('Nie udało się zamknąć :(');
    }
}
