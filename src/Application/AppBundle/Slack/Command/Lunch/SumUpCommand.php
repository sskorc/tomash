<?php

namespace Application\AppBundle\Slack\Command\Lunch;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\SumUpOrder;
use Infrastructure\File\OrderStorage;
use Slack\Message\Attachment;

class SumUpCommand extends AbstractCommand implements SlackCommand, SumUpOrder\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/podsumuj (.+)/');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $restaurant = $this->getPart(1);

        $command = new SumUpOrder\Command($restaurant);

        $useCase = new SumUpOrder(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function successfullySummedUpOrder(Order $order, array $items)
    {
        $this->output->setText('Lista zamówień w #'. $order->getRestaurant()->getName());

        $lines = [];
        foreach ($items as $item) {
            $lines[] = sprintf(
                '%d. %s x%d',
                $item['item']->getPosition(),
                $item['item']->getName(),
                $item['qty']
            );
        }

        $attachment = new Attachment('Zamówienia', implode($lines, "\n"));
        $this->output->setAttachment($attachment);
    }

    public function sumUpOrderFailed(\Exception $e)
    {
        $this->output->setText('nie udało się wykonać polecenia');
    }
}
