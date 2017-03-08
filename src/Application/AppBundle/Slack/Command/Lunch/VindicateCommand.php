<?php

namespace Application\AppBundle\Slack\Command\Lunch;

use Application\AppBundle\Slack\Command\AbstractCommand;
use Application\AppBundle\Slack\Command\CommandInput;
use Application\AppBundle\Slack\Command\CommandOutput;
use Application\AppBundle\Slack\Command\SlackCommand;
use Domain\Model\Lunch\Order;
use Domain\UseCase\Lunch\CollectBill;
use Infrastructure\File\OrderStorage;
use Slack\Message\Attachment;

class VindicateCommand extends AbstractCommand implements SlackCommand, CollectBill\Responder
{
    /** @var CommandOutput */
    private $output;

    public function configure()
    {
        $this->setRegex('/windykuj (.+)/iu');
    }

    public function execute(CommandInput $input, CommandOutput $output)
    {
        $this->output = $output;

        $restaurant = $this->getPart(1);

        $command = new CollectBill\Command($restaurant, $input->getUsername());

        $useCase = new CollectBill(new OrderStorage());
        $useCase->execute($command, $this);
    }

    public function billCollectedSuccessfully(Order $order, float $totalSum)
    {
        $this->output->setText('Lista zamówień w #'. $order->getRestaurant()->getName());

        $lines = [];
        foreach ($order->getParticipants() as $participant) {
            $sum = 0;

            foreach ($participant->getItems() as $item) {
                $sum += $item->getPrice()->toFloat();
            }

            $lines[] = sprintf(
                '<@%s> %s zł',
                $participant->getName(),
                number_format($sum, 2, ',', ' ')
            );
        }

        $attachment = new Attachment('Zamówienia', implode($lines, "\n"));
        $this->output->setAttachment($attachment);
    }

    public function collectingBillFailed(\Exception $e)
    {
        $this->output->setText('nie udało się wykonać polecenia');
    }
}
